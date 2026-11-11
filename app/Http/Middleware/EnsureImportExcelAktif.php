<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fitur Import Excel bisa dimatikan untuk semua akun lewat config('skm.import_excel')
 * (SKM_IMPORT_EXCEL di .env). Saat mati, halamannya tidak bisa dibuka meski URL diketik langsung.
 */
class EnsureImportExcelAktif
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('skm.import_excel')) {
            return redirect('/rekapTotal?jenkel=1&usia=1&pekerjaan=1&pendidikan=1')
                ->with('akses_ditolak', 'Fitur Import Excel sedang tidak diaktifkan. Gunakan menu Input Responden.');
        }

        return $next($request);
    }
}
