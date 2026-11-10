<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Satu baris survey_responses = satu penilaian (responden x rincian program).
 * Responden yang sama bisa menilai beberapa program; barisnya dikelompokkan
 * lewat responden_uuid sehingga identitas tidak perlu diisi ulang.
 */
return new class extends Migration
{
    private array $columns = [
        'responden_uuid', 'jenis_responden', 'desa_id', 'pilar_id', 'program_id', 'rincian_id',
        'perusahaan_jawaban', 'perusahaan_dinilai', 'sumber_atribusi', 'perusahaan_opsi',
    ];

    public function up(): void
    {
        if (Schema::hasColumn('survey_responses', 'rincian_id')) {
            return;
        }

        Schema::table('survey_responses', function (Blueprint $table) {
            $table->string('responden_uuid', 36)->nullable()->after('client_uuid');
            $table->string('jenis_responden', 40)->nullable()->after('nik');
            $table->unsignedSmallInteger('desa_id')->nullable()->after('jenis_responden');
            $table->string('pilar_id', 10)->nullable()->after('desa_id');
            $table->string('program_id', 10)->nullable()->after('pilar_id');
            $table->string('rincian_id', 12)->nullable()->after('program_id');
            // Kode perusahaan pilihan responden, atau TIDAK_TAHU
            $table->string('perusahaan_jawaban', 12)->nullable()->after('rincian_id');
            // Perusahaan yang akhirnya dinilai: FSP/BT/TA/TJ atau BAYAN (gabungan)
            $table->string('perusahaan_dinilai', 12)->nullable()->after('perusahaan_jawaban');
            // jawaban_responden | asumsi_sistem
            $table->string('sumber_atribusi', 20)->nullable()->after('perusahaan_dinilai');
            // Pilihan perusahaan yang ditampilkan ke responden (CSV), untuk indikator "tidak tahu"
            $table->string('perusahaan_opsi', 60)->nullable()->after('sumber_atribusi');

            $table->index('responden_uuid');
            $table->index(['rincian_id', 'desa_id']);
            $table->index('perusahaan_dinilai');
            $table->index('desa_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('survey_responses', 'rincian_id')) {
            return;
        }

        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropIndex(['responden_uuid']);
            $table->dropIndex(['rincian_id', 'desa_id']);
            $table->dropIndex(['perusahaan_dinilai']);
            $table->dropIndex(['desa_id']);
            $table->dropColumn($this->columns);
        });
    }
};
