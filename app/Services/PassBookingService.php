<?php

namespace App\Services;

use App\Models\Event;
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
        $schedule = $sale->event?->creatorRole;
        $ids = $passTicket->coveredEventIds($schedule);

        if (empty($ids)) {
            return collect();
        }

        return Event::with(['tickets', 'creatorRole'])
            ->whereIn('id', $ids)
            ->where('is_deleted', false)
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

        if ($event->days_of_week) {
            $cursor = $now->copy()->startOfDay();
            while ($cursor->lte($end) && count($dates) < self::MAX_DATES_PER_EVENT) {
                if ($event->matchesDate($cursor) && $event->canSellTickets($cursor->format('Y-m-d'))) {
                    $dates[] = $cursor->format('Y-m-d');
                }
                $cursor->addDay();
            }
        } else {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->format('Y-m-d');
            if ($now->copy()->startOfDay()->lte(Carbon::parse($date)->endOfDay())
                && (! $expiresAt || $expiresAt->gte(Carbon::parse($date)->startOfDay()))) {
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
        $seatTickets = $event->seatTickets();
        $reserved = $event->passReservedSeats($date);

        // Shared house capacity for this occurrence.
        if ($event->total_tickets_mode === 'combined' && $event->hasSameTicketQuantities()) {
            $capacity = $event->getSameTicketQuantity();
        } elseif ($seatTickets->isEmpty() || $seatTickets->contains(fn ($t) => $t->quantity <= 0)) {
            $capacity = null; // unlimited
        } else {
            $capacity = $seatTickets->sum('quantity');
        }

        $regularSold = $seatTickets->sum(fn ($t) => $t->soldCountFor($date));
        $houseLeft = $capacity === null ? null : max(0, (int) $capacity - $regularSold - $reserved);

        // Optional per-occurrence cap on how many seats passes may take.
        $passCap = $passTicket->pass_seats_per_occurrence;
        $passLeft = $passCap ? max(0, (int) $passCap - $reserved) : null;

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
    public function bookedOccurrences(Sale $sale): array
    {
        $saleTicket = $this->passSaleTicket($sale);
        if (! $saleTicket) {
            return [];
        }

        $bookings = collect($saleTicket->pass_usages ?? [])
            ->filter(fn ($u) => ($u['kind'] ?? 'redemption') === 'booking');

        if ($bookings->isEmpty()) {
            return [];
        }

        $events = Event::whereIn('id', $bookings->pluck('event_id')->unique()->all())
            ->get()->keyBy('id');

        return $bookings
            ->map(function ($u) use ($events) {
                $event = $events->get((int) ($u['event_id'] ?? 0));
                if (! $event) {
                    return null;
                }

                return [
                    'event_id' => UrlUtils::encodeId($event->id),
                    'event_name' => $event->name,
                    'date' => $u['date'] ?? null,
                    'date_label' => $event->localStartsAt(true, $u['date'] ?? null),
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
            ->filter(fn ($u) => ($u['kind'] ?? 'redemption') === 'booking')
            ->map(fn ($u) => ((int) ($u['event_id'] ?? 0)).'|'.($u['date'] ?? ''))
            ->all();

        $occurrences = [];
        foreach ($this->coveredEvents($sale, $passTicket) as $event) {
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
        $schedule = $sale->event?->creatorRole;

        $event = Event::with(['tickets', 'creatorRole'])->find($eventId);
        if (! $event || ! $passTicket->covers($event, $schedule)) {
            $result->status = 'not_covered';

            return $result;
        }

        // Must be a real, sellable, future-or-today occurrence of this event.
        $dateCarbon = Carbon::parse($date)->startOfDay();
        if ($dateCarbon->lt($now->copy()->startOfDay()) || ! $event->matchesDate($dateCarbon) || ! $event->canSellTickets($date)) {
            $result->status = 'invalid_date';

            return $result;
        }

        if ($saleTicket->pass_expires_at && $saleTicket->pass_expires_at->lt($dateCarbon)) {
            $result->status = 'expired';

            return $result;
        }

        return DB::transaction(function () use ($event, $saleTicket, $passTicket, $eventId, $date, $now, $result) {
            // Acquire the occurrence's seat lock (serializes vs checkout + other
            // bookings) and read seat counts from the locked rows, not a later
            // snapshot read.
            $lockedTickets = $event->tickets()->lockForUpdate()->get();
            $event->setRelation('tickets', $lockedTickets);
            $fresh = SaleTicket::lockForUpdate()->find($saleTicket->id);
            $usages = $fresh->pass_usages ?? [];

            // Already holding this occurrence?
            $already = collect($usages)->contains(fn ($u) => (int) ($u['event_id'] ?? 0) === $eventId
                && ($u['date'] ?? null) === $date);
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

    /**
     * Release a reservation for $eventId on $date (booking entries only; an
     * already-redeemed visit can't be cancelled). Atomic. Returns {ok, status}.
     */
    public function cancel(Sale $sale, int $eventId, string $date): \stdClass
    {
        $result = new \stdClass;
        $result->ok = false;

        $saleTicket = $this->passSaleTicket($sale);
        if (! $saleTicket) {
            $result->status = 'not_bookable';

            return $result;
        }

        $event = Event::with('tickets')->find($eventId);

        return DB::transaction(function () use ($event, $saleTicket, $eventId, $date, $result) {
            if ($event) {
                $event->tickets()->lockForUpdate()->get();
            }
            $fresh = SaleTicket::lockForUpdate()->find($saleTicket->id);
            $usages = $fresh->pass_usages ?? [];

            $index = collect($usages)->search(fn ($u) => (int) ($u['event_id'] ?? 0) === $eventId
                && ($u['date'] ?? null) === $date
                && ($u['kind'] ?? 'redemption') === 'booking');

            if ($index === false) {
                $result->status = 'not_found';

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
