<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When an appointment booking was last moved.
 *
 * The per-booking reschedule cooldown originally compared `events.updated_at`, which is wrong twice
 * over: it is set when the booking is CREATED, so a guest who spotted their mistake seconds after
 * booking was told "this booking was just moved, please wait" and could not fix it; and unrelated jobs
 * write it (the Translate command has no is_private filter and picks up booking events, and
 * inbound calendar syncs call save()), so a legitimate move could be refused at random.
 *
 * Written only by AppointmentService::reschedule(), so it is null until a real move happens.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Anchored to starts_at, an original column: anchoring to a later-dated migration's column
            // breaks a fresh migrate.
            $table->timestamp('rescheduled_at')->nullable()->after('starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('rescheduled_at');
        });
    }
};
