<?php

namespace App\Http\Controllers;

use App\Support\PpmCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Laporan SKM PPM Bayan.
 *
 * - SKM Bayan (grup): semua penilaian.
 * - SKM per anak perusahaan: hanya penilaian dengan perusahaan_dinilai = FSP/BT/TA/TJ.
 *   Penilaian BAYAN (gabungan) tidak dibebankan ke anak perusahaan.
 * - Per desa, pilar, program, rincian: semua penilaian.
 * - Persentase "tidak tahu" per perusahaan: dari penilaian di mana perusahaan
 *   itu muncul sebagai pilihan, berapa persen responden menjawab "Tidak tahu".
 */
class RekapPpmController extends Controller
{
    private const SKOR = 'COALESCE(u1,0)+COALESCE(u2,0)+COALESCE(u3,0)+COALESCE(u4,0)+COALESCE(u5,0)+COALESCE(u6,0)+COALESCE(u7,0)+COALESCE(u8,0)+COALESCE(u9,0)';

    public function index(Request $request)
    {
        $years = DB::table('survey_responses')->whereNotNull('rincian_id')
            ->distinct()->orderByDesc('tahun')->pluck('tahun')->map(fn ($y) => (int) $y)->all();

        // Parameter "Tahun" sama dengan halaman laporan lain; "semua" = seluruh tahun.
        $tahun = $request->query('Tahun', $years[0] ?? (int) date('Y'));
        $tahun = $tahun === 'semua' ? 'semua' : (int) $tahun;

        $base = fn () => DB::table('survey_responses')
            ->whereNotNull('rincian_id')
            ->when($tahun !== 'semua', fn ($q) => $q->where('tahun', $tahun));

        $grup = $this->skm($base()->selectRaw($this->aggregate())->first());

        $perusahaanRows = $base()->selectRaw('perusahaan_dinilai as k, ' . $this->aggregate())->groupBy('perusahaan_dinilai')->get()->keyBy('k');
        $perPerusahaan = [];
        foreach (PpmCatalog::anakPerusahaan() as $kode => $nama) {
            $perPerusahaan[] = ['kode' => $kode, 'nama' => $nama] + $this->skm($perusahaanRows[$kode] ?? null);
        }
        $gabungan = $this->skm($perusahaanRows[PpmCatalog::GRUP] ?? null);

        $perDesa = $this->grouped($base(), 'desa_id', PpmCatalog::desa());
        $perPilar = $this->grouped($base(), 'pilar_id', array_map(fn ($p) => $p['nama'], PpmCatalog::pilar()));
        $perProgram = $this->grouped($base(), 'program_id', array_map(fn ($p) => $p['nama'], PpmCatalog::program()));
        $perRincian = $this->grouped($base(), 'rincian_id', $this->rincianNames());

        $tidakTahu = [];
        foreach (PpmCatalog::anakPerusahaan() as $kode => $nama) {
            $row = $base()
                ->whereRaw('FIND_IN_SET(?, perusahaan_opsi)', [$kode])
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN perusahaan_jawaban = ? THEN 1 ELSE 0 END) as tidak_tahu', [PpmCatalog::TIDAK_TAHU])
                ->first();
            $total = (int) ($row->total ?? 0);
            $tt = (int) ($row->tidak_tahu ?? 0);
            $tidakTahu[] = [
                'kode' => $kode,
                'nama' => $nama,
                'total' => $total,
                'tidak_tahu' => $tt,
                'persen' => $total > 0 ? round($tt / $total * 100, 1) : null,
            ];
        }

        $sumber = $base()->selectRaw('sumber_atribusi as k, COUNT(*) as n')->groupBy('sumber_atribusi')->pluck('n', 'k')->all();
        $jenisResponden = $base()->selectRaw('jenis_responden as k, COUNT(*) as n')->groupBy('jenis_responden')->orderByDesc('n')->pluck('n', 'k')->all();
        $jumlahResponden = (int) $base()->distinct()->count('responden_uuid');

        return view('RekapPpm.index', compact(
            'years', 'tahun', 'grup', 'perPerusahaan', 'gabungan', 'perDesa', 'perPilar',
            'perProgram', 'perRincian', 'tidakTahu', 'sumber', 'jenisResponden', 'jumlahResponden'
        ));
    }

    private function aggregate(): string
    {
        return 'COUNT(*) as n, SUM(' . self::SKOR . ') as skor';
    }

    private function skm(?object $row): array
    {
        $n = (int) ($row->n ?? 0);
        $nilai = $n > 0 ? round(((float) $row->skor / ($n * 9)) * 25, 2) : null;

        return ['n' => $n, 'skm' => $nilai, 'mutu' => $this->mutu($nilai)];
    }

    /** Baris per kelompok, diurutkan mengikuti urutan master; kelompok tanpa data tetap tampil. */
    private function grouped($query, string $column, array $names): array
    {
        $rows = $query->selectRaw("{$column} as k, " . $this->aggregate())->groupBy($column)->get()->keyBy('k');

        $out = [];
        foreach ($names as $key => $nama) {
            $out[] = ['key' => $key, 'nama' => $nama] + $this->skm($rows[$key] ?? null);
        }

        return $out;
    }

    private function rincianNames(): array
    {
        return array_map(fn ($r) => $r['nama'], PpmCatalog::rincian());
    }

    private function mutu(?float $nilai): string
    {
        return match (true) {
            $nilai === null => '-',
            $nilai >= 88.31 => 'A (Sangat Baik)',
            $nilai >= 76.61 => 'B (Baik)',
            $nilai >= 65 => 'C (Kurang Baik)',
            default => 'D (Tidak Baik)',
        };
    }
}
