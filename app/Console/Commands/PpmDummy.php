<?php

namespace App\Console\Commands;

use App\Support\PpmCatalog;
use App\Support\SurveyOptions;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Data dummy penilaian PPM untuk uji tampilan & laporan.
 *
 * Mengikuti alur survei asli: desa -> rincian yang berjalan di desa itu ->
 * pilihan perusahaan pelaksana / "Tidak tahu" -> atribusi lewat PpmCatalog.
 * Semua baris dummy ditandai responden_uuid berawalan "dummy-" sehingga
 * mudah dihapus dan tidak bercampur dengan data asli.
 *
 *   php artisan ppm:dummy                 buat ulang dummy (tahun lalu + tahun ini)
 *   php artisan ppm:dummy --tahun=2026    hanya tahun tertentu (boleh diulang)
 *   php artisan ppm:dummy --hapus         hapus semua data dummy
 */
class PpmDummy extends Command
{
    protected $signature = 'ppm:dummy {--tahun=* : Tahun data dummy} {--hapus : Hapus semua data dummy saja}';

    protected $description = 'Buat (atau hapus) data dummy penilaian SKM PPM';

    public const PREFIX = 'dummy-';

    private const NAMA_DEPAN = ['Agus', 'Budi', 'Dewi', 'Eka', 'Fitri', 'Hendra', 'Indah', 'Joko', 'Kartini', 'Lestari', 'Made', 'Nur', 'Oktavia', 'Putri', 'Rahmat', 'Siti', 'Taufik', 'Udin', 'Wati', 'Yohanes', 'Yuliana', 'Rudi', 'Maria', 'Simon', 'Lusia', 'Antonius', 'Rina', 'Dedi', 'Sri', 'Hasan'];
    private const NAMA_BELAKANG = ['Saputra', 'Lestari', 'Pratama', 'Wulandari', 'Hidayat', 'Ningsih', 'Kurniawan', 'Sari', 'Setiawan', 'Rahayu', 'Ladung', 'Jalung', 'Ngau', 'Lawing', 'Bilung', 'Anyeq', 'Hasanah', 'Gunawan'];

    private const SARAN = [
        'Program sangat membantu, semoga dilanjutkan tahun depan.',
        'Mohon sosialisasi lebih awal agar warga bisa ikut semua.',
        'Informasi jadwal kegiatan kurang jelas, sebaiknya diumumkan di balai desa.',
        'Perlu pendampingan lanjutan setelah program selesai.',
        'Bantuan sudah tepat sasaran, terima kasih Bayan.',
        'Pelaksana ramah dan cepat tanggap.',
        'Kuota penerima perlu ditambah karena banyak yang belum kebagian.',
        'Mohon libatkan aparat desa dalam pendataan penerima.',
        'Sarana yang dibangun perlu perawatan rutin.',
        'Pelatihan sangat bermanfaat, mohon ada tingkat lanjutan.',
        'Akses ke lokasi kegiatan cukup jauh, mohon dipertimbangkan.',
        'Semoga program kesehatan diadakan lebih sering.',
        'Hasil program sudah terasa untuk ekonomi keluarga.',
        'Perlu laporan hasil program kepada masyarakat.',
        'Mohon prioritaskan warga kurang mampu.',
    ];

    /** Rata-rata nilai per perusahaan dinilai, agar SKM antarperusahaan berbeda. */
    private const RATA_PERUSAHAAN = ['BAYAN' => 3.45, 'BT' => 3.30, 'FSP' => 3.15, 'TA' => 3.55, 'TJ' => 2.95];

    /** Selisih per unsur (U1..U9): informasi & keberlanjutan sedikit lebih rendah. */
    private const SELISIH_UNSUR = [1 => 0.05, 2 => 0.0, 3 => -0.25, 4 => -0.35, 5 => 0.05, 6 => -0.10, 7 => 0.25, 8 => -0.05, 9 => 0.15];

    public function handle(): int
    {
        $hapus = DB::table('survey_responses')->where('responden_uuid', 'like', self::PREFIX . '%')->delete();
        $this->info("Data dummy lama dihapus: {$hapus} baris.");

        if ($this->option('hapus')) {
            return self::SUCCESS;
        }

        if (! PpmCatalog::isReady()) {
            $this->error('Master data PPM belum ada. Jalankan: php artisan db:seed --class=PpmMasterSeeder');

            return self::FAILURE;
        }

        $tahunIni = (int) date('Y');
        $daftarTahun = array_map('intval', $this->option('tahun')) ?: [$tahunIni - 1, $tahunIni];
        mt_srand(20261110); // hasil sama setiap dijalankan

        $total = 0;
        foreach ($daftarTahun as $tahun) {
            $rows = $this->buatTahun($tahun, $tahun === $tahunIni);
            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table('survey_responses')->insert($chunk);
            }
            $responden = count(array_unique(array_column($rows, 'responden_uuid')));
            $this->line(sprintf('  %d: %d penilaian dari %d responden', $tahun, count($rows), $responden));
            $total += count($rows);
        }

        $this->info("Selesai: {$total} penilaian dummy dibuat. Hapus kapan saja dengan: php artisan ppm:dummy --hapus");

        return self::SUCCESS;
    }

    private function buatTahun(int $tahun, bool $tahunBerjalan): array
    {
        $desa = PpmCatalog::desa();
        $rincian = PpmCatalog::rincian();
        $program = PpmCatalog::program();
        $pilar = PpmCatalog::pilar();

        // Slot penilaian per desa: setiap rincian yang berjalan di desa itu dinilai 1-4 kali
        $slotPerDesa = [];
        foreach ($rincian as $rid => $r) {
            foreach ($r['pelaksana'] as $desaId => $opsi) {
                $kali = $tahunBerjalan ? mt_rand(2, 4) : mt_rand(1, 2);
                for ($i = 0; $i < $kali; $i++) {
                    $slotPerDesa[$desaId][] = $rid;
                }
            }
        }

        $akhir = $tahunBerjalan ? Carbon::now() : Carbon::create($tahun, 12, 31, 17);
        $awal = Carbon::create($tahun, 1, 2, 8);
        $rows = [];

        foreach ($slotPerDesa as $desaId => $slots) {
            shuffle($slots);
            $offsetDesa = (mt_rand(-20, 20) / 100) - ($tahunBerjalan ? 0 : 0.15);

            while ($slots) {
                // Satu responden menilai 1-3 rincian berbeda di desanya
                $identitas = $this->identitas($desa[$desaId], $tahun);
                $jumlah = min(count($slots), $this->pilih([1 => 55, 2 => 30, 3 => 15]));
                $dinilai = [];
                $waktu = Carbon::createFromTimestamp(mt_rand($awal->timestamp, $akhir->timestamp));

                foreach ($slots as $k => $rid) {
                    if (count($dinilai) >= $jumlah) {
                        break;
                    }
                    if (in_array($rid, $dinilai, true)) {
                        continue;
                    }
                    unset($slots[$k]);
                    $dinilai[] = $rid;

                    $opsi = $rincian[$rid]['pelaksana'][$desaId];
                    // ±20% "Tidak tahu"; selebihnya memilih salah satu pelaksana
                    $jawaban = mt_rand(1, 100) <= 20 ? PpmCatalog::TIDAK_TAHU : $opsi[array_rand($opsi)];
                    $atribusi = PpmCatalog::attribute($rid, (int) $desaId, $jawaban);
                    $programId = $rincian[$rid]['program_id'];
                    $pilarId = $program[$programId]['pilar_id'];

                    $rata = (self::RATA_PERUSAHAAN[$atribusi['perusahaan_dinilai']] ?? 3.3) + $offsetDesa;
                    $nilai = [];
                    foreach (range(1, 9) as $u) {
                        $nilai["u{$u}"] = $this->skor($rata + self::SELISIH_UNSUR[$u]);
                    }

                    $waktu = $waktu->copy()->addMinutes(mt_rand(3, 9));
                    $rows[] = $identitas + $nilai + $atribusi + [
                        'tahun' => $tahun,
                        'jenisPelayanan' => $pilar[$pilarId]['kode'] ?? null,
                        'desa_id' => (int) $desaId,
                        'pilar_id' => $pilarId,
                        'program_id' => $programId,
                        'rincian_id' => $rid,
                        'perusahaan_jawaban' => $jawaban,
                        'sumber_input' => 'dummy',
                        'saran' => mt_rand(1, 100) <= 55 ? self::SARAN[array_rand(self::SARAN)] : null,
                        'created_at' => $waktu->toDateTimeString(),
                        'updated_at' => $waktu->toDateTimeString(),
                    ];
                }
                $slots = array_values($slots);
            }
        }

        return $rows;
    }

    private function identitas(string $namaDesa, int $tahun): array
    {
        $jenkel = $this->pilih(['Laki - Laki' => 55, 'Perempuan' => 45]);

        return [
            'responden_uuid' => self::PREFIX . $tahun . '-' . Str::lower(Str::random(20)),
            'nama' => self::NAMA_DEPAN[array_rand(self::NAMA_DEPAN)] . ' ' . self::NAMA_BELAKANG[array_rand(self::NAMA_BELAKANG)],
            'nohp' => '08' . mt_rand(11, 59) . mt_rand(1000000, 9999999),
            'alamat' => 'RT ' . str_pad((string) mt_rand(1, 12), 2, '0', STR_PAD_LEFT) . ', Desa ' . $namaDesa,
            'jenkel' => $jenkel,
            'usia' => $this->pilih([29 => 25, 30 => 35, 41 => 25, 51 => 15]),
            'pendidikan' => $this->pilih(array_combine(SurveyOptions::PENDIDIKAN, [22, 25, 33, 8, 10, 2])),
            'pekerjaan' => $this->pilih(array_combine(SurveyOptions::PEKERJAAN, [8, 2, 30, 20, 10, 30])),
            'jenis_responden' => $this->pilih(array_combine(SurveyOptions::JENIS_RESPONDEN, [45, 33, 14, 8])),
            'nik' => null,
        ];
    }

    /** Nilai 1-4 di sekitar rata-rata (sebaran normal sederhana). */
    private function skor(float $rata): int
    {
        $acak = (mt_rand() / mt_getrandmax() + mt_rand() / mt_getrandmax() + mt_rand() / mt_getrandmax() - 1.5) * 0.9;

        return (int) max(1, min(4, round($rata + $acak)));
    }

    /** Pilih kunci secara acak berbobot. */
    private function pilih(array $bobot)
    {
        $r = mt_rand(1, array_sum($bobot));
        foreach ($bobot as $kunci => $b) {
            if (($r -= $b) <= 0) {
                return $kunci;
            }
        }

        return array_key_first($bobot);
    }
}
