<?php

namespace Tests\Feature;

use App\Support\SurveyFilter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Filter laporan tahunan: Tahun + Pilar, tanpa triwulan.
 * Semua perubahan database di-rollback setelah tiap test.
 */
class FilterTahunanTest extends TestCase
{
    use DatabaseTransactions;

    private function insertRow(int $tahun, ?string $pilar, ?string $uuid = null): void
    {
        $row = ['tahun' => $tahun, 'jenisPelayanan' => $pilar, 'responden_uuid' => $uuid, 'nama' => 'Uji Filter',
            'jenkel' => 'Perempuan', 'usia' => 30, 'created_at' => "{$tahun}-06-15 10:00:00", 'updated_at' => now()];
        foreach (range(1, 9) as $i) {
            $row["u{$i}"] = 3;
        }
        DB::table('survey_responses')->insert($row);
    }

    private function countFor(array $query): int
    {
        $this->app['request']->query->replace($query);

        return SurveyFilter::query()->where('nama', 'Uji Filter')->count();
    }

    public function test_filter_tahun_dan_pilar(): void
    {
        $this->insertRow(2025, 'pendidikan');
        $this->insertRow(2025, 'kesehatan');
        $this->insertRow(2025, null); // data lama tanpa pilar
        $this->insertRow(2026, 'pendidikan');

        $this->assertSame(3, $this->countFor(['Tahun' => '2025']));            // semua pilar, termasuk tanpa pilar
        $this->assertSame(1, $this->countFor(['Tahun' => '2025', 'bagian' => 'pendidikan']));
        $this->assertSame(2, $this->countFor(['Tahun' => '2025', 'bagian' => 'pendidikan,kesehatan']));
        $this->assertSame(1, $this->countFor(['Tahun' => '2026']));
        // Pilar tidak dikenal (mis. "setkab" dari link lama) = semua pilar
        $this->assertSame(3, $this->countFor(['Tahun' => '2025', 'bagian' => 'setkab']));
        // Parameter triwulan lama diabaikan: tetap satu tahun penuh
        $this->assertSame(3, $this->countFor(['Tahun' => '2025', 'tw' => '1']));
    }

    public function test_filter_perusahaan(): void
    {
        $this->insertRow(2025, 'pendidikan');
        $this->insertRow(2025, 'kesehatan');
        DB::table('survey_responses')->where('nama', 'Uji Filter')->where('jenisPelayanan', 'pendidikan')->update(['perusahaan_dinilai' => 'TA']);
        DB::table('survey_responses')->where('nama', 'Uji Filter')->where('jenisPelayanan', 'kesehatan')->update(['perusahaan_dinilai' => 'BAYAN']);

        $this->assertSame(2, $this->countFor(['Tahun' => '2025']));
        $this->assertSame(1, $this->countFor(['Tahun' => '2025', 'perusahaan' => 'TA']));
        $this->assertSame(1, $this->countFor(['Tahun' => '2025', 'perusahaan' => 'bayan'])); // huruf kecil tetap dikenali
        $this->assertSame(0, $this->countFor(['Tahun' => '2025', 'perusahaan' => 'FSP']));
        $this->assertSame(2, $this->countFor(['Tahun' => '2025', 'perusahaan' => 'XYZ'])); // tidak dikenal = semua
        $this->assertSame(1, $this->countFor(['Tahun' => '2025', 'bagian' => 'pendidikan', 'perusahaan' => 'TA']));
    }

    public function test_detail_isian_responden(): void
    {
        $desaId = (int) DB::table('ppm_desa')->where('nama', 'Long Beleh Modang')->value('id');
        // Tahun 2031 khusus test, agar baris uji tidak tergeser data dummy di halaman pertama
        $this->insertRow(2031, 'sosial', 'uuid-detail');
        $this->insertRow(2031, 'infrastruktur', 'uuid-detail');
        $rows = DB::table('survey_responses')->where('responden_uuid', 'uuid-detail')->orderBy('id')->pluck('id');
        DB::table('survey_responses')->where('id', $rows[0])->update([
            'desa_id' => $desaId, 'pilar_id' => 'P5', 'program_id' => 'P5.1', 'rincian_id' => 'P5.1.2',
            'perusahaan_jawaban' => 'TIDAK_TAHU', 'perusahaan_dinilai' => 'BAYAN', 'sumber_atribusi' => 'asumsi_sistem',
            'perusahaan_opsi' => 'BT,TA,TJ', 'jenis_responden' => 'Aparat Desa', 'u1' => 1, 'saran' => 'Perbanyak sosialisasi',
        ]);

        $admin = $this->withSession(['user_id' => 1, 'keterangan' => 'admin']);

        $admin->get('/rekapTotal?jenkel=1&usia=1&pekerjaan=1&pendidikan=1&Tahun=2031&perusahaan=BAYAN')
            ->assertOk()
            ->assertSee(route('rekapTotal.show', $rows[0]), false)
            ->assertSee('Long Beleh Modang')
            ->assertSee('Pembangunan Sarana dan Prasarana Ibadah');

        $admin->get(route('rekapTotal.show', $rows[0]))
            ->assertOk()
            ->assertSee('Aparat Desa')
            ->assertSee('Long Beleh Modang')
            ->assertSee('Pembangunan Sarana dan Prasarana Ibadah')
            ->assertSee('Tidak tahu')
            ->assertSee('BAYAN (gabungan)')
            ->assertSee('Asumsi sistem')
            ->assertSee('Tidak Sesuai')       // jawaban U1 = 1
            ->assertSee('Kesesuaian Program')
            ->assertSee('Perbanyak sosialisasi')
            ->assertSee('Penilaian lain oleh responden ini (1)');

        $admin->get(route('rekapTotal.show', 999999999))->assertNotFound();
    }

    public function test_responden_dihitung_sekali(): void
    {
        $this->insertRow(2025, 'pendidikan', 'uuid-a');
        $this->insertRow(2025, 'kesehatan', 'uuid-a'); // responden sama, program lain
        $this->insertRow(2025, 'pendidikan', 'uuid-b');
        $this->app['request']->query->replace(['Tahun' => '2025']);

        $this->assertSame(2, SurveyFilter::respondenQuery()->where('nama', 'Uji Filter')->count());
    }

    public function test_semua_halaman_tampil_tanpa_triwulan(): void
    {
        $this->insertRow(2026, 'pendidikan');

        $public = ['/', '/?Tahun=2026&perusahaan=TA', '/dashboard?Tahun=2026', '/dashboard?Tahun=2026&bagian=pendidikan&perusahaan=BAYAN'];
        $admin = [
            '/rekapTotal?jenkel=1&usia=1&pekerjaan=1&pendidikan=1&Tahun=2026',
            '/nilaiRekap?Tahun=2026&bagian=pendidikan&perusahaan=FSP',
            '/dashboardAdmin/create?Tahun=2026&perusahaan=TJ',
            '/nilaiUnsur?Tahun=2026',
            '/dashboardAdmin/create?Tahun=2026',
            '/rekap-ppm?Tahun=2026',
            '/rekap-ppm?Tahun=semua',
            '/exports?Tahun=2026',
        ];

        foreach ($public as $url) {
            $res = $this->get($url);
            $res->assertOk();
            $this->assertStringNotContainsString('tw=', $res->getContent(), $url);
        }
        foreach ($admin as $url) {
            $res = $this->withSession(['user_id' => 1, 'keterangan' => 'admin'])->get($url);
            $res->assertOk();
            $this->assertStringNotContainsString('tw=', $res->getContent(), $url);
            $this->assertStringNotContainsString('Triwulan', $res->getContent(), $url);
        }
    }
}
