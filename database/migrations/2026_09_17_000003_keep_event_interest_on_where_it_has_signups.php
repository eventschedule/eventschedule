<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Keeps the "Notify me" card on for every schedule that was already using it.
 *
 * 2026_09_17_000002 added roles.show_event_interest with default(false), which switched the card
 * off for every existing schedule. For most of them that is the point. For a schedule whose events
 * already collected sign-ups THROUGH THE CARD it would silently stop a list that is still growing,
 * while the event editor and the dashboard go on reporting how many people are waiting.
 *
 * "Through the card" is the whole predicate: see the source filter in up().
 *
 * The switch that governs the card is the event CREATOR's (Event::offersInterestCapture()), and the
 * creator is who the list's mail goes out as, so that is the schedule this turns back on.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Plucked first, then updated in batches, rather than one UPDATE with an IN (subquery).
        // MySQL does not semi-join a subquery inside an UPDATE the way it does inside a SELECT, so
        // that form can degrade to a dependent subquery re-run per scanned row, and under REPEATABLE
        // READ an UPDATE takes next-key locks on every row it EXAMINES - which would write-lock the
        // whole of `roles` (171 columns) for the duration. The id set here is small and knowable.
        //
        // source = 'event_page' is the load-bearing filter. event_interests also carries
        // source = 'checkout' (TicketController's opt-in checkbox at checkout), which is a different
        // surface from the "Notify me" card this column governs. Without it, a schedule that only
        // ever collected checkout opt-ins - and so never displayed the card - would have it switched
        // on across all of its public event pages, which is the opposite of what this migration is
        // for. distinct() because one schedule can have thousands of interest rows.
        $roleIds = DB::table('event_interests')
            ->join('events', 'events.id', '=', 'event_interests.event_id')
            ->whereNotNull('events.creator_role_id')
            ->where('event_interests.source', 'event_page')
            ->distinct()
            ->pluck('events.creator_role_id')
            ->all();

        // updated_at is left alone: nobody edited these schedules.
        foreach (array_chunk($roleIds, 500) as $chunk) {
            DB::table('roles')->whereIn('id', $chunk)->update(['show_event_interest' => true]);
        }
    }

    public function down(): void
    {
        // Nothing to undo: the rows this switched on are indistinguishable from ones an owner
        // switched on since, and rolling back 000002 drops the column anyway.
    }
};
