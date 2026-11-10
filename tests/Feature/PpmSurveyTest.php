<?php

namespace Tests\Feature;

use App\Http\Controllers\PpmSurveyController;
use App\Support\PpmCatalog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Alur survei PPM. Butuh master data (php artisan db:seed --class=PpmMasterSeeder).
 * Semua perubahan database di-rollback setelah tiap test.
 */
class PpmSurveyTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        PpmCatalog::flush();

        if (! PpmCatalog::isReady()) {
            $this->markTestSkipped('Master data PPM belum di-seed.');
        }
    }

    private function desaId(string $nama): int
    {
        return (int) array_search($nama, PpmCatalog::desa(), true);
    }

    private function payload(array $extra = []): array
    {
        $data = [
            'nama' => 'Uji Responden',
            'nohp' => '0812',
            'alamat' => 'RT 1',
            'jenkel' => 'Perempuan',
            'usia' => '30',
            'pendidikan' => 'SD',
            'pekerjaan' => 'Lainnya',
            'jenis_responden' => 'Penerima Manfaat',
            'saran' => 'ok',
        ];
        foreach (range(1, 9) as $i) {
            $data["u{$i}"] = 3;
        }

        return array_merge($data, $extra);
    }

    public function test_halaman_survei_tampil(): void
    {
        $this->get('/skm')->assertOk()->assertSee('Silakan pilih desa Anda')->assertSee('Long Beleh Modang');
    }

    public function test_program_grup_selalu_dinilai_bayan(): void
    {
        $this->post('/skm', $this->payload([
            'desa_id' => $this->desaId('Umaq Dian'),
            'rincian_id' => 'P1.1.1',
            'perusahaan_jawaban' => 'FSP',
        ]))->assertRedirect('/terimakasih');

        $row = DB::table('survey_responses')->where('nama', 'Uji Responden')->latest('id')->first();
        $this->assertSame('FSP', $row->perusahaan_jawaban);
        $this->assertSame('BAYAN', $row->perusahaan_dinilai);
        $this->assertSame(PpmCatalog::SUMBER_SISTEM, $row->sumber_atribusi);
        $this->assertSame('P1', $row->pilar_id);
        $this->assertSame('P1.1', $row->program_id);
        $this->assertSame('pendidikan', $row->jenisPelayanan);
        $this->assertSame('Penerima Manfaat', $row->jenis_responden);
    }

    public function test_jawaban_responden_dipakai_untuk_program_non_grup(): void
    {
        $this->post('/skm', $this->payload([
            'desa_id' => $this->desaId('Long Beleh Modang'),
            'rincian_id' => 'P5.1.2',
            'perusahaan_jawaban' => 'TA',
        ]))->assertRedirect('/terimakasih');

        $row = DB::table('survey_responses')->where('nama', 'Uji Responden')->latest('id')->first();
        $this->assertSame('TA', $row->perusahaan_dinilai);
        $this->assertSame(PpmCatalog::SUMBER_JAWABAN, $row->sumber_atribusi);
        $this->assertSame('BT,TA,TJ', $row->perusahaan_opsi);
    }

    public function test_tidak_tahu_dengan_satu_pelaksana_dan_banyak_pelaksana(): void
    {
        $one = PpmCatalog::attribute('P1.2.1', $this->desaId('Dusun Ruang'), PpmCatalog::TIDAK_TAHU);
        $this->assertSame(['FSP', PpmCatalog::SUMBER_SISTEM], [$one['perusahaan_dinilai'], $one['sumber_atribusi']]);

        $many = PpmCatalog::attribute('P5.1.2', $this->desaId('Long Beleh Modang'), PpmCatalog::TIDAK_TAHU);
        $this->assertSame(['BAYAN', PpmCatalog::SUMBER_SISTEM], [$many['perusahaan_dinilai'], $many['sumber_atribusi']]);
    }

    public function test_perusahaan_yang_bukan_pelaksana_di_desa_ditolak(): void
    {
        $this->post('/skm', $this->payload([
            'desa_id' => $this->desaId('Long Beleh Modang'),
            'rincian_id' => 'P5.1.2',
            'perusahaan_jawaban' => 'FSP',
        ]))->assertSessionHasErrors('perusahaan_jawaban');
    }

    public function test_responden_lanjutan_tidak_mengisi_ulang_identitas(): void
    {
        $desa = $this->desaId('Long Beleh Modang');
        $this->post('/skm', $this->payload(['desa_id' => $desa, 'rincian_id' => 'P5.1.2', 'perusahaan_jawaban' => 'TJ']));
        $uuid = session(PpmSurveyController::SESSION_KEY)['uuid'];

        // Penilaian kedua: hanya pilihan program + kuesioner, tanpa identitas
        $kedua = ['lanjutan' => 1, 'desa_id' => $desa, 'rincian_id' => 'P8.1.7', 'perusahaan_jawaban' => PpmCatalog::TIDAK_TAHU];
        foreach (range(1, 9) as $i) {
            $kedua["u{$i}"] = 4;
        }
        $this->post('/skm', $kedua)->assertRedirect('/terimakasih');

        $rows = DB::table('survey_responses')->where('responden_uuid', $uuid)->orderBy('id')->get();
        $this->assertCount(2, $rows);
        $this->assertSame('Uji Responden', $rows[1]->nama);
        $this->assertSame('Penerima Manfaat', $rows[1]->jenis_responden);
        $this->assertSame('TJ', $rows[1]->perusahaan_dinilai); // satu-satunya pelaksana

        // Program yang sama tidak bisa dinilai dua kali
        $this->post('/skm', $kedua)->assertSessionHasErrors('rincian_id');
    }

    public function test_rekap_ppm_tampil_untuk_admin(): void
    {
        $this->withSession(['user_id' => 1])->get('/rekap-ppm')->assertOk()->assertSee('SKM BAYAN (GRUP)');
    }
}
