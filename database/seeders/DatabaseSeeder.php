<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUsersSeeder::class,
        ]);

        // Master PPM dari JSON hanya diisi saat masih kosong, agar perubahan
        // lewat menu "Master Data PPM" tidak tertimpa.
        if (! \Illuminate\Support\Facades\DB::table('ppm_rincian')->exists()) {
            $this->call(PpmMasterSeeder::class);
        }
    }
}
