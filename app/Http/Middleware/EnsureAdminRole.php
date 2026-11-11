<?php

namespace App\Http\Middleware;

use App\Support\Akses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fitur khusus admin (Master Data PPM, Akun Pengguna). Surveyor diarahkan
 * kembali ke halaman Resume dengan pesan.
 */
class EnsureAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Akses::isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Fitur ini hanya untuk admin.'], 403);
            }

            return redirect('/rekapTotal?jenkel=1&usia=1&pekerjaan=1&pendidikan=1')
                ->with('akses_ditolak', 'Menu tersebut hanya dapat dibuka oleh akun Admin.');
        }

        return $next($request);
    }
}
