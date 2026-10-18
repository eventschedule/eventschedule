<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An event a calendar sync is keeping off the public page, and why (App\Services\CalendarArrival).
 *
 * reason: review (it arrived as a draft for the owner to look at), meeting_details or
 * email_thread (CalendarPrivacy found them), private / declined / cancelled / not_an_event (the
 * owner's calendar says so).
 * was_visibility: what the event was before it was hidden, for one that was already public;
 * null for one that arrived as a draft. What is put back when the reason goes away.
 * first_pull: it arrived on the sync's very first read, so publishing it is not news to followers.
 * held_by: the operator who hid it; null when the sync did. The sync only ever undoes its own.
 * decided_*: the owner published it, or chose to keep it hidden; decided_hash is the text the
 * decision was made on (CalendarPrivacy::textHash()), so details that come back later are new.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_import_holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('reason', 20);
            $table->string('was_visibility', 10)->nullable();
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
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_import_holds');
    }
};
