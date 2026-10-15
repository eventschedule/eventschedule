<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The host a booking was made with, stamped by AppointmentService::book() from the type's
     * host at that moment. A booking keeps it when the type's host changes later: a new host
     * applies to new bookings only, and a moved booking is checked against the person it was
     * made with.
     *
     * It is also what a host's busy time is read from, across every schedule, so the index leads
     * with it and carries starts_at for the window.
     *
     * `events` has room for it: about 25.7 KB of MySQL's 65,535-byte row limit was in use when
     * this was written, where `roles` has none left (see 2026_09_07_000000).
     *
     * Not fillable (EventRepo::saveEvent() fills an Event from the whole request), and in
     * BackupService::EVENT_EXPORT_EXCLUDE: a user id from this install means nothing elsewhere.
     *
     * No ->after(), guarded statements and a short lock wait, for the reasons 2026_10_08_000000
     * gives.
     */
    public function up(): void
    {
        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            if (! Schema::hasColumn('events', 'appointment_host_user_id')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->unsignedBigInteger('appointment_host_user_id')->nullable();
                });
            }

            if (! Schema::hasIndex('events', 'events_appointment_host_user_id_starts_at_index')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->index(['appointment_host_user_id', 'starts_at']);
                });
            }
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('events', 'events_appointment_host_user_id_starts_at_index')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropIndex(['appointment_host_user_id', 'starts_at']);
            });
        }

        if (Schema::hasColumn('events', 'appointment_host_user_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('appointment_host_user_id');
            });
        }
    }
};
