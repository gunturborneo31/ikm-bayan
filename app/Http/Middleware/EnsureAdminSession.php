<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSession
{
    /**
     * Ensure admin pages are only accessible after successful login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('user_id')) {
            return redirect('/login')->withErrors([
                'username' => 'Silakan login terlebih dahulu.',
            ]);
        }

        // Akun yang dihapus/dinonaktifkan langsung keluar; perubahan role langsung berlaku.
        $user = \Illuminate\Support\Facades\DB::table('users')->where('id', $request->session()->get('user_id'))->first();
        if (! $user || ! ($user->is_active ?? true)) {
            $request->session()->forget(['user_id', 'username', 'keterangan', 'nama']);

            return redirect('/login')->withErrors([
                'username' => 'Akun Anda tidak aktif. Hubungi admin.',
            ]);
        }
        $request->session()->put('keterangan', $user->keterangan ?? 'admin');
        $request->session()->put('nama', $user->name);

        return $next($request);
    }
}
