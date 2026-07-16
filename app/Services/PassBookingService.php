<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Models\Ticket;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Advance booking for passes / subscriptions.
 *
 * When a pass has `pass_allow_booking`, the holder may reserve a seat for a
 * specific occurrence ahead of time instead of only scanning in at the door. A
 * reservation is one `pass_usages` entry tagged `kind: booking`; it draws from
 * the same shared per-occurrence seat pool as regular ticket sales (see
 * Event::passReservedSeats), so the two can never oversell the room. Scanning a
 * booked date later upgrades that entry to a redemption (PassRedemptionService),
 * never a second use. A reservation is released by cancellation here or, for the
 * whole pass, automatically when the sale stops being `paid` (refund/cancel/expiry).
 */
class PassBookingService
{
    /** Max upcoming occurrences listed per covered event. */
    protected const MAX_DATES_PER_EVENT = 60;

    public function passSaleTicket(Sale $sale): ?SaleTicket
    {
        $sale->loadMissing('saleTickets.ticket');

        return $sale->saleTickets->first(fn ($st) => $st->ticket?->is_pass);
    }

    /**
     * The schedule a pass's coverage resolves within: its home event's.
     *
     * `events.creator_role_id` is nullable (CheckData backfills it), so fall back to the first
     * schedule listing the home event. Without this, Ticket::covers() sees no schedule and denies
     * every `specific_events` pass sold on a legacy event.
     */
    public function homeSchedule(Sale $sale): ?Role
    {
        return $sale->event?->creatorRole ?? $sale->event?->roles->first();
    }

    /**
     * Whether this sale is a paid, booking-enabled pass.
     */
    public function isBookable(Sale $sale): bool
    {
        if ($sale->status !== 'paid') {
            return false;
        }

        $ticket = $this->passSaleTicket($sale)?->ticket;

        return (bool) ($ticket && $ticket->is_pass && $ticket->pass_allow_booking);
    }

    /**
     * Events this pass covers, as models in its home schedule.
     *
     * @return \Illuminate\Support\Collection<int, Event>
     */
    protected function coveredEvents(Sale $sale, Ticket $passTicket): \Illuminate\Support\Collection
    {
        $schedule = $this->homeSchedule($sale);
        $ids = $passTicket->coveredEventIds($schedule);

        if (empty($ids)) {
            return collect();
        }

        // Note: the events table has no is_deleted column (unlike tickets/sales);
        // coveredEventIds already scopes ids to the pass's own schedule.
        return Event::with(['tickets', 'creatorRole'])
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * Upcoming occurrence dates (Y-m-d) for an event, capped and bounded by the
     * pass expiry. Reuses the recurring-date cursor pattern used elsewhere.
     *
     * @return string[]
     */
    protected function upcomingDates(Event $event, Carbon $now, ?Carbon $expiresAt): array
    {
        if (! $event->starts_at && ! $event->days_of_week) {
            return [];
        }

        $dates = [];
        $end = $now->copy()->addYear();
        if ($expiresAt && $expiresAt->lt($end)) {
            $end = $expiresAt->copy();
        }

        // A date is bookable only if its occurrence start is on or before the pass
        // expiry instant, so every offered date is still redeemable at the event
        // (matches redeem()'s instant check, not a date-midnight comparison).
        $withinExpiry = fn (string $d) => ! $expiresAt || $event->occurrenceStartUtc($d)->lte($expiresAt);
        $tz = $event->scheduleTimezone();

        if ($event->days_of_week) {
            // Walk venue-local calendar days: `$now` is in the app timezone, and starting the
            // cursor there skips tonight's occurrence for any venue west of UTC.
            $cursor = $now->copy()->setTimezone($tz)->startOfDay();
            while ($cursor->lte($end) && count($dates) < self::MAX_DATES_PER_EVENT) {
                $d = $cursor->format('Y-m-d');
                if ($event->matchesDate($cursor, $tz) && $event->canSellTickets($d) && $withinExpiry($d)) {
                    $dates[] = $d;
                }
                $cursor->addDay();
            }
        } else {
            // Use the canonical schedule-TZ occurrence date: this handles a date-only
            // starts_at (the raw createFromFormat('Y-m-d H:i:s', ...) would throw) and
            // matches the seat-pool key + redemption's $today (the UTC date could differ
            // and would never match at scan time). Apply the same sell + expiry gate as
            // the recurring branch and book().
            $date = $event->saleEventDateFromStartsAt();
            if ($date
                && $event->scheduleToday($now) <= $date
                && $event->canSellTickets($date)
                && $withinExpiry($date)) {
                $dates[] = $date;
            }
        }

        return $dates;
    }

    /**
     * Seats still bookable for an occurrence: the shared house pool minus what is
     * sold/reserved, further capped by the pass's optional per-occurrence limit.
     * Null means unlimited (no defined ceiling).
     */
    public function seatsLeft(Event $event, string $date, Ticket $passTicket): ?int
    {
        // The shared per-occurrence house, after regular sales AND existing pass
        // reservations - the same figure the regular-sale side enforces.
        $houseLeft = $event->occurrenceSeatsRemaining($date);

        // Optional per-occurrence cap on how many seats passes may take.
        $passCap = $passTicket->pass_seats_per_occurrence;
        $passLeft = $passCap ? max(0, (int) $passCap - $event->passReservedSeats($date)) : null;

        if ($houseLeft === null) {
            return $passLeft;
        }
        if ($passLeft === null) {
            return $houseLeft;
        }

        return min($houseLeft, $passLeft);
    }

    /**
     * Reservations the holder currently holds (booking-kind entries), upcoming
     * first. Each: event, date, label, plus encoded ids for the view.
     */
    public function bookedOccurrences(Sale $sale, ?Carbon $now = null): array
    {
        $now = $now ?: now();
        $saleTicket = $this->passSaleTicket($sale);
        if (! $saleTicket) {
            return [];
        }

        $bookings = collect($saleTicket->pass_usages ?? [])
            ->filter(fn ($u) => SaleTicket::usageKind($u) === 'booking');

        if ($bookings->isEmpty()) {
            return [];
        }

        $ticket = $saleTicket->ticket;
        $events = Event::with('creatorRole')
            ->whereIn('id', $bookings->pluck('event_id')->unique()->all())
            ->get()->keyBy('id');

        return $bookings
            ->map(function ($u) use ($events, $ticket, $now) {
                $event = $events->get((int) ($u['event_id'] ?? 0));
                if (! $event) {
                    return null;
                }

                $date = $u['date'] ?? null;
                $deadline = ($ticket && $date) ? $ticket->passCancelDeadlineUtc($event, $date) : null;

                // Mirror cancel()'s undo grace so the UI never warns about a
                // forfeit that the server would still credit.
                $bookedAt = isset($u['at']) ? Carbon::createFromTimestamp((int) $u['at'], 'UTC') : null;
                $inGrace = $bookedAt && $now->lte($bookedAt->copy()->addMinutes(self::CANCEL_GRACE_MINUTES));

                // With a cancellation policy in force, a booking whose occurrence
                // has ended is dead weight (visit consumed, seat worthless) - drop
                // it from the list instead of pinning "cancellation closed" rows
                // forever. Never while the undo grace still runs (a last-minute
                // mis-booking must keep its credited-cancel button), and without
                // a policy legacy behavior stands: the row stays and may still be
                // cancelled with credit.
                if ($deadline && $event->starts_at && ! $inGrace) {
                    $duration = $event->duration > 0 ? $event->duration : 2;
                    $endUtc = $event->occurrenceStartUtc($date)
                        ->addMinutes(Event::durationHoursToMinutes($duration));
                    if ($now->gt($endUtc)) {
                        return null;
                    }
                }

                return [
                    'event_id' => UrlUtils::encodeId($event->id),
                    'event_name' => $event->name,
                    'date' => $date,
                    'date_label' => $event->localStartsAt(true, $date),
                    'cancel_deadline_label' => $deadline ? $event->localizedInstantLabel($deadline) : null,
                    'past_cutoff' => $deadline ? ($now->gt($deadline) && ! $inGrace) : false,
                    'deadline_past' => $deadline ? $now->gt($deadline) : false,
                    'late_policy' => $deadline ? $ticket->passLateCancelPolicy() : null,
                ];
            })
            ->filter()
            ->sortBy('date')
            ->values()
            ->all();
    }

    /**
     * Upcoming occurrences the holder may book across the pass's coverage, with
     * seats-left and whether they've already booked each.
     */
    public function bookableOccurrences(Sale $sale, ?Carbon $now = null): array
    {
        $now = $now ?: now();
        $saleTicket = $this->passSaleTicket($sale);
        $passTicket = $saleTicket?->ticket;

        if (! $passTicket || ! $passTicket->pass_allow_booking) {
            return [];
        }

        $bookedKeys = collect($saleTicket->pass_usages ?? [])
            ->filter(fn ($u) => SaleTicket::usageKind($u) === 'booking')
            ->map(fn ($u) => ((int) ($u['event_id'] ?? 0)).'|'.($u['date'] ?? ''))
            ->all();

        // A per-event pass grants one visit per event, and EVERY usage entry
        // spends it - a booking on any date, a redemption, or a forfeited late
        // cancellation. Don't offer dates of events the visit limit would
        // reject anyway (book() would return limit_reached).
        $spentEventIds = $passTicket->pass_usage_type === 'per_event'
            ? collect($saleTicket->pass_usages ?? [])
                ->map(fn ($u) => (int) ($u['event_id'] ?? 0))
                ->unique()
                ->all()
            : [];

        $occurrences = [];
        foreach ($this->coveredEvents($sale, $passTicket) as $event) {
            if (in_array($event->id, $spentEventIds, true)) {
                continue;
            }
            foreach ($this->upcomingDates($event, $now, $saleTicket->pass_expires_at) as $date) {
                $seatsLeft = $this->seatsLeft($event, $date, $passTicket);
                $occurrences[] = [
                    'event_id' => UrlUtils::encodeId($event->id),
                    'event_name' => $event->name,
                    'date' => $date,
                    'date_label' => $event->localStartsAt(true, $date),
                    'seats_left' => $seatsLeft,
                    'sold_out' => $seatsLeft !== null && $seatsLeft <= 0,
                    'booked' => in_array($event->id.'|'.$date, $bookedKeys, true),
                ];
            }
        }

        usort($occurrences, fn ($a, $b) => strcmp($a['date'], $b['date']));

        return $occurrences;
    }

    /**
     * Reserve a seat for $eventId on $date. Atomic: serializes on the event's
     * seat-ticket rows (the same lock checkout uses) so concurrent bookings and
     * sales can't oversell. Returns {ok, status}.
     */
    public function book(Sale $sale, int $eventId, string $date, ?Carbon $now = null): \stdClass
    {
        $now = $now ?: now();
        $result = new \stdClass;
        $result->ok = false;

        if (! $this->isBookable($sale)) {
            $result->status = 'not_bookable';

            return $result;
        }

        $saleTicket = $this->passSaleTicket($sale);
        $passTicket = $saleTicket->ticket;
        $schedule = $this->homeSchedule($sale);

        $event = Event::with(['tickets', 'creatorRole'])->find($eventId);
        if (! $event || ! $passTicket->covers($event, $schedule)) {
            $result->status = 'not_covered';

            return $result;
        }

        // Must be a real, sellable, future-or-today occurrence of this event. Resolve the
        // occurrence in the schedule's timezone so book() agrees with the dates
        // upcomingDates() offered, and with what redeem() will accept at the door.
        $dateCarbon = Carbon::parse($date)->startOfDay();
        $isPast = $date < $event->scheduleToday($now);
        if ($isPast || ! $event->matchesDate($dateCarbon, $event->scheduleTimezone()) || ! $event->canSellTickets($date)) {
            $result->status = 'invalid_date';

            return $result;
        }

        // Expiry is a precise instant; reject a date only if its occurrence start is
        // after expiry, so every bookable date is still redeemable at the event
        // (book() and redeem() then agree on the boundary).
        if ($saleTicket->pass_expires_at && $event->occurrenceStartUtc($date)->gt($saleTicket->pass_expires_at)) {
            $result->status = 'expired';

            return $result;
        }

        return DB::transaction(function () use ($event, $saleTicket, $passTicket, $eventId, $date, $now, $result) {
            // Lock the event row first so concurrent bookings/checkouts serialize on this
            // occurrence even when the event has no ticket rows of its own - a FOR UPDATE on
            // tickets() locks nothing for a zero-row result, which would let the per-occurrence
            // pass cap be exceeded under concurrency.
            Event::whereKey($event->id)->lockForUpdate()->first();

            // Acquire the occurrence's seat lock (serializes vs checkout + other
            // bookings) and read seat counts from the locked rows, not a later
            // snapshot read.
            $lockedTickets = $event->tickets()->lockForUpdate()->get();
            $event->setRelation('tickets', $lockedTickets);
            $fresh = SaleTicket::lockForUpdate()->find($saleTicket->id);
            $usages = $fresh->pass_usages ?? [];

            // Already holding this occurrence? Forfeited entries don't count -
            // that visit is spent, but the holder may book the date again as a
            // new visit (subject to the limits below, which count every entry).
            $already = collect($usages)->contains(fn ($u) => (int) ($u['event_id'] ?? 0) === $eventId
                && ($u['date'] ?? null) === $date
                && SaleTicket::usageKind($u) !== 'forfeited');
            if ($already) {
                $result->status = 'already_booked';

                return $result;
            }

            // Visit limit (a booking consumes a use up front).
            if ($passTicket->pass_usage_type === 'total'
                && $passTicket->pass_max_uses
                && count($usages) >= $passTicket->pass_max_uses) {
                $result->status = 'limit_reached';

                return $result;
            }
            if ($passTicket->pass_usage_type === 'per_event'
                && collect($usages)->contains(fn ($u) => (int) ($u['event_id'] ?? 0) === $eventId)) {
                $result->status = 'limit_reached';

                return $result;
            }

            // Seats still available in the shared pool?
            $seatsLeft = $this->seatsLeft($event, $date, $passTicket);
            if ($seatsLeft !== null && $seatsLeft <= 0) {
                $result->status = 'sold_out';

                return $result;
            }

            $usages[] = [
                'event_id' => $eventId,
                'date' => $date,
                'at' => $now->copy()->setTimezone('UTC')->timestamp,
                'kind' => 'booking',
            ];
            $fresh->pass_usages = $usages;
            $fresh->save();

            $result->ok = true;
            $result->status = 'valid';
            $result->event_id = UrlUtils::encodeId($eventId);
            $result->date = $date;

            return $result;
        });
    }

    /** Minutes after making a booking during which it may always be undone with credit. */
    public const CANCEL_GRACE_MINUTES = 15;

    /**
     * Release a reservation for $eventId on $date (booking entries only; an
     * already-redeemed visit can't be cancelled). Atomic. Returns {ok, status}.
     *
     * When the pass ticket sets pass_cancel_cutoff_hours, cancelling past that
     * deadline either forfeits the visit (entry kept as kind 'forfeited': the
     * seat returns to the pool but the visit stays consumed) or is refused
     * entirely, per pass_late_cancel_policy. Two escape hatches:
     * - a booking may always be cancelled with credit within CANCEL_GRACE_MINUTES
     *   of being made, so a booking made inside the cutoff window (born past its
     *   own deadline) is never an irreversible mis-click;
     * - the forfeit path only runs when $allowForfeit is set (the guest's form
     *   acknowledged the warning); otherwise it returns 'confirm_forfeit' with
     *   no mutation, so a cancel submitted from a page rendered before the
     *   deadline can't silently burn the visit.
     */
    public function cancel(Sale $sale, int $eventId, string $date, ?Carbon $now = null, bool $allowForfeit = false): \stdClass
    {
        $now = $now ?: now();
        $result = new \stdClass;
        $result->ok = false;

        $saleTicket = $this->passSaleTicket($sale);
        if (! $saleTicket) {
            $result->status = 'not_bookable';

            return $result;
        }

        $event = Event::with('tickets')->find($eventId);

        return DB::transaction(function () use ($event, $saleTicket, $eventId, $date, $now, $allowForfeit, $result) {
            if ($event) {
                $event->tickets()->lockForUpdate()->get();
            }
            $fresh = SaleTicket::lockForUpdate()->find($saleTicket->id);
            $usages = $fresh->pass_usages ?? [];

            $index = collect($usages)->search(fn ($u) => (int) ($u['event_id'] ?? 0) === $eventId
                && ($u['date'] ?? null) === $date
                && SaleTicket::usageKind($u) === 'booking');

            if ($index === false) {
                $result->status = 'not_found';

                return $result;
            }

            // A deleted booked event leaves no occurrence start to measure the
            // deadline against, so the holder keeps the credited cancel.
            $deadline = $event ? $fresh->ticket?->passCancelDeadlineUtc($event, $date) : null;

            // Undo grace: within a few minutes of booking, credit-cancel is
            // always allowed even past the configured deadline.
            $bookedAt = isset($usages[$index]['at'])
                ? Carbon::createFromTimestamp((int) $usages[$index]['at'], 'UTC')
                : null;
            $inGrace = $bookedAt && $now->lte($bookedAt->copy()->addMinutes(self::CANCEL_GRACE_MINUTES));

            if ($deadline && $now->gt($deadline) && ! $inGrace) {
                if ($fresh->ticket->passLateCancelPolicy() === 'block') {
                    $result->status = 'too_late';

                    return $result;
                }

                if (! $allowForfeit) {
                    $result->status = 'confirm_forfeit';

                    return $result;
                }

                $usages[$index]['kind'] = 'forfeited';
                $fresh->pass_usages = array_values($usages);
                $fresh->save();

                $result->ok = true;
                $result->status = 'forfeited';

                return $result;
            }

            array_splice($usages, $index, 1);
            $fresh->pass_usages = array_values($usages);
            $fresh->save();

            $result->ok = true;
            $result->status = 'cancelled';

            return $result;
        });
    }
}
