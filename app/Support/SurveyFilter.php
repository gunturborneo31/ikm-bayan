<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Filter laporan yang dipakai semua halaman: Tahun + Pilar.
 * Sistem penilaian bersifat tahunan, jadi tidak ada filter triwulan/bulan.
 *
 * Parameter URL: ?Tahun=2026&bagian=pendidikan,kesehatan
 * - Tahun kosong/tidak valid -> tahun berjalan (atau tahun terakhir yang ada datanya).
 * - bagian kosong -> semua pilar (data lama tanpa pilar ikut terhitung).
 */
class SurveyFilter
{
    /** Tahun yang punya data, terbaru dulu; tahun berjalan selalu ada. */
    public static function years(): array
    {
        return DB::table('survey_responses')->whereNotNull('tahun')->distinct()->pluck('tahun')
            ->map(fn ($y) => (int) $y)->push((int) date('Y'))->unique()->sortDesc()->values()->all();
    }

    public static function year(): int
    {
        $requested = (int) request('Tahun');
        $years = self::years();

        if (in_array($requested, $years, true)) {
            return $requested;
        }

        $current = (int) date('Y');
        $hasCurrent = DB::table('survey_responses')->where('tahun', $current)->exists();

        return $hasCurrent ? $current : ($years[0] ?? $current);
    }

    /** Pilar yang boleh dilihat admin/pengunjung ini [kode => nama]. */
    public static function pilarOptions(): array
    {
        return BagianOptions::visibleOptionsForRole(session('keterangan'));
    }

    /** Kode pilar terpilih (urutan mengikuti master). */
    public static function selectedPilar(): array
    {
        $options = self::pilarOptions();
        $requested = array_filter(array_map('trim', explode(',', (string) request('bagian'))));
        $selected = array_values(array_intersect(array_keys($options), $requested));

        return $selected ?: array_keys($options);
    }

    /** True bila semua pilar dipilih: tidak perlu menyaring jenisPelayanan. */
    public static function isAllPilar(): bool
    {
        return count(self::selectedPilar()) >= count(BagianOptions::codes());
    }

    public static function pilarCsv(): string
    {
        return implode(',', self::selectedPilar());
    }

    /**
     * Pilihan filter perusahaan [kode => label]. Disaring berdasarkan perusahaan yang
     * DINILAI (hasil atribusi), sama dengan dasar SKM per anak perusahaan.
     */
    public static function perusahaanOptions(): array
    {
        $options = [];
        foreach (PpmCatalog::perusahaan() as $kode => $nama) {
            $options[$kode] = $kode === PpmCatalog::GRUP ? 'BAYAN (gabungan)' : "{$kode} · {$nama}";
        }

        return $options;
    }

    /** Kode perusahaan terpilih, atau null = semua perusahaan. */
    public static function selectedPerusahaan(): ?string
    {
        $kode = strtoupper(trim((string) request('perusahaan')));

        return array_key_exists($kode, self::perusahaanOptions()) ? $kode : null;
    }

    /** Terapkan filter Tahun + Pilar + Perusahaan ke query survey_responses. */
    public static function apply(Builder $query, string $table = 'survey_responses'): Builder
    {
        $query->where("{$table}.tahun", self::year());

        if (! self::isAllPilar()) {
            $query->whereIn("{$table}.jenisPelayanan", self::selectedPilar());
        }

        if ($perusahaan = self::selectedPerusahaan()) {
            $query->where("{$table}.perusahaan_dinilai", $perusahaan);
        }

        return $query;
    }

    public static function query(): Builder
    {
        return self::apply(DB::table('survey_responses'));
    }

    /**
     * Satu baris per responden (untuk statistik demografi). Responden yang menilai
     * beberapa program punya responden_uuid sama; data lama tanpa uuid dihitung per baris.
     */
    public static function respondenQuery(): Builder
    {
        $firstRowIds = self::query()->selectRaw('MIN(id)')->groupByRaw("COALESCE(responden_uuid, CONCAT('row-', id))");

        return DB::table('survey_responses')->whereIn('id', $firstRowIds);
    }

    /** [['label' => ..., 'total' => ...]] per jenis responden. */
    public static function jenisRespondenData(Builder $respondenQuery): array
    {
        $counts = (clone $respondenQuery)->selectRaw('jenis_responden as k, COUNT(*) as n')
            ->groupBy('jenis_responden')->pluck('n', 'k')->all();

        $data = [];
        foreach (SurveyOptions::JENIS_RESPONDEN as $jenis) {
            $data[] = ['label' => $jenis, 'total' => (int) ($counts[$jenis] ?? 0)];
        }
        $tanpa = (int) ($counts[''] ?? 0);
        if ($tanpa > 0) {
            $data[] = ['label' => 'Belum diisi (data lama)', 'total' => $tanpa];
        }

        return $data;
    }

    /** Query string filter aktif, untuk link antar halaman. */
    public static function queryString(array $extra = []): string
    {
        $params = ['Tahun' => self::year()];
        if (! self::isAllPilar()) {
            $params['bagian'] = self::pilarCsv();
        }
        if ($perusahaan = self::selectedPerusahaan()) {
            $params['perusahaan'] = $perusahaan;
        }

        return http_build_query($params + $extra);
    }
}
