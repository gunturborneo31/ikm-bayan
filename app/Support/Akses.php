<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Hak akses akun panel admin.
 *
 * - admin    : semua fitur, termasuk Master Data PPM dan Akun Pengguna.
 * - surveyor : semua fitur kecuali Master Data PPM dan Akun Pengguna.
 */
class Akses
{
    public const ADMIN = 'admin';
    public const SURVEYOR = 'surveyor';

    /** Role yang bisa dipilih di menu Akun Pengguna. */
    public const ROLES = [
        self::ADMIN => 'Admin',
        self::SURVEYOR => 'Surveyor',
    ];

    /** Role lama yang juga dianggap admin penuh. */
    private const ROLE_ADMIN = ['admin', 'superadmin'];

    public const SUMBER = [
        'survei' => 'Survei online',
        'input_admin' => 'Input Responden',
        'import_excel' => 'Import Excel',
        'dummy' => 'Data dummy',
    ];

    public static function isAdmin(?string $role = null): bool
    {
        return in_array($role ?? session('keterangan'), self::ROLE_ADMIN, true);
    }

    public static function roleLabel(?string $role): string
    {
        return self::ROLES[$role] ?? ($role ? ucfirst($role) : '-');
    }

    public static function userId(): ?int
    {
        return session('user_id') ? (int) session('user_id') : null;
    }

    /** [user_id => nama] semua akun, untuk label "diinput oleh". */
    private static ?array $namaCache = null;

    public static function namaAkun(?int $perlu = null): array
    {
        // Muat ulang bila id yang dicari belum ada (mis. akun baru dibuat setelah daftar dimuat)
        if (self::$namaCache === null || ($perlu && ! isset(self::$namaCache[$perlu]))) {
            self::$namaCache = DB::table('users')->pluck('name', 'id')->all();
        }

        return self::$namaCache;
    }

    public static function penginputLabel(?int $userId, ?string $sumber): string
    {
        $nama = $userId ? (self::namaAkun($userId)[$userId] ?? 'Akun #' . $userId . ' (dihapus)') : null;
        $sumberLabel = self::SUMBER[$sumber] ?? null;

        return match (true) {
            $nama && $sumberLabel => "{$nama} · {$sumberLabel}",
            (bool) $nama => $nama,
            $sumber === 'survei' => 'Responden (survei online)',
            (bool) $sumberLabel => $sumberLabel,
            default => '-',
        };
    }
}
