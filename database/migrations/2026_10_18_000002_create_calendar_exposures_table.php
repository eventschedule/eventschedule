<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Events a calendar import published that look like somebody's private calendar: a meeting's
 * passcode or dial-in code, a join link carrying its password, an email thread made into an
 * entry (App\Utils\CalendarPrivacy). Written by App\Services\CalendarExposureWatch, which reads
 * each event's text once, when it changes, so the operator's page (/admin/private-calendars) and
 * its alert read this table and never the events' descriptions.
 *
 * kinds: what was found, as CalendarPrivacy::FLAGS names it.
 * text_hash: what the kinds were found in (CalendarPrivacy::textHash()).
 * matched_at: moves only when the kinds or the text change, so "reviewed" holds until something
 * new turns up.
 */
return new class extends Migration
{
    public function up(): void
    {
        // A short lock wait, and safe to run again after a run that stopped half way: the
        // foreign keys take metadata locks on events, roles and users.
        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            if (! Schema::hasTable('calendar_exposures')) {
                Schema::create('calendar_exposures', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('event_id')->unique()->constrained()->cascadeOnDelete();
                    $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                    $table->json('kinds');
                    $table->char('text_hash', 40);
                    $table->timestamp('matched_at');
                    $table->timestamps();

                    // What the list and the alert read: a schedule's matches newer than its review.
                    $table->index(['role_id', 'matched_at']);
                });
            }
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_exposures');
    }
};
