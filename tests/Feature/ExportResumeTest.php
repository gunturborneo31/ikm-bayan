<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Unduh Excel Resume / rekap tanpa parameter kolom tidak boleh error. */
class ExportResumeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unduh_resume_tanpa_parameter_kolom(): void
    {
        $res = $this->withSession(['user_id' => 1, 'keterangan' => 'admin'])
            ->get('/exports/download?type=resume&Tahun=2026');
        $res->assertOk();
        $this->assertStringContainsString('spreadsheetml', (string) $res->headers->get('content-type'));
    }

    public function test_unduh_resume_dengan_kolom_dan_rekap_lain(): void
    {
        $this->withSession(['user_id' => 1, 'keterangan' => 'admin']);
        $this->get('/exports/download?type=resume&Tahun=2026&jenkel=0&usia=1&pekerjaan=0&pendidikan=1')->assertOk();
        foreach (['jenkel', 'usia', 'pekerjaan', 'pendidikan'] as $type) {
            $this->get("/exports/download?type={$type}&Tahun=2026")->assertOk();
        }
    }

    public function test_halaman_resume_tanpa_parameter_kolom(): void
    {
        $this->withSession(['user_id' => 1, 'keterangan' => 'admin'])->get('/rekapTotal?Tahun=2026')->assertOk();
    }
}
