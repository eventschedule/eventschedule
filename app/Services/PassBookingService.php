<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Models\SeatingSeat;
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

        // Passes are Pro. This is the chokepoint every pass-booking surface reads, including the
        // public secret-link routes, so the gate belongs here. A pass that was sold while the
        // schedule was Pro keeps working only while the schedule still is; the redemption side is
        // deliberately separate, the same way already-sold gift cards stay redeemable.
        $event = $sale->event;

        if (! $event || ! $event->isPro()) {
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
        return Event::with(['tickets', 'creatorRole', 'roles'])
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
        // On an allocated event there is no shared house - occurrenceSeatsRemaining() returns
        // null by design, and reading it here let UNLIMITED pass holders book a physically full
        // room. The real ceiling is seats left in the band this pass admits to.
        $houseLeft = $event->hasAllocatedSeating()
            ? $this->allocatedSeatsLeft($event, $date, $passTicket)
            : $event->occurrenceSeatsRemaining($date);

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
     * Seats a pass may still take at an allocated occurrence.
     *
     * `tickets.seating_band` on the PASS names the band its holder sits in. A pass with no band
     * may take any allocated seat, which is the sensible default for a house-wide season pass.
     */
    private function allocatedSeatsLeft(Event $event, string $date, Ticket $passTicket): int
    {
        $bands = $this->bandTicketsFor($event, $date, $passTicket);

        // allocatedSeatsRemaining() already knows how to answer before the occurrence has been
        // snapshotted, which a pass booking can easily be the first thing to do.
        return (int) $bands->sum(fn (Ticket $t) => (int) $event->allocatedSeatsRemaining($date, $t));
    }

    /**
     * The allocated ticket bands a pass admits to.
     *
     * `tickets.seating_band` on the PASS names the band its holder sits in. A pass with no band may
     * take any allocated seat, which is the sensible default for a house-wide season pass.
     */
    private function bandTicketsFor(Event $event, string $date, Ticket $passTicket): \Illuminate\Support\Collection
    {
        $allocated = $event->tickets->filter(fn (Ticket $t) => $t->isAllocated($date));

        if ($passTicket->seating_band) {
            return $allocated->where('seating_band', $passTicket->seating_band)->values();
        }

        return $allocated->values();
    }

    /**
     * Claim an actual seat for a pass booking.
     *
     * The holder gets no picker - that was the decision, and it keeps one picker entry point - so
     * best-available is what they get. The seat id goes on the usage entry, which is what the
     * cancel path reads to give it back.
     */
    private function claimSeatForBooking(Event $event, string $date, Ticket $passTicket, SaleTicket $line): ?int
    {
        $map = app(SeatingMapService::class)->materialize($event, $date);

        if (! $map) {
            return null;
        }

        $best = app(BestAvailableService::class);
        $holds = app(SeatHoldService::class);

        foreach ($this->bandTicketsFor($event, $date, $passTicket) as $ticket) {
            // Pick, then claim under a lock, with one retry if the seat went in between.
            // sale_id only, NOT sale_ticket_id: the pass line is one row reused across every
            // occurrence, so binding seats to it would make SaleTicket::seatLabels() list every
            // seat the holder ever booked on every ticket they print. The usage entry carries the
            // per-occurrence seat.
            for ($attempt = 0; $attempt < 2; $attempt++) {
                $ids = $best->pick($map, $ticket, 1);

                if (! $ids) {
                    break;
                }

                if ($holds->claimPickedSeats($map, $ids, $line->sale_id, null)) {
                    return $ids[0];
                }
            }
        }

        return null;
    }

    /**
     * Give back the seat a pass booking took.
     *
     * Only the seat recorded on the usage entry, and only if it is still bound to this pass - a box
     * office exchange may have moved the holder since, and that seat belongs to whoever holds it now.
     */
    private function releaseSeatForUsage(?Event $event, SaleTicket $line, array $usage): void
    {
        $seatId = $usage['seat_id'] ?? null;

        if (! $seatId || ! $event) {
            return;
        }

        $seat = SeatingSeat::find($seatId);

        // Still sold AND still this order's. If the box office released or exchanged it since, the
        // seat now belongs to whoever holds it, and freeing it here would sell it twice.
        if (! $seat || $seat->status !== 'sold' || (int) $seat->sale_id !== (int) $line->sale_id) {
            return;
        }

        $map = $seat->eventSeatingMap;

        $seat->update([
            'status' => 'available',
            'hold_kind' => null,
            'hold_token' => null,
            'hold_expires_at' => null,
            'sale_id' => null,
            'sale_ticket_id' => null,
            'state_version' => $map ? $map->bumpVersion() : $seat->state_version,
        ]);
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

        // Allocated bookings took a real seat - the holder needs to be told which one, and it is
        // the only place they will ever see it before the door.
        $seats = SeatingSeat::with(['section', 'seatingTable'])
            ->whereIn('id', $bookings->pluck('seat_id')->filter()->all())
            ->get()->keyBy('id');

        return $bookings
            ->map(function ($u) use ($events, $ticket, $now, $seats) {
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
                    'seat_label' => isset($u['seat_id'])
                        ? $seats->get((int) $u['seat_id'])?->fullLabel()
                        : null,
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

        $event = Event::with(['tickets', 'creatorRole', 'roles'])->find($eventId);
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

            // An allocated occurrence must hand the holder a real seat, or the booking is a
            // promise the door cannot keep - they would arrive with a valid QR and nowhere to sit.
            $seatId = null;
            if ($event->hasAllocatedSeating()) {
                $seatId = $this->claimSeatForBooking($event, $date, $passTicket, $fresh);

                if (! $seatId) {
                    $result->status = 'sold_out';

                    return $result;
                }
            }

            $usages[] = array_filter([
                'event_id' => $eventId,
                'date' => $date,
                'at' => $now->copy()->setTimezone('UTC')->timestamp,
                'kind' => 'booking',
                // The cancel path reads this to give the seat back.
                'seat_id' => $seatId,
            ], fn ($v) => $v !== null);
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

                // Forfeited still means they are not coming, so the seat goes back on sale.
                $this->releaseSeatForUsage($event, $fresh, $usages[$index]);
                unset($usages[$index]['seat_id']);
                $usages[$index]['kind'] = 'forfeited';
                $fresh->pass_usages = array_values($usages);
                $fresh->save();

                $result->ok = true;
                $result->status = 'forfeited';

                return $result;
            }

            $this->releaseSeatForUsage($event, $fresh, $usages[$index]);
            array_splice($usages, $index, 1);
            $fresh->pass_usages = array_values($usages);
            $fresh->save();

            $result->ok = true;
            $result->status = 'cancelled';

            return $result;
        });
    }
}
