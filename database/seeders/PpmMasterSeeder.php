<?php

namespace Database\Seeders;

use App\Services\PpmMasterData;
use Illuminate\Database\Seeder;

/**
 * Mengisi master data PPM dari database/data/klasifikasi_ppm_bayan.json.
 *
 * PERHATIAN: setelah master data diubah lewat menu "Master Data PPM",
 * menjalankan seeder ini akan MENIMPA pilar/program/rincian dengan isi file JSON.
 * Desa dicocokkan lewat nama sehingga ID desa (dan penilaian yang sudah ada) tetap aman.
 *
 *   php artisan db:seed --class=PpmMasterSeeder
 */
class PpmMasterSeeder extends Seeder
{
    public const JSON_PATH = 'data/klasifikasi_ppm_bayan.json';

    public function run(): void
    {
        $summary = app(PpmMasterData::class)->importFile(database_path(self::JSON_PATH));

        $this->command?->info('Master data PPM: ' . json_encode($summary));
    }
}
