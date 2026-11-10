<?php

namespace App\Http\Controllers;

use App\Models\NilaiUnsur;
use App\Services\SurveyExcelImport;
use App\Support\BagianOptions;
use App\Support\SurveyOptions;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Import respondent data from Excel: download template -> upload ->
 * review the duplicate/validity analysis -> import the chosen rows.
 */
class OfflineSkmController extends Controller
{
    private const SESSION_KEY = 'excel_import';
    private const TEMPLATE_ROWS = 1000;

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
        $spreadsheet = new Spreadsheet();
        $bagian = $this->allowedBagian();

        // Hidden sheet holding the dropdown lists (names may contain commas, so inline lists won't do).
        $lists = $spreadsheet->createSheet();
        $lists->setTitle('Pilihan');
        $columns = [
            'A' => array_values($bagian),
            'B' => SurveyOptions::JENKEL,
            'C' => array_values(SurveyOptions::USIA),
            'D' => SurveyOptions::PENDIDIKAN,
            'E' => SurveyOptions::PEKERJAAN,
        ];
        foreach ($columns as $col => $values) {
            foreach (array_values($values) as $i => $value) {
                $lists->setCellValueExplicit($col . ($i + 1), $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
        }
        $lists->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        // Data sheet
        $sheet = $spreadsheet->getSheet(0);
        $sheet->setTitle('Data');
        $headers = SurveyExcelImport::templateHeaders();
        $sheet->fromArray($headers, null, 'A1');
        $lastCol = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EA580C']],
            'alignment' => ['wrapText' => true, 'vertical' => 'center'],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->freezePane('A2');

        $widths = ['A' => 14, 'B' => 30, 'C' => 24, 'D' => 16, 'E' => 24, 'F' => 14, 'G' => 16, 'H' => 14, 'I' => 14];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        $n = self::TEMPLATE_ROWS + 1;
        $sheet->getStyle("A2:A{$n}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle("D2:D{$n}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        $listRef = fn (string $col, array $values) => '=Pilihan!$' . $col . '$1:$' . $col . '$' . max(1, count($values));
        $this->dropdown($sheet, "B2:B{$n}", $listRef('A', $bagian), 'Pilar', 'Pilih pilar/program dari daftar.');
        $this->dropdown($sheet, "F2:F{$n}", $listRef('B', SurveyOptions::JENKEL), 'Jenis kelamin', 'Pilih dari daftar.');
        $this->dropdown($sheet, "G2:G{$n}", $listRef('C', SurveyOptions::USIA), 'Usia', 'Pilih kelompok usia, atau ketik umur dalam tahun.', false);
        $this->dropdown($sheet, "H2:H{$n}", $listRef('D', SurveyOptions::PENDIDIKAN), 'Pendidikan', 'Pilih dari daftar.');
        $this->dropdown($sheet, "I2:I{$n}", $listRef('E', SurveyOptions::PEKERJAAN), 'Pekerjaan', 'Pilih dari daftar.');

        $col = 'J';
        foreach (SurveyOptions::UNSUR as $no => [$name, $answers]) {
            $sheet->getColumnDimension($col)->setWidth(13);
            $legend = implode("\n", array_map(fn ($a, $i) => ($i + 1) . ' = ' . $a, $answers, array_keys($answers)));
            $this->dropdown($sheet, "{$col}2:{$col}{$n}", '"1,2,3,4"', "U{$no} {$name}", $legend);
            $col++;
        }
        $sheet->getColumnDimension($col)->setWidth(40);
        $sheet->setSelectedCell('A2');

        // Instructions sheet
        $help = $spreadsheet->createSheet(1);
        $help->setTitle('Petunjuk');
        $lines = [
            ['PETUNJUK PENGISIAN DATA RESPONDEN'],
            [''],
            ['1. Isi data di sheet "Data", satu baris untuk satu responden. Jangan ubah judul kolom di baris pertama.'],
            ['2. Tanggal survei ditulis hari/bulan/tahun, contoh 15/10/2026.'],
            ['3. Pilar, jenis kelamin, usia, pendidikan, dan pekerjaan dipilih dari daftar (klik sel, lalu panah kecil di kanan).'],
            ['4. Nilai U1–U9 diisi angka 1 sampai 4 sesuai jawaban responden (lihat tabel di bawah).'],
            ['5. Wajib diisi: tanggal, pilar, nama, jenis kelamin, usia, pendidikan, pekerjaan, dan U1–U9.'],
            ['6. Simpan file, lalu upload di menu Admin > Import Excel. Sistem akan memeriksa data duplikat sebelum disimpan.'],
            [''],
            ['ARTI NILAI 1–4 PER UNSUR'],
            ['Unsur', 'Nilai 1', 'Nilai 2', 'Nilai 3', 'Nilai 4'],
        ];
        foreach (SurveyOptions::UNSUR as $no => [$name, $answers]) {
            $lines[] = array_merge(["U{$no} {$name}"], $answers);
        }
        $lines[] = [''];
        $lines[] = ['DAFTAR PILAR'];
        foreach ($bagian as $name) {
            $lines[] = [$name];
        }
        $help->fromArray($lines, null, 'A1');
        $help->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $help->getStyle('A10')->getFont()->setBold(true);
        $help->getStyle('A11:E11')->getFont()->setBold(true);
        $help->getStyle('A' . (count($lines) - count($bagian)))->getFont()->setBold(true);
        $help->getColumnDimension('A')->setWidth(34);
        foreach (['B', 'C', 'D', 'E'] as $c) {
            $help->getColumnDimension($c)->setWidth(24);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'Format_Input_Responden_SKM.xlsx', [
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
            $result = (new SurveyExcelImport($this->allowedBagian()))->analyze($request->file('file')->getPathname());
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
                $record = new NilaiUnsur();
                $record->fill([
                    'client_uuid' => SurveyExcelImport::uuidFor($row['key']),
                    'jenisPelayanan' => $d['bagian'],
                    'tahun' => (int) substr($d['tanggal'], 0, 4),
                    'nama' => $d['nama'],
                    'nohp' => $d['nohp'],
                    'alamat' => $d['alamat'],
                    'jenkel' => $d['jenkel'],
                    'usia' => $d['usia'],
                    'pendidikan' => $d['pendidikan'],
                    'pekerjaan' => $d['pekerjaan'],
                    'saran' => $d['saran'],
                ] + array_intersect_key($d, array_flip(['u1', 'u2', 'u3', 'u4', 'u5', 'u6', 'u7', 'u8', 'u9'])));
                // Midnight keeps the record inside period reports that filter up to "Y-m-d" of the last day.
                $record->created_at = Carbon::parse($d['tanggal'])->startOfDay();
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

    private function dropdown(Worksheet $sheet, string $range, string $formula, string $title, string $prompt, bool $strict = true): void
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
            ->setError('Pilih salah satu nilai dari daftar.')
            ->setFormula1($formula);
        $sheet->setDataValidation($range, $v);
    }

    /** Pilar this admin may import into, based on the role saved at login. */
    private function allowedBagian(): array
    {
        $codes = BagianOptions::codesForRole(session('keterangan', 'admin'));

        return array_intersect_key(BagianOptions::codeNameMap(), array_flip($codes));
    }
}
