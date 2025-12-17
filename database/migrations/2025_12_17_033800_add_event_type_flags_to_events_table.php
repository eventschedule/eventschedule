<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'is_in_person')) {
                $table->boolean('is_in_person')->default(false)->after('event_url');
            }
            if (! Schema::hasColumn('events', 'is_online')) {
                $table->boolean('is_online')->default(false)->after('is_in_person');
            }
        });

        // Best-effort backfill: infer flags from existing data
        try {
            DB::statement("UPDATE events SET is_in_person = CASE WHEN venue_id IS NOT NULL THEN 1 ELSE 0 END");
            DB::statement("UPDATE events SET is_online = CASE WHEN event_url IS NOT NULL AND event_url <> '' THEN 1 ELSE 0 END");
        } catch (\Throwable $e) {
            // Ignore if columns or table differ in some environments
        }
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'is_online')) {
                $table->dropColumn('is_online');
            }
            if (Schema::hasColumn('events', 'is_in_person')) {
                $table->dropColumn('is_in_person');
            }
        });
    }
};
