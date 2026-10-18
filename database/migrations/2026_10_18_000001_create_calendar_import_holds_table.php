<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * An event a calendar sync is keeping off the public page, and why (App\Services\CalendarArrival).
 *
 * reason: review (it arrived as a draft for the owner to look at), meeting_details or
 * email_thread (CalendarPrivacy found them), private / declined / cancelled / not_an_event (the
 * owner's calendar says so).
 * was_visibility: where the event goes when the reason goes away: what it was, for one that was
 * on the page, or what it would have been, for one that arrived held on a sync that publishes;
 * null for one that waits for the owner anyway.
 * was_public: it WAS on the page and was taken off it, which is what "taken off your page" (the
 * schedule's notice, the review page's tab, the owner's mail) means. Not one that arrived held.
 * first_pull: it arrived on the sync's very first read, so publishing it is not news to followers.
 * held_by: the operator who hid it; null when the sync did. The sync only ever undoes its own.
 * decided_*: the owner published it, or chose to keep it hidden; decided_hash is the text the
 * decision was made on (CalendarPrivacy::textHash()), so details that come back later are new.
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
            if (! Schema::hasTable('calendar_import_holds')) {
                try {
                    Schema::create('calendar_import_holds', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('event_id')->unique()->constrained()->cascadeOnDelete();
                        $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                        $table->string('reason', 20);
                        $table->string('was_visibility', 10)->nullable();
                        $table->boolean('was_public')->default(false);
                        $table->boolean('first_pull')->default(false);
                        $table->foreignId('held_by')->nullable()->constrained('users')->nullOnDelete();
                        $table->string('decision', 10)->nullable();
                        $table->timestamp('decided_at')->nullable();
                        $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
                        $table->char('decided_hash', 40)->nullable();
                        $table->timestamps();

                        // What the schedule's notice and review page read: the holds still waiting.
                        $table->index(['role_id', 'decided_at']);
                    });
                } catch (\Throwable $e) {
                    // Each key and index is its own statement after CREATE TABLE. One that fails (a
                    // lock wait on a busy table) leaves the table without it, and the next run would
                    // pass it by. Nothing has written to it yet.
                    Schema::dropIfExists('calendar_import_holds');

                    throw $e;
                }
            }
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_import_holds');
    }
};
