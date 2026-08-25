<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Jobs\NotifyWaitlist;
use App\Models\EventSeatingMap;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Models\SeatingSeat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Staff-side seat operations: holding seats back, and moving or releasing a single sold seat.
 *
 * Staff are exempt from the orphan and accessibility rules on purpose - they can see the whole map,
 * and holding back a lone seat or seating a wheelchair user's party is exactly their job.
 */
class BoxOfficeSeatingService
{
    public const HOLD_KINDS = ['house', 'production', 'accessibility', 'box_office'];

    /**
     * Take seats off sale with an internal note.
     *
     * A staff hold carries NO expiry, so it never lapses on its own - that is the difference
     * between it and a cart hold, and it is why SeatingSeat::isBlocked() keys on a null expiry.
     * The note is organizer-only and never reaches a guest payload.
     */
    public function block(EventSeatingMap $map, array $seatIds, string $kind, ?string $note = null): int
    {
        if (! in_array($kind, self::HOLD_KINDS, true)) {
            throw new BusinessException(__('messages.seating_invalid_hold_kind'));
        }

        return DB::transaction(function () use ($map, $seatIds, $kind, $note) {
            $seats = SeatingSeat::where('event_seating_map_id', $map->id)
                ->whereIn('id', $seatIds)
                ->inLiveSection()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            // A sold seat is somebody's booking. Blocking it would hide a real attendee from the
            // door list while leaving their ticket valid.
            if ($sold = $seats->firstWhere('status', 'sold')) {
                throw new BusinessException(__('messages.seating_cannot_block_sold_seat', [
                    'seat' => $sold->fullLabel() ?: $sold->id,
                ]));
            }

            if ($seats->isEmpty()) {
                return 0;
            }

            $version = $map->bumpVersion();

            return SeatingSeat::whereIn('id', $seats->pluck('id'))->update([
                'status' => 'held',
                'hold_kind' => $kind,
                'hold_note' => $note ? mb_substr(trim($note), 0, 255) : null,
                'hold_token' => null,
                'hold_expires_at' => null,
                'state_version' => $version,
            ]);
        });
    }

    /**
     * Put staff-held seats back on sale.
     *
     * Deliberately narrow: only holds with no expiry, which are the ones staff placed. A cart hold
     * belongs to a guest who is mid-purchase and is not the box office's to cancel; it lapses on
     * its own within minutes.
     */
    public function unblock(EventSeatingMap $map, array $seatIds): int
    {
        $freed = DB::transaction(function () use ($map, $seatIds) {
            $ids = SeatingSeat::where('event_seating_map_id', $map->id)
                ->whereIn('id', $seatIds)
                ->where('status', 'held')
                ->whereNull('hold_expires_at')
                ->lockForUpdate()
                ->pluck('id');

            if ($ids->isEmpty()) {
                return 0;
            }

            $version = $map->bumpVersion();

            return SeatingSeat::whereIn('id', $ids)->update([
                'status' => 'available', 'hold_kind' => null, 'hold_note' => null,
                'hold_token' => null, 'hold_expires_at' => null, 'state_version' => $version,
            ]);
        });

        if ($freed > 0) {
            $this->notifyWaitlist($map);
        }

        return $freed;
    }

    /**
     * Release ONE seat from a multi-seat booking.
     *
     * Sale.status is per sale, not per seat, so this cannot go through the status machine: a
     * four-seat order refunding one seat is still a paid order. Instead the seat goes back to the
     * map, the line's quantity drops by one, and the ticket's sold counter is decremented directly
     * - SaleTicket only takes stock on CREATE, so an update would otherwise leave the counter high.
     *
     * The money is the organizer's to refund, exactly as `refundSale()` works today.
     */
    public function releaseSeat(EventSeatingMap $map, int $seatId): void
    {
        DB::transaction(function () use ($map, $seatId) {
            $seat = SeatingSeat::where('event_seating_map_id', $map->id)
                ->lockForUpdate()->find($seatId);

            if (! $seat || $seat->status !== 'sold') {
                throw new BusinessException(__('messages.seating_seat_not_sold'));
            }

            $line = $seat->sale_ticket_id
                ? SaleTicket::with(['ticket', 'sale'])->lockForUpdate()->find($seat->sale_ticket_id)
                : null;

            // The tickets row is taken BEFORE the map row, and the order is load-bearing.
            //
            // PassBookingService::book() locks every ticket of the event and only then reaches the
            // map (via the seat claim's bumpVersion). Doing the reverse here - bumpVersion, which
            // is an UPDATE on event_seating_maps and so holds an X lock to commit, and only then
            // Ticket::updateSold()'s lockForUpdate - closed a genuine cycle on
            // {event_seating_maps, tickets}. Both rows really are shared: the same occurrence, and
            // book() locks ALL of the event's tickets. DB::transaction here is single-attempt, so
            // the victim got a hard 500 rather than a retry.
            //
            // Keep the quantity counter in step. It is not what an allocated ticket sells against -
            // the map is - but Sale::booted's release loop reads it, so letting the two drift would
            // hand back the wrong amount if the rest of the order were later cancelled.
            $line?->ticket?->updateSold($line->sale?->event_date, -1);

            $version = $map->bumpVersion();

            $seat->forceFill([
                'status' => 'available', 'hold_kind' => null, 'hold_note' => null,
                'hold_token' => null, 'hold_expires_at' => null,
                'sale_id' => null, 'sale_ticket_id' => null, 'state_version' => $version,
            ])->save();

            if (! $line) {
                return;
            }

            $line->quantity = max(0, (int) $line->quantity - 1);
            $line->save();
        });

        $this->notifyWaitlist($map);
    }

    /**
     * Move a sold seat to a free one, keeping the same booking.
     *
     * One transaction, both rows locked in id order like every other seat write, so an exchange
     * cannot race a guest taking the destination.
     */
    /**
     * Tell the waitlist a seat came back.
     *
     * Every other way inventory frees up runs through Sale::booted, which dispatches this. The two
     * box office operations that put seats back on sale did not, and nothing else ever would:
     * ExpireWaitlistNotifications only re-sends rows already in `notified` state, so a list of
     * purely `waiting` entries is never revisited and there is no manual trigger. The seat just
     * went silently to whoever refreshed the page next.
     *
     * exchange() deliberately does NOT call this: it requires the destination seat to be free
     * already, so the event was not sold out and net availability is unchanged.
     *
     * Dispatched after the transaction commits, per the post-commit rule SaleSettlementService
     * documents - a queued job must never observe a transaction that might still roll back.
     * Skipped for an occurrence that has already started: a "spot opened" email for a running
     * event is noise that burns the guest's one-shot notification.
     */
    private function notifyWaitlist(EventSeatingMap $map): void
    {
        $event = $map->event;

        if (! $event || ! $map->event_date) {
            return;
        }

        $upcoming = $event->starts_at
            ? $event->occurrenceStartUtc($map->event_date)->isFuture()
            : $map->event_date >= $event->scheduleToday(now());

        if ($upcoming) {
            NotifyWaitlist::dispatch($event->id, $map->event_date);
        }
    }

    /**
     * Sell seats over the counter or the phone.
     *
     * The caller named their seats, so this takes exactly those - not best-available, which is what
     * the import and API paths get. Seats spanning several bands produce one sale line per band, so
     * the money and the quantity counters land on the right ticket.
     *
     * @param  array{name: string, email: ?string, phone: ?string, status: string, amount: ?float, subdomain: string}  $buyer
     */
    public function bookSeats(EventSeatingMap $map, array $seatIds, array $buyer): Sale
    {
        $event = $map->event;

        if (! $event) {
            throw new BusinessException(__('messages.seating_no_map'));
        }

        // Cancelled, and only cancelled. A PAST date is deliberately still sellable here - staff
        // recording a walk-up after the doors opened is ordinary box office work, and this console
        // is where it happens. A cancelled show is not a rule staff are overriding: the audience
        // has been told it is off and refunded, so a new seat sold against it is always a mistake.
        if ($event->is_cancelled) {
            throw new BusinessException(__('messages.seating_event_cancelled'));
        }

        return DB::transaction(function () use ($map, $seatIds, $buyer, $event) {
            $seats = SeatingSeat::where('event_seating_map_id', $map->id)
                ->whereIn('id', $seatIds)
                ->inLiveSection()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($seats->isEmpty()) {
                throw new BusinessException(__('messages.seating_select_seats'));
            }

            foreach ($seats as $seat) {
                // A staff hold is fine to sell out of - holding a seat back for a phone caller and
                // then selling it to them is the whole point. A cart hold is somebody else's.
                if (! $seat->isAvailable() && ! $seat->isBlocked()) {
                    throw new BusinessException(__('messages.seating_seat_taken', [
                        'seat' => $seat->fullLabel() ?: $seat->id,
                    ]));
                }
            }

            $sections = $map->sections()->whereIn('id', $seats->pluck('seating_section_id')->unique())->get()->keyBy('id');
            $byTicket = $seats->groupBy(fn ($seat) => $sections[$seat->seating_section_id]->ticket_id ?? 0);

            if ($byTicket->has(0)) {
                throw new BusinessException(__('messages.seating_section_has_no_ticket'));
            }

            // Locked, and locked HERE - before $map->bumpVersion() below. Same rule releaseSeat()
            // spells out: the tickets rows are taken BEFORE the map row, because
            // PassBookingService::book() locks every ticket of the event and only then reaches the
            // map, and guest checkout does the same (assertLegTicketsAvailable locks tickets, then
            // claimSeatsForLeg bumps the version). Taking them the other way round - bumpVersion(),
            // an UPDATE on event_seating_maps holding an X lock to commit, and only then
            // SaleTicket::created -> Ticket::updateSold() -> lockForUpdate - is a live cycle on
            // {event_seating_maps, tickets} against both of them. DB::transaction here is
            // single-attempt, so the victim gets a hard 500 rather than a retry.
            $tickets = $event->tickets()->whereIn('id', $byTicket->keys())->lockForUpdate()->get()->keyBy('id');

            $sale = new Sale;
            $sale->event_id = $event->id;
            $sale->event_date = $map->event_date;
            // The console's own subdomain, not one guessed off the event: a curated event belongs to
            // several schedules, and the sale has to land under the one selling it.
            $sale->subdomain = $buyer['subdomain'];
            $sale->name = $buyer['name'];
            // sales.email is NOT NULL, and a walk-up cash sale legitimately has no address. Empty
            // string rather than a placeholder, so scopeExcludeTestEmails and the mailers skip it.
            $sale->email = $buyer['email'] ?: '';
            $sale->phone = $buyer['phone'] ?: null;
            $sale->secret = strtolower(Str::random(32));
            $sale->payment_method = 'box_office';
            $sale->status = $buyer['status'];
            // Real figure is set below, once the lines are priced; Sale requires one up front.
            $sale->payment_amount = 0;
            $sale->save();

            $version = $map->bumpVersion();
            $total = 0.0;

            foreach ($byTicket as $ticketId => $group) {
                $ticket = $tickets[$ticketId] ?? null;

                if (! $ticket) {
                    throw new BusinessException(__('messages.seating_section_has_no_ticket'));
                }

                $total += (float) $ticket->price * $group->count();

                // SaleTicket::created takes the quantity stock; do not adjust updateSold() again
                // here or the counter double-counts, exactly as the importer notes.
                $line = $sale->saleTickets()->create([
                    'ticket_id' => $ticket->id,
                    'quantity' => $group->count(),
                    'seats' => json_encode(array_fill(1, $group->count(), null)),
                ]);

                SeatingSeat::whereIn('id', $group->pluck('id'))->update([
                    'status' => 'sold',
                    'hold_kind' => null, 'hold_note' => null, 'hold_token' => null, 'hold_expires_at' => null,
                    'sale_id' => $sale->id, 'sale_ticket_id' => $line->id,
                    'state_version' => $version,
                ]);
            }

            // A comped booking is an explicit zero, so only fall back to the list price when the
            // operator left the amount blank entirely.
            $sale->payment_amount = $buyer['amount'] !== null ? (float) $buyer['amount'] : $total;
            $sale->save();

            return $sale->fresh();
        });
    }

    public function exchange(EventSeatingMap $map, int $fromSeatId, int $toSeatId): void
    {
        DB::transaction(function () use ($map, $fromSeatId, $toSeatId) {
            $ids = [$fromSeatId, $toSeatId];
            sort($ids);

            $seats = SeatingSeat::where('event_seating_map_id', $map->id)
                ->whereIn('id', $ids)->inLiveSection()
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            $from = $seats[$fromSeatId] ?? null;
            $to = $seats[$toSeatId] ?? null;

            if (! $from || ! $to || $from->status !== 'sold') {
                throw new BusinessException(__('messages.seating_seat_not_sold'));
            }

            if (! $to->isAvailable()) {
                throw new BusinessException(__('messages.seating_seat_taken', [
                    'seat' => $to->fullLabel() ?: $to->id,
                ]));
            }

            $version = $map->bumpVersion();

            $to->forceFill([
                'status' => 'sold',
                'hold_kind' => null, 'hold_note' => null, 'hold_token' => null, 'hold_expires_at' => null,
                'sale_id' => $from->sale_id, 'sale_ticket_id' => $from->sale_ticket_id,
                'state_version' => $version,
            ])->save();

            $from->forceFill([
                'status' => 'available',
                'hold_kind' => null, 'hold_note' => null, 'hold_token' => null, 'hold_expires_at' => null,
                'sale_id' => null, 'sale_ticket_id' => null, 'state_version' => $version,
            ])->save();
        });
    }
}
