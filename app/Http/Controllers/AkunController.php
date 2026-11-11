<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Akses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Menu "Akun Pengguna" (khusus admin): kelola akun admin & surveyor.
 *
 * Pengaman: admin tidak bisa menonaktifkan, menurunkan role, atau menghapus
 * akunnya sendiri, dan harus selalu ada minimal satu admin aktif.
 * Akun yang sudah pernah menginput data tidak dihapus permanen, cukup dinonaktifkan,
 * agar keterangan "diinput oleh" tetap terbaca.
 */
class AkunController extends Controller
{
    public function index()
    {
        $akun = User::query()->orderByRaw("CASE WHEN keterangan IN ('admin','superadmin') THEN 0 ELSE 1 END")->orderBy('name')->get();
        $jumlahInput = DB::table('survey_responses')->whereNotNull('input_oleh')
            ->selectRaw('input_oleh, COUNT(*) as n, MAX(created_at) as terakhir')->groupBy('input_oleh')->get()->keyBy('input_oleh');

        return view('akun.index', [
            'akun' => $akun,
            'jumlahInput' => $jumlahInput,
            'roles' => Akses::ROLES,
            'saya' => Akses::userId(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);

        $user = new User();
        $user->forceFill([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $this->emailFor($data['username']),
            'keterangan' => $data['keterangan'],
            'is_active' => true,
            'password' => Hash::make($data['password']),
        ])->save();

        return back()->with('success', "Akun {$user->name} ({$data['username']}) dibuat sebagai " . Akses::roleLabel($data['keterangan']) . '.');
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $data = $this->validated($request, $user);
        $aktif = $request->boolean('is_active');

        if ($user->id === Akses::userId()) {
            if (! $aktif) {
                return back()->with('error', 'Anda tidak bisa menonaktifkan akun Anda sendiri.');
            }
            if (! Akses::isAdmin($data['keterangan'])) {
                return back()->with('error', 'Anda tidak bisa mengubah role akun Anda sendiri menjadi surveyor.');
            }
        }
        if ($this->adminTerakhir($user) && (! $aktif || ! Akses::isAdmin($data['keterangan']))) {
            return back()->with('error', 'Harus ada minimal satu admin aktif. Buat atau aktifkan admin lain terlebih dahulu.');
        }

        $user->forceFill([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $user->email ?: $this->emailFor($data['username']),
            'keterangan' => $data['keterangan'],
            'is_active' => $aktif,
        ]);
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return back()->with('success', "Akun {$user->name} diperbarui." . (! empty($data['password']) ? ' Password baru sudah berlaku.' : ''));
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Akses::userId()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }
        if ($this->adminTerakhir($user)) {
            return back()->with('error', 'Harus ada minimal satu admin aktif.');
        }
        if (DB::table('survey_responses')->where('input_oleh', $user->id)->exists()) {
            return back()->with('error', "Akun {$user->name} sudah pernah menginput data, jadi tidak dihapus agar keterangan penginput tetap tercatat. Nonaktifkan saja akun ini.");
        }

        $user->delete();

        return back()->with('success', "Akun {$user->name} dihapus.");
    }

    private function validated(Request $request, ?User $user): array
    {
        $request->merge(['username' => strtolower(trim((string) $request->input('username')))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-z0-9._@-]+$/', Rule::unique('users', 'username')->ignore($user?->id)],
            'keterangan' => ['required', Rule::in(array_keys(Akses::ROLES))],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ], [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute sudah dipakai akun lain.',
            'min' => ':attribute minimal :min karakter.',
            'max' => ':attribute maksimal :max karakter.',
            'regex' => ':attribute hanya boleh huruf kecil, angka, titik, garis bawah, strip, atau @.',
            'confirmed' => 'Konfirmasi password tidak sama.',
            'in' => ':attribute tidak valid.',
        ], ['name' => 'Nama', 'username' => 'Username', 'keterangan' => 'Role', 'password' => 'Password']);
    }

    /** True bila $user satu-satunya admin aktif. */
    private function adminTerakhir(User $user): bool
    {
        if (! Akses::isAdmin($user->keterangan) || ! $user->is_active) {
            return false;
        }

        return ! User::query()->where('id', '!=', $user->id)->whereIn('keterangan', ['admin', 'superadmin'])->where('is_active', true)->exists();
    }

    /** Kolom email wajib unik di tabel users; login memakai username. */
    private function emailFor(string $username): string
    {
        $email = str_contains($username, '@') ? $username : $username . '@akun.eskm.local';

        return User::where('email', $email)->exists() ? $username . '.' . uniqid() . '@akun.eskm.local' : $email;
    }
}
