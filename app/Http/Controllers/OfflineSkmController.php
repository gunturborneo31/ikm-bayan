<?php

namespace App\Http\Controllers;

use App\Models\NilaiUnsur;
use App\Services\SurveyExcelImport;
use App\Support\PpmCatalog;
use App\Support\SurveyOptions;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Import respondent data from Excel: download template -> upload ->
 * review the duplicate/validity analysis -> import the chosen rows.
 *
 * Kolom Excel mengikuti alur survei & form Input Responden:
 * desa -> rincian program -> perusahaan (jawaban) -> identitas -> U1–U9.
 */
class OfflineSkmController extends Controller
{
    private const SESSION_KEY = 'excel_import';
    private const TEMPLATE_ROWS = 1000;

    /** Kolom sheet Data (huruf => judul diambil dari SurveyExcelImport::templateHeaders). */
    private const COL = [
        'tanggal' => 'A', 'desa' => 'B', 'rincian' => 'C', 'perusahaan' => 'D', 'kode' => 'E', 'jenis_responden' => 'F',
        'nama' => 'G', 'nohp' => 'H', 'alamat' => 'I', 'jenkel' => 'J', 'usia' => 'K', 'pendidikan' => 'L', 'pekerjaan' => 'M',
        'u1' => 'N', 'saran' => 'W',
    ];

    public function page(Request $request)
    {
        $analysis = null;
        $token = $request->query('analisis');
        if ($token && ($stored = session(self::SESSION_KEY)) && ($stored['token'] ?? null) === $token) {
            $analysis = $stored;
        }

        return view('admin.offline-skm', ['analysis' => $analysis]);
    }

    public function template()
    {
        $catalog = $this->catalog();
        $spreadsheet = new Spreadsheet();

        // ── Sheet tersembunyi berisi daftar pilihan dropdown ──
        $lists = $spreadsheet->createSheet();
        $lists->setTitle('Pilihan');
        $perusahaan = [];
        foreach ($catalog['perusahaan'] as $kode => $nama) {
            if ($kode !== PpmCatalog::GRUP) {
                $perusahaan[] = SurveyExcelImport::perusahaanLabel($kode, $nama);
            }
        }
        $perusahaan[] = SurveyExcelImport::LABEL_TIDAK_TAHU;
        $fixed = [
            'A' => array_values($catalog['desa']),
            'B' => SurveyOptions::JENKEL,
            'C' => array_values(SurveyOptions::USIA),
            'D' => SurveyOptions::PENDIDIKAN,
            'E' => SurveyOptions::PEKERJAAN,
            'F' => SurveyOptions::JENIS_RESPONDEN,
            'G' => $perusahaan,
        ];
        foreach ($fixed as $col => $values) {
            $this->writeList($lists, $col, $values);
        }
        // Nilai unsur 1-4 disimpan sebagai ANGKA agar dropdown U1–U9 hanya menerima angka
        foreach ([1, 2, 3, 4] as $i => $nilai) {
            $lists->setCellValueExplicit('H' . ($i + 1), $nilai, DataType::TYPE_NUMERIC);
        }

        // Satu kolom per desa: rincian yang berjalan di desa itu (untuk dropdown bertingkat)
        $col = 'I';
        foreach ($catalog['desa'] as $desaId => $desaNama) {
            $labels = [];
            foreach ($catalog['rincian'] as $rid => $r) {
                if (! empty($r['pelaksana'][$desaId])) {
                    $labels[] = SurveyExcelImport::rincianLabel($rid, $r['nama']);
                }
            }
            $labels = $labels ?: ['(belum ada program di desa ini)'];
            $this->writeList($lists, $col, $labels);
            $spreadsheet->addNamedRange(new NamedRange($this->desaRangeName($desaNama), $lists, '$' . $col . '$1:$' . $col . '$' . count($labels)));
            $col++;
        }
        $lists->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        // ── Sheet Data ──
        $sheet = $spreadsheet->getSheet(0);
        $sheet->setTitle('Data');
        $sheet->fromArray(SurveyExcelImport::templateHeaders(), null, 'A1');
        $lastCol = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EA580C']],
            'alignment' => ['wrapText' => true, 'vertical' => 'center'],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(45);
        $sheet->freezePane('C2');

        $widths = ['A' => 13, 'B' => 18, 'C' => 46, 'D' => 26, 'E' => 12, 'F' => 18, 'G' => 24, 'H' => 15, 'I' => 20,
            'J' => 13, 'K' => 15, 'L' => 13, 'M' => 13, 'W' => 40];
        foreach ($widths as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }
        $n = self::TEMPLATE_ROWS + 1;
        $c = self::COL;
        $range = fn (string $field) => "{$c[$field]}2:{$c[$field]}{$n}";

        // ── Format sel per kolom ──
        // Tanggal: format tanggal; isian harus tanggal antara 1/1/2020 dan hari ini
        $sheet->getStyle($range('tanggal'))->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle($range('tanggal'))->getAlignment()->setHorizontal('center');
        $this->validation($sheet, $range('tanggal'), DataValidation::TYPE_DATE, DataValidation::OPERATOR_BETWEEN, 'DATE(2020,1,1)', 'TODAY()',
            'Tanggal survei', 'Ketik tanggal hari/bulan/tahun, mis. 15/03/2026. Tidak boleh melebihi hari ini.',
            'Isi tanggal yang valid (hari/bulan/tahun), paling lambat hari ini.');

        // Kolom teks: disimpan sebagai teks agar tidak diubah Excel (mis. angka 0 di depan no. HP)
        foreach (['desa', 'rincian', 'perusahaan', 'kode', 'jenis_responden', 'nama', 'nohp', 'alamat', 'jenkel', 'pendidikan', 'pekerjaan', 'saran'] as $field) {
            $sheet->getStyle($range($field))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        }
        $sheet->getStyle($range('saran'))->getAlignment()->setWrapText(true);
        $sheet->getStyle($range('rincian'))->getAlignment()->setWrapText(true);

        // Batas panjang teks sama dengan sistem
        $this->validation($sheet, $range('kode'), DataValidation::TYPE_TEXTLENGTH, DataValidation::OPERATOR_LESSTHANOREQUAL, '50', null,
            'Kode responden', 'Opsional. Isi kode yang sama (mis. R001) bila satu responden menilai beberapa program.', 'Kode responden maksimal 50 karakter.');
        $this->validation($sheet, $range('nama'), DataValidation::TYPE_TEXTLENGTH, DataValidation::OPERATOR_BETWEEN, '1', '150',
            'Nama', 'Nama responden (wajib, maksimal 150 karakter).', 'Nama wajib diisi, maksimal 150 karakter.');
        $this->validation($sheet, $range('nohp'), DataValidation::TYPE_CUSTOM, null,
            'AND(LEN(' . $c['nohp'] . '2)<=16,ISNUMBER(--SUBSTITUTE(SUBSTITUTE(' . $c['nohp'] . '2," ",""),"-","")))', null,
            'No HP', 'Opsional. Hanya angka, maksimal 16 digit, mis. 081234567890.', 'No. HP hanya boleh berisi angka, maksimal 16 digit.');
        $this->validation($sheet, $range('alamat'), DataValidation::TYPE_TEXTLENGTH, DataValidation::OPERATOR_LESSTHANOREQUAL, '300', null,
            'Alamat', 'RT / dusun (desa sudah dari kolom Desa). Maksimal 300 karakter.', 'Alamat maksimal 300 karakter.');
        $this->validation($sheet, $range('saran'), DataValidation::TYPE_TEXTLENGTH, DataValidation::OPERATOR_LESSTHANOREQUAL, '600', null,
            'Saran / masukan', 'Opsional. Maksimal 600 karakter.', 'Saran maksimal 600 karakter.');

        // Usia: pilih kelompok usia atau ketik umur (angka), jadi format umum
        $sheet->getStyle($range('usia'))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_GENERAL);

        $ref = fn (string $col, array $values) => 'Pilihan!$' . $col . '$1:$' . $col . '$' . max(1, count($values));
        $this->dropdown($sheet, "{$c['desa']}2:{$c['desa']}{$n}", $ref('A', $fixed['A']), 'Desa', 'Pilih desa responden.');
        // Rincian hanya menampilkan program yang berjalan di desa pada kolom Desa
        $this->dropdown($sheet, "{$c['rincian']}2:{$c['rincian']}{$n}", 'INDIRECT("DESA_"&SUBSTITUTE($' . $c['desa'] . '2," ","_"))',
            'Rincian program', 'Pilih desa dulu. Daftar hanya berisi rincian yang berjalan di desa tersebut.');
        $this->dropdown($sheet, "{$c['perusahaan']}2:{$c['perusahaan']}{$n}", $ref('G', $perusahaan), 'Perusahaan',
            'Perusahaan yang menurut responden melaksanakan program, atau "Tidak tahu". Harus pelaksana di desa itu (lihat sheet Daftar Program per Desa).');
        $this->dropdown($sheet, "{$c['jenis_responden']}2:{$c['jenis_responden']}{$n}", $ref('F', SurveyOptions::JENIS_RESPONDEN), 'Jenis responden', 'Pilih dari daftar.');
        $this->dropdown($sheet, "{$c['jenkel']}2:{$c['jenkel']}{$n}", $ref('B', SurveyOptions::JENKEL), 'Jenis kelamin', 'Pilih dari daftar.');
        $this->dropdown($sheet, "{$c['usia']}2:{$c['usia']}{$n}", $ref('C', $fixed['C']), 'Usia', 'Pilih kelompok usia, atau ketik umur dalam tahun.', false);
        $this->dropdown($sheet, "{$c['pendidikan']}2:{$c['pendidikan']}{$n}", $ref('D', SurveyOptions::PENDIDIKAN), 'Pendidikan', 'Pilih dari daftar.');
        $this->dropdown($sheet, "{$c['pekerjaan']}2:{$c['pekerjaan']}{$n}", $ref('E', SurveyOptions::PEKERJAAN), 'Pekerjaan', 'Pilih dari daftar.');
        $sheet->getComment("{$c['kode']}1")->getText()->createText('Opsional. Isi kode yang sama (mis. R001) bila satu responden menilai beberapa program, agar dihitung sebagai satu orang.');

        // U1–U9: angka bulat, hanya 1/2/3/4 (dropdown dari sel angka, isian lain ditolak)
        $col = $c['u1'];
        foreach (SurveyOptions::UNSUR as $no => [$name, $answers]) {
            $sheet->getColumnDimension($col)->setWidth(12);
            $sheet->getStyle("{$col}2:{$col}{$n}")->getNumberFormat()->setFormatCode('0');
            $sheet->getStyle("{$col}2:{$col}{$n}")->getAlignment()->setHorizontal('center');
            $legend = implode("\n", array_map(fn ($a, $i) => ($i + 1) . ' = ' . $a, $answers, array_keys($answers)));
            $this->dropdown($sheet, "{$col}2:{$col}{$n}", 'Pilihan!$H$1:$H$4', "U{$no} {$name}", $legend, true,
                'Nilai U' . $no . ' hanya boleh angka 1, 2, 3, atau 4.');
            $col++;
        }

        // Kunci baris judul agar nama kolom tidak berubah; sel isian tetap bisa diisi
        $sheet->getStyle("A2:{$lastCol}{$n}")->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
        $protection = $sheet->getProtection();
        $protection->setSheet(true);
        $protection->setFormatColumns(false); // tetap boleh ubah lebar kolom
        $protection->setFormatRows(false);
        $protection->setSort(false);
        $protection->setAutoFilter(false);
        $sheet->setSelectedCell('A2');

        // ── Sheet referensi program per desa ──
        $ref2 = $spreadsheet->createSheet(1);
        $ref2->setTitle('Daftar Program per Desa');
        $rows = [['Desa', 'Pilar', 'Program', 'Rincian Program (salin ke kolom C)', 'Perusahaan Pelaksana', 'Penilaian Dicatat Sebagai']];
        foreach ($catalog['desa'] as $desaId => $desaNama) {
            foreach ($catalog['rincian'] as $rid => $r) {
                $pelaksana = $r['pelaksana'][$desaId] ?? [];
                if (! $pelaksana) {
                    continue;
                }
                $program = $catalog['program'][$r['program']];
                $grup = PpmCatalog::rincian()[$rid]['grup'] ?? false;
                $rows[] = [
                    $desaNama,
                    $catalog['pilar'][$program['pilar']] ?? $program['pilar'],
                    $program['nama'],
                    SurveyExcelImport::rincianLabel($rid, $r['nama']),
                    implode(', ', $pelaksana),
                    $grup ? 'Selalu BAYAN (grup)' : (count($pelaksana) === 1 ? 'Perusahaan pilihan / ' . $pelaksana[0] . ' bila "Tidak tahu"' : 'Perusahaan pilihan / BAYAN bila "Tidak tahu"'),
                ];
            }
        }
        $ref2->fromArray($rows, null, 'A1');
        $ref2->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EA580C']],
        ]);
        foreach (['A' => 18, 'B' => 30, 'C' => 40, 'D' => 60, 'E' => 20, 'F' => 40] as $cc => $w) {
            $ref2->getColumnDimension($cc)->setWidth($w);
        }
        $ref2->setAutoFilter('A1:F' . count($rows));
        $ref2->freezePane('A2');

        // ── Sheet petunjuk ──
        $help = $spreadsheet->createSheet(1);
        $help->setTitle('Petunjuk');
        $lines = [
            ['PETUNJUK PENGISIAN DATA RESPONDEN'],
            [''],
            ['1. Isi data di sheet "Data". Satu baris = satu penilaian (satu responden menilai satu rincian program). Jangan ubah judul kolom.'],
            ['2. Pilih Desa dulu, lalu Rincian Program. Daftar rincian hanya berisi program yang berjalan di desa tersebut.'],
            ['3. Perusahaan: pilih perusahaan yang menurut responden melaksanakan program, atau "Tidak tahu". Lihat sheet "Daftar Program per Desa".'],
            ['4. Bila satu responden menilai beberapa program, isi beberapa baris dengan identitas yang sama dan Kode Responden yang sama (mis. R001).'],
            ['5. Tanggal survei ditulis hari/bulan/tahun, contoh 15/10/2026. Nilai U1–U9 diisi angka 1 sampai 4.'],
            ['6. Wajib diisi: tanggal, desa, rincian program, perusahaan, jenis responden, nama, jenis kelamin, usia, pendidikan, pekerjaan, U1–U9.'],
            ['7. Simpan file, lalu upload di menu Admin > Import Excel. Sistem memeriksa kecocokan program-desa-perusahaan dan data duplikat sebelum menyimpan.'],
            [''],
            ['ATURAN PERUSAHAAN YANG DINILAI'],
            ['• Beasiswa dan 4 program Kesehatan Masyarakat Sekitar Tambang selalu dinilai sebagai BAYAN (grup).'],
            ['• Responden memilih perusahaan pelaksana -> perusahaan itu yang dinilai.'],
            ['• "Tidak tahu" -> perusahaan pelaksana bila hanya satu, atau BAYAN (gabungan) bila lebih dari satu.'],
            [''],
            ['ARTI NILAI 1–4 PER UNSUR'],
            ['Unsur', 'Nilai 1', 'Nilai 2', 'Nilai 3', 'Nilai 4'],
        ];
        $unsurStart = count($lines) + 1;
        foreach (SurveyOptions::UNSUR as $no => [$name, $answers]) {
            $lines[] = array_merge(["U{$no} {$name}"], $answers);
        }
        $help->fromArray($lines, null, 'A1');
        $help->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $help->getStyle('A11')->getFont()->setBold(true);
        $help->getStyle('A16')->getFont()->setBold(true);
        $help->getStyle('A' . ($unsurStart - 1) . ':E' . ($unsurStart - 1))->getFont()->setBold(true);
        $help->getColumnDimension('A')->setWidth(34);
        foreach (['B', 'C', 'D', 'E'] as $cc) {
            $help->getColumnDimension($cc)->setWidth(24);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'Format_Input_Responden_SKM_PPM.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:xls,xlsx,xlsm,csv,txt',
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes' => 'File harus berformat Excel (.xlsx, .xls, .xlsm) atau .csv.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        try {
            $result = (new SurveyExcelImport($this->catalog()))->analyze($request->file('file')->getPathname());
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.offline-skm')->withErrors(['file' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.offline-skm')->withErrors(['file' => 'File tidak bisa dibaca. Pastikan file berasal dari format Excel yang disediakan dan tidak rusak.']);
        }

        if ($result['summary']['total'] === 0) {
            return redirect()->route('admin.offline-skm')->withErrors(['file' => 'File tidak berisi data (semua baris kosong).']);
        }

        $token = Str::random(24);
        session([self::SESSION_KEY => $result + [
            'token' => $token,
            'filename' => $request->file('file')->getClientOriginalName(),
            'analyzed_at' => now()->format('d/m/Y H:i'),
        ]]);

        return redirect()->route('admin.offline-skm', ['analisis' => $token]);
    }

    public function import(Request $request)
    {
        $stored = session(self::SESSION_KEY);
        if (! $stored || ($stored['token'] ?? null) !== $request->input('token')) {
            return redirect()->route('admin.offline-skm')->withErrors(['file' => 'Hasil analisis sudah kedaluwarsa atau sudah diimport. Silakan upload ulang file.']);
        }

        $chosen = array_map('intval', (array) $request->input('rows', []));
        $rows = array_values(array_filter($stored['rows'], fn ($r) => in_array($r['line'], $chosen, true)
            && in_array($r['status'], [SurveyExcelImport::NEW, SurveyExcelImport::SIMILAR], true)));

        if (! $rows) {
            return redirect()->route('admin.offline-skm', ['analisis' => $stored['token']])->withErrors(['file' => 'Belum ada baris yang dipilih untuk diimport.']);
        }

        // Check again right before saving: someone may have added the same data since the analysis.
        $existing = SurveyExcelImport::existingKeys($rows);
        $imported = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, $existing, &$imported, &$skipped) {
            foreach ($rows as $row) {
                if (isset($existing[$row['key']])) {
                    $skipped++;
                    continue;
                }
                $d = $row['data'];
                $programId = PpmCatalog::rincian()[$d['rincian_id']]['program_id'] ?? null;
                $pilarId = $programId ? PpmCatalog::program()[$programId]['pilar_id'] : null;

                $record = new NilaiUnsur();
                $record->fill([
                    'client_uuid' => SurveyExcelImport::uuidFor($row['key']),
                    'responden_uuid' => $d['responden_uuid'],
                    'tahun' => (int) substr($d['tanggal'], 0, 4),
                    'jenisPelayanan' => PpmCatalog::pilar()[$pilarId]['kode'] ?? null,
                    'desa_id' => $d['desa_id'],
                    'pilar_id' => $pilarId,
                    'program_id' => $programId,
                    'rincian_id' => $d['rincian_id'],
                    'perusahaan_jawaban' => $d['perusahaan_jawaban'],
                    'perusahaan_dinilai' => $d['perusahaan_dinilai'],
                    'sumber_atribusi' => $d['sumber_atribusi'],
                    'perusahaan_opsi' => $d['perusahaan_opsi'],
                    'jenis_responden' => $d['jenis_responden'],
                    'nama' => $d['nama'],
                    'nohp' => $d['nohp'],
                    'alamat' => $d['alamat'],
                    'jenkel' => $d['jenkel'],
                    'usia' => $d['usia'],
                    'pendidikan' => $d['pendidikan'],
                    'pekerjaan' => $d['pekerjaan'],
                    'saran' => $d['saran'],
                    'input_oleh' => \App\Support\Akses::userId(),
                    'sumber_input' => 'import_excel',
                ] + array_intersect_key($d, array_flip(['u1', 'u2', 'u3', 'u4', 'u5', 'u6', 'u7', 'u8', 'u9'])));
                $record->created_at = Carbon::parse($d['tanggal'])->setTime(9, 0);
                try {
                    DB::transaction(fn () => $record->save());
                    $imported++;
                } catch (QueryException $e) {
                    if (($e->errorInfo[0] ?? null) !== '23000') {
                        throw $e;
                    }
                    $skipped++; // same row already imported earlier (unique client_uuid)
                }
            }
        });

        session()->forget(self::SESSION_KEY);

        $message = "Berhasil mengimport {$imported} data dari file {$stored['filename']}.";
        if ($skipped) {
            $message .= " {$skipped} baris dilewati karena ternyata sudah ada di sistem.";
        }

        return redirect()->route('admin.offline-skm')->with('success', $message);
    }

    private function writeList(Worksheet $sheet, string $col, array $values): void
    {
        foreach (array_values($values) as $i => $value) {
            $sheet->setCellValueExplicit($col . ($i + 1), $value, DataType::TYPE_STRING);
        }
    }

    /** Nama range per desa, mis. "DESA_Long_Beleh_Modang" (dipakai rumus INDIRECT). */
    private function desaRangeName(string $desa): string
    {
        return 'DESA_' . preg_replace('/[^A-Za-z0-9_]/', '', str_replace(' ', '_', $desa));
    }

    private function dropdown(Worksheet $sheet, string $range, string $formula, string $title, string $prompt, bool $strict = true, ?string $error = null): void
    {
        $v = new DataValidation();
        $v->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle($strict ? DataValidation::STYLE_STOP : DataValidation::STYLE_INFORMATION)
            ->setAllowBlank(true)
            ->setShowDropDown(true) // in PhpSpreadsheet "true" means the in-cell arrow is shown
            ->setShowInputMessage(true)
            ->setShowErrorMessage(true)
            ->setPromptTitle(mb_substr($title, 0, 32))
            ->setPrompt(mb_substr($prompt, 0, 255))
            ->setErrorTitle('Nilai tidak sesuai')
            ->setError($error ?? 'Pilih salah satu nilai dari daftar.')
            ->setFormula1($formula); // tanpa "=" di depan, sesuai format file .xlsx
        $sheet->setDataValidation($range, $v);
    }

    /** Validasi non-daftar (tanggal, panjang teks, rumus) dengan pesan bantuan & pesan error. */
    private function validation(Worksheet $sheet, string $range, string $type, ?string $operator, string $formula1, ?string $formula2,
        string $title, string $prompt, string $error): void
    {
        $v = new DataValidation();
        $v->setType($type)
            ->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank(true)
            ->setShowInputMessage(true)
            ->setShowErrorMessage(true)
            ->setPromptTitle(mb_substr($title, 0, 32))
            ->setPrompt(mb_substr($prompt, 0, 255))
            ->setErrorTitle('Isian tidak sesuai')
            ->setError(mb_substr($error, 0, 225))
            ->setFormula1($formula1);
        if ($operator) {
            $v->setOperator($operator);
        }
        if ($formula2 !== null) {
            $v->setFormula2($formula2);
        }
        $sheet->setDataValidation($range, $v);
    }

    /** Desa, program & perusahaan yang boleh diimport admin ini (berdasarkan role saat login). */
    private function catalog(): array
    {
        return PpmCatalog::forRole(session('keterangan', 'admin'));
    }
}
