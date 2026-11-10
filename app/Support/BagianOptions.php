<?php

namespace App\Support;

use App\Models\BagianLayanan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BagianOptions
{
    public static function legacyCodeNameMap(): array
    {
        return [
            'pendidikan' => 'Pendidikan',
            'kesehatan' => 'Kesehatan',
            'pendapatan' => 'Tingkat Pendapatan Riil/Pekerjaan',
            'kemandirian' => 'Kemandirian Ekonomi',
            'sosial' => 'Sosial Budaya',
            'infrastruktur' => 'Infrastruktur',
            'kelembagaan' => 'Kelembagaan',
            'lingkungan' => 'Lingkungan',
        ];
    }

    public static function legacyIdNameMap(): array
    {
        return array_combine(
            range(1, count(self::legacyCodeNameMap())),
            array_values(self::legacyCodeNameMap())
        );
    }

    /**
     * Pilar PPM [kode => nama]. Sumber utama: master PPM (ppm_pilar), karena kode
     * inilah yang disimpan survei di survey_responses.jenisPelayanan.
     */
    public static function codeNameMap(): array
    {
        static $ppm = null;
        if ($ppm === null) {
            $ppm = Schema::hasTable('ppm_pilar')
                ? \Illuminate\Support\Facades\DB::table('ppm_pilar')->orderBy('urutan')->pluck('nama', 'kode')
                    ->map(fn ($nama) => str_replace(' Ppm', ' PPM', Str::title(Str::lower($nama))))->all()
                : [];
        }
        if (! empty($ppm)) {
            return $ppm;
        }

        if (Schema::hasTable('bagian_layanan')) {
            $rows = BagianLayanan::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->get(['nama', 'kode']);

            if ($rows->isNotEmpty()) {
                $mapped = [];

                foreach ($rows as $row) {
                    $code = self::normalizeCode($row->kode ?: Str::slug($row->nama));
                    if ($code === '') {
                        continue;
                    }

                    $mapped[$code] = $row->nama;
                }

                if (!empty($mapped)) {
                    return $mapped;
                }
            }
        }

        return self::legacyCodeNameMap();
    }

    public static function idNameMap(): array
    {
        if (Schema::hasTable('bagian_layanan')) {
            $rows = BagianLayanan::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->pluck('nama', 'id')
                ->toArray();

            if (!empty($rows)) {
                return $rows;
            }
        }

        return self::legacyIdNameMap();
    }

    public static function codes(): array
    {
        return array_keys(self::codeNameMap());
    }

    public static function allCodesCsv(): string
    {
        return implode(',', self::codes());
    }

    public static function normalizeCode(?string $code): string
    {
        $normalized = strtolower(trim((string) $code));

        return match ($normalized) {
            'barjas' => 'pbj',
            'ortal' => 'organisasi',
            default => $normalized,
        };
    }

    public static function codesForRole(?string $role): array
    {
        $availableCodes = self::codes();
        $normalizedRole = self::normalizeCode($role);

        $roleGroups = [
            'asisten1' => ['pemerintahan', 'hukum', 'kesra'],
            'asisten2' => ['adbang', 'pbj', 'ekosda'],
            'asisten3' => ['organisasi', 'umum', 'prokopim'],
        ];

        if (in_array($normalizedRole, ['', 'admin', 'superadmin'], true)) {
            return $availableCodes;
        }

        if ($role === 'ortal') {
            return $availableCodes;
        }

        if (array_key_exists($role, $roleGroups)) {
            return array_values(array_intersect($roleGroups[$role], $availableCodes));
        }

        if (in_array($normalizedRole, $availableCodes, true)) {
            return [$normalizedRole];
        }

        return $availableCodes;
    }

    public static function csvForRole(?string $role): string
    {
        return implode(',', self::codesForRole($role));
    }

    public static function visibleOptionsForRole(?string $role): array
    {
        $allOptions = self::codeNameMap();
        $visibleCodes = self::codesForRole($role);

        return array_filter(
            $allOptions,
            fn (string $code) => in_array($code, $visibleCodes, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    public static function labelForCode(?string $code): string
    {
        $normalized = self::normalizeCode($code);

        if ($normalized === 'setkab') {
            return 'Sekretariat Pemkab Mahulu';
        }

        return self::codeNameMap()[$normalized] ?? '-';
    }
}