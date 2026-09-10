<?php

namespace App\Console\Commands;

use App\Jobs\SendQueuedEmail;
use App\Mail\EventInterestNotification;
use App\Models\EventInterest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The two emails somebody who asked about an event gets: tickets went on sale, and it is coming up.
 *
 * Dry by default, like SendEventAnnouncements and SendActivationNudges: no flag prints what it
 * would do, --apply sends. Nothing here can be recalled once sent.
 *
 * The safety properties, all load-bearing, and every one of them a property of this file that a
 * later change could quietly remove:
 *
 *   1. THE CLAIM IS A CONDITIONAL UPDATE, TAKEN BEFORE THE DISPATCH. tickets_notified_at and
 *      reminder_sent_at are stamped with a whereNull() guard, so exactly one runner transitions a
 *      row and the loser skips. The two rails (routes/console.php and translateData()) hold
 *      different mutexes and can reach the same row at once, which is the same hazard
 *      SendEventAnnouncements::claimWindow() documents. Claiming AFTER the send would re-send to
 *      everyone already mailed on the next tick if anything threw mid-loop.
 *
 *   2. THERE IS NO FIRST-RUN BACKLOG, and that is by construction rather than by luck. The
 *      "tickets are on sale" claim is pre-stamped at CAPTURE time by EventInterestController when
 *      the event already sells, so a row can only qualify if the tickets appeared after somebody
 *      asked. Without that, the first run would mail every interested person about every event
 *      that already had a ticket type - the mailshot SendEventAnnouncements needs a watermark
 *      column to avoid.
 *
 *   3. THE CEILING COUNTS RECIPIENTS, not events. An event-shaped ceiling would let one event with
 *      a large interest list spend the platform's whole sending reputation as a single tick.
 *
 *   4. THE OCCURRENCE IS RESOLVED IN THE SCHEDULE'S TIMEZONE. Modelled on SendCarpoolReminders,
 *      which uses getStartDateTime($date, true, $event->scheduleTimezone()) - NOT on
 *      SendAppointmentReminders, which compares raw starts_at strings with no conversion and has
 *      no per-occurrence logic at all (an appointment is a single-instance row). For a recurring
 *      event starts_at is the series anchor, so comparing it would remind people about the wrong
 *      date, or never.
 *
 *   5. IT RUNS ON THE ANNOUNCEMENT RAIL, NOT THE APPOINTMENT ONE. SendAppointmentReminders skips
 *      `config('app.hosted') && ! $role->hasEmailSettings()` outright - "a schedule whose guests
 *      never got a confirmation must not suddenly get a platform-branded reminder" - and
 *      EventChangeNotifier returns on the same condition. That gate would make this dark for most
 *      schedules on the platform, which are exactly the ones this feature exists for. It is
 *      defensible to send here because the recipient ASKED US, by name, on that schedule's page.
 *      Role::canSendAudienceMail() is what bounds it instead.
 */
class SendEventInterestMail extends Command
{
    /** How many messages share one dispatch delay step, and how many seconds apart the steps are. */
    private const DISPATCH_CHUNK = 50;

    private const DISPATCH_STAGGER_SECONDS = 15;

    protected $signature = 'app:send-event-interest-mail {--apply : Actually send} {--kind= : tickets|reminder}';

    protected $description = 'Email people who asked about an event when tickets go on sale, and before it starts';

    private int $queued = 0;

    /** Per-event memo of the transport gate's answer, which cannot change within a run. */
    private array $mailable = [];

    /** Per-event memo of the guest-visibility check, for the same reason. */
    private array $visible = [];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $only = $this->option('kind');

        if ($only && ! in_array($only, [EventInterestNotification::KIND_TICKETS, EventInterestNotification::KIND_REMINDER], true)) {
            $this->error('--kind must be tickets or reminder');

            return self::FAILURE;
        }

        // NO MAIL TRANSPORT, NO SEND - and on this command that is a data-integrity rule, not a
        // politeness. SendEventAnnouncements and SendFeedbackRequests both bail on the same
        // condition because queueing bulk mail into the log driver helps nobody. Here it is worse:
        // the claim at sendPass() is a one-shot conditional UPDATE taken BEFORE the dispatch, and
        // it is handed back only when the dispatch THROWS. The log mailer never throws. So a run
        // against `log` stamps tickets_notified_at / reminder_sent_at on every candidate, writes
        // the message to storage/logs, and candidates() - which filters on whereNull($column) -
        // can never return those rows again. The people who asked to hear about an event are
        // silently and permanently unreachable.
        //
        // That is reachable by default: .env.example ships MAIL_MAILER=log, capture is single
        // opt-in (EventInterestController stamps confirmed_at at capture, so rows are live
        // immediately), and Role::canSendAudienceMail() - this command's only other gate -
        // short-circuits to true off-platform.
        if (! config('app.hosted') && in_array(config('mail.default'), ['log', 'array'], true)) {
            $this->info('Skipping: no mail transport configured.');

            return self::SUCCESS;
        }

        if (! $only || $only === EventInterestNotification::KIND_TICKETS) {
            $this->sendPass(EventInterestNotification::KIND_TICKETS, $apply);
        }

        if (! $only || $only === EventInterestNotification::KIND_REMINDER) {
            $this->sendPass(EventInterestNotification::KIND_REMINDER, $apply);
        }

        if (! $apply) {
            $this->info('Dry run. Pass --apply to send.');
        }

        return self::SUCCESS;
    }

    private function sendPass(string $kind, bool $apply): void
    {
        $column = $kind === EventInterestNotification::KIND_TICKETS ? 'tickets_notified_at' : 'reminder_sent_at';
        $budget = max(1, (int) config('usage.event_interest_recipient_batch'));

        foreach ($this->candidates($kind, $budget) as $interest) {
            if ($this->queued >= $budget) {
                Log::info('app:send-event-interest-mail hit the recipient ceiling', ['queued' => $this->queued]);
                break;
            }

            $event = $interest->event;
            $role = $event?->creatorRole;

            // getGuestUrl() returns '' for a schedule with no subdomain, which would render the
            // email's primary button with an empty href.
            if (! $event || ! $role || $role->is_deleted || ! $role->subdomain || is_demo_role($role)) {
                continue;
            }

            if (! $this->isDue($kind, $interest)) {
                continue;
            }

            // Visibility is re-checked at SEND time, not just at capture. An owner who moves an
            // event to Draft, Internal, Unlisted or password-protected after somebody asked about
            // it would otherwise still have "tickets are on sale" mailed to strangers, with a
            // primary button pointing at a page that 404s or password-gates for them.
            //
            // Memoised per event: it costs a pivot query, and this loop streams.
            if (! isset($this->visible[$event->id])) {
                $this->visible[$event->id] = ! $event->guestVisibilityFailure($role, false);
            }

            if (! $this->visible[$event->id]) {
                continue;
            }

            // The transport gate, given the REAL number of people this event would reach.
            //
            // It used to pass a hardcoded 1, which made it inert: canSendAudienceMail() ends in
            // `$recipients > 0 && $recipients <= $limit` with a limit of 50, so 1 always passed and
            // an unverified schedule could push its whole list through the shared platform mailer
            // one message at a time. Every other caller passes the real count.
            //
            // Memoised per event: the answer cannot change within a run, and this loop streams.
            // Returns true on selfhost and in tests, so a test asserting a refusal has to turn
            // app.is_testing off - see EventAnnouncementTest.
            if (! isset($this->mailable[$event->id])) {
                $this->mailable[$event->id] = $role->canSendAudienceMail(
                    EventInterest::confirmed()->where('event_id', $event->id)->distinct()->count('email'),
                    $role->user
                );
            }

            if (! $this->mailable[$event->id]) {
                continue;
            }

            if (! $apply) {
                $this->line(sprintf('[dry] %s -> %s (%s)', $kind, $interest->email, $event->name));
                $this->queued++;

                continue;
            }

            // Claim BEFORE dispatching, conditionally: a row already stamped by the other rail
            // returns 0 and is skipped rather than mailed twice.
            $claimed = DB::table('event_interests')
                ->where('id', $interest->id)
                ->whereNull($column)
                ->update([$column => now(), 'updated_at' => now()]) > 0;

            if (! $claimed) {
                continue;
            }

            try {
                $this->dispatchOne($kind, $interest, $event, $role);
                $this->queued++;
            } catch (\Throwable $e) {
                // Hand the claim back so the next run retries, rather than swallowing a failure
                // behind a stamped column.
                //
                // whereNotNull matches ANY non-null value, so this is not the conditional the
                // forward claim is. It does not need to be: the claim above is whereNull, so only
                // one runner can ever hold a row, and the only stamp this can find is its own.
                DB::table('event_interests')
                    ->where('id', $interest->id)
                    ->whereNotNull($column)
                    ->update([$column => null]);

                report($e);
            }
        }
    }

    /**
     * Bounded, and prefiltered in SQL before the per-row timezone work.
     *
     * The reminder pass narrows on event_date, a 'Y-m-d' string, to a window generous enough to
     * cover every timezone either side of the target - the exact occurrence is then resolved in
     * the schedule's own zone by isDue(). Filtering only in PHP would mean loading every unsent
     * row on the platform, which is the unbounded ->get() SendCarpoolReminders still has.
     */
    private function candidates(string $kind, int $budget)
    {
        $column = $kind === EventInterestNotification::KIND_TICKETS ? 'tickets_notified_at' : 'reminder_sent_at';

        $query = EventInterest::confirmed()
            ->whereNull($column)
            ->with(['event.creatorRole', 'event.venue'])
            // A cancelled event owes its list a cancellation notice, which EventChangeNotifier
            // sends. It will never owe them either of these, so its rows must not sit in the window
            // for ever - see the starvation note below.
            ->whereHas('event', fn ($q) => $q->where('is_cancelled', false))
            ->orderBy('id');

        if ($kind === EventInterestNotification::KIND_REMINDER) {
            $hours = max(1, (int) config('usage.event_interest_reminder_hours'));

            $query->where(function ($q) use ($hours) {
                $q->where('event_date', '')
                    ->orWhereBetween('event_date', [
                        now()->subDay()->format('Y-m-d'),
                        now()->addHours($hours)->addDay()->format('Y-m-d'),
                    ]);
            });
        } else {
            // THE STARVATION GUARD, and it is load-bearing.
            //
            // isDue() skips a row without stamping it, which is correct - an upcoming event with no
            // ticket type yet is "not yet", not "never". But a row whose occurrence has PASSED can
            // never become due again, and nothing removes it. Those rows keep the lowest ids, so
            // ORDER BY id LIMIT n eventually returns nothing but corpses and the feature stops
            // sending, silently, with no error and no log. Only ~33% of schedules ever create a
            // ticket type, so dead rows are the majority case by construction.
            //
            // Excluded in SQL rather than stamped: stamping would permanently silence anyone whose
            // event is later rescheduled or un-cancelled. A day of slack absorbs the timezone skew
            // between this UTC-ish comparison and isDue()'s per-schedule one.
            $maxAge = max(1, (int) config('usage.event_interest_tickets_max_age_days'));

            $query->where(function ($q) {
                $q->where('event_date', '')
                    ->orWhere('event_date', '>=', now()->subDay()->format('Y-m-d'));
            });

            // The dateless case has no occurrence to age out, so it is bounded by the age of the
            // ASK instead: an event that has not started selling within six months of somebody
            // asking is not going to produce a welcome email.
            $query->where('event_interests.created_at', '>=', now()->subDays($maxAge));
        }

        // Twice the budget: isDue() rejects some of what SQL let through, so a limit of exactly
        // the budget would under-fill a run.
        //
        // lazyById(), not cursor(): Builder::cursor() maps raw records through newFromBuilder() and
        // never calls eagerLoadRelations(), so the ->with() above is silently discarded and every
        // row re-queries its event, creator role, venue and ticket set.
        return $query->limit($budget * 2)->lazyById(200);
    }

    private function isDue(string $kind, EventInterest $interest): bool
    {
        $event = $interest->event;
        $date = $interest->event_date ?: null;

        if ($event->is_cancelled) {
            // A cancelled event owes its interest list a cancellation notice, which
            // EventChangeNotifier sends. It must never send "tickets are on sale" instead.
            return false;
        }

        if ($kind === EventInterestNotification::KIND_TICKETS) {
            // starts_at guarded here as well as on the reminder branch below. canSellTickets()
            // skips all its date checks for a dateless event and can return true, and both mail
            // views then call getStartDateTime(), which has no null guard and THROWS - poisoning
            // the job with the claim column already stamped, so that person never hears.
            return $event->starts_at && $event->canSellTickets($date);
        }

        if (! $event->starts_at) {
            return false;
        }

        $hours = max(1, (int) config('usage.event_interest_reminder_hours'));
        $starts = $event->getStartDateTime($date, true, $event->scheduleTimezone());

        // Inside the window and not already gone.
        return $starts->isFuture() && $starts->lte(now()->addHours($hours));
    }

    private function dispatchOne(string $kind, EventInterest $interest, $event, $role): void
    {
        $mailable = new EventInterestNotification(
            $role,
            $event,
            $interest,
            $kind,
            $event->getGuestUrl($role->subdomain, $interest->event_date ?: null, true),
            route('event.interest.show_unsubscribe', ['token' => $interest->token]),
        );

        SendQueuedEmail::dispatch(
            $mailable,
            $interest->email,
            $role->id,
            $interest->locale ?: ($role->language_code ?: config('app.locale')),
        )->delay(now()->addSeconds(intdiv($this->queued, self::DISPATCH_CHUNK) * self::DISPATCH_STAGGER_SECONDS));
    }
}
