<?php

namespace App\Console\Commands;

use App\Mail\OnboardingNudge;
use App\Models\User;
use App\Services\DemoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

    public function handle(): int
    {
        if (! config('app.hosted')) {
            $this->info('Skipping: not in hosted mode.');

            return 0;
        }

        $apply = (bool) $this->option('apply');
        $sent = 0;

        // Descending, so an account that has been sitting for days receives the stage that
        // matches where it actually is rather than starting at stage 1 and taking three more
        // days to catch up.
        foreach (array_reverse(self::STAGES, true) as $stage => $hours) {
            $users = $this->dueForStage($stage, $hours);

            foreach ($users as $user) {
                if (! $apply) {
                    $this->line("  would send stage {$stage} to {$user->email}");
                    $sent++;

                    continue;
                }

                try {
                    Mail::to($user->email)->send(new OnboardingNudge($user, $stage));

                    $user->onboarding_nudge_stage = $stage;
                    $user->save();

                    $this->info("Sent stage {$stage} nudge to {$user->email}.");
                    $sent++;
                } catch (\Exception $e) {
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
     * Verified organizer-intent accounts, old enough for this stage, still with no schedule.
     */
    private function dueForStage(int $stage, int $hours)
    {
        return User::query()
            ->whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            // Only ever moves forward, so a re-run or a double-fired scheduler cannot resend.
            ->where('onboarding_nudge_stage', '<', $stage)
            ->where('created_at', '<=', now()->subHours($hours))
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
            ->get();
    }
}
