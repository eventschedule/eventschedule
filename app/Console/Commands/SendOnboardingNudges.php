<?php

namespace App\Console\Commands;

use App\Jobs\SendQueuedEmail;
use App\Mail\OnboardingNudge;
use App\Models\User;
use App\Services\DemoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Emails accounts that signed up to run a schedule and never created one.
 *
 * More than half of all accounts never create a schedule, and activation is
 * first-session-or-never: 73.5% of first schedules happen within ten minutes of signup, 91%
 * within the hour, and only 5% after a day. Until now nothing at all reached the people who
 * dropped out - there was no welcome or onboarding email of any kind - so the entire tail was
 * unrecoverable by construction.
 *
 * Only organizer-intent accounts are nudged. Someone who signed up to follow a schedule or
 * buy a ticket did not fail to do anything, and telling them to "create your first schedule"
 * would be noise. That matches the cohort definition in GrowthExportService.
 */
class SendOnboardingNudges extends Command
{
    protected $signature = 'app:send-onboarding-nudges
        {--apply : Send the emails. Without this the command only reports.}';

    protected $description = 'Nudge verified accounts that never created their first schedule';

    /** stage => hours since signup at which it becomes due. */
    private const STAGES = [1 => 1, 2 => 24, 3 => 72];

    /**
     * How old a signup may be and still be nudged.
     *
     * Load-bearing, not a tidy-up. Without an upper bound the due-query matches every account
     * ever created, and because the stages run in descending order the whole historical base
     * would receive the STAGE 3 copy - a "last note about your schedule ... this is the last
     * email we will send" to people who never got a first one. A per-run cap alone does not
     * fix that; it only spreads the same blast over more hours.
     *
     * The sequence ends at 72 hours, so anything beyond a few days is already past the moment
     * this exists to catch. The generous margin is for a scheduler that was down for a while.
     */
    private const MAX_AGE_DAYS = 14;

    /**
     * Ceiling on one run, so a backlog drains over several passes instead of in one burst.
     * The command is scheduled hourly, so the remainder is picked up 60 minutes later.
     */
    private const BATCH = 500;

    public function handle(): int
    {
        if (! config('app.hosted')) {
            $this->info('Skipping: not in hosted mode.');

            return 0;
        }

        $apply = (bool) $this->option('apply');
        $sent = 0;

        // Dry-run only. Nothing is persisted on that path, so without this the same account
        // matches the stage 3, 2 and 1 queries in one pass and is reported three times - and
        // this is precisely the command an operator runs to check the blast radius first.
        $reported = [];

        // Descending, so an account that has been sitting for days receives the stage that
        // matches where it actually is rather than starting at stage 1 and taking three more
        // days to catch up.
        foreach (array_reverse(self::STAGES, true) as $stage => $hours) {
            $users = $this->dueForStage($stage, $hours);

            foreach ($users as $user) {
                if (! $apply) {
                    if (isset($reported[$user->id])) {
                        continue;
                    }

                    $reported[$user->id] = true;
                    $this->line("  would send stage {$stage} to {$user->email}");
                    $sent++;

                    continue;
                }

                // Claim the stage BEFORE sending, conditionally. The stage is the only thing
                // stopping a second send, and the two schedulers (routes/console.php and
                // AppController::translateData) hold different mutexes, so a concurrent run can
                // otherwise read the same rows and email everyone twice. A conditional UPDATE is
                // atomic, so exactly one runner claims each row.
                $previous = (int) $user->onboarding_nudge_stage;

                $claimed = User::where('id', $user->id)
                    ->where('onboarding_nudge_stage', '<', $stage)
                    ->update(['onboarding_nudge_stage' => $stage]);

                if ($claimed === 0) {
                    continue;
                }

                try {
                    // Queued, and with the recipient's own language. Sending inline blocked the
                    // hourly chain (this also runs inside a web request), and Mail::to() with a
                    // bare address renders in the CLI locale - so every one of the translations
                    // added alongside this command went unused.
                    SendQueuedEmail::dispatch(
                        new OnboardingNudge($user, $stage),
                        $user->email,
                        null,
                        $user->language_code ?? app()->getLocale()
                    );

                    $this->info("Sent stage {$stage} nudge to {$user->email}.");
                    $sent++;
                } catch (\Exception $e) {
                    // Release the claim, or a queue that was briefly unavailable silently eats
                    // this account's nudge for good.
                    User::where('id', $user->id)
                        ->where('onboarding_nudge_stage', $stage)
                        ->update(['onboarding_nudge_stage' => $previous]);

                    $this->error("Failed nudge for {$user->email}: {$e->getMessage()}");
                    Log::error('Failed to send onboarding nudge', [
                        'user_id' => $user->id,
                        'stage' => $stage,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->newLine();
        $this->info($apply ? "Onboarding nudges: {$sent} sent." : "DRY RUN - {$sent} would be sent. Re-run with --apply.");

        return 0;
    }

    /**
     * Verified organizer-intent accounts, old enough for this stage, young enough to still be
     * worth reaching, and still with no schedule.
     */
    private function dueForStage(int $stage, int $hours)
    {
        return User::query()
            ->whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            // Only ever moves forward, so a re-run or a double-fired scheduler cannot resend.
            ->where('onboarding_nudge_stage', '<', $stage)
            ->where('created_at', '<=', now()->subHours($hours))
            // See MAX_AGE_DAYS: the upper bound is what stops this sweeping the whole users
            // table, not the limit below.
            ->where('created_at', '>=', now()->subDays(self::MAX_AGE_DAYS))
            // Attendee signups (follow / request / ticket / team invite) are not failed
            // organizers; NULL predates intent tracking and is treated as organizer.
            ->where(function ($query) {
                $query->whereNull('signup_intent')->orWhere('signup_intent', 'organizer');
            })
            ->where('is_subscribed', true)
            // Any tie to a schedule at all counts as activated, including a claim that landed
            // them as a follower - matches post_signup_redirect_url()'s roles() check.
            ->whereDoesntHave('roles')
            ->whereDoesntHave('tickets')
            // Oldest first, so a backlog drains in signup order rather than leaving the
            // earliest stalled accounts at the back of every run.
            ->orderBy('created_at')
            ->limit(self::BATCH)
            ->get();
    }
}
