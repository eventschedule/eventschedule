<?php

namespace App\Console\Commands;

use App\Jobs\SendQueuedEmail;
use App\Mail\EventAnnouncement;
use App\Models\Event;
use App\Models\Role;
use App\Services\AudienceResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Emails a schedule's confirmed audience when it publishes new events.
 *
 * This is the email the audience feature already promises and nothing sent. Six surfaces tell a
 * visitor they will hear about new events - subscribe_panel_body, audience_opt_in_label, the
 * checkout and cart checkboxes, subscription_confirmed_body, and subscription_confirm_cadence
 * inside the double-opt-in confirmation itself - while role_subscribers was reachable only from
 * the newsletter composer, by an owner who remembered to write one. On the hosted free tier
 * Role::newsletterLimit() caps that at 10 recipients a month.
 *
 * Three things keep this from being a mailshot, and all three are load-bearing:
 *
 *   1. A DIGEST per schedule, never one email per event. subscription_confirm_cadence promises
 *      "at most one email every few days", so a schedule that publishes a season in one sitting
 *      owes its audience one message.
 *   2. A cadence floor (usage.audience_announcement_min_hours) between sends to one schedule.
 *   3. A first-run WATERMARK. roles.last_announced_at starts NULL, and a schedule seen for the
 *      first time is stamped WITHOUT being sent. Without it the first run would announce every
 *      event in the historical base from every schedule at once, on the platform's shared
 *      sending reputation - the same lesson SendActivationNudges::WINDOWS records.
 *
 * Dry by default, like SendActivationNudges. Read a run before scheduling it.
 */
class SendEventAnnouncements extends Command
{
    protected $signature = 'app:send-event-announcements
        {--apply : Send the emails. Without this the command only reports.}
        {--subdomain= : Run one schedule only, for testing.}';

    protected $description = 'Email each schedule\'s confirmed audience about newly published events';

    public function handle(AudienceResolver $audience): int
    {
        $apply = (bool) $this->option('apply');
        $minHours = max(1, (int) config('usage.audience_announcement_min_hours', 72));
        $budget = max(1, (int) config('usage.audience_announcement_batch', 100));

        $sent = 0;
        $stamped = 0;

        foreach ($this->dueRoles() as $role) {
            if ($budget <= 0) {
                $this->info('Batch ceiling reached; the rest will go out on the next run.');
                break;
            }

            // First sighting. Stamp the watermark and send nothing - everything already on the
            // schedule predates the audience being told they would hear about new events.
            if (! $role->last_announced_at) {
                $this->line("[baseline] {$role->subdomain}");
                if ($apply) {
                    $role->forceFill(['last_announced_at' => now()])->save();
                }
                $stamped++;

                continue;
            }

            if ($role->last_announced_at->diffInHours(now()) < $minHours) {
                continue;
            }

            $events = $this->newEventsFor($role);

            if ($events->isEmpty()) {
                continue;
            }

            $recipients = $audience->announcementRecipients($role);

            if ($recipients->isEmpty()) {
                // No audience to tell, but the events are no longer "new" for the next pass -
                // otherwise a schedule that gains its first subscriber a year from now mails them
                // everything published since.
                if ($apply) {
                    $role->forceFill(['last_announced_at' => now()])->save();
                }

                continue;
            }

            // The one shared trust gate, sized to this send. An unverified schedule on the shared
            // platform mailer may reach a small audience and no more.
            if (! $role->canSendAudienceMail($recipients->count(), $role->user)) {
                Log::warning('Event announcement blocked: schedule cannot send audience mail', [
                    'role_id' => $role->id,
                    'recipients' => $recipients->count(),
                ]);

                continue;
            }

            $this->line(sprintf(
                '[announce] %s: %d event(s) to %d recipient(s)',
                $role->subdomain,
                $events->count(),
                $recipients->count(),
            ));

            if ($apply) {
                foreach ($recipients as $subscriber) {
                    $mailable = new EventAnnouncement(
                        $role,
                        $subscriber,
                        $events,
                        route('subscriber.show_unsubscribe', ['token' => $subscriber->token]),
                    );

                    SendQueuedEmail::dispatch(
                        $mailable,
                        $subscriber->email,
                        $role->id,
                        $subscriber->locale ?: ($role->language_code ?: config('app.locale')),
                    );
                }

                $role->forceFill(['last_announced_at' => now()])->save();
            }

            $sent++;
            $budget--;
        }

        $this->info($apply
            ? "Announced for {$sent} schedule(s); {$stamped} baselined."
            : "Would announce for {$sent} schedule(s); {$stamped} would be baselined. Re-run with --apply.");

        return 0;
    }

    /**
     * Schedules that could plausibly owe their audience an email.
     *
     * Deliberately NOT hosted-only, unlike SendActivationNudges. That command sells to owners;
     * this one keeps a promise made to guests, and on selfhost the subscribe panel is the ONLY
     * capture surface a visitor ever sees (the Follow modal is hosted-gated), so selfhost is
     * where an unkept promise would be most visible.
     *
     * @return Collection<int, Role>
     */
    private function dueRoles(): Collection
    {
        // Matches SendFeedbackRequests: on selfhost a log/array mailer means the operator has not
        // configured mail at all, and queueing bulk mail into it helps nobody.
        if (! config('app.hosted') && in_array(config('mail.default'), ['log', 'array'], true)) {
            $this->info('Skipping: no mail transport configured.');

            return collect();
        }

        return Role::query()
            ->where('is_deleted', false)
            ->where('announce_new_events', true)
            ->when($this->option('subdomain'), fn ($q) => $q->where('subdomain', $this->option('subdomain')))
            // whereHas rather than a join: a schedule with no confirmed subscriber has nobody to
            // tell, and there are far more of those than not.
            ->whereHas('subscribers', fn ($q) => $q->whereNotNull('confirmed_at'))
            ->with('user')
            ->get()
            ->reject(fn (Role $role) => is_demo_role($role));
    }

    /**
     * Events this schedule published since its last announcement, that a stranger could actually
     * open.
     *
     * PUBLIC only: is_draft covers both draft and internal (Event::setVisibilityState sets
     * is_draft for each), and is_private is the unlisted state, whose whole point is that it is
     * not broadcast. Announcing either would leak an event the owner deliberately hid.
     *
     * @return Collection<int, Event>
     */
    private function newEventsFor(Role $role): Collection
    {
        return Event::query()
            ->where('creator_role_id', $role->id)
            ->where('is_draft', false)
            ->where('is_private', false)
            // Matches the guest calendar's own filter (RoleController::viewGuest and friends): a
            // password-gated event is not something to broadcast either.
            ->whereNull('event_password')
            ->where('created_at', '>', $role->last_announced_at)
            // The repo's own scope, so "still worth telling somebody about" means the same thing
            // here as on the calendar - including a multi-day event that has started and is still
            // running. Back-filling last month's gigs is an ordinary thing to do and is not news.
            ->upcomingOrOngoing()
            ->orderBy('starts_at')
            ->with(['venue', 'creatorRole'])
            ->limit(25)
            ->get();
    }
}
