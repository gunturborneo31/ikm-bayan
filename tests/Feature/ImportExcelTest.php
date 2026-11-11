<?php

namespace Tests\Feature;

use App\Services\SurveyExcelImport;
use App\Support\PpmCatalog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * Import Excel responden: format -> upload -> analisis -> import.
 * Semua perubahan database di-rollback setelah tiap test.
 */
class ImportExcelTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->withSession(['user_id' => 1, 'keterangan' => 'admin']);
        PpmCatalog::flush();
    }

    /** Unduh format, lalu isi baris data seperti admin mengisi di Excel. */
    private function filledTemplate(array $rows): UploadedFile
    {
        $res = $this->get('/admin/offline-skm/format');
        $res->assertOk();
        $tmp = tempnam(sys_get_temp_dir(), 'tpl') . '.xlsx';
        file_put_contents($tmp, $res->streamedContent());

        $book = IOFactory::load($tmp);
        $sheet = $book->getSheetByName('Data');
        $this->assertSame(SurveyExcelImport::templateHeaders(), $sheet->rangeToArray('A1:W1')[0]);
        $sheet->fromArray($rows, null, 'A2');
        IOFactory::createWriter($book, 'Xlsx')->save($tmp);

        return new UploadedFile($tmp, 'uji.xlsx', null, null, true);
    }

    private function row(array $over = []): array
    {
        $base = [
            'tanggal' => '15/03/2026', 'desa' => 'Long Beleh Modang', 'rincian' => 'P5.1.2 - Pembangunan Sarana dan Prasarana Ibadah',
            'perusahaan' => 'TA - Tabang Alas', 'kode' => 'R001', 'jenis' => 'Aparat Desa', 'nama' => 'Uji Excel', 'nohp' => '0812555',
            'alamat' => 'RT 03', 'jk' => 'Perempuan', 'usia' => '30 s/d 40', 'pend' => 'SMP', 'kerja' => 'Lainnya',
        ];
        $r = array_values(array_merge($base, $over));

        return array_merge($r, [3, 3, 3, 3, 3, 3, 3, 3, 3, 'saran uji']);
    }

    public function test_template_berisi_dropdown_bertingkat_dan_daftar_program(): void
    {
        $res = $this->get('/admin/offline-skm/format');
        $tmp = tempnam(sys_get_temp_dir(), 'tpl') . '.xlsx';
        file_put_contents($tmp, $res->streamedContent());
        $book = IOFactory::load($tmp);

        $this->assertNotNull($book->getSheetByName('Daftar Program per Desa'));
        $this->assertNotNull($book->getNamedRange('DESA_Long_Beleh_Modang'));
        $v = $book->getSheetByName('Data')->getDataValidation('C2');
        $this->assertStringContainsString('INDIRECT', $v->getFormula1());
        $data = $book->getSheetByName('Data');
        $this->assertStringStartsNotWith('=', $data->getDataValidation('B2')->getFormula1());

        // U1–U9: daftar angka 1–4, isian lain ditolak; format angka bulat
        $u = $data->getDataValidation('N2');
        $this->assertSame('list', $u->getType());
        $this->assertSame('Pilihan!$H$1:$H$4', $u->getFormula1());
        $this->assertSame('stop', $u->getErrorStyle());
        $this->assertSame('0', $data->getStyle('V500')->getNumberFormat()->getFormatCode());
        $this->assertSame([1, 2, 3, 4], array_map('intval', array_column($book->getSheetByName('Pilihan')->rangeToArray('H1:H4'), 0)));

        // Tanggal, teks, panjang teks
        $this->assertSame('date', $data->getDataValidation('A2')->getType());
        $this->assertSame('TODAY()', $data->getDataValidation('A2')->getFormula2());
        $this->assertSame('dd/mm/yyyy', $data->getStyle('A2')->getNumberFormat()->getFormatCode());
        foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'L', 'M', 'W'] as $col) {
            $this->assertSame('@', $data->getStyle("{$col}2")->getNumberFormat()->getFormatCode(), "kolom {$col} harus teks");
        }
        $this->assertSame('textLength', $data->getDataValidation('G2')->getType());
        $this->assertSame('custom', $data->getDataValidation('H2')->getType());

        // Judul kolom terkunci, sel isian tidak
        $this->assertTrue($data->getProtection()->getSheet());
        $this->assertSame('unprotected', $data->getStyle('N2')->getProtection()->getLocked());
    }

    public function test_analisis_dan_import_mengikuti_alur_survei(): void
    {
        $file = $this->filledTemplate([
            $this->row(), // baris 2: TA dipilih -> TA
            $this->row(['rincian' => 'P8.1.7 - Pemasangan Sambungan Listrik Rumah', 'perusahaan' => 'Tidak tahu']), // baris 3: -> TJ, responden sama
            $this->row(['desa' => 'Umaq Dian', 'rincian' => 'P1.1.1', 'perusahaan' => 'FSP', 'kode' => 'R002', 'nama' => 'Uji Excel Dua']), // baris 4: beasiswa -> BAYAN
            $this->row(['perusahaan' => 'FSP - Fajar Sakti Prima', 'kode' => 'R003', 'nama' => 'Uji Salah']), // baris 5: FSP bukan pelaksana
            $this->row(['desa' => 'Muara Siran', 'kode' => 'R004', 'nama' => 'Uji Salah Desa']), // baris 6: rincian tidak berjalan di desa
            $this->row(), // baris 7: duplikat baris 2
        ]);

        $this->post('/admin/offline-skm/analisis', ['file' => $file])->assertRedirect();
        $analysis = session('excel_import');
        $status = array_column($analysis['rows'], 'status', 'line');

        $this->assertSame(SurveyExcelImport::NEW, $status[2]);
        $this->assertSame(SurveyExcelImport::NEW, $status[3]);
        $this->assertSame(SurveyExcelImport::NEW, $status[4]);
        $this->assertSame(SurveyExcelImport::INVALID, $status[5]);
        $this->assertSame(SurveyExcelImport::INVALID, $status[6]);
        $this->assertSame(SurveyExcelImport::DUP_FILE, $status[7]);
        $notes = array_column($analysis['rows'], 'notes', 'line');
        $this->assertStringContainsString('bukan pelaksana', implode(' ', $notes[5]));
        $this->assertStringContainsString('tidak berjalan di desa', implode(' ', $notes[6]));

        // Halaman hasil analisis menampilkan kolom baru
        $this->get('/admin/offline-skm?analisis=' . $analysis['token'])->assertOk()
            ->assertSee('Rincian Program')->assertSee('Pemasangan Sambungan Listrik Rumah');

        $this->post('/admin/import-offline-skm', ['token' => $analysis['token'], 'rows' => [2, 3, 4]])
            ->assertSessionHas('success');

        $rows = DB::table('survey_responses')->whereIn('nama', ['Uji Excel', 'Uji Excel Dua'])->orderBy('id')->get()->keyBy('rincian_id');
        $this->assertCount(3, $rows);
        $this->assertSame('TA', $rows['P5.1.2']->perusahaan_dinilai);
        $this->assertSame('TJ', $rows['P8.1.7']->perusahaan_dinilai);
        $this->assertSame(PpmCatalog::SUMBER_SISTEM, $rows['P8.1.7']->sumber_atribusi);
        $this->assertSame('BAYAN', $rows['P1.1.1']->perusahaan_dinilai);
        $this->assertSame('sosial', $rows['P5.1.2']->jenisPelayanan);
        $this->assertSame('P5.1', $rows['P5.1.2']->program_id);
        $this->assertSame('Aparat Desa', $rows['P5.1.2']->jenis_responden);
        $this->assertSame(30, (int) $rows['P5.1.2']->usia); // "30 s/d 40" -> kode 30
        // Kode Responden sama -> satu responden
        $this->assertSame($rows['P5.1.2']->responden_uuid, $rows['P8.1.7']->responden_uuid);
        $this->assertNotSame($rows['P5.1.2']->responden_uuid, $rows['P1.1.1']->responden_uuid);

        // Upload ulang file yang sama -> sudah ada di sistem
        $this->post('/admin/offline-skm/analisis', ['file' => $this->filledTemplate([$this->row()])]);
        $this->assertSame(SurveyExcelImport::DUP_DB, session('excel_import')['rows'][0]['status']);
    }

    public function test_format_lama_ditolak_dengan_pesan_jelas(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'old') . '.xlsx';
        $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $book->getActiveSheet()->fromArray([['Tanggal Survei', 'Pilar', 'Nama', 'U1 Kesesuaian Program'], ['15/03/2026', 'Pendidikan', 'X', 3]]);
        IOFactory::createWriter($book, 'Xlsx')->save($tmp);

        $this->post('/admin/offline-skm/analisis', ['file' => new UploadedFile($tmp, 'lama.xlsx', null, null, true)])
            ->assertSessionHasErrors('file');
        $this->assertStringContainsString('format Excel terbaru', session('errors')->first('file'));
    }
}
