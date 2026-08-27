<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Event;
use App\Models\EventSeatingMap;
use App\Models\Sale;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Short-lived cart holds on specific seats.
 *
 * Stage one of the two-stage claim. A guest picking seats holds them here; at checkout they flip to
 * `sold` BEFORE the buyer is sent to Stripe (see TicketController), which is what stops the
 * disaster case of paying for seats that lapsed while the payment page was open.
 *
 * Expiry is evaluated at READ time by SeatingSeat::scopeAvailable(), never by a sweeper, so a
 * lapsed hold is sellable the instant it lapses. The sweep in ReleaseTickets runs only hourly and
 * is pure housekeeping - nothing a client can observe depends on it having run.
 */
class SeatHoldService
{
    /**
     * Generous on purpose: the hold has to outlive a guest filling in attendee details, and the
     * checkout claim happens before Stripe, so this window never has to cover the payment page.
     */
    public const HOLD_SECONDS = 720;

    private const SESSION_KEY = 'seating_hold_token';

    /**
     * The caller's hold token, kept in the SESSION rather than passed by the client.
     *
     * A client-supplied token would let anyone claim somebody else's held seats simply by guessing
     * or replaying it, and the checkout path trusts this token to decide which seats the buyer is
     * allowed to convert.
     */
    public function tokenFor(Request $request): string
    {
        $token = $request->session()->get(self::SESSION_KEY);

        if (! is_string($token) || strlen($token) !== 32) {
            $token = Str::random(32);
            $request->session()->put(self::SESSION_KEY, $token);
        }

        return $token;
    }

    /**
     * Hold EXACTLY these seats for this token, releasing anything else the token was holding.
     *
     * Replace rather than add, because the guest's selection changes every time they click a seat -
     * an additive API would leak holds for every seat they ever touched.
     *
     * Throws if any requested seat is not free, naming it, so the picker can drop that one seat and
     * keep the rest of the selection instead of failing the whole thing.
     */
    public function acquire(EventSeatingMap $map, array $seatIds, string $token, ?Carbon $now = null, bool $isBoxOffice = false, bool $orphanAdvisory = false): array
    {
        $warning = null;

        $now = $now ?: now();
        // Sorted so concurrent carts always take row locks in the same order. Two carts grabbing
        // an overlapping pair in opposite orders is a textbook deadlock.
        $seatIds = array_values(array_unique(array_map('intval', $seatIds)));
        sort($seatIds);

        return DB::transaction(function () use ($map, $seatIds, $token, $now, $isBoxOffice, $orphanAdvisory, &$warning) {
            $seats = collect();

            if ($seatIds) {
                // inLiveSection(), like every other seat read. Without it a guest whose picker was
                // open when the organizer removed a section could still hold and buy seats in it -
                // and availableSeatCount(), which does filter, would report they do not exist.
                $seats = SeatingSeat::where('event_seating_map_id', $map->id)
                    ->whereIn('id', $seatIds)
                    ->inLiveSection()
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($seats->count() !== count($seatIds)) {
                    throw new BusinessException(__('messages.seating_seat_unavailable'));
                }

                foreach ($seats as $seat) {
                    $alreadyMine = $seat->status === 'held' && $seat->hold_token === $token;

                    if (! $seat->isAvailable($now) && ! $alreadyMine) {
                        throw new BusinessException(__('messages.seating_seat_taken', [
                            'seat' => $seat->fullLabel() ?: $seat->id,
                        ]));
                    }
                }

                // Every rule below asks whether some seat is free, and the answer has to describe
                // the room as it will be a few lines from now - not as it is stored right this
                // second. The token's existing cart holds are released at the bottom of this
                // transaction, so a seat this buyer is dropping from their selection is FREE for
                // the purposes of judging that selection.
                //
                // Without this the rules compare a room against itself. The picker posts the whole
                // selection on every click, so from the second click onward every seat being judged
                // is already `held` by this token: the orphan before/after delta is always zero,
                // and a companion seat's wheelchair space never looks available. Both rules go
                // quiet exactly when the buyer starts adjusting their selection.
                //
                // Same set the release below acts on, so the two cannot disagree.
                $mine = SeatingSeat::where('event_seating_map_id', $map->id)
                    ->where('hold_token', $token)
                    ->where('hold_kind', 'cart')
                    ->where('status', 'held')
                    ->pluck('id')
                    ->all();

                if (! $isBoxOffice) {
                    app(AccessibleSeatingRule::class)->validate($map, $seats, $mine);
                    app(WholeTableRule::class)->validate($map, $seats);
                }

                // Advisory for a guest picking seats: a selection is built one click at a time,
                // and judging every intermediate state makes some valid final selections literally
                // unreachable - to take a whole row of eight you must pass through seven, which
                // strands the eighth. The warning travels back with the hold and checkout asks the
                // question again once the selection is finished.
                $advisory = $isBoxOffice
                    ? null
                    : app(OrphanSeatRule::class)->advisoryFor($map, $seatIds, $now, $mine);

                if ($advisory) {
                    if (! $orphanAdvisory) {
                        throw new BusinessException(__('messages.seating_orphan_seat'));
                    }

                    // The whole advisory, not just its text: it names the stranded seat so the
                    // picker can offer to add it. The terser original is kept for the checkout
                    // refusal, where there is nothing left to adjust in place.
                    $warning = $advisory;
                }
            }

            $version = $map->bumpVersion();
            $expires = $now->copy()->addSeconds(self::HOLD_SECONDS);

            // Drop the token's other cart holds first, so a shrinking selection frees seats
            // immediately rather than at expiry.
            SeatingSeat::where('event_seating_map_id', $map->id)
                ->where('hold_token', $token)
                ->where('hold_kind', 'cart')
                ->where('status', 'held')
                ->whereNotIn('id', $seatIds ?: [0])
                ->update([
                    'status' => 'available', 'hold_kind' => null, 'hold_token' => null,
                    'hold_expires_at' => null, 'state_version' => $version,
                ]);

            if ($seatIds) {
                SeatingSeat::whereIn('id', $seatIds)->update([
                    'status' => 'held', 'hold_kind' => 'cart', 'hold_token' => $token,
                    'hold_expires_at' => $expires, 'state_version' => $version,
                ]);
            }

            return ['seat_ids' => $seatIds, 'expires_at' => $expires, 'version' => $version, 'warning' => $warning];
        });
    }

    /** Free everything this token holds on the map. Safe to call when it holds nothing. */
    public function release(EventSeatingMap $map, string $token): void
    {
        DB::transaction(function () use ($map, $token) {
            $held = SeatingSeat::where('event_seating_map_id', $map->id)
                ->where('hold_token', $token)->where('hold_kind', 'cart')->where('status', 'held')
                ->lockForUpdate()->pluck('id');

            if ($held->isEmpty()) {
                return;
            }

            $version = $map->bumpVersion();

            SeatingSeat::whereIn('id', $held)->update([
                'status' => 'available', 'hold_kind' => null, 'hold_token' => null,
                'hold_expires_at' => null, 'state_version' => $version,
            ]);
        });
    }

    /**
     * Push the expiry out, but only for holds that have not already lapsed - reviving a lapsed
     * hold would take back a seat another guest may already have.
     */
    public function extend(EventSeatingMap $map, string $token, ?Carbon $now = null): ?Carbon
    {
        $now = $now ?: now();
        $expires = $now->copy()->addSeconds(self::HOLD_SECONDS);

        $live = SeatingSeat::where('event_seating_map_id', $map->id)
            ->where('hold_token', $token)->where('hold_kind', 'cart')->where('status', 'held')
            ->where('hold_expires_at', '>=', $now);

        // exists() first, then update. update() returns AFFECTED rows and MySQL counts a row whose
        // value did not actually change as zero - so extending twice inside the same second
        // reported "nothing to extend" and the caller concluded the hold had lapsed.
        if (! $live->exists()) {
            return null;
        }

        $live->update(['hold_expires_at' => $expires]);

        return $expires;
    }

    /**
     * Stage TWO of the two-stage claim: turn this token's holds into sold seats on a sale.
     *
     * Runs inside the checkout transaction and BEFORE the buyer is sent to a payment page, matching
     * how SaleTicket::created already takes quantity stock. Deferring it until payment succeeds is
     * the one ordering that produces the disaster case - a buyer who pays and finds their seats
     * gone because the hold lapsed while the payment page was open.
     *
     * Trusts the TOKEN, never the posted seat ids: the client says what it wants, the server claims
     * only what it is actually holding. Seats held for a band this order did not buy are released
     * rather than left to expire.
     *
     * @return array<int,int> claimed seat count, keyed by sale_ticket_id
     */
    public function claimForSale(EventSeatingMap $map, string $token, Sale $sale, ?Carbon $now = null): array
    {
        $now = $now ?: now();

        $seats = SeatingSeat::where('event_seating_map_id', $map->id)
            ->heldByToken($token, $now)
            ->inLiveSection()
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($seats->isEmpty()) {
            return [];
        }

        // The selection is finished now, so the orphan rule gets its say - the guest picker only
        // warned about it while seats were being chosen. validateFinal(), not validate(): these
        // seats are already held, and the plain call would compare an identical before and after
        // and never fire.
        app(OrphanSeatRule::class)->validateFinal($map, $seats->pluck('id')->all());

        $sectionTicket = SeatingSection::where('event_seating_map_id', $map->id)
            ->where('is_deleted', false)
            ->pluck('ticket_id', 'id');

        // Every line in the GROUP, not just the primary's. In individual-tickets mode the primary
        // holds one line at quantity 1 and each further attendee gets their OWN Sale with its own
        // line - so keying off $sale->saleTickets() alone meant N seats all mapped onto one
        // quantity-1 line (unbuyable), or a guest's seat found no line at all and was handed back
        // while the money was taken.
        $lines = $this->groupLines($sale);

        $version = $map->bumpVersion();
        $counts = [];
        $orphaned = [];

        foreach ($seats as $seat) {
            $ticketId = $sectionTicket[$seat->seating_section_id] ?? null;
            $line = $this->nextLineFor($lines, $ticketId);

            if (! $line) {
                $orphaned[] = $seat->id;

                continue;
            }

            $seat->forceFill([
                'status' => 'sold',
                'hold_kind' => null,
                'hold_token' => null,
                'hold_expires_at' => null,
                // The ATTENDEE's own sale, so their ticket page and email show their own seat.
                'sale_id' => $line->sale_id,
                'sale_ticket_id' => $line->id,
                // Whoever arrived on this seat before, it is not this buyer.
                'checked_in_at' => null,
                'state_version' => $version,
            ])->save();

            $counts[$line->id] = ($counts[$line->id] ?? 0) + 1;
        }

        if ($orphaned) {
            SeatingSeat::whereIn('id', $orphaned)->update([
                'status' => 'available', 'hold_kind' => null, 'hold_token' => null,
                'hold_expires_at' => null, 'checked_in_at' => null, 'state_version' => $version,
            ]);
        }

        return $counts;
    }

    /**
     * Seat a sale that never went near the picker.
     *
     * The API, the attendee importer and any future server-side sale have no seat map in front of
     * them, so best-available is the only sensible answer - and leaving them unseated is not an
     * option: an allocated ticket's availability reads the MAP, not the sold counter, so a sale
     * with no seats is invisible to the picker and the band double-books.
     *
     * Throws if the band cannot supply enough seats, so the caller's transaction rolls back rather
     * than writing a sale nobody can seat.
     */
    /**
     * Turn a pick into a claim, under a lock, refusing if any seat has gone since.
     *
     * BestAvailableService::pick() reads WITHOUT a lock on purpose: it scans the whole band to
     * score contiguous runs, and locking every candidate would serialize the entire house behind
     * one shopper. That makes a pick a PROPOSAL, and this is the only place it becomes a fact.
     *
     * Without this the two unlocked claim paths - an API/imported sale and a pass booking - could
     * both pick the same seat and both write it, because they take no common lock. book() happens
     * to hold the event row, but relying on a lock three call frames up is how an oversell gets
     * reintroduced by a refactor that looks unrelated.
     *
     * Ids are claimed in sorted order, the same rule acquire() and exchange() follow, so two
     * claimants overlapping on a set cannot deadlock against each other.
     */
    public function claimPickedSeats(EventSeatingMap $map, array $seatIds, int $saleId, ?int $saleTicketId, ?int $version = null): bool
    {
        $seatIds = array_values(array_unique(array_map('intval', $seatIds)));

        if (! $seatIds) {
            return false;
        }

        sort($seatIds);

        $seats = SeatingSeat::where('event_seating_map_id', $map->id)
            ->whereIn('id', $seatIds)
            ->inLiveSection()
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($seats->count() !== count($seatIds)) {
            return false;
        }

        foreach ($seats as $seat) {
            if (! $seat->isAvailable()) {
                return false;
            }
        }

        SeatingSeat::whereIn('id', $seatIds)->update([
            'status' => 'sold',
            'hold_kind' => null, 'hold_token' => null, 'hold_expires_at' => null,
            'sale_id' => $saleId, 'sale_ticket_id' => $saleTicketId,
            'checked_in_at' => null,
            'state_version' => $version ?? $map->bumpVersion(),
        ]);

        return true;
    }

    public function assignBestAvailableForSale(Event $event, Sale $sale): void
    {
        if (! $event->hasAllocatedSeating()) {
            return;
        }

        $map = app(SeatingMapService::class)->materialize($event, $sale->event_date);

        if (! $map) {
            return;
        }

        $best = app(BestAvailableService::class);
        $version = $map->bumpVersion();

        foreach ($this->groupLines($sale) as $line) {
            $ticket = $line->ticket;

            if (! $ticket) {
                continue;
            }

            $ticket->setRelation('event', $event);

            if (! $ticket->isAllocated($sale->event_date)) {
                continue;
            }

            $wanted = (int) $line->quantity;

            // Pick, then claim under a lock. One retry: losing the race means somebody took a seat
            // between the two, and the second pick sees the map as it now is.
            $claimed = false;
            for ($attempt = 0; $attempt < 2 && ! $claimed; $attempt++) {
                $ids = $best->pick($map, $ticket, $wanted);

                if (count($ids) !== $wanted) {
                    break;
                }

                $claimed = $this->claimPickedSeats($map, $ids, $line->sale_id, $line->id, $version);
            }

            if (! $claimed) {
                throw new BusinessException(__('messages.seating_not_enough_seats', [
                    'type' => $ticket->type ?: __('messages.ticket'),
                ]));
            }
        }
    }

    /**
     * Every SaleTicket in this sale's group, as a queue that hands out seats up to each line's
     * quantity. Ordered so the primary fills first and guests follow in creation order.
     *
     * @return \Illuminate\Support\Collection<int,\App\Models\SaleTicket>
     */
    private function groupLines(Sale $sale)
    {
        $saleIds = Sale::where('id', $sale->id)
            ->orWhere('group_id', $sale->id)
            ->pluck('id');

        $lines = \App\Models\SaleTicket::whereIn('sale_id', $saleIds)
            ->orderByRaw('sale_id = ? desc', [$sale->id])
            ->orderBy('id')
            ->get();

        $lines->each(fn ($line) => $line->seatsLeftToAssign = (int) $line->quantity);

        return $lines;
    }

    /** The next line for this ticket that still has room, or null. */
    private function nextLineFor($lines, $ticketId)
    {
        if (! $ticketId) {
            return null;
        }

        return $lines->first(fn ($line) => (int) $line->ticket_id === (int) $ticketId
            && $line->seatsLeftToAssign > 0
            && $line->seatsLeftToAssign-- > 0);
    }

    /**
     * Give a sale's seats back to the map.
     *
     * Called from Sale::booted()'s cancel / refund / expire branch, which is where every way a sale
     * can die converges - the admin action, the API, the expiry sweep and the group/order cascades.
     */
    public function releaseForSale(Sale $sale): int
    {
        return $this->releaseForSaleIds([$sale->id]);
    }

    /** Same, for a set of sales - a delete cascades to siblings via a raw update that fires no hooks. */
    public function releaseForSaleIds(array $saleIds): int
    {
        $saleIds = array_values(array_filter($saleIds));

        if (! $saleIds) {
            return 0;
        }

        $seats = SeatingSeat::whereIn('sale_id', $saleIds)->get(['id', 'event_seating_map_id']);

        if ($seats->isEmpty()) {
            return 0;
        }

        foreach ($seats->groupBy('event_seating_map_id') as $mapId => $group) {
            $map = EventSeatingMap::find($mapId);
            $version = $map ? $map->bumpVersion() : 0;

            SeatingSeat::whereIn('id', $group->pluck('id'))->update([
                'status' => 'available', 'hold_kind' => null, 'hold_token' => null,
                'hold_expires_at' => null, 'sale_id' => null, 'sale_ticket_id' => null,
                // A refunded seat carries no arrival into its next buyer's hands.
                'checked_in_at' => null,
                'state_version' => $version,
            ]);
        }

        return $seats->count();
    }

    /**
     * Housekeeping for lapsed cart holds.
     *
     * Correctness never depends on this - scopeAvailable() already treats a lapsed hold as free at
     * read time - but leaving the rows in a held state makes every box-office view and every export
     * read as though seats are still out.
     */
    public function sweepExpiredHolds(?Carbon $now = null): int
    {
        $now = $now ?: now();

        return SeatingSeat::where('status', 'held')
            ->where('hold_kind', 'cart')
            ->whereNotNull('hold_expires_at')
            ->where('hold_expires_at', '<', $now)
            ->update([
                'status' => 'available', 'hold_kind' => null,
                'hold_token' => null, 'hold_expires_at' => null, 'checked_in_at' => null,
            ]);
    }

    /** The seats this token still holds, lapsed ones excluded. */
    public function heldByToken(EventSeatingMap $map, string $token, ?Carbon $now = null)
    {
        return SeatingSeat::where('event_seating_map_id', $map->id)
            ->heldByToken($token, $now)
            ->orderBy('id')
            ->get();
    }
}
