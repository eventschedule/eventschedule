<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Repeating newsletters (issue #134) and the instant a send lists its events as of.
 *
 * - repeat: {"every": "week"|"month", "at": "Y-m-d H:i"} on the newsletter that repeats, its first
 *   send in the schedule's own wall clock. Every run is worked out from that anchor.
 * - repeat_parent_id: on each copy a repeating newsletter sent. No foreign key: a copy outlives
 *   its repeating newsletter (deleting one only marks it cancelled), and adding one would copy
 *   the table.
 * - events_as_of: the instant an Events block with a date range is read against, stamped when a
 *   send is claimed, so every batch of one send (and its A/B remainder) lists the same events.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            $table->json('repeat')->nullable();
            $table->unsignedBigInteger('repeat_parent_id')->nullable()->index();
            $table->timestamp('events_as_of')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            $table->dropIndex(['repeat_parent_id']);
            $table->dropColumn(['repeat', 'repeat_parent_id', 'events_as_of']);
        });
    }
};
