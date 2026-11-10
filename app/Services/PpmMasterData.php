<?php

namespace App\Services;

use App\Support\PpmCatalog;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Impor/ekspor master data PPM (format klasifikasi_ppm_bayan.json).
 *
 * Impor mengganti isi master, tetapi ID yang sudah ada dipertahankan
 * (desa dicocokkan lewat nama, pilar/program/rincian lewat ID-nya), sehingga
 * penilaian yang sudah tersimpan tetap terhubung ke desa & programnya.
 */
class PpmMasterData
{
    /** Kode pendek bawaan tiap pilar (dipakai juga sebagai survey_responses.jenisPelayanan). */
    public const PILAR_KODE = [
        'P1' => 'pendidikan',
        'P2' => 'kesehatan',
        'P3' => 'pendapatan',
        'P4' => 'kemandirian',
        'P5' => 'sosial',
        'P6' => 'lingkungan',
        'P7' => 'kelembagaan',
        'P8' => 'infrastruktur',
    ];

    /** Isian lokasi_lain yang sebenarnya nama desa. */
    private const DESA_ALIAS = ['tj. pagar' => 'Tanjung Pagar'];

    /** Isian lokasi_lain yang berarti seluruh desa binaan. */
    private const SEMUA_DESA = ['desa binaan'];

    public function importFile(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("File master PPM tidak ditemukan: {$path}");
        }

        return $this->import(json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR));
    }

    /** @return array ringkasan jumlah data */
    public function import(array $data): array
    {
        foreach (['perusahaan', 'desa', 'pilar'] as $key) {
            if (! isset($data[$key]) || ! is_array($data[$key])) {
                throw new RuntimeException("Format JSON tidak sesuai: bagian \"{$key}\" tidak ada.");
            }
        }

        $now = now();
        $summary = ['desa' => 0, 'perusahaan' => 0, 'pilar' => 0, 'program' => 0, 'rincian' => 0, 'lokasi' => 0];

        DB::transaction(function () use ($data, $now, &$summary) {
            // Perusahaan: upsert per kode
            $kodeAktif = [];
            foreach ($data['perusahaan'] as $i => $p) {
                $kode = strtoupper(trim($p['kode']));
                DB::table('ppm_perusahaan')->updateOrInsert(['kode' => $kode], [
                    'nama' => $p['nama'],
                    'is_grup' => $kode === PpmCatalog::GRUP,
                    'urutan' => $i,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]);
                $kodeAktif[] = $kode;
            }
            DB::table('ppm_perusahaan')->whereNotIn('kode', $kodeAktif)->delete();
            $summary['perusahaan'] = count($kodeAktif);

            // Desa: cocokkan lewat nama agar ID lama tetap; desa yang tidak ada di JSON dinonaktifkan
            $desaIds = [];
            foreach ($data['desa'] as $i => $nama) {
                $nama = trim(is_array($nama) ? ($nama['nama'] ?? '') : $nama);
                if ($nama === '') {
                    continue;
                }
                $id = DB::table('ppm_desa')->where('nama', $nama)->value('id');
                if ($id) {
                    DB::table('ppm_desa')->where('id', $id)->update(['urutan' => $i, 'is_active' => true, 'updated_at' => $now]);
                } else {
                    $id = DB::table('ppm_desa')->insertGetId(['nama' => $nama, 'urutan' => $i, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
                }
                $desaIds[$nama] = (int) $id;
            }
            DB::table('ppm_desa')->whereNotIn('id', array_values($desaIds) ?: [0])->update(['is_active' => false, 'updated_at' => $now]);
            $summary['desa'] = count($desaIds);

            // Pilar, program, rincian: ganti isi, ID dari JSON dipertahankan
            DB::table('ppm_rincian_lokasi')->delete();
            DB::table('ppm_rincian')->delete();
            DB::table('ppm_program')->delete();
            DB::table('ppm_pilar')->delete();

            foreach ($data['pilar'] as $pi => $pilar) {
                DB::table('ppm_pilar')->insert([
                    'id' => $pilar['id'],
                    'nama' => $pilar['nama'],
                    'kode' => $pilar['kode'] ?? (self::PILAR_KODE[$pilar['id']] ?? strtolower($pilar['id'])),
                    'urutan' => $pi,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $summary['pilar']++;

                foreach ($pilar['program'] ?? [] as $gi => $program) {
                    DB::table('ppm_program')->insert([
                        'id' => $program['id'],
                        'pilar_id' => $pilar['id'],
                        'nama' => $program['nama'],
                        'jumlah_dalam_kurung' => $program['jumlah_dalam_kurung'] ?? null,
                        'urutan' => $gi,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $summary['program']++;

                    foreach ($program['rincian'] ?? [] as $ri => $rincian) {
                        $penilai = array_values(array_map('strtoupper', $rincian['penilai'] ?? []));
                        DB::table('ppm_rincian')->insert([
                            'id' => $rincian['id'],
                            'program_id' => $program['id'],
                            'nama' => $rincian['nama'],
                            'penilai' => implode(',', $penilai),
                            'dinilai_sebagai_grup' => (bool) ($rincian['dinilai_sebagai_grup'] ?? false),
                            'catatan' => $rincian['catatan'] ?? null,
                            'is_active' => array_key_exists('aktif', $rincian) ? (bool) $rincian['aktif'] : count($penilai) > 0,
                            'urutan' => $ri,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                        $summary['rincian']++;

                        $rows = $this->lokasiRows($rincian['id'], $rincian['lokasi_per_perusahaan'] ?? [], $desaIds);
                        if ($rows) {
                            DB::table('ppm_rincian_lokasi')->insert($rows);
                            $summary['lokasi'] += count($rows);
                        }
                    }
                }
            }
        });

        PpmCatalog::flush();

        return $summary;
    }

    /** Master data saat ini dalam format JSON yang sama dengan file impor. */
    public function export(): array
    {
        $desa = DB::table('ppm_desa')->orderBy('urutan')->orderBy('nama')->get();
        $desaNama = $desa->pluck('nama', 'id')->all();

        $lokasi = [];
        foreach (DB::table('ppm_rincian_lokasi')->orderBy('id')->get() as $row) {
            $entry = &$lokasi[$row->rincian_id][$row->perusahaan_kode];
            $entry ??= ['desa' => [], 'lokasi_lain' => []];
            if ($row->desa_id && isset($desaNama[$row->desa_id])) {
                $entry['desa'][] = $desaNama[$row->desa_id];
            } elseif ($row->lokasi_lain) {
                $entry['lokasi_lain'][] = $row->lokasi_lain;
            }
            unset($entry);
        }

        $programs = DB::table('ppm_program')->orderBy('urutan')->get()->groupBy('pilar_id');
        $rincians = DB::table('ppm_rincian')->orderBy('urutan')->get()->groupBy('program_id');

        $pilar = [];
        foreach (DB::table('ppm_pilar')->orderBy('urutan')->get() as $p) {
            $pilar[] = [
                'id' => $p->id,
                'nama' => $p->nama,
                'kode' => $p->kode,
                'program' => ($programs[$p->id] ?? collect())->map(fn ($g) => [
                    'id' => $g->id,
                    'nama' => $g->nama,
                    'jumlah_dalam_kurung' => $g->jumlah_dalam_kurung,
                    'rincian' => ($rincians[$g->id] ?? collect())->map(fn ($r) => [
                        'id' => $r->id,
                        'nama' => $r->nama,
                        'penilai' => array_values(array_filter(explode(',', (string) $r->penilai))),
                        'dinilai_sebagai_grup' => (bool) $r->dinilai_sebagai_grup,
                        'catatan' => $r->catatan,
                        'aktif' => (bool) $r->is_active,
                        'lokasi_per_perusahaan' => (object) ($lokasi[$r->id] ?? []),
                    ])->values()->all(),
                ])->values()->all(),
            ];
        }

        return [
            'sumber' => 'Ekspor dari menu Master Data PPM, ' . now()->format('d-m-Y H:i'),
            'perusahaan' => DB::table('ppm_perusahaan')->orderBy('urutan')->get(['kode', 'nama'])->map(fn ($p) => (array) $p)->all(),
            'desa' => $desa->where('is_active', true)->pluck('nama')->values()->all(),
            'pilar' => $pilar,
        ];
    }

    private function lokasiRows(string $rincianId, array $lokasi, array $desaIds): array
    {
        $rows = [];
        $add = function (string $kode, ?int $desaId, ?string $lain) use (&$rows, $rincianId) {
            // Satu baris per (perusahaan, desa) atau per (perusahaan, lokasi lain)
            $key = $kode . '|' . ($desaId ?: 'x:' . $lain);
            $rows[$key] ??= ['rincian_id' => $rincianId, 'perusahaan_kode' => $kode, 'desa_id' => $desaId, 'lokasi_lain' => $lain];
        };

        foreach ($lokasi as $kode => $lok) {
            $kode = strtoupper($kode);
            foreach ($lok['desa'] ?? [] as $nama) {
                if (! isset($desaIds[$nama])) {
                    throw new RuntimeException("Desa \"{$nama}\" pada rincian {$rincianId} tidak ada di daftar desa.");
                }
                $add($kode, $desaIds[$nama], null);
            }
            foreach ($lok['lokasi_lain'] ?? [] as $lain) {
                $key = strtolower(trim($lain));
                if (isset(self::DESA_ALIAS[$key]) && isset($desaIds[self::DESA_ALIAS[$key]])) {
                    $add($kode, $desaIds[self::DESA_ALIAS[$key]], null);
                } elseif (in_array($key, self::SEMUA_DESA, true)) {
                    foreach ($desaIds as $desaId) {
                        $add($kode, $desaId, $lain);
                    }
                } else {
                    // Ring 2/3, kecamatan: tidak terikat desa tertentu
                    $add($kode, null, $lain);
                }
            }
        }

        return array_values($rows);
    }
}
