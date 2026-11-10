<?php

namespace Tests\Feature;

use App\Services\PpmMasterData;
use App\Support\PpmCatalog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menu Master Data PPM. Semua perubahan database di-rollback setelah tiap test.
 */
class MasterPpmTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->withSession(['user_id' => 1, 'keterangan' => 'admin']);
        PpmCatalog::flush();
    }

    public function test_semua_tab_tampil_dan_sub_jenis_dialihkan(): void
    {
        foreach (['program', 'desa', 'perusahaan', 'json'] as $tab) {
            $this->get("/master-ppm?tab={$tab}")->assertOk()->assertSee('Master Data PPM');
        }
        $this->get('/master-ppm?tab=program')->assertSee('PENDIDIKAN')->assertSee('Beasiswa');
        $this->get('/master-ppm?tab=desa')->assertSee('Long Beleh Modang');
        $this->get('/master-ppm/rincian/P5.1.2')->assertOk()->assertSee('Perusahaan pelaksana per desa');
        $this->get('/sub-jenis')->assertRedirect('/master-ppm');
    }

    public function test_kelola_desa(): void
    {
        $this->post('/master-ppm/desa', ['nama' => 'Desa Uji'])->assertRedirect();
        $id = (int) DB::table('ppm_desa')->where('nama', 'Desa Uji')->value('id');
        $this->assertGreaterThan(0, $id);

        // Nama ganda ditolak
        $this->post('/master-ppm/desa', ['nama' => 'Desa Uji'])->assertSessionHasErrors('nama');

        // Nonaktif -> tidak muncul di survei
        $this->put("/master-ppm/desa/{$id}", ['nama' => 'Desa Uji Baru', 'urutan' => 99, 'is_active' => 0])->assertRedirect();
        PpmCatalog::flush();
        $this->assertArrayNotHasKey($id, PpmCatalog::desa());
        $this->assertSame('Desa Uji Baru', DB::table('ppm_desa')->where('id', $id)->value('nama'));

        // Desa yang dipakai penilaian tidak boleh dihapus
        DB::table('survey_responses')->insert(['tahun' => 2026, 'desa_id' => $id, 'nama' => 'x', 'created_at' => now(), 'updated_at' => now()]);
        $this->delete("/master-ppm/desa/{$id}")->assertSessionHas('error');
        $this->assertTrue(DB::table('ppm_desa')->where('id', $id)->exists());

        DB::table('survey_responses')->where('desa_id', $id)->delete();
        $this->delete("/master-ppm/desa/{$id}")->assertSessionHas('success');
        $this->assertFalse(DB::table('ppm_desa')->where('id', $id)->exists());
    }

    public function test_tambah_rincian_baru_lalu_muncul_di_survei(): void
    {
        $this->post('/master-ppm/program', ['pilar_id' => 'P1', 'nama' => 'Program Uji']);
        $programId = DB::table('ppm_program')->where('nama', 'Program Uji')->value('id');
        $this->assertSame('P1.6', $programId);

        $this->post('/master-ppm/rincian', ['program_id' => $programId, 'nama' => 'Rincian Uji'])
            ->assertRedirect('/master-ppm/rincian/P1.6.1');

        // Aktif tanpa desa pelaksana ditolak
        $this->put('/master-ppm/rincian/P1.6.1', ['nama' => 'Rincian Uji', 'is_active' => 1, 'penilai' => ['TA']])
            ->assertSessionHasErrors('is_active');

        $desa = (int) DB::table('ppm_desa')->where('nama', 'Abit')->value('id');
        $this->put('/master-ppm/rincian/P1.6.1', [
            'nama' => 'Rincian Uji', 'is_active' => 1, 'penilai' => ['TA', 'TJ'],
            'lokasi' => ['TA' => [$desa], 'TJ' => [$desa]], 'lokasi_lain' => ['TA' => 'Ring 2; Kec. Tabang'],
        ])->assertSessionHas('success');

        PpmCatalog::flush();
        $this->assertSame(['TA', 'TJ'], PpmCatalog::pelaksana('P1.6.1', $desa));
        $this->assertSame(2, DB::table('ppm_rincian_lokasi')->where('rincian_id', 'P1.6.1')->whereNull('desa_id')->count());
        $this->assertSame('BAYAN', PpmCatalog::attribute('P1.6.1', $desa, PpmCatalog::TIDAK_TAHU)['perusahaan_dinilai']);

        // Halaman survei memuat rincian baru
        $this->get('/skm')->assertSee('Rincian Uji');

        // Dinilai sebagai grup -> BAYAN ditambahkan ke penilai
        $this->put('/master-ppm/rincian/P1.6.1', [
            'nama' => 'Rincian Uji', 'is_active' => 1, 'dinilai_sebagai_grup' => 1, 'penilai' => ['TA'], 'lokasi' => ['TA' => [$desa]],
        ]);
        $this->assertSame('TA,BAYAN', DB::table('ppm_rincian')->where('id', 'P1.6.1')->value('penilai'));
    }

    public function test_rincian_terpakai_tidak_bisa_dihapus(): void
    {
        DB::table('survey_responses')->insert(['tahun' => 2026, 'rincian_id' => 'P5.1.2', 'nama' => 'x', 'created_at' => now(), 'updated_at' => now()]);
        $this->delete('/master-ppm/rincian/P5.1.2')->assertSessionHas('error');
        $this->assertTrue(DB::table('ppm_rincian')->where('id', 'P5.1.2')->exists());
        // Program yang masih berisi rincian juga tidak bisa dihapus
        $this->delete('/master-ppm/program/P5.1')->assertSessionHas('error');
    }

    public function test_ekspor_lalu_impor_tetap_sama(): void
    {
        $before = app(PpmMasterData::class)->export();
        $desaIds = DB::table('ppm_desa')->orderBy('id')->pluck('nama', 'id')->all();
        $lokasiBefore = DB::table('ppm_rincian_lokasi')->whereNotNull('desa_id')->count();

        $res = $this->get('/master-ppm/export');
        $res->assertOk()->assertHeader('Content-Disposition');
        $json = $res->getContent();
        $this->assertSame(json_decode(json_encode($before['pilar']), true), json_decode($json, true)['pilar']);

        $file = UploadedFile::fake()->createWithContent('master.json', $json);
        $this->post('/master-ppm/import', ['file' => $file, 'konfirmasi' => 1])->assertSessionHas('success');

        $this->assertSame($desaIds, DB::table('ppm_desa')->orderBy('id')->pluck('nama', 'id')->all());
        $this->assertSame(51, DB::table('ppm_rincian')->count());
        $this->assertSame(50, DB::table('ppm_rincian')->where('is_active', 1)->count());
        $this->assertSame($lokasiBefore, DB::table('ppm_rincian_lokasi')->whereNotNull('desa_id')->count());

        // Tanpa konfirmasi atau JSON rusak: master tidak berubah
        $this->post('/master-ppm/import', ['file' => UploadedFile::fake()->createWithContent('x.json', $json)])->assertSessionHasErrors('konfirmasi');
        $this->post('/master-ppm/import', ['file' => UploadedFile::fake()->createWithContent('x.json', '{"rusak":'), 'konfirmasi' => 1])->assertSessionHas('error');
        $this->assertSame(51, DB::table('ppm_rincian')->count());
    }
}
