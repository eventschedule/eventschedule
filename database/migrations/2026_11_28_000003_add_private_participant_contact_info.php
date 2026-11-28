<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backstage_entries', function (Blueprint $t) {
            $t->string('participant_email')->nullable();
            $t->string('participant_phone', 80)->nullable();
            $t->string('participant_website', 2048)->nullable();
        });
        DB::statement(<<<'SQL'
            UPDATE backstage_entries AS e
            JOIN roles AS p ON p.id = e.participant_id
            SET e.participant_email = p.email,
                e.participant_phone = p.phone,
                e.participant_website = p.website
            SQL);
    }

    public function down(): void
    {
        Schema::table('backstage_entries', function (Blueprint $t) {
            $t->dropColumn(['participant_email', 'participant_phone', 'participant_website']);
        });
    }
};
