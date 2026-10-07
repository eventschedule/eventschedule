<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Two numbers about one date of one event, for the pages that show them beside its name: how many
 * seats are taken (the dashboard's "Coming up", the Realtime tab's door card) and how many of
 * those people have arrived (the door card).
 *
 * Neither answers who. checkedIn() reads three columns of the ticket rows and never a sale's name,
 * which is what lets it sit on a tab that names nobody (ScheduleActivity).
 */
class EventSeats
{
    /** Ticket rows read for one date. An occurrence past this is told its count is a floor. */
    public const ROW_CAP = 5000;

    /** Arrivals in this many seconds are the door card's "last 30 minutes". */
    public const RECENT_SECONDS = 1800;

    /**
     * What an event's tickets or sign-ups say for one date: how many seats are taken and, where
     * there is a limit, out of how many. Null where the event takes neither.
     *
     * The seats are the event's own arithmetic (Event::occurrenceSeatsRemaining()), not a sum
     * over ticket types: three types that share one house of 100 are 100 seats, not 300; a pass
     * is sold once for a whole series, so its pool belongs to no date, while the seats its
     * holders booked on this date are taken; and a seated house has no single number at all.
     *
     * @return array{sold: int, capacity: ?int, paid: bool}|null
     */
    public static function line(Event $event, string $date): ?array
    {
        // Sign-ups first, as the event itself reads them (Event::isFree()).
        if ($event->rsvp_enabled) {
            return [
                'sold' => (int) $event->rsvpSoldCount($date),
                'capacity' => (int) $event->rsvp_limit > 0 ? (int) $event->rsvp_limit : null,
                'paid' => false,
            ];
        }

        if (! $event->tickets_enabled) {
            return null;
        }

        // tickets() already leaves add-ons out; seatTickets() leaves passes out too.
        $seats = $event->seatTickets();
        if ($seats->isEmpty()) {
            return null;
        }

        $seated = $event->hasAllocatedSeating();
        $sold = (int) $seats->sum(fn ($ticket) => $ticket->soldCountFor($date));
        if (! $seated) {
            try {
                $sold += (int) $event->passReservedSeats($date);
            } catch (Throwable $e) {
                report($e);
            }
        }
        $limited = ! $seated && $seats->every(fn ($ticket) => (int) $ticket->quantity > 0);

        return [
            'sold' => $sold,
            'capacity' => $limited ? (int) $event->getTotalTicketQuantity() : null,
            'paid' => $seats->contains(fn ($ticket) => (float) $ticket->price > 0),
        ];
    }

    /**
     * How many ticket holders have been scanned in for one date, and how many of them in the last
     * half hour.
     *
     * The same count as CheckInController::stats()'s `total_checked_in`, by the same rules: a
     * scanned seat is one, a pass redeemed at this event on this date is one (whoever it admits
     * with it), and a ticket type that was deleted, or an add-on, is not counted. It is a second
     * implementation and not a call into that method on purpose: that one loads every buyer's name
     * for its list of recent arrivals, and this is polled from a page that must never hold one.
     * tests/Feature/ScheduleActivityTest.php holds the two equal.
     *
     * @return array{count: int, recent: int, truncated: bool}
     */
    public static function checkedIn(Event $event, string $date, int $nowTs): array
    {
        $ticketIds = $event->tickets->pluck('id')->all();
        if (! $ticketIds) {
            return ['count' => 0, 'recent' => 0, 'truncated' => false];
        }

        $rows = DB::table('sale_tickets')
            ->join('sales', 'sales.id', '=', 'sale_tickets.sale_id')
            ->where('sales.event_id', $event->id)
            ->where('sales.event_date', $date)
            ->where('sales.status', 'paid')
            ->where('sales.is_deleted', false)
            ->whereIn('sale_tickets.ticket_id', $ticketIds)
            ->limit(self::ROW_CAP)
            ->get(['sale_tickets.ticket_id', 'sale_tickets.seats', 'sale_tickets.pass_usages']);

        $count = 0;
        $recent = 0;
        $since = $nowTs - self::RECENT_SECONDS;

        foreach ($rows as $row) {
            $seats = $row->seats ? json_decode((string) $row->seats, true) : [];
            foreach (is_array($seats) ? $seats : [] as $timestamp) {
                if ($timestamp !== null) {
                    $count++;
                    $recent += (int) $timestamp >= $since ? 1 : 0;
                }
            }

            $usages = $row->pass_usages ? json_decode((string) $row->pass_usages, true) : [];
            foreach (is_array($usages) ? $usages : [] as $usage) {
                if ((int) ($usage['event_id'] ?? 0) === (int) $event->id
                    && ($usage['date'] ?? null) === $date
                    && ($usage['kind'] ?? 'redemption') === 'redemption') {
                    $count++;
                    $recent += (int) ($usage['at'] ?? 0) >= $since ? 1 : 0;
                }
            }
        }

        return ['count' => $count, 'recent' => $recent, 'truncated' => $rows->count() >= self::ROW_CAP];
    }
}
