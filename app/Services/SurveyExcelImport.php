<?php

namespace App\Services;

use App\Support\PpmCatalog;
use App\Support\SurveyOptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Reads an uploaded respondent spreadsheet, checks every row and finds
 * duplicates (inside the file and against the database) before anything
 * is saved.
 *
 * Satu baris = satu penilaian, dengan alur yang sama seperti survei:
 * desa -> rincian program -> perusahaan (jawaban responden / "Tidak tahu").
 * Perusahaan yang dinilai ditentukan oleh PpmCatalog::attribute.
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

    /** Label "Tidak tahu" di Excel. */
    public const LABEL_TIDAK_TAHU = 'Tidak tahu';

    /** @param array $catalog PpmCatalog::forRole(): desa, perusahaan, pilar, program, rincian yang boleh diisi */
    public function __construct(private array $catalog)
    {
    }

    /** Column headers of the downloadable template, in order. */
    public static function templateHeaders(): array
    {
        $headers = ['Tanggal Survei', 'Desa', 'Rincian Program', 'Perusahaan (jawaban responden)', 'Kode Responden',
            'Jenis Responden', 'Nama', 'No HP', 'Alamat (RT/Dusun)', 'Jenis Kelamin', 'Usia', 'Pendidikan', 'Pekerjaan'];
        foreach (SurveyOptions::UNSUR as $no => [$name]) {
            $headers[] = "U{$no} {$name}";
        }
        $headers[] = 'Saran / Masukan';

        return $headers;
    }

    /** Teks pilihan rincian di Excel: "P5.1.2 - Nama rincian". */
    public static function rincianLabel(string $id, string $nama): string
    {
        return mb_substr($id . ' - ' . $nama, 0, 250);
    }

    /** Teks pilihan perusahaan di Excel: "TA - Tabang Alas". */
    public static function perusahaanLabel(string $kode, string $nama): string
    {
        return $kode . ' - ' . $nama;
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

        $missing = array_diff(['desa', 'rincian', 'perusahaan', 'nama', 'u1'], array_keys($columns));
        if ($missing) {
            throw new \RuntimeException('Format kolom tidak sesuai. Gunakan format Excel terbaru dari tombol "Download Format" (wajib ada kolom Desa, Rincian Program, Perusahaan, Nama, dan U1–U9).');
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

        $this->assignRespondents($rows);
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
            ->whereNotNull('rincian_id')
            ->select('nama', 'nohp', 'rincian_id', 'created_at')
            ->orderBy('id')
            ->chunk(1000, function ($chunk) use (&$keys) {
                foreach ($chunk as $db) {
                    $keys[self::key($db->nama, $db->nohp, Carbon::parse($db->created_at)->format('Y-m-d'), $db->rincian_id)] = true;
                }
            });

        return $keys;
    }

    /** Identitas satu penilaian: orang yang sama, hari yang sama, rincian program yang sama. */
    public static function key(?string $nama, ?string $nohp, string $tanggal, ?string $rincianId): string
    {
        return implode('|', [self::norm($nama), self::digits($nohp), $tanggal, (string) $rincianId]);
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
            'desa' => ['desa', 'namadesa'],
            'rincian' => ['rincianprogram', 'rincian', 'kegiatan', 'rincianprogramppm'],
            'perusahaan' => ['perusahaanjawabanresponden', 'perusahaan', 'perusahaanpelaksana', 'pelaksana'],
            'kode' => ['koderesponden', 'kode', 'noresponden', 'idresponden'],
            'jenis_responden' => ['jenisresponden', 'kategoriresponden'],
            'nama' => ['nama', 'namaresponden', 'namalengkap'],
            'nohp' => ['nohp', 'hp', 'nomorhp', 'notelepon', 'telepon', 'nowa', 'notelp'],
            'alamat' => ['alamatrtdusun', 'alamat', 'rtdusun', 'alamatdesa'],
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
            // "U1 Kesesuaian Program", "Unsur 1", "1. Kesesuaian Program"
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

        $tanggal = $this->parseDate($raw['tanggal'] ?? null);
        if ($tanggal === false) {
            $errors[] = 'Tanggal survei tidak terbaca (gunakan format hari/bulan/tahun, mis. 15/10/2026)';
        } elseif ($tanggal === null) {
            $errors[] = 'Tanggal survei wajib diisi';
        }
        if ($tanggal && $tanggal > date('Y-m-d')) {
            $errors[] = 'Tanggal survei melebihi hari ini';
        }
        $data['tanggal'] = $tanggal ?: null;

        // Desa -> rincian -> perusahaan (sama dengan alur survei)
        $data['desa_id'] = $this->matchDesa($raw['desa'] ?? null);
        if (! $data['desa_id']) {
            $errors[] = $this->blank($raw['desa'] ?? null) ? 'Desa wajib diisi' : 'Desa "' . $raw['desa'] . '" tidak dikenal';
        }
        $data['rincian_id'] = $this->matchRincian($raw['rincian'] ?? null);
        if (! $data['rincian_id']) {
            $errors[] = $this->blank($raw['rincian'] ?? null) ? 'Rincian program wajib diisi' : 'Rincian program "' . $raw['rincian'] . '" tidak dikenal';
        }
        $data['perusahaan_jawaban'] = $this->matchPerusahaan($raw['perusahaan'] ?? null);
        if (! $data['perusahaan_jawaban']) {
            $errors[] = $this->blank($raw['perusahaan'] ?? null) ? 'Perusahaan wajib diisi (pilih "Tidak tahu" bila responden tidak tahu)' : 'Perusahaan "' . $raw['perusahaan'] . '" tidak dikenal';
        }

        $atribusi = null;
        if ($data['desa_id'] && $data['rincian_id'] && $data['perusahaan_jawaban']) {
            $pelaksana = PpmCatalog::pelaksana($data['rincian_id'], $data['desa_id']);
            if (! $pelaksana) {
                $errors[] = 'Rincian ini tidak berjalan di desa ' . $this->catalog['desa'][$data['desa_id']] . ' (lihat sheet "Daftar Program per Desa")';
            } else {
                $atribusi = PpmCatalog::attribute($data['rincian_id'], $data['desa_id'], $data['perusahaan_jawaban']);
                if (! $atribusi) {
                    $errors[] = 'Perusahaan ' . $data['perusahaan_jawaban'] . ' bukan pelaksana rincian ini di desa tersebut (pelaksana: ' . implode(', ', $pelaksana) . ', atau "Tidak tahu")';
                }
            }
        }
        $data += $atribusi ?? ['perusahaan_dinilai' => null, 'sumber_atribusi' => null, 'perusahaan_opsi' => null];

        $data['kode'] = $this->text($raw['kode'] ?? null, 50);
        $data['nama'] = $this->text($raw['nama'] ?? null, 150);
        if ($data['nama'] === null) {
            $errors[] = 'Nama wajib diisi';
        }
        $data['nohp'] = $this->phone($raw['nohp'] ?? null);
        $data['alamat'] = $this->text($raw['alamat'] ?? null, 300);
        $data['saran'] = $this->text($raw['saran'] ?? null, 600);

        $choice = [
            'jenis_responden' => ['Jenis responden', $this->matchOption($raw['jenis_responden'] ?? null, SurveyOptions::JENIS_RESPONDEN, [
                'penerima' => 'Penerima Manfaat', 'masyarakat' => 'Masyarakat Umum', 'umum' => 'Masyarakat Umum',
                'aparat' => 'Aparat Desa', 'perangkatdesa' => 'Aparat Desa', 'aparatdesa' => 'Aparat Desa',
            ])],
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

        $rincianNama = $data['rincian_id'] ? ($this->catalog['rincian'][$data['rincian_id']]['nama'] ?? '') : (string) ($raw['rincian'] ?? '');

        return [
            'line' => $line,
            'data' => $data,
            'display' => [
                'nama' => $data['nama'] ?? (string) ($raw['nama'] ?? ''),
                'nohp' => $data['nohp'],
                'tanggal' => $data['tanggal'] ? Carbon::parse($data['tanggal'])->format('d/m/Y') : (string) ($raw['tanggal'] ?? ''),
                'desa' => $data['desa_id'] ? $this->catalog['desa'][$data['desa_id']] : (string) ($raw['desa'] ?? ''),
                'rincian' => $rincianNama,
                'perusahaan' => $data['perusahaan_dinilai']
                    ? $data['perusahaan_dinilai'] . ($data['perusahaan_jawaban'] === PpmCatalog::TIDAK_TAHU ? ' (jawaban: tidak tahu)' : '')
                    : (string) ($raw['perusahaan'] ?? ''),
            ],
            'status' => $errors ? self::INVALID : self::NEW,
            'notes' => array_merge($errors, $notes),
            'key' => $errors ? null : self::key($data['nama'], $data['nohp'], $data['tanggal'], $data['rincian_id']),
        ];
    }

    /**
     * Baris milik responden yang sama berbagi responden_uuid: memakai Kode Responden
     * bila diisi, selain itu nama + no. HP + desa + tanggal yang sama.
     */
    private function assignRespondents(array &$rows): void
    {
        foreach ($rows as &$row) {
            $d = $row['data'];
            $basis = $d['kode']
                ? 'kode|' . self::norm($d['kode']) . '|' . $d['tanggal']
                : 'orang|' . self::norm($d['nama']) . '|' . self::digits($d['nohp']) . '|' . $d['desa_id'] . '|' . $d['tanggal'];
            $row['data']['responden_uuid'] = 'xls-r-' . substr(sha1($basis), 0, 30);
        }
        unset($row);
    }

    private function markDuplicates(array &$rows): void
    {
        $valid = array_filter($rows, fn ($r) => $r['status'] !== self::INVALID);
        if (! $valid) {
            return;
        }

        // Database rows in the same years and rincian, for exact and "similar" matches.
        $years = array_values(array_unique(array_map(fn ($r) => (int) substr($r['data']['tanggal'], 0, 4), $valid)));
        $rincian = array_values(array_unique(array_map(fn ($r) => $r['data']['rincian_id'], $valid)));
        $dbExact = [];
        $dbPerson = [];
        DB::table('survey_responses')
            ->whereIn('tahun', $years)
            ->whereIn('rincian_id', $rincian)
            ->select('id', 'nama', 'nohp', 'rincian_id', 'created_at')
            ->orderBy('id')
            ->chunk(1000, function ($chunk) use (&$dbExact, &$dbPerson) {
                foreach ($chunk as $db) {
                    $date = Carbon::parse($db->created_at)->format('Y-m-d');
                    $dbExact[self::key($db->nama, $db->nohp, $date, $db->rincian_id)] = $db->id;
                    if (self::digits($db->nohp) !== '') {
                        $dbPerson[self::personKey($db->nama, $db->nohp, $db->rincian_id)][] = $date;
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
            $person = self::digits($d['nohp']) !== '' ? self::personKey($d['nama'], $d['nohp'], $d['rincian_id']) : null;

            if (isset($seenKey[$row['key']])) {
                $row['status'] = self::DUP_FILE;
                array_unshift($row['notes'], 'Sama dengan baris ' . $seenKey[$row['key']] . ' di file ini');
            } elseif (isset($dbExact[$row['key']])) {
                $row['status'] = self::DUP_DB;
                array_unshift($row['notes'], 'Sudah tersimpan di sistem (ID ' . $dbExact[$row['key']] . '): nama, no. HP, tanggal, dan rincian program sama');
            } elseif ($person && isset($dbPerson[$person])) {
                $row['status'] = self::SIMILAR;
                $dates = implode(', ', array_map(fn ($x) => Carbon::parse($x)->format('d/m/Y'), array_slice(array_unique($dbPerson[$person]), 0, 3)));
                array_unshift($row['notes'], "Nama & no. HP ini sudah menilai rincian program yang sama di sistem pada {$dates}");
            } elseif ($person && isset($seenPerson[$person])) {
                $row['status'] = self::SIMILAR;
                array_unshift($row['notes'], 'Nama & no. HP sama dengan baris ' . $seenPerson[$person] . ' untuk rincian yang sama (tanggal berbeda)');
            }

            $seenKey[$row['key']] ??= $row['line'];
            if ($person) {
                $seenPerson[$person] ??= $row['line'];
            }
        }
        unset($row);
    }

    private static function personKey(?string $nama, ?string $nohp, ?string $rincianId): string
    {
        return self::norm($nama) . '|' . self::digits($nohp) . '|' . $rincianId;
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

    private function matchDesa($value): ?int
    {
        $n = self::norm(preg_replace('/^desa\s+/i', '', trim((string) $value)));
        if ($n === '') {
            return null;
        }
        foreach ($this->catalog['desa'] as $id => $nama) {
            if ($n === self::norm($nama)) {
                return (int) $id;
            }
        }

        return null;
    }

    /** "P5.1.2 - Nama", "P5.1.2", atau nama rincian persis. */
    private function matchRincian($value): ?string
    {
        $s = trim((string) $value);
        if ($s === '') {
            return null;
        }
        if (preg_match('/^(P\d+(?:\.\d+){2})\b/i', $s, $m)) {
            $id = strtoupper($m[1]);

            return isset($this->catalog['rincian'][$id]) ? $id : null;
        }
        $n = self::norm($s);
        foreach ($this->catalog['rincian'] as $id => $r) {
            if ($n === self::norm($r['nama'])) {
                return $id;
            }
        }

        return null;
    }

    /** Kode perusahaan ("TA", "TA - Tabang Alas", "Tabang Alas") atau TIDAK_TAHU. */
    private function matchPerusahaan($value): ?string
    {
        $n = self::norm($value);
        if ($n === '') {
            return null;
        }
        if (in_array($n, ['tidaktahu', 'tidaktau', 'tdktahu', 'gatau', 'nggaktahu'], true)) {
            return PpmCatalog::TIDAK_TAHU;
        }
        foreach ($this->catalog['perusahaan'] as $kode => $nama) {
            if ($kode === PpmCatalog::GRUP) {
                continue; // BAYAN bukan pilihan jawaban responden
            }
            if (in_array($n, [self::norm($kode), self::norm($nama), self::norm(self::perusahaanLabel($kode, $nama)), self::norm('PT ' . $nama)], true)) {
                return $kode;
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
