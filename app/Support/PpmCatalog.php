<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Master data PPM Bayan dan aturan atribusi perusahaan.
 *
 * Alur survei: desa -> program (dikelompokkan per pilar) -> rincian -> perusahaan.
 * Hanya rincian aktif yang punya pelaksana di desa terpilih yang ditampilkan.
 */
class PpmCatalog
{
    public const TIDAK_TAHU = 'TIDAK_TAHU';
    public const GRUP = 'BAYAN';

    public const SUMBER_JAWABAN = 'jawaban_responden';
    public const SUMBER_SISTEM = 'asumsi_sistem';

    public const SUMBER_LABEL = [
        self::SUMBER_JAWABAN => 'Jawaban responden',
        self::SUMBER_SISTEM => 'Asumsi sistem',
    ];

    private static ?array $cache = null;

    public static function isReady(): bool
    {
        return Schema::hasTable('ppm_rincian') && DB::table('ppm_rincian')->exists();
    }

    /** [desa_id => nama] */
    public static function desa(): array
    {
        return self::load()['desa'];
    }

    /** [kode => nama], termasuk BAYAN */
    public static function perusahaan(): array
    {
        return self::load()['perusahaan'];
    }

    /** [kode => nama] anak perusahaan saja (tanpa BAYAN) */
    public static function anakPerusahaan(): array
    {
        return array_diff_key(self::perusahaan(), [self::GRUP => true]);
    }

    /** [pilar_id => ['nama', 'kode']] */
    public static function pilar(): array
    {
        return self::load()['pilar'];
    }

    /** [program_id => ['nama', 'pilar_id']] */
    public static function program(): array
    {
        return self::load()['program'];
    }

    /** [rincian_id => ['nama', 'program_id', 'grup', 'penilai' => [], 'pelaksana' => [desa_id => [kode]]]] */
    public static function rincian(): array
    {
        return self::load()['rincian'];
    }

    /** Kode perusahaan pelaksana rincian di desa tersebut (pilihan untuk responden). */
    public static function pelaksana(string $rincianId, int $desaId): array
    {
        return self::rincian()[$rincianId]['pelaksana'][$desaId] ?? [];
    }

    /**
     * Data ringkas untuk halaman survei (dipakai JavaScript untuk menyaring pilihan).
     */
    public static function forSurvey(): array
    {
        $data = self::load();

        return [
            'desa' => $data['desa'],
            'perusahaan' => $data['perusahaan'],
            'pilar' => array_map(fn ($p) => $p['nama'], $data['pilar']),
            'program' => array_map(fn ($p) => ['nama' => $p['nama'], 'pilar' => $p['pilar_id']], $data['program']),
            'rincian' => array_map(fn ($r) => [
                'nama' => $r['nama'],
                'program' => $r['program_id'],
                'pelaksana' => $r['pelaksana'],
            ], $data['rincian']),
        ];
    }

    /**
     * Tentukan perusahaan yang dinilai.
     *
     * - Rincian "dinilai sebagai grup" (Beasiswa, 4 program Kesehatan) -> selalu BAYAN.
     * - Responden memilih perusahaan -> perusahaan itu.
     * - Responden "Tidak tahu" -> satu-satunya pelaksana di desa itu,
     *   atau BAYAN (gabungan) bila pelaksananya lebih dari satu.
     *
     * @return array{perusahaan_dinilai: string, sumber_atribusi: string, perusahaan_opsi: string}|null
     *         null bila rincian tidak berjalan di desa itu atau jawaban tidak termasuk pilihan.
     */
    public static function attribute(string $rincianId, int $desaId, string $jawaban): ?array
    {
        $rincian = self::rincian()[$rincianId] ?? null;
        $opsi = self::pelaksana($rincianId, $desaId);

        if (! $rincian || $opsi === []) {
            return null;
        }
        if ($jawaban !== self::TIDAK_TAHU && ! in_array($jawaban, $opsi, true)) {
            return null;
        }

        if ($rincian['grup']) {
            [$dinilai, $sumber] = [self::GRUP, self::SUMBER_SISTEM];
        } elseif ($jawaban !== self::TIDAK_TAHU) {
            [$dinilai, $sumber] = [$jawaban, self::SUMBER_JAWABAN];
        } elseif (count($opsi) === 1) {
            [$dinilai, $sumber] = [$opsi[0], self::SUMBER_SISTEM];
        } else {
            [$dinilai, $sumber] = [self::GRUP, self::SUMBER_SISTEM];
        }

        return [
            'perusahaan_dinilai' => $dinilai,
            'sumber_atribusi' => $sumber,
            'perusahaan_opsi' => implode(',', $opsi),
        ];
    }

    public static function perusahaanLabel(?string $kode): string
    {
        if ($kode === self::TIDAK_TAHU) {
            return 'Tidak tahu';
        }

        return self::perusahaan()[$kode] ?? ($kode ?: '-');
    }

    /** Hapus cache (dipakai setelah seeding ulang di proses yang sama). */
    public static function flush(): void
    {
        self::$cache = null;
    }

    private static function load(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $empty = ['desa' => [], 'perusahaan' => [], 'pilar' => [], 'program' => [], 'rincian' => []];
        if (! Schema::hasTable('ppm_rincian')) {
            return self::$cache = $empty;
        }

        $desa = DB::table('ppm_desa')->where('is_active', true)->orderBy('urutan')->pluck('nama', 'id')->all();

        $perusahaan = DB::table('ppm_perusahaan')->orderBy('urutan')->pluck('nama', 'kode')->all();

        $pilar = [];
        foreach (DB::table('ppm_pilar')->orderBy('urutan')->get() as $row) {
            $pilar[$row->id] = ['nama' => $row->nama, 'kode' => $row->kode];
        }

        $program = [];
        foreach (DB::table('ppm_program')->orderBy('pilar_id')->orderBy('urutan')->get() as $row) {
            $program[$row->id] = ['nama' => $row->nama, 'pilar_id' => $row->pilar_id];
        }
        // Urutkan program mengikuti urutan pilar
        $pilarOrder = array_flip(array_keys($pilar));
        uksort($program, fn ($a, $b) => [$pilarOrder[$program[$a]['pilar_id']] ?? 99, $a] <=> [$pilarOrder[$program[$b]['pilar_id']] ?? 99, $b]);

        $pelaksana = [];
        $lokasi = DB::table('ppm_rincian_lokasi')->whereNotNull('desa_id')->get(['rincian_id', 'perusahaan_kode', 'desa_id']);
        $kodeOrder = array_flip(array_keys($perusahaan));
        foreach ($lokasi as $row) {
            if (! isset($desa[$row->desa_id])) {
                continue;
            }
            $pelaksana[$row->rincian_id][$row->desa_id][$row->perusahaan_kode] = true;
        }

        $rincian = [];
        $rows = DB::table('ppm_rincian')->where('is_active', true)->orderBy('program_id')->orderBy('urutan')->get();
        foreach ($rows as $row) {
            if (! isset($program[$row->program_id])) {
                continue;
            }
            $byDesa = [];
            foreach ($pelaksana[$row->id] ?? [] as $desaId => $kodes) {
                $list = array_keys($kodes);
                usort($list, fn ($a, $b) => ($kodeOrder[$a] ?? 99) <=> ($kodeOrder[$b] ?? 99));
                $byDesa[$desaId] = $list;
            }

            $rincian[$row->id] = [
                'nama' => $row->nama,
                'program_id' => $row->program_id,
                'grup' => (bool) $row->dinilai_sebagai_grup,
                'penilai' => array_values(array_filter(explode(',', (string) $row->penilai))),
                'pelaksana' => $byDesa,
            ];
        }

        return self::$cache = compact('desa', 'perusahaan', 'pilar', 'program', 'rincian');
    }
}
