<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\EventSeatingMap;
use App\Models\SeatingSeat;
use Carbon\Carbon;

/**
 * Stops a selection stranding a single seat between two booked groups.
 *
 * The customer's ask verbatim: "an orphan seat rule automatically stops a single seat being left
 * stranded between two booked groups (this restriction lifts automatically for online bookings
 * once an event is nearly sold out, so it doesn't block the last few seats going)".
 *
 * Two decisions worth knowing:
 *
 * 1. It only rejects orphans the SELECTION creates. A row that already had a stranded seat before
 *    this buyer arrived is somebody else's doing, and refusing their order for it would be both
 *    unfair and unfixable from their side.
 *
 * 2. It never judges a buyer for seats they already hold. Both the hold path and checkout pass
 *    the buyer's own seats as $treatAsFree, so the comparison measures what THEIR selection does
 *    to the row and blames them for nothing else - an orphan created beside them by somebody
 *    else's purchase is not theirs to answer for.
 */
class OrphanSeatRule
{
    /**
     * @param  bool  $isBoxOffice  Staff are always exempt - they can see the whole map and are
     *                             often deliberately filling awkward gaps.
     */
    public function validate(EventSeatingMap $map, array $seatIds, bool $isBoxOffice = false, ?Carbon $now = null, array $treatAsFree = []): void
    {
        if ($this->strandedBy($map, $seatIds, $isBoxOffice, $now, $treatAsFree)->isNotEmpty()) {
            throw new BusinessException(__('messages.seating_orphan_seat'));
        }
    }

    /**
     * What a buyer ALREADY holding these seats should be seeing, or null.
     *
     * Deliberately the same walk validateFinal() throws on, so the notice a guest is shown and the
     * refusal they meet at checkout can never disagree - a warning that says "fine" while checkout
     * says "no" would be worse than no warning at all.
     *
     * @param  array<int,int>|null  $treatAsFree  seats to judge as free, defaulting to the
     *                                            selection itself (which is the "already held"
     *                                            case). The hold path passes the seats it is
     *                                            about to release instead.
     * @return array{message: string, seat_ids: array<int,int>, label: string, count: int}|null
     */
    public function advisoryFor(EventSeatingMap $map, array $seatIds, ?Carbon $now = null, ?array $treatAsFree = null): ?array
    {
        $stranded = $this->strandedBy($map, $seatIds, false, $now, $treatAsFree ?? $seatIds);

        if ($stranded->isEmpty()) {
            return null;
        }

        $first = $stranded->first();
        // Named, because "a single seat" is not findable on a three-hundred-seat map.
        $label = $first->fullLabel() ?: (string) $first->id;

        return [
            'message' => __('messages.seating_orphan_seat_advisory', ['seat' => $label]),
            'seat_ids' => $stranded->pluck('id')->all(),
            'label' => $label,
            'count' => $stranded->count(),
        ];
    }

    /**
     * The same question, asked of a selection the buyer is ALREADY holding.
     *
     * The rule's original note said it ran at hold time only, because at checkout "any new orphan
     * beside them was created by a different purchase, and refusing the sale at the payment step
     * would punish the wrong person". That reasoning still stands and this respects it: the
     * buyer's own seats are treated as free in the baseline, so the comparison measures what
     * THEIR selection does and blames them for nothing else.
     *
     * Needed because selection is now incremental - a click that would strand a seat is allowed
     * and merely warned about, since the next click may well fill the gap. Something has to ask
     * the question once the selection is final, and this is it.
     */
    public function validateFinal(EventSeatingMap $map, array $seatIds, ?Carbon $now = null): void
    {
        $this->validate($map, $seatIds, false, $now, $seatIds);
    }

    /**
     * The seats THIS selection would leave stranded. Empty means the selection is fine.
     *
     * @return \Illuminate\Support\Collection<int,SeatingSeat>
     */
    private function strandedBy(EventSeatingMap $map, array $seatIds, bool $isBoxOffice, ?Carbon $now, array $treatAsFree)
    {
        if ($isBoxOffice || ! $map->orphan_rule_enabled || empty($seatIds)) {
            return collect();
        }

        // "Lifts once nearly sold out" - past the threshold the rule would be blocking the last
        // few seats from selling at all, which is the opposite of what it is for.
        if ($this->soldPercent($map) >= (int) $map->orphan_rule_lift_pct) {
            return collect();
        }

        $selected = SeatingSeat::whereIn('id', $seatIds)
            ->where('event_seating_map_id', $map->id)
            ->get(['id', 'seating_section_id', 'row_position']);

        $rows = $selected->groupBy(fn ($s) => $s->seating_section_id.'|'.$s->row_position);
        $selectedIds = $selected->pluck('id')->all();
        $gap = max(1, (int) $map->orphan_rule_min_gap);
        $stranded = collect();

        foreach ($rows as $key => $ignored) {
            [$sectionId, $rowPosition] = explode('|', $key);

            $row = SeatingSeat::where('event_seating_map_id', $map->id)
                ->where('seating_section_id', $sectionId)
                ->where('row_position', $rowPosition)
                ->orderBy('position')
                ->get();

            // $treatAsFree carries the seats the caller is about to release - the buyer's own
            // holds. Every call after their first click has the whole selection already `held`, so
            // without it both passes see an identical room and the comparison can never fire. See
            // SeatHoldService::acquire() for the hold path and validateFinal() for checkout.
            $before = $this->orphanRuns($row, [], $gap, $now, $treatAsFree);
            $after = $this->orphanRuns($row, $selectedIds, $gap, $now, $treatAsFree);

            // RUNS, not seats: with a gap of 2 a stranded pair is one orphan, and a row that
            // merely swaps one orphan for another has not been made worse by this buyer.
            if (count($after) <= count($before)) {
                continue;
            }

            $had = collect($before)->flatten(1)->pluck('id')->all();
            $new = collect($after)->flatten(1)->reject(fn ($seat) => in_array($seat->id, $had, true));

            // A row can get worse without any individual seat being new to an orphan run. Naming
            // the first orphan is still better than naming nothing.
            $stranded = $stranded->merge($new->isNotEmpty() ? $new : collect($after[0]));
        }

        return $stranded;
    }

    /**
     * The stranded runs of this row, treating $alsoTaken as sold.
     *
     * A run is a block of still-free seats bounded by taken seats, a gangway (`aisle_after`) or the
     * end of the row. A run at or under the configured gap is an orphan - the default gap of 1
     * means "never leave exactly one seat on its own".
     *
     * @return array<int,array<int,SeatingSeat>>
     */
    private function orphanRuns($row, array $alsoTaken, int $gap, ?Carbon $now, array $treatAsFree = []): array
    {
        $runs = [];
        $run = [];

        foreach ($row as $seat) {
            // $treatAsFree wins over the seat's stored status: at claim time the buyer's own seats
            // are already `held`, so without this both passes would see them as taken and the
            // before/after comparison would be identical - the check would never fire at all.
            $taken = in_array($seat->id, $alsoTaken, true)
                || (! in_array($seat->id, $treatAsFree, true) && ! $seat->isAvailable($now));

            if ($taken) {
                if ($run) {
                    $runs[] = $run;
                }
                $run = [];

                continue;
            }

            $run[] = $seat;

            // A gangway means the seats either side are not neighbours, so the run ends here even
            // though the next seat is free.
            if ($seat->aisle_after) {
                $runs[] = $run;
                $run = [];
            }
        }

        if ($run) {
            $runs[] = $run;
        }

        return array_values(array_filter($runs, fn ($seats) => count($seats) <= $gap));
    }

    /** Percent of the map's live seats that are no longer available. */
    public function soldPercent(EventSeatingMap $map): int
    {
        $maps = app(SeatingMapService::class);
        $total = $maps->totalSeatCount($map);

        if ($total === 0) {
            return 0;
        }

        return (int) round((($total - $maps->availableSeatCount($map)) / $total) * 100);
    }
}
