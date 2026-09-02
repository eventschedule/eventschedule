<?php

namespace App\Console\Commands;

use App\Jobs\SendQueuedEmail;
use App\Mail\EventAnnouncement;
use App\Models\Event;
use App\Models\Role;
use App\Services\AudienceResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
 * Dry by default, like SendActivationNudges: no flag prints what it would do, --apply sends.
 *
 * SCHEDULED ON BOTH RAILS, hourly. It was hand-run only until the seven hazards below were
 * closed; each one could put duplicated or unbounded mail on the shared sending reputation, and
 * none of it can be recalled once sent. Kept here as the record of what makes it safe, because
 * every one of them is a property of this file that a later change could quietly remove:
 *
 *   a. CLOSED. The batch ceiling counted SCHEDULES, not recipients, so one schedule with a large
 *      audience was a single tick against it. usage.audience_announcement_recipient_batch is now
 *      the ceiling that bounds outbound mail, and dispatchTo() staggers the queue the way
 *      NewsletterService::send() does - 50 messages per 15-second step.
 *   b. CLOSED. last_announced_at was stamped AFTER the dispatch loop with no try/catch, so a
 *      throw mid-loop re-sent to everyone already mailed on the next tick, deterministically.
 *      claimWindow() now claims BEFORE sending and the catch hands the window back.
 *   c. CLOSED. The two rails hold different mutexes, so both could reach one row at once.
 *      claimWindow() is a conditional UPDATE naming the value that was read, so exactly one
 *      runner transitions it and the loser skips - the column-shaped version of the
 *      insertOrIgnore claim in SendActivationNudges.
 *   d. CLOSED. newEventsFor() keyed on created_at, so a draft written before the watermark and
 *      published after it was never announced - the ordinary draft-ahead workflow. Event::boot()
 *      stamps published_at on the draft-to-public transition and the query reads
 *      COALESCE(published_at, created_at), so older rows keep today's behaviour exactly.
 *   e. CLOSED. A canSendAudienceMail() refusal continued without stamping and so re-resolved and
 *      re-refused every hour for ever. It now claims the window like any other outcome.
 *   f. CLOSED. The watermark writes used save(), firing Role::boot()'s saving hook and with it a
 *      synchronous Google Geocoding call per schedule inside a mail loop. claimWindow() goes
 *      through the query builder.
 *   g. CLOSED. AudienceResolver::suppressedEmails() ran a platform-wide scan of an unindexed
 *      users.is_subscribed once PER ROLE. The platform half is memoized per resolver instance.
 *
 * Also closed, and worth keeping closed: dueRoles() orders by watermark so budget exhaustion
 * cannot starve the same schedule run after run; the digest cap reports truncation instead of
 * dropping events 26+ while the watermark advanced past them; and dueRoles() requires a claimed
 * schedule, because getGuestUrl() returns '' for an unclaimed one and would render the email's
 * primary button with an empty href.
 */
class SendEventAnnouncements extends Command
{
    /**
     * How many messages share one dispatch delay step, and how many seconds apart the steps are.
     *
     * Mirrors NewsletterService::send(), which stages its batches 50 at a time 15 seconds apart.
     */
    private const DISPATCH_CHUNK = 50;

    /** How many events one digest lists. Going over this is reported rather than silently cut. */
    private const MAX_EVENTS_PER_DIGEST = 25;

    private const DISPATCH_STAGGER_SECONDS = 15;

    protected $signature = 'app:send-event-announcements
        {--apply : Send the emails. Without this the command only reports.}
        {--subdomain= : Run one schedule only, for testing.}';

    protected $description = 'Email each schedule\'s confirmed audience about newly published events';

    public function handle(AudienceResolver $audience): int
    {
        $apply = (bool) $this->option('apply');
        $minHours = max(1, (int) config('usage.audience_announcement_min_hours', 72));
        $budget = max(1, (int) config('usage.audience_announcement_batch', 100));

        // The ceiling that actually bounds outbound mail. The schedule budget above bounds how
        // many WATERMARKS move; one schedule with 40k confirmed subscribers is a single tick
        // against it and 40k messages against the platform's sending reputation.
        $recipientBudget = max(1, (int) config('usage.audience_announcement_recipient_batch', 2000));

        $sent = 0;
        $stamped = 0;
        $mailed = 0;

        foreach ($this->dueRoles() as $role) {
            if ($budget <= 0 || $recipientBudget <= 0) {
                $this->info('Batch ceiling reached; the rest will go out on the next run.');
                break;
            }

            // First sighting. Stamp the watermark and send nothing - everything already on the
            // schedule predates the audience being told they would hear about new events.
            if (! $role->last_announced_at) {
                $this->line("[baseline] {$role->subdomain}");
                if ($apply) {
                    $this->claimWindow($role, null);
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
                    $this->claimWindow($role, $role->last_announced_at);
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

                // Stamp anyway. A refusal that leaves the watermark alone re-resolves the same
                // events, re-counts the same audience and re-refuses on every run for ever - the
                // defect this feature already fixed for scheduled newsletters in
                // NewsletterService::send(). The events are not announced; they are also no longer
                // pending, which is the honest state.
                if ($apply) {
                    $this->claimWindow($role, $role->last_announced_at);
                }

                continue;
            }

            $this->line(sprintf(
                '[announce] %s: %d event(s) to %d recipient(s)',
                $role->subdomain,
                $events->count(),
                $recipients->count(),
            ));

            if ($apply) {
                // CLAIM BEFORE SENDING, and claim by naming the value we read.
                //
                // Two things were wrong with stamping afterwards. A throw anywhere in the dispatch
                // loop left the watermark untouched, so the next run re-sent to everyone already
                // mailed - deterministically, not as a race. And routes/console.php and
                // AppController::translateData() hold DIFFERENT mutexes, so both rails can reach
                // this row at once; a conditional UPDATE naming the value we read means exactly one
                // of them changes a row and the loser skips. Same reasoning as the insertOrIgnore
                // claim in SendActivationNudges, expressed against a column instead of a table.
                $claimedFrom = $role->last_announced_at;

                if (! $this->claimWindow($role, $claimedFrom)) {
                    $this->line("[skip] {$role->subdomain}: claimed by a concurrent run");

                    continue;
                }

                try {
                    $this->dispatchTo($role, $recipients, $events);
                } catch (\Throwable $e) {
                    // Hand the window back so the next run retries, rather than swallowing a
                    // partial send behind an advanced watermark. Conditional again: if another
                    // runner has since claimed it, the newer value is the correct one to keep.
                    //
                    // $role->last_announced_at, NOT now(): claimWindow() wrote a specific timestamp
                    // and synced it onto the model, and this column has second precision. Naming
                    // now() here would match only while the dispatch loop finished inside the same
                    // second - so a fast failure rolled back and a slow one, which is the case that
                    // actually happens, silently did not.
                    $this->claimWindow($role, $role->last_announced_at, $claimedFrom);
                    report($e);
                    Log::error('Event announcement dispatch failed', [
                        'role_id' => $role->id,
                        'error' => $e->getMessage(),
                    ]);

                    continue;
                }

                $mailed += $recipients->count();
            }

            $sent++;
            $budget--;
            $recipientBudget -= $recipients->count();
        }

        $this->info($apply
            ? "Announced for {$sent} schedule(s) to {$mailed} recipient(s); {$stamped} baselined."
            : "Would announce for {$sent} schedule(s); {$stamped} would be baselined. Re-run with --apply.");

        return 0;
    }

    /**
     * Queue one announcement per recipient, staggered.
     *
     * One mailable each rather than one batch job, because every message carries that
     * subscriber's own unsubscribe token. Chunked and delayed the way NewsletterService::send()
     * staggers its batches, so a schedule with a large audience drains over minutes instead of
     * handing the mailer its whole list in one tick.
     *
     * @param  Collection<int, \App\Models\RoleSubscriber>  $recipients
     * @param  Collection<int, Event>  $events
     */
    private function dispatchTo(Role $role, Collection $recipients, Collection $events): void
    {
        foreach ($recipients->values() as $index => $subscriber) {
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
            )->delay(now()->addSeconds(intdiv($index, self::DISPATCH_CHUNK) * self::DISPATCH_STAGGER_SECONDS));
        }
    }

    /**
     * Move roles.last_announced_at from the value we read to a new one, atomically.
     *
     * Returns whether this runner owned the transition. A false means a concurrent run on the
     * other rail got there first and has already taken responsibility for this window.
     *
     * Written with the query builder, not save(): Role::boot()'s saving hook performs a
     * SYNCHRONOUS Google Geocoding call for any role whose geo_address is stale, and a watermark
     * is not a reason to make a network request per schedule inside a mail loop.
     */
    private function claimWindow(Role $role, ?\Illuminate\Support\Carbon $from, ?\Illuminate\Support\Carbon $to = null): bool
    {
        $to = $to ?: now();

        $query = DB::table('roles')->where('id', $role->id);

        // `where('col', null)` compiles to `= NULL`, which matches nothing - the baseline branch
        // is exactly the case where the value we read IS null, so it needs whereNull.
        $query = $from === null
            ? $query->whereNull('last_announced_at')
            : $query->where('last_announced_at', $from);

        $claimed = $query->update(['last_announced_at' => $to]) > 0;

        if ($claimed) {
            // Keep the in-memory model honest for anything downstream in this run, without
            // marking it dirty - nothing here may trigger a save().
            $role->last_announced_at = $to;
            $role->syncOriginalAttribute('last_announced_at');
        }

        return $claimed;
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
            // Claimed only. getGuestUrl() returns '' for an unclaimed schedule, which would render
            // the announcement's primary button with an empty href; and an unclaimed schedule has
            // no owner who could have opted into announcing in the first place. canSendAudienceMail()
            // fails closed on a missing owner, but only on hosted - it returns true on its first
            // line for selfhost, so this filter is what covers a selfhost install.
            ->whereNotNull('user_id')
            ->when($this->option('subdomain'), fn ($q) => $q->where('subdomain', $this->option('subdomain')))
            // whereHas rather than a join: a schedule with no confirmed subscriber has nobody to
            // tell, and there are far more of those than not.
            ->whereHas('subscribers', fn ($q) => $q->whereNotNull('confirmed_at'))
            ->with('user')
            // Deterministic order, oldest watermark first. Without it, which schedules get served
            // when the budget runs out was whatever order MySQL happened to return - so an
            // unlucky schedule could be starved run after run. NULLs sort first in MySQL ASC,
            // which puts never-seen schedules at the front where a baseline stamp is cheap.
            ->orderByRaw('last_announced_at IS NOT NULL, last_announced_at ASC')
            ->orderBy('id')
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
            // published_at, falling back to created_at. Keying on created_at alone meant a draft
            // written before the watermark and made public after it was never announced - which is
            // the ordinary "write it up now, publish on the day" workflow, i.e. exactly the events
            // an audience most wants to hear about. Event::boot() stamps published_at on the
            // draft-to-public transition; the COALESCE covers rows that predate that stamp, for
            // which created_at is what this used to compare and so changes nothing.
            ->whereRaw('COALESCE(events.published_at, events.created_at) > ?', [$role->last_announced_at])
            // The repo's own scope, so "still worth telling somebody about" means the same thing
            // here as on the calendar - including a multi-day event that has started and is still
            // running. Back-filling last month's gigs is an ordinary thing to do and is not news.
            ->upcomingOrOngoing()
            ->orderBy('starts_at')
            ->with(['venue', 'creatorRole'])
            ->limit(self::MAX_EVENTS_PER_DIGEST + 1)
            ->get()
            ->pipe(function (Collection $events) use ($role) {
                // The cap used to truncate in silence while the watermark advanced past
                // everything, so events 26+ were dropped and never announced by any later run.
                // Fetching one extra row is how we can tell "exactly at the cap" from "over it".
                if ($events->count() <= self::MAX_EVENTS_PER_DIGEST) {
                    return $events;
                }

                Log::warning('Event announcement digest truncated', [
                    'role_id' => $role->id,
                    'cap' => self::MAX_EVENTS_PER_DIGEST,
                ]);

                $this->warn(sprintf(
                    '[truncated] %s: more than %d new events; the digest lists the soonest %d.',
                    $role->subdomain,
                    self::MAX_EVENTS_PER_DIGEST,
                    self::MAX_EVENTS_PER_DIGEST,
                ));

                return $events->take(self::MAX_EVENTS_PER_DIGEST);
            });
    }
}
