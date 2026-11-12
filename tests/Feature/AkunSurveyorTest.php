<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\PpmCatalog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Multi akun (admin & surveyor), manajemen akun, dan pencatatan penginput.
 * Semua perubahan database di-rollback setelah tiap test.
 */
class AkunSurveyorTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        PpmCatalog::flush();
    }

    private function akun(string $role, array $extra = []): User
    {
        $u = new User();
        $u->forceFill(array_merge([
            'name' => 'Uji ' . ucfirst($role),
            'username' => 'uji.' . $role . '.' . uniqid(),
            'email' => uniqid() . '@uji.local',
            'keterangan' => $role,
            'is_active' => true,
            'password' => Hash::make('rahasia123'),
        ], $extra))->save();

        return $u;
    }

    private function sebagai(User $u): static
    {
        return $this->withSession(['user_id' => $u->id, 'keterangan' => $u->keterangan]);
    }

    public function test_surveyor_bisa_semua_fitur_kecuali_master_dan_akun(): void
    {
        $s = $this->akun('surveyor');

        foreach (['/rekapTotal?Tahun=2026', '/nilaiUnsur?Tahun=2026', '/nilaiRekap?Tahun=2026', '/dashboardAdmin/create?Tahun=2026',
            '/rekap-ppm', '/admin/input-responden', '/exports'] as $url) {
            $this->sebagai($s)->get($url)->assertOk();
        }
        foreach (['/master-ppm', '/master-ppm/rincian/P5.1.2', '/akun'] as $url) {
            $this->sebagai($s)->get($url)->assertRedirect()->assertSessionHas('akses_ditolak');
        }
        $this->sebagai($s)->post('/master-ppm/desa', ['nama' => 'Desa Surveyor'])->assertRedirect();
        $this->assertFalse(DB::table('ppm_desa')->where('nama', 'Desa Surveyor')->exists());

        // Menu khusus admin tidak tampil di sidebar surveyor
        $this->sebagai($s)->get('/rekapTotal?Tahun=2026')->assertDontSee('MASTER DATA PPM')->assertDontSee('AKUN PENGGUNA')->assertSee('Surveyor');
        $this->sebagai($this->akun('admin'))->get('/rekapTotal?Tahun=2026')->assertSee('MASTER DATA PPM')->assertSee('AKUN PENGGUNA');

        // Import Excel disembunyikan untuk admin maupun surveyor
        foreach ([$s, $this->akun('admin')] as $akun) {
            $this->sebagai($akun)->get('/rekapTotal?Tahun=2026')->assertDontSee('IMPORT EXCEL');
            $this->sebagai($akun)->get('/admin/offline-skm')->assertRedirect()->assertSessionHas('akses_ditolak');
        }
    }

    public function test_login_dan_akun_nonaktif(): void
    {
        $s = $this->akun('surveyor', ['username' => 'uji.login.surveyor']);
        $this->post('/login', ['username' => 'uji.login.surveyor', 'password' => 'rahasia123'])->assertRedirect();
        $this->assertSame($s->id, session('user_id'));
        $this->assertNotNull($s->fresh()->last_login_at);

        // Dinonaktifkan: tidak bisa login, sesi yang sedang berjalan langsung keluar
        $s->forceFill(['is_active' => false])->save();
        $this->sebagai($s)->get('/rekapTotal')->assertRedirect('/login');
        $this->post('/logout');
        $this->post('/login', ['username' => 'uji.login.surveyor', 'password' => 'rahasia123'])->assertSessionHasErrors('username');
    }

    public function test_admin_kelola_akun(): void
    {
        $admin = $this->akun('admin');

        $this->sebagai($admin)->post('/akun', ['name' => 'Surveyor Baru', 'username' => 'Surveyor.Baru', 'keterangan' => 'surveyor',
            'password' => 'password99', 'password_confirmation' => 'password99'])->assertSessionHas('success');
        $baru = User::where('username', 'surveyor.baru')->firstOrFail();
        $this->assertSame('surveyor', $baru->keterangan);
        $this->assertTrue(Hash::check('password99', $baru->password));

        // Username ganda & password pendek ditolak
        $this->sebagai($admin)->post('/akun', ['name' => 'X', 'username' => 'surveyor.baru', 'keterangan' => 'surveyor',
            'password' => 'pendek', 'password_confirmation' => 'pendek'])->assertSessionHasErrors(['username', 'password']);

        // Ubah: jadi admin, ganti password, nonaktifkan
        $this->sebagai($admin)->put('/akun/' . $baru->id, ['name' => 'Surveyor Baru 2', 'username' => 'surveyor.baru', 'keterangan' => 'surveyor',
            'is_active' => 0, 'password' => 'gantibaru1', 'password_confirmation' => 'gantibaru1'])->assertSessionHas('success');
        $baru->refresh();
        $this->assertFalse($baru->is_active);
        $this->assertTrue(Hash::check('gantibaru1', $baru->password));

        // Halaman akun tampil
        $this->sebagai($admin)->get('/akun')->assertOk()->assertSee('Surveyor Baru 2')->assertSee('Nonaktif');

        // Hapus akun tanpa data -> boleh
        $this->sebagai($admin)->delete('/akun/' . $baru->id)->assertSessionHas('success');
        $this->assertNull(User::find($baru->id));
    }

    public function test_pengaman_akun_sendiri_dan_admin_terakhir(): void
    {
        DB::table('users')->whereIn('keterangan', ['admin', 'superadmin'])->update(['is_active' => false]);
        $admin = $this->akun('admin');

        // Tidak bisa menonaktifkan / menurunkan / menghapus diri sendiri
        $this->sebagai($admin)->put('/akun/' . $admin->id, ['name' => 'A', 'username' => $admin->username, 'keterangan' => 'admin', 'is_active' => 0])
            ->assertSessionHas('error');
        $this->sebagai($admin)->put('/akun/' . $admin->id, ['name' => 'A', 'username' => $admin->username, 'keterangan' => 'surveyor', 'is_active' => 1])
            ->assertSessionHas('error');
        $this->sebagai($admin)->delete('/akun/' . $admin->id)->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->is_active);

        // Akun yang sudah menginput data tidak dihapus, cukup dinonaktifkan
        $s = $this->akun('surveyor');
        DB::table('survey_responses')->insert(['tahun' => 2031, 'nama' => 'x', 'input_oleh' => $s->id, 'sumber_input' => 'input_admin', 'created_at' => now(), 'updated_at' => now()]);
        $this->sebagai($admin)->delete('/akun/' . $s->id)->assertSessionHas('error');
        $this->assertNotNull(User::find($s->id));
    }

    public function test_penginput_tercatat_dan_tampil(): void
    {
        $s = $this->akun('surveyor', ['name' => 'Budi Surveyor']);
        $desa = (int) DB::table('ppm_desa')->where('nama', 'Long Beleh Modang')->value('id');
        $payload = ['client_uuid' => 'uji-' . uniqid(), 'tanggal' => '2031-01-01', 'desa_id' => $desa, 'rincian_id' => 'P5.1.2',
            'perusahaan_jawaban' => 'TA', 'jenis_responden' => 'Aparat Desa', 'nama' => 'Responden Uji Penginput', 'jenkel' => 'Perempuan',
            'usia' => 41, 'pendidikan' => 'SMP', 'pekerjaan' => 'Lainnya'];
        foreach (range(1, 9) as $i) {
            $payload["u{$i}"] = 3;
        }
        $payload['tanggal'] = '2026-03-01';

        $res = $this->sebagai($s)->postJson('/admin/responden', $payload)->assertCreated();
        $this->assertStringContainsString('Budi Surveyor', $res->json('data.penginput'));
        $row = DB::table('survey_responses')->where('id', $res->json('data.id'))->first();
        $this->assertSame($s->id, (int) $row->input_oleh);
        $this->assertSame('input_admin', $row->sumber_input);

        // Penginput mengubah datanya sendiri: penginput tetap
        $this->sebagai($s)->putJson('/admin/responden/' . $row->id, ['perusahaan_jawaban' => 'TJ'] + $payload)->assertOk();
        $this->assertSame($s->id, (int) DB::table('survey_responses')->where('id', $row->id)->value('input_oleh'));

        // Akun lain tidak melihat / mengubah data ini di Input Responden (tetap terlihat di Resume)
        $admin = $this->akun('admin');
        $this->sebagai($admin)->putJson('/admin/responden/' . $row->id, $payload)->assertNotFound();
        $ids = collect($this->sebagai($admin)->getJson('/admin/responden?q=Responden+Uji+Penginput')->json('data'))->pluck('id');
        $this->assertNotContains($row->id, $ids);

        // Resume: kolom & filter penginput, detail
        $this->sebagai($admin)->get('/rekapTotal?jenkel=1&usia=1&pekerjaan=1&pendidikan=1&Tahun=2026&penginput=' . $s->id)
            ->assertOk()->assertSee('Responden Uji Penginput')->assertSee('DIINPUT');
        $this->sebagai($admin)->get('/rekapTotal?Tahun=2026&penginput=survei')->assertOk()->assertDontSee('Responden Uji Penginput');
        $this->sebagai($admin)->get(route('rekapTotal.show', $row->id))->assertOk()->assertSee('Budi Surveyor · Input Responden');

        // Survei online -> sumber "survei" tanpa akun
        $this->post('/skm', array_diff_key($payload, ['client_uuid' => 1, 'tanggal' => 1]) + ['desa_id' => $desa])->assertRedirect('/terimakasih');
        $this->assertSame('survei', DB::table('survey_responses')->where('nama', 'Responden Uji Penginput')->whereNull('input_oleh')->value('sumber_input'));
    }

    public function test_filament_hanya_admin(): void
    {
        $panel = app(\Filament\Panel::class);
        $this->assertTrue($this->akun('admin')->canAccessPanel($panel));
        $this->assertFalse($this->akun('surveyor')->canAccessPanel($panel));
        $this->assertFalse($this->akun('admin', ['is_active' => false])->canAccessPanel($panel));
    }
}
