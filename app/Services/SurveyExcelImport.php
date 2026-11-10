<?php

namespace App\Services;

use App\Support\BagianOptions;
use App\Support\SurveyOptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Reads an uploaded respondent spreadsheet, checks every row and finds
 * duplicates (inside the file and against the database) before anything
 * is saved.
 */
class SurveyExcelImport
{
    public const MAX_ROWS = 3000;

    // Row status, in order of priority.
    public const INVALID = 'invalid';
    public const DUP_FILE = 'dup_file';
    public const DUP_DB = 'dup_db';
    public const SIMILAR = 'similar';
    public const NEW = 'new';

    /** @param array<string,string> $allowedBagian code => name */
    public function __construct(private array $allowedBagian)
    {
    }

    /** Column headers of the downloadable template, in order. */
    public static function templateHeaders(): array
    {
        $headers = ['Tanggal Survei', 'Pilar', 'Nama', 'No HP', 'Alamat / Desa', 'Jenis Kelamin', 'Usia', 'Pendidikan', 'Pekerjaan'];
        foreach (SurveyOptions::UNSUR as $no => [$name]) {
            $headers[] = "U{$no} {$name}";
        }
        $headers[] = 'Saran / Masukan';

        return $headers;
    }

    /**
     * @return array{rows: array<int,array>, summary: array<string,int>, unknownHeaders: array<int,string>}
     */
    public function analyze(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName('Data') ?? $spreadsheet->getActiveSheet();
        // Raw values (formatData=false) so dates arrive as Excel serial numbers.
        $table = $sheet->toArray(null, true, false, false);

        $headerRow = array_shift($table) ?? [];
        [$columns, $unknown] = $this->mapHeaders($headerRow);

        $missing = array_diff(['bagian', 'nama', 'u1'], array_keys($columns));
        if ($missing) {
            throw new \RuntimeException('Format kolom tidak dikenali. Pastikan baris pertama berisi judul kolom dari format Excel yang disediakan (minimal kolom Pilar, Nama, dan U1–U9).');
        }

        $rows = [];
        foreach ($table as $index => $cells) {
            if ($this->isEmptyRow($cells)) {
                continue;
            }
            if (count($rows) >= self::MAX_ROWS) {
                throw new \RuntimeException('File berisi lebih dari ' . self::MAX_ROWS . ' baris data. Silakan bagi menjadi beberapa file.');
            }
            $raw = [];
            foreach ($columns as $field => $col) {
                $raw[$field] = $cells[$col] ?? null;
            }
            $rows[] = $this->parseRow($index + 2, $raw);
        }

        $this->markDuplicates($rows);

        $summary = ['total' => count($rows)];
        foreach ([self::NEW, self::SIMILAR, self::DUP_FILE, self::DUP_DB, self::INVALID] as $status) {
            $summary[$status] = count(array_filter($rows, fn ($r) => $r['status'] === $status));
        }

        return ['rows' => $rows, 'summary' => $summary, 'unknownHeaders' => $unknown];
    }

    /** Keys of database records that match the given rows exactly. */
    public static function existingKeys(array $rows): array
    {
        $dates = array_values(array_unique(array_map(fn ($r) => $r['data']['tanggal'], $rows)));
        if (! $dates) {
            return [];
        }

        $keys = [];
        DB::table('survey_responses')
            ->whereIn(DB::raw('DATE(created_at)'), $dates)
            ->select('nama', 'nohp', 'jenisPelayanan', 'created_at')
            ->orderBy('id')
            ->chunk(1000, function ($chunk) use (&$keys) {
                foreach ($chunk as $db) {
                    $keys[self::key($db->nama, $db->nohp, Carbon::parse($db->created_at)->format('Y-m-d'), $db->jenisPelayanan)] = true;
                }
            });

        return $keys;
    }

    /** Identity of one survey: same person, same day, same pilar. */
    public static function key(?string $nama, ?string $nohp, string $tanggal, ?string $bagian): string
    {
        return implode('|', [self::norm($nama), self::digits($nohp), $tanggal, (string) $bagian]);
    }

    /** Stored in client_uuid so the database itself refuses a second copy. */
    public static function uuidFor(string $key): string
    {
        return 'xls-' . substr(sha1($key), 0, 32);
    }

    // ------------------------------------------------------------------

    private function mapHeaders(array $headerRow): array
    {
        $aliases = [
            'tanggal' => ['tanggalsurvei', 'tanggal', 'tgl', 'tglsurvei'],
            'tahun' => ['tahun'],
            'bagian' => ['pilar', 'pilarprogram', 'program', 'bagian', 'jenispelayanan'],
            'nama' => ['nama', 'namaresponden', 'namalengkap'],
            'nohp' => ['nohp', 'hp', 'nomorhp', 'notelepon', 'telepon', 'nowa', 'notelp'],
            'alamat' => ['alamat', 'alamatdesa', 'desa'],
            'jenkel' => ['jeniskelamin', 'jenkel', 'jk'],
            'usia' => ['usia', 'umur'],
            'pendidikan' => ['pendidikan'],
            'pekerjaan' => ['pekerjaan'],
            'saran' => ['saran', 'saranmasukan', 'masukan'],
        ];
        $unsurNames = [];
        foreach (SurveyOptions::UNSUR as $no => [$name]) {
            $unsurNames[self::norm($name)] = "u{$no}";
        }

        $columns = [];
        $unknown = [];
        foreach ($headerRow as $col => $title) {
            $n = self::norm($title);
            if ($n === '') {
                continue;
            }
            $field = null;
            foreach ($aliases as $f => $names) {
                if (in_array($n, $names, true)) {
                    $field = $f;
                    break;
                }
            }
            // "U1 Kesesuaian Program", "Unsur 1", "1. Kesesuaian Program" (and the old "Unusr 8" typo)
            if (! $field && preg_match('/^(?:u|unsur|unusr)?([1-9])(?![0-9])/', $n, $m)) {
                $field = 'u' . $m[1];
            }
            if (! $field && isset($unsurNames[$n])) {
                $field = $unsurNames[$n];
            }
            if ($field && ! isset($columns[$field])) {
                $columns[$field] = $col;
            } else {
                $unknown[] = (string) $title;
            }
        }

        return [$columns, $unknown];
    }

    private function parseRow(int $line, array $raw): array
    {
        $errors = [];
        $notes = [];
        $data = [];

        // Tanggal (old template only had "Tahun")
        $tanggal = $this->parseDate($raw['tanggal'] ?? null);
        if ($tanggal === false) {
            $errors[] = 'Tanggal survei tidak terbaca (gunakan format hari/bulan/tahun, mis. 15/10/2026)';
        } elseif ($tanggal === null) {
            $tahun = (int) ($raw['tahun'] ?? 0);
            if ($tahun >= 2000 && $tahun <= (int) date('Y')) {
                $tanggal = $tahun === (int) date('Y') ? date('Y-m-d') : "{$tahun}-12-31";
                $notes[] = 'Tanggal kosong, memakai ' . Carbon::parse($tanggal)->format('d/m/Y');
            } else {
                $errors[] = 'Tanggal survei wajib diisi';
            }
        }
        if ($tanggal && $tanggal > date('Y-m-d')) {
            $errors[] = 'Tanggal survei melebihi hari ini';
        }
        $data['tanggal'] = $tanggal ?: null;

        $data['bagian'] = $this->matchBagian($raw['bagian'] ?? null);
        if (! $data['bagian']) {
            $errors[] = $this->blank($raw['bagian'] ?? null) ? 'Pilar wajib diisi' : 'Pilar "' . $raw['bagian'] . '" tidak dikenal';
        }

        $data['nama'] = $this->text($raw['nama'] ?? null, 150);
        if ($data['nama'] === null) {
            $errors[] = 'Nama wajib diisi';
        }
        $data['nohp'] = $this->phone($raw['nohp'] ?? null);
        $data['alamat'] = $this->text($raw['alamat'] ?? null, 300);
        $data['saran'] = $this->text($raw['saran'] ?? null, 600);

        $choice = [
            'jenkel' => ['Jenis kelamin', $this->matchJenkel($raw['jenkel'] ?? null)],
            'usia' => ['Usia', $this->matchUsia($raw['usia'] ?? null)],
            'pendidikan' => ['Pendidikan', $this->matchOption($raw['pendidikan'] ?? null, SurveyOptions::PENDIDIKAN, [
                'sma' => 'SMA / SMK', 'smk' => 'SMA / SMK', 'slta' => 'SMA / SMK', 'sltp' => 'SMP', 'd1' => 'D-I / D-III', 'd3' => 'D-I / D-III',
                'diii' => 'D-I / D-III', 'diploma' => 'D-I / D-III', 's1' => 'S1 / Setara', 'sarjana' => 'S1 / Setara', 'd4' => 'S1 / Setara',
                's2' => 'S2 / S3', 's3' => 'S2 / S3',
            ])],
            'pekerjaan' => ['Pekerjaan', $this->matchOption($raw['pekerjaan'] ?? null, SurveyOptions::PEKERJAAN, [
                'pns' => 'ASN', 'tni' => 'TNI / POLRI', 'polri' => 'TNI / POLRI', 'karyawanswasta' => 'Swasta', 'karyawan' => 'Swasta',
                'wiraswasta' => 'Pengusaha', 'wirausaha' => 'Pengusaha', 'mahasiswa' => 'Pelajar', 'siswa' => 'Pelajar', 'pelajarmahasiswa' => 'Pelajar',
            ])],
        ];
        foreach ($choice as $field => [$label, $value]) {
            $data[$field] = $value;
            if ($value === null) {
                $errors[] = $this->blank($raw[$field] ?? null) ? "{$label} wajib diisi" : "{$label} \"" . $raw[$field] . '" tidak dikenal';
            }
        }

        $badUnsur = [];
        foreach (SurveyOptions::UNSUR as $no => [, $answers]) {
            $data["u{$no}"] = $this->matchScore($raw["u{$no}"] ?? null, $answers);
            if ($data["u{$no}"] === null) {
                $badUnsur[] = $no;
            }
        }
        if ($badUnsur) {
            $errors[] = 'Nilai U' . implode(', U', $badUnsur) . ' kosong atau bukan 1–4';
        }

        return [
            'line' => $line,
            'data' => $data,
            'display' => [
                'nama' => $data['nama'] ?? (string) ($raw['nama'] ?? ''),
                'nohp' => $data['nohp'],
                'tanggal' => $data['tanggal'] ? Carbon::parse($data['tanggal'])->format('d/m/Y') : (string) ($raw['tanggal'] ?? ''),
                'pilar' => $data['bagian'] ? $this->allowedBagian[$data['bagian']] : (string) ($raw['bagian'] ?? ''),
            ],
            'status' => $errors ? self::INVALID : self::NEW,
            'notes' => array_merge($errors, $notes),
            'key' => $errors ? null : self::key($data['nama'], $data['nohp'], $data['tanggal'], $data['bagian']),
        ];
    }

    private function markDuplicates(array &$rows): void
    {
        $valid = array_filter($rows, fn ($r) => $r['status'] !== self::INVALID);
        if (! $valid) {
            return;
        }

        // Database rows in the same years and pilar, for exact and "similar" matches.
        $years = array_values(array_unique(array_map(fn ($r) => (int) substr($r['data']['tanggal'], 0, 4), $valid)));
        $pilars = array_values(array_unique(array_map(fn ($r) => $r['data']['bagian'], $valid)));
        $dbExact = [];
        $dbPerson = [];
        DB::table('survey_responses')
            ->whereIn(DB::raw('YEAR(created_at)'), $years)
            ->whereIn('jenisPelayanan', $pilars)
            ->select('id', 'nama', 'nohp', 'jenisPelayanan', 'created_at')
            ->orderBy('id')
            ->chunk(1000, function ($chunk) use (&$dbExact, &$dbPerson) {
                foreach ($chunk as $db) {
                    $date = Carbon::parse($db->created_at)->format('Y-m-d');
                    $dbExact[self::key($db->nama, $db->nohp, $date, $db->jenisPelayanan)] = $db->id;
                    if (self::digits($db->nohp) !== '') {
                        $dbPerson[self::personKey($db->nama, $db->nohp, $db->jenisPelayanan)][] = $date;
                    }
                }
            });

        $seenKey = [];
        $seenPerson = [];
        foreach ($rows as &$row) {
            if ($row['status'] === self::INVALID) {
                continue;
            }
            $d = $row['data'];
            $person = self::digits($d['nohp']) !== '' ? self::personKey($d['nama'], $d['nohp'], $d['bagian']) : null;

            if (isset($seenKey[$row['key']])) {
                $row['status'] = self::DUP_FILE;
                array_unshift($row['notes'], 'Sama dengan baris ' . $seenKey[$row['key']] . ' di file ini');
            } elseif (isset($dbExact[$row['key']])) {
                $row['status'] = self::DUP_DB;
                array_unshift($row['notes'], 'Sudah tersimpan di sistem (ID ' . $dbExact[$row['key']] . '): nama, no. HP, tanggal, dan pilar sama');
            } elseif ($person && isset($dbPerson[$person])) {
                $row['status'] = self::SIMILAR;
                $dates = implode(', ', array_map(fn ($x) => Carbon::parse($x)->format('d/m/Y'), array_slice(array_unique($dbPerson[$person]), 0, 3)));
                array_unshift($row['notes'], "Nama & no. HP ini sudah mengisi pilar yang sama di sistem pada {$dates}");
            } elseif ($person && isset($seenPerson[$person])) {
                $row['status'] = self::SIMILAR;
                array_unshift($row['notes'], 'Nama & no. HP sama dengan baris ' . $seenPerson[$person] . ' (tanggal berbeda)');
            }

            $seenKey[$row['key']] ??= $row['line'];
            if ($person) {
                $seenPerson[$person] ??= $row['line'];
            }
        }
        unset($row);
    }

    private static function personKey(?string $nama, ?string $nohp, ?string $bagian): string
    {
        return self::norm($nama) . '|' . self::digits($nohp) . '|' . $bagian;
    }

    public static function norm($value): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim((string) $value)));
    }

    private static function digits($value): string
    {
        $d = preg_replace('/\D/', '', (string) $value);
        if (str_starts_with($d, '62')) {
            $d = '0' . substr($d, 2);
        }

        return $d;
    }

    private function blank($value): bool
    {
        return trim((string) $value) === '';
    }

    private function text($value, int $max): ?string
    {
        $v = trim(preg_replace('/\s+/', ' ', (string) $value));

        return $v === '' ? null : mb_substr($v, 0, $max);
    }

    private function phone($value): ?string
    {
        if ($this->blank($value)) {
            return null;
        }
        $d = self::digits(is_float($value) ? number_format($value, 0, '', '') : $value);
        // Excel drops the leading zero when the cell is a number: 81234567890 -> 081234567890
        if (str_starts_with($d, '8') && strlen($d) >= 9) {
            $d = '0' . $d;
        }

        return $d === '' ? null : substr($d, 0, 16);
    }

    /** @return string|null|false  Y-m-d, null when empty, false when unreadable */
    private function parseDate($value)
    {
        if ($this->blank($value)) {
            return null;
        }
        if (is_numeric($value) && $value > 20000 && $value < 80000) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }
        $s = trim((string) $value);
        foreach (['d/m/Y', 'd-m-Y', 'd.m.Y', 'Y-m-d', 'Y/m/d', 'd/m/y', 'j/n/Y', 'Y-m-d H:i:s'] as $format) {
            $dt = \DateTime::createFromFormat('!' . $format, $s);
            if ($dt && $dt->format($format) === $s) {
                return $dt->format('Y-m-d');
            }
        }

        return false;
    }

    private function matchBagian($value): ?string
    {
        $n = self::norm($value);
        if ($n === '') {
            return null;
        }
        foreach ($this->allowedBagian as $code => $name) {
            if ($n === self::norm($code) || $n === self::norm($name) || $n === self::norm(BagianOptions::normalizeCode($code))) {
                return $code;
            }
        }

        return null;
    }

    private function matchJenkel($value): ?string
    {
        $n = self::norm($value);

        return match (true) {
            in_array($n, ['lakilaki', 'laki', 'l', 'pria', 'lk'], true) => 'Laki - Laki',
            in_array($n, ['perempuan', 'p', 'wanita', 'pr'], true) => 'Perempuan',
            default => null,
        };
    }

    private function matchUsia($value): ?int
    {
        if ($this->blank($value)) {
            return null;
        }
        if (is_numeric($value)) {
            // An age in years; the stored group codes (29/30/41/51) map onto themselves.
            $age = (int) $value;
            if ($age < 10 || $age > 120) {
                return null;
            }

            return $age < 30 ? 29 : ($age <= 40 ? 30 : ($age <= 50 ? 41 : 51));
        }
        foreach (SurveyOptions::USIA as $code => $label) {
            if (self::norm($value) === self::norm($label)) {
                return $code;
            }
        }

        return null;
    }

    private function matchOption($value, array $options, array $aliases): ?string
    {
        $n = self::norm($value);
        if ($n === '') {
            return null;
        }
        foreach ($options as $opt) {
            if ($n === self::norm($opt)) {
                return $opt;
            }
        }

        return $aliases[$n] ?? null;
    }

    private function matchScore($value, array $answers): ?int
    {
        if (is_numeric($value) && (float) $value == (int) $value && $value >= 1 && $value <= 4) {
            return (int) $value;
        }
        $n = self::norm($value);
        foreach ($answers as $i => $answer) {
            if ($n !== '' && $n === self::norm($answer)) {
                return $i + 1;
            }
        }

        return null;
    }

    private function isEmptyRow(array $cells): bool
    {
        foreach ($cells as $cell) {
            if (! $this->blank($cell)) {
                return false;
            }
        }

        return true;
    }
}
