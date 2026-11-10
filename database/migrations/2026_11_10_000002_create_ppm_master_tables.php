<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master data PPM Bayan: desa, perusahaan, pilar, program, rincian program,
 * dan lokasi pelaksanaan tiap rincian per perusahaan.
 * Isi diambil dari database/data/klasifikasi_ppm_bayan.json (PpmMasterSeeder).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ppm_desa')) {
            Schema::create('ppm_desa', function (Blueprint $table) {
                $table->smallIncrements('id');
                $table->string('nama', 100)->unique();
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ppm_perusahaan')) {
            Schema::create('ppm_perusahaan', function (Blueprint $table) {
                $table->string('kode', 10)->primary();
                $table->string('nama', 100);
                // BAYAN = grup (gabungan); anak perusahaan = false
                $table->boolean('is_grup')->default(false);
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ppm_pilar')) {
            Schema::create('ppm_pilar', function (Blueprint $table) {
                $table->string('id', 10)->primary();
                $table->string('nama', 255);
                // Kode pendek, juga disimpan di survey_responses.jenisPelayanan
                $table->string('kode', 40)->unique();
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ppm_program')) {
            Schema::create('ppm_program', function (Blueprint $table) {
                $table->string('id', 10)->primary();
                $table->string('pilar_id', 10)->index();
                $table->string('nama', 255);
                $table->unsignedSmallInteger('jumlah_dalam_kurung')->nullable();
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ppm_rincian')) {
            Schema::create('ppm_rincian', function (Blueprint $table) {
                $table->string('id', 12)->primary();
                $table->string('program_id', 10)->index();
                $table->string('nama', 255);
                // Perusahaan yang ditandai √ di sheet Klasifikasi (CSV), mis. "FSP,TA"
                $table->string('penilai', 60)->nullable();
                // true = selalu dinilai sebagai BAYAN (grup)
                $table->boolean('dinilai_sebagai_grup')->default(false);
                $table->string('catatan', 255)->nullable();
                // Rincian tanpa penilai tidak ditampilkan di survei
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ppm_rincian_lokasi')) {
            Schema::create('ppm_rincian_lokasi', function (Blueprint $table) {
                $table->id();
                $table->string('rincian_id', 12);
                $table->string('perusahaan_kode', 10);
                // Terisi bila lokasi berupa desa; null bila lokasi_lain (Ring 2, Kec. ...)
                $table->unsignedSmallInteger('desa_id')->nullable();
                $table->string('lokasi_lain', 100)->nullable();

                $table->index(['rincian_id', 'desa_id']);
                $table->index('desa_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ppm_rincian_lokasi');
        Schema::dropIfExists('ppm_rincian');
        Schema::dropIfExists('ppm_program');
        Schema::dropIfExists('ppm_pilar');
        Schema::dropIfExists('ppm_perusahaan');
        Schema::dropIfExists('ppm_desa');
    }
};
