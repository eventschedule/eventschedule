<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The id another system knows an event by, so that system's next write updates the event it
     * made instead of adding a second one.
     *
     * It is the API's: set on create, changed or cleared on update, looked up with
     * ?external_id=, and what an upsert matches on. Unique for the schedule that owns the event
     * (creator_role_id), because two systems feeding two schedules may well number their records
     * from 1. Most events never have one, and NULL does not collide with NULL.
     *
     * utf8mb4_bin, not the table's own collation: under utf8mb4_unicode_ci "aB3" and "Ab3" are one
     * key, and two records of the source would be written onto one event.
     *
     * Not fillable (see Event::$fillable): EventRepo::saveEvent() fills an Event from the whole
     * request, so a fillable column here would be writable from every event form.
     *
     * No ->after(), guarded statements and a short lock wait, for the reasons the import columns'
     * migration gives (2026_10_04_000003).
     */
    public function up(): void
    {
        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            if (! Schema::hasColumn('events', 'external_id')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->string('external_id', 255)->nullable()->collation('utf8mb4_bin');
                });
            }

            if (! Schema::hasIndex('events', 'events_creator_role_id_external_id_unique')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->unique(['creator_role_id', 'external_id']);
                });
            }
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('events', 'events_creator_role_id_external_id_unique')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropUnique(['creator_role_id', 'external_id']);
            });
        }

        if (Schema::hasColumn('events', 'external_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('external_id');
            });
        }
    }
};
