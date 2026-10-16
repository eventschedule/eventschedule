<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The ticket preset an event's tickets came from (issue #130), so its Tickets tab can say which
     * one is chosen and whether the preset has changed since. Written only by EventRepo::saveEvent()
     * through App\Services\TicketPresets; not fillable, and in BackupService::EVENT_EXPORT_EXCLUDE (a
     * preset id from this install means nothing elsewhere).
     *
     * An index and no foreign key: MySQL can add a foreign key with foreign_key_checks on only by
     * copying the table, which would rebuild `events` under lock. Deleting a preset clears the
     * column in code (TicketPresets::forget()).
     *
     * No ->after(), guarded statements and a short lock wait, for the reasons 2026_10_08_000000 gives.
     */
    public function up(): void
    {
        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            if (! Schema::hasColumn('events', 'ticket_preset_id')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->unsignedBigInteger('ticket_preset_id')->nullable();
                });
            }

            if (! Schema::hasIndex('events', 'events_ticket_preset_id_index')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->index('ticket_preset_id');
                });
            }
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('events', 'events_ticket_preset_id_index')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropIndex(['ticket_preset_id']);
            });
        }

        if (Schema::hasColumn('events', 'ticket_preset_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('ticket_preset_id');
            });
        }
    }
};
