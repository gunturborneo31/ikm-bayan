<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Multi akun (admin & surveyor) dan pencatatan penginput data.
 *
 * users: is_active (akun bisa dinonaktifkan tanpa dihapus), last_login_at.
 * survey_responses: input_oleh (users.id) dan sumber_input
 *   (survei | input_admin | import_excel | dummy).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('keterangan');
            }
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('is_active');
            }
        });

        Schema::table('survey_responses', function (Blueprint $table) {
            if (! Schema::hasColumn('survey_responses', 'input_oleh')) {
                $table->unsignedBigInteger('input_oleh')->nullable()->after('perusahaan_opsi');
                $table->string('sumber_input', 20)->nullable()->after('input_oleh');
                $table->index('input_oleh');
            }
        });

        // Data yang sudah ada: tandai sumbernya sebisa mungkin
        DB::table('survey_responses')->whereNull('sumber_input')->where('responden_uuid', 'like', 'dummy-%')->update(['sumber_input' => 'dummy']);
        DB::table('survey_responses')->whereNull('sumber_input')->where('client_uuid', 'like', 'xls-%')->update(['sumber_input' => 'import_excel']);
        DB::table('survey_responses')->whereNull('sumber_input')->whereNotNull('client_uuid')->update(['sumber_input' => 'input_admin']);
        DB::table('survey_responses')->whereNull('sumber_input')->update(['sumber_input' => 'survei']);
    }

    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            if (Schema::hasColumn('survey_responses', 'input_oleh')) {
                $table->dropIndex(['input_oleh']);
                $table->dropColumn(['input_oleh', 'sumber_input']);
            }
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(array_filter(['is_active', 'last_login_at'], fn ($c) => Schema::hasColumn('users', $c)));
        });
    }
};
