<?php

namespace Tests\Feature;

use App\Support\PpmCatalog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Input responden oleh admin (endpoint JSON yang dipakai halaman offline).
 * Semua perubahan database di-rollback setelah tiap test.
 */
class AdminRespondenTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->withSession(['user_id' => 1, 'keterangan' => 'admin']);
        PpmCatalog::flush();
    }

    private function desa(string $nama): int
    {
        return (int) DB::table('ppm_desa')->where('nama', $nama)->value('id');
    }

    private function payload(array $extra = []): array
    {
        $data = [
            'client_uuid' => 'test-' . uniqid(),
            'responden_uuid' => 'resp-uji-admin',
            'tanggal' => '2026-03-15',
            'desa_id' => $this->desa('Long Beleh Modang'),
            'rincian_id' => 'P5.1.2',
            'perusahaan_jawaban' => 'TA',
            'jenis_responden' => 'Aparat Desa',
            'nama' => 'Uji Admin',
            'nohp' => '0812',
            'alamat' => 'RT 02',
            'jenkel' => 'Perempuan',
            'usia' => 41,
            'pendidikan' => 'SMP',
            'pekerjaan' => 'Lainnya',
            'saran' => 'ok',
        ];
        foreach (range(1, 9) as $i) {
            $data["u{$i}"] = 3;
        }

        return array_merge($data, $extra);
    }

    public function test_halaman_memuat_pilihan_desa_program_dan_perusahaan(): void
    {
        $this->get('/admin/input-responden')->assertOk()
            ->assertSee('Long Beleh Modang')
            ->assertSee('Rincian program')
            ->assertSee('Jenis responden')
            ->assertSee('Pembangunan Sarana dan Prasarana Ibadah', false)
            ->assertSee('Simpan &amp; nilai program lain', false);
    }

    public function test_simpan_mengikuti_alur_survei(): void
    {
        $res = $this->postJson('/admin/responden', $this->payload())->assertCreated();

        $row = DB::table('survey_responses')->where('id', $res->json('data.id'))->first();
        $this->assertSame('P5', $row->pilar_id);
        $this->assertSame('P5.1', $row->program_id);
        $this->assertSame('sosial', $row->jenisPelayanan);
        $this->assertSame('TA', $row->perusahaan_dinilai);
        $this->assertSame(PpmCatalog::SUMBER_JAWABAN, $row->sumber_atribusi);
        $this->assertSame('BT,TA,TJ', $row->perusahaan_opsi);
        $this->assertSame('Aparat Desa', $row->jenis_responden);
        $this->assertSame(2026, (int) $row->tahun);
    }

    public function test_program_grup_dan_tidak_tahu(): void
    {
        // Beasiswa selalu BAYAN walau responden memilih perusahaan
        $res = $this->postJson('/admin/responden', $this->payload(['desa_id' => $this->desa('Umaq Dian'), 'rincian_id' => 'P1.1.1', 'perusahaan_jawaban' => 'FSP']))->assertCreated();
        $this->assertSame('BAYAN', $res->json('data.perusahaan_dinilai'));

        // Tidak tahu + satu pelaksana -> perusahaan itu
        $res = $this->postJson('/admin/responden', $this->payload(['desa_id' => $this->desa('Long Beleh Modang'), 'rincian_id' => 'P8.1.7', 'perusahaan_jawaban' => PpmCatalog::TIDAK_TAHU]))->assertCreated();
        $this->assertSame('TJ', $res->json('data.perusahaan_dinilai'));
    }

    public function test_perusahaan_bukan_pelaksana_ditolak(): void
    {
        $this->postJson('/admin/responden', $this->payload(['perusahaan_jawaban' => 'FSP']))
            ->assertStatus(422)->assertJsonValidationErrors('perusahaan_jawaban');

        // Rincian yang tidak berjalan di desa itu juga ditolak
        $this->postJson('/admin/responden', $this->payload(['desa_id' => $this->desa('Muara Siran'), 'rincian_id' => 'P5.1.2']))
            ->assertStatus(422)->assertJsonValidationErrors('perusahaan_jawaban');

        // Data lama tanpa desa/program tidak lolos
        $this->postJson('/admin/responden', $this->payload(['desa_id' => null, 'rincian_id' => null]))
            ->assertStatus(422)->assertJsonValidationErrors(['desa_id', 'rincian_id']);
    }

    public function test_responden_sama_menilai_program_lain_dan_ubah(): void
    {
        $a = $this->postJson('/admin/responden', $this->payload())->assertCreated()->json('data');
        $this->postJson('/admin/responden', $this->payload(['rincian_id' => 'P8.1.7', 'perusahaan_jawaban' => 'TJ']))->assertCreated();
        $this->assertSame(2, DB::table('survey_responses')->where('responden_uuid', 'resp-uji-admin')->count());

        // Ubah: jawaban jadi Tidak tahu -> 3 pelaksana -> BAYAN
        $this->putJson('/admin/responden/' . $a['id'], $this->payload(['perusahaan_jawaban' => PpmCatalog::TIDAK_TAHU]))
            ->assertOk()->assertJsonPath('data.perusahaan_dinilai', 'BAYAN');

        // Kirim ulang client_uuid yang sama tidak membuat data ganda
        $this->postJson('/admin/responden', $this->payload(['client_uuid' => $a['client_uuid']]))->assertOk()->assertJsonPath('duplicate', true);
    }
}
