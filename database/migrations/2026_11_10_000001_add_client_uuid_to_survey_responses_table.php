<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * client_uuid is generated on the admin's device for data entered offline.
 * It lets the server recognise a record that was already received when the
 * device retries a sync after a dropped connection, so no duplicates appear.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('survey_responses', 'client_uuid')) {
            return;
        }

        Schema::table('survey_responses', function (Blueprint $table) {
            $table->string('client_uuid', 36)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('survey_responses', 'client_uuid')) {
            return;
        }

        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropUnique(['client_uuid']);
            $table->dropColumn('client_uuid');
        });
    }
};
