<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Keeps the "Notify me" card on for every schedule that was already using it.
 *
 * 2026_09_17_000002 added roles.show_event_interest with default(false), which switched the card
 * off for every existing schedule. For most of them that is the point. For a schedule whose events
 * already collected sign-ups it would silently stop a list that is still growing, while the event
 * editor and the dashboard go on reporting how many people are waiting.
 *
 * The switch that governs the card is the event CREATOR's (Event::offersInterestCapture()), and the
 * creator is who the list's mail goes out as, so that is the schedule this turns back on.
 */
return new class extends Migration
{
    public function up(): void
    {
        // A subquery that never reads `roles`, so MySQL's "cannot select from the table being
        // updated" rule does not apply. updated_at is left alone: nobody edited these schedules.
        DB::table('roles')
            ->whereIn('id', DB::table('event_interests')
                ->join('events', 'events.id', '=', 'event_interests.event_id')
                ->whereNotNull('events.creator_role_id')
                ->select('events.creator_role_id'))
            ->update(['show_event_interest' => true]);
    }

    public function down(): void
    {
        // Nothing to undo: the rows this switched on are indistinguishable from ones an owner
        // switched on since, and rolling back 000002 drops the column anyway.
    }
};
