<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Retire every account that existed before the onboarding nudges did.
 *
 * `onboarding_nudge_stage` was added with `default(0)`, which means "no nudge sent yet" - so on
 * the first `app:send-onboarding-nudges --apply` run the entire historical user base matched the
 * due-query. Worse, the command walks the stages in DESCENDING order so that someone stalled for
 * days gets the message that fits, which means those accounts would each have received the
 * stage 3 copy: "Last note about your schedule ... this is the last email we will send about
 * getting set up" - a final reminder to people who never got a first one.
 *
 * Stage 3 is the terminal stage, so writing it here marks these accounts as done without
 * inventing a new sentinel value.
 *
 * Every row, not just the old ones. The command now also refuses anything older than
 * SendOnboardingNudges::MAX_AGE_DAYS, so a date-based backfill would be the second guard on the
 * same condition and would quietly stop guarding if that window were ever widened. The cost is
 * that accounts created in the last hour or two before this deploy never get nudged - they are
 * a rounding error against emailing the whole base, and nobody was ever promised the sequence.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->update(['onboarding_nudge_stage' => 3]);
    }

    public function down(): void
    {
        // Deliberately not reversed. Resetting to 0 would re-arm exactly the blast this
        // migration exists to prevent, on the next scheduler tick after a rollback.
    }
};
