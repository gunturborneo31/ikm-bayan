<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use ZipArchive;

/**
 * Laporan SKM (.docx). Semua perubahan database di-rollback setelah tiap test.
 */
class LaporanSkmTest extends TestCase
{
    use DatabaseTransactions;

    /** Isi word/document.xml dari laporan yang diunduh; gagal bila XML rusak. */
    private function documentXml(string $url): string
    {
        $res = $this->withSession(['user_id' => 1, 'keterangan' => 'admin'])->get($url);
        $res->assertOk();
        $this->assertStringContainsString('.docx', $res->headers->get('Content-Disposition'));

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($res->getFile()->getPathname()) === true, 'File .docx tidak valid');
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        $this->assertNotFalse(@simplexml_load_string($xml), 'document.xml rusak (karakter tidak di-escape?)');

        return strip_tags($xml);
    }

    public function test_harus_login(): void
    {
        $this->get('/export?Tahun=2026')->assertRedirect('/login');
    }

    public function test_laporan_mengikuti_filter_dan_aman_untuk_karakter_khusus(): void
    {
        // Tahun 2031 dipakai khusus test agar tidak tercampur data asli / dummy
        DB::table('survey_responses')->insert($this->row(['jenisPelayanan' => 'sosial', 'perusahaan_dinilai' => 'TA',
            'saran' => 'Jalan & jembatan <rusak> "segera"']));

        $text = $this->documentXml('/export?Tahun=2031&perusahaan=TA&bagian=sosial');

        $this->assertStringContainsString('TA · Tabang Alas', $text);
        $this->assertStringContainsString('Sosial &amp; Budaya', $text);
        $this->assertStringContainsString('Jalan &amp; jembatan &lt;rusak&gt;', $text);
        $this->assertStringContainsString('BAB IV', $text);
        $this->assertStringNotContainsString('Mahakam Ulu', $text);
        $this->assertStringContainsString('75,00', $text); // semua jawaban 3 -> SKM 75,00
    }

    public function test_laporan_tanpa_data(): void
    {
        // Ada data 2031 untuk pilar pendidikan, tetapi tidak untuk pilar sosial
        DB::table('survey_responses')->insert($this->row(['jenisPelayanan' => 'pendidikan', 'perusahaan_dinilai' => 'FSP']));

        $text = $this->documentXml('/export?Tahun=2031&bagian=sosial');

        $this->assertStringContainsString('Belum ada data penilaian', $text);
    }

    private function row(array $extra): array
    {
        $row = ['tahun' => 2031, 'nama' => 'Uji Laporan', 'jenkel' => 'Perempuan', 'usia' => 30, 'pendidikan' => 'SD',
            'pekerjaan' => 'Lainnya', 'created_at' => '2031-05-01 10:00:00', 'updated_at' => now()];
        foreach (range(1, 9) as $i) {
            $row["u{$i}"] = 3;
        }

        return array_merge($row, $extra);
    }
}
