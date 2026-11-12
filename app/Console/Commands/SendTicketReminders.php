<?php

namespace App\Console\Commands;

use App\Jobs\SendQueuedEmail;
use App\Mail\TicketReminder;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\TicketReminderSetting;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * "See you tomorrow": one email to every ticket holder and registered guest before their occurrence
 * starts, at the time the owning schedule chose (Tickets > Reminders; on, a day before, when it has
 * chosen nothing).
 *
 * Dry by default like SendEventInterestMail: no flag prints what it would do, --apply sends. Both
 * cron rails pass --apply.
 *
 * Load-bearing properties, each one something a later change could quietly remove:
 *
 *   1. OCCURRENCE FIRST, so nothing starves. The rows that are never due here (a schedule that
 *      switched reminders off, a ticket bought after its reminder moment, an occurrence three days
 *      out on a three-hour schedule) are never stamped, so a sale-first walk ordered by id with a
 *      limit would keep returning them and stop sending. Instead the distinct (event, date) pairs
 *      in a few days' window are listed through the sales.event_date index, each is resolved once
 *      (its start in the schedule's zone, its schedule's setting), and only a DUE occurrence's
 *      sales are fetched, already filtered in SQL to the ones that will be sent. An occurrence is
 *      always the sale's own date: the Event saving hook re-dates the sales when a one-off event
 *      moves, and clears their reminder stamp when it moves to another day.
 *
 *   2. THE CLAIM IS A CONDITIONAL UPDATE, TAKEN BEFORE THE DISPATCH, on sales.reminder_sent_at
 *      (the column appointment reminders use; appointments are excluded here, so the two never
 *      meet). The two rails hold different mutexes and can reach the same row at once; the
 *      whereNull() guard lets exactly one of them through. Handed back if the dispatch throws.
 *
 *   3. THE SETTING IS THE OWNING SCHEDULE'S (events.creator_role_id, or the schedule an older event
 *      was first put on), never the schedule whose mail settings carry the message:
 *      getRoleWithEmailSettings() prefers the venue, and a talent owner's "off" must not be
 *      overridden by the venue the event happens at.
 *
 *   4. WHO IS NOT MAILED: imported attendees (the import asked its owner whether to email them),
 *      season-pass holders (no occurrence of their own), an instalment plan in arrears (the door
 *      turns them away), an occurrence a recurring event no longer has, a cancelled event, an
 *      appointment (its own reminder), a demo schedule, a deleted or blocked owning schedule.
 *
 *   5. NO MAIL TRANSPORT, NO SEND. Off hosted with MAIL_MAILER=log the claim would be stamped and
 *      the message written to a log, and those guests would never be reminded. Same guard as
 *      SendEventInterestMail. TICKET_REMINDERS=false stops the whole install.
 */
class SendTicketReminders extends Command
{
    /** How many messages share one dispatch delay step, and how many seconds apart the steps are. */
    private const DISPATCH_CHUNK = 50;

    private const DISPATCH_STAGGER_SECONDS = 15;

    /** Day-based reminders never land in the night: a moment between these hours moves to 20:00 the evening before. */
    private const QUIET_FROM = 22;

    private const QUIET_UNTIL = 8;

    private const QUIET_SEND_HOUR = 20;

    protected $signature = 'app:send-ticket-reminders {--apply : Actually send}';

    protected $description = 'Email ticket holders and registered guests before their event starts';

    private int $queued = 0;

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        // The operator's stop switch (TICKET_REMINDERS=false), for the install as a whole. Owners
        // have their own, per schedule.
        if (! config('usage.ticket_reminders_enabled')) {
            $this->info('Skipping: ticket reminders are switched off for this install.');

            return self::SUCCESS;
        }

        if (! config('app.hosted') && in_array(config('mail.default'), ['log', 'array'], true)) {
            $this->info('Skipping: no mail transport configured.');

            return self::SUCCESS;
        }

        $budget = max(1, (int) config('usage.ticket_reminder_recipient_batch'));
        $maxHours = max(TicketReminderSetting::HOURS_OPTIONS);

        // Wide enough for the longest reminder plus a day of slack either side for timezones, and
        // through the sales.event_date index: a condition on the events table here (an OR with
        // events.starts_at, say) walks every unreminded paid sale on the install, every hour.
        $occurrences = self::remindable(DB::table('sales')->join('events', 'events.id', '=', 'sales.event_id'))
            ->whereBetween('sales.event_date', [
                now()->subDay()->format('Y-m-d'),
                now()->addHours($maxHours)->addDay()->format('Y-m-d'),
            ])
            ->where('events.is_cancelled', false)
            ->whereNull('events.appointment_type_id')
            ->whereNotNull('events.starts_at')
            ->groupBy('sales.event_id', 'sales.event_date')
            ->orderBy('sales.event_date')
            ->get(['sales.event_id', 'sales.event_date']);

        if ($occurrences->isEmpty()) {
            $this->info('No reminders due.');

            return self::SUCCESS;
        }

        $events = Event::with(['creatorRole.ticketReminderSetting', 'creatorRole.subscriptions', 'roles.ticketReminderSetting', 'roles.subscriptions'])
            ->whereIn('id', $occurrences->pluck('event_id')->unique()->all())
            ->get()
            ->keyBy('id');

        foreach ($occurrences as $occurrence) {
            if ($this->queued >= $budget) {
                Log::info('app:send-ticket-reminders hit the recipient ceiling', ['queued' => $this->queued]);
                break;
            }

            $event = $events->get($occurrence->event_id);
            $moment = $event ? $this->dueMoment($event, $occurrence->event_date) : null;

            if (! $moment) {
                continue;
            }

            $this->remindOccurrence($event, $occurrence->event_date, $moment, $budget, $apply);
        }

        $this->info(($apply ? 'Queued ' : '[dry] Would queue ').$this->queued.' ticket reminders.');

        return self::SUCCESS;
    }

    /**
     * The sales this command would ever mail, as SQL: paid (or a "pay the organizer" order, which
     * stays unpaid until it is paid at the door, and is as much a seat as a paid one), live, not
     * reminded yet, not imported,
     * with an address that is not a test one, and not a season pass. Shared by the occurrence list,
     * the per-occurrence fetch and the interest list's stand-down (SendEventInterestMail, which asks
     * whether a reminder WILL go, so it leaves the "not reminded yet" condition out), so a row that
     * can never be sent cannot hold a place in either, and the two commands cannot disagree.
     */
    public static function remindable($query, bool $unremindedOnly = true)
    {
        return $query->where(fn ($q) => $q->where('sales.status', 'paid')
            ->orWhere(fn ($q) => $q->where('sales.status', 'unpaid')->where('sales.payment_method', 'cash')))
            ->where('sales.is_deleted', false)
            ->when($unremindedOnly, fn ($q) => $q->whereNull('sales.reminder_sent_at'))
            ->where(fn ($q) => $q->whereNull('sales.payment_method')->orWhere('sales.payment_method', '!=', 'import'))
            ->whereNotNull('sales.email')
            ->where('sales.email', '!=', '')
            ->where('sales.email', 'not like', '%@example.%')
            ->where('sales.email', 'not like', '%@test.com')
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                ->from('sale_tickets')
                ->join('tickets', 'tickets.id', '=', 'sale_tickets.ticket_id')
                ->whereColumn('sale_tickets.sale_id', 'sales.id')
                ->where('tickets.is_pass', true));
    }

    /** The schedule whose reminder setting counts: the event's own, or, for an event older than that column, the one it was first put on. */
    public static function owningSchedule(Event $event): ?Role
    {
        return $event->creatorRole ?? $event->roles->sortBy(fn ($role) => $role->pivot->id)->first();
    }

    /**
     * The moment this occurrence's reminder is due, when it is due now; null when it is not (yet,
     * or ever).
     */
    private function dueMoment(Event $event, string $date): ?Carbon
    {
        $owner = self::owningSchedule($event);

        if (! $owner || $owner->is_deleted || is_demo_role($owner) || ! $event->starts_at) {
            return null;
        }

        if (! $owner->ticketRemindersOn()) {
            return null;
        }

        $setting = $owner->ticketReminderSetting;

        // A sale keeps the date it was bought for; the owner may since have skipped that date,
        // ended the series, or turned the series into a one-off on another day. Asked of every
        // event: matchesDate() reads a one-off's (and a multi-day event's) own days too.
        if (! $event->matchesDate($date)) {
            return null;
        }

        $start = $event->occurrenceStartUtc($date);
        $moment = self::reminderMoment($start, (int) $setting->hours_before, $event->scheduleTimezone());

        return now()->gte($moment) && now()->lt($start) ? $moment : null;
    }

    /**
     * When a reminder for an occurrence starting at $start (UTC) goes out. A day-based reminder whose
     * moment falls in the night, in the schedule's own zone, goes at 20:00 the evening before
     * instead: "1 day before" a midnight show would otherwise wake people at midnight.
     */
    public static function reminderMoment(Carbon $start, int $hours, string $timezone): Carbon
    {
        $moment = $start->copy()->subHours($hours);

        if ($hours < 24) {
            return $moment;
        }

        $local = $moment->copy()->setTimezone($timezone);

        if ($local->hour >= self::QUIET_FROM) {
            $local->setTime(self::QUIET_SEND_HOUR, 0);
        } elseif ($local->hour < self::QUIET_UNTIL) {
            $local->subDay()->setTime(self::QUIET_SEND_HOUR, 0);
        }

        return $local->setTimezone('UTC');
    }

    private function remindOccurrence(Event $event, string $date, Carbon $moment, int $budget, bool $apply): void
    {
        $owner = self::owningSchedule($event);
        $mailRole = $event->getRoleWithEmailSettings();

        // getRoleWithEmailSettings() falls back to the venue whatever its state; a venue somebody
        // deleted must not silence every holder of every event at it.
        if (! $mailRole || $mailRole->is_deleted) {
            $mailRole = $owner;
        }

        if (! EmailService::canSendScheduleMail($mailRole, true)) {
            return;
        }

        $sales = self::remindable(Sale::query())
            ->where('sales.event_id', $event->id)
            ->where('sales.event_date', $date)
            // Bought before the reminder was due: a ticket bought two hours before the doors open
            // has its receipt, and a "see you soon" an hour later says nothing new.
            // created_at is written on the app's clock, $moment is UTC: compared on the same one.
            ->where('sales.created_at', '<', $moment->copy()->setTimezone(config('app.timezone')))
            ->with(['installmentPlan'])
            ->orderBy('sales.id')
            ->limit($budget - $this->queued)
            ->get();

        foreach ($sales as $sale) {
            if ($sale->isInstallmentDelinquent()) {
                continue;
            }

            if (! $apply) {
                $this->line(sprintf('[dry] %s -> %s (%s %s)', $sale->id, $sale->email, $event->name, $date));
                $this->queued++;

                continue;
            }

            $claimed = DB::table('sales')
                ->where('id', $sale->id)
                ->whereNull('reminder_sent_at')
                ->update(['reminder_sent_at' => now()]) > 0;

            if (! $claimed) {
                continue;
            }

            try {
                SendQueuedEmail::dispatch(
                    new TicketReminder($sale, $event, $mailRole),
                    $sale->email,
                    $mailRole->id,
                    $mailRole->language_code ?: config('app.locale'),
                )->delay(now()->addSeconds(intdiv($this->queued, self::DISPATCH_CHUNK) * self::DISPATCH_STAGGER_SECONDS));
                $this->queued++;
            } catch (\Throwable $e) {
                DB::table('sales')->where('id', $sale->id)->update(['reminder_sent_at' => null]);
                report($e);
            }
        }
    }
}
