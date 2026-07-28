<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Undo the silent enrolment that shipped with the federation migrations.
     *
     * 2026_07_26_000002 added `federation_enabled` as `boolean()->default(true)`, which makes MySQL
     * backfill every existing row with 1. 2026_07_26_000008 then made the column nullable so that
     * "nobody has answered" became expressible - but it did not backfill, and its docblock justified
     * that by saying a schedule that was already federating carries on. No schedule could have been:
     * both migrations shipped together in v1.0.121, so on every upgrade they run back-to-back and
     * every pre-existing schedule ends up explicitly opted in without its owner ever being asked.
     * undecidedScheduleCount() then reports 0, so the admin settings page shows no sign of it either.
     *
     * Reset those rows to null. An auto-backfilled true cannot be told apart from a deliberate one,
     * so this also clears opt-ins made since v1.0.121 - two days at the time of writing, and only on
     * installs whose operator had already switched the network on. Those owners re-pick "Listed on
     * the network" on their schedule; that is the cheaper mistake to make.
     *
     * Note null, not false: false is an explicit opt-out that vetoes co-listed events, which is not
     * what "never answered" means.
     */
    public function up(): void
    {
        DB::table('roles')->where('federation_enabled', true)->update(['federation_enabled' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to reverse: the previous state cannot be reconstructed, and re-enrolling every
        // schedule is the exact harm this migration exists to undo.
    }
};
