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
 * 2. It runs at HOLD time only, not again at checkout. By the time a buyer checks out the seats
 *    are already theirs; any new orphan beside them was created by a different purchase, and
 *    refusing the sale at the payment step would punish the wrong person for it.
 */
class OrphanSeatRule
{
    /**
     * @param  bool  $isBoxOffice  Staff are always exempt - they can see the whole map and are
     *                             often deliberately filling awkward gaps.
     */
    public function validate(EventSeatingMap $map, array $seatIds, bool $isBoxOffice = false, ?Carbon $now = null): void
    {
        if ($isBoxOffice || ! $map->orphan_rule_enabled || empty($seatIds)) {
            return;
        }

        // "Lifts once nearly sold out" - past the threshold the rule would be blocking the last
        // few seats from selling at all, which is the opposite of what it is for.
        if ($this->soldPercent($map) >= (int) $map->orphan_rule_lift_pct) {
            return;
        }

        $selected = SeatingSeat::whereIn('id', $seatIds)
            ->where('event_seating_map_id', $map->id)
            ->get(['id', 'seating_section_id', 'row_position']);

        $rows = $selected->groupBy(fn ($s) => $s->seating_section_id.'|'.$s->row_position);
        $selectedIds = $selected->pluck('id')->all();

        foreach ($rows as $key => $ignored) {
            [$sectionId, $rowPosition] = explode('|', $key);

            $row = SeatingSeat::where('event_seating_map_id', $map->id)
                ->where('seating_section_id', $sectionId)
                ->where('row_position', $rowPosition)
                ->orderBy('position')
                ->get();

            $gap = max(1, (int) $map->orphan_rule_min_gap);
            $before = $this->orphanCount($row, [], $gap, $now);
            $after = $this->orphanCount($row, $selectedIds, $gap, $now);

            if ($after > $before) {
                throw new BusinessException(__('messages.seating_orphan_seat'));
            }
        }
    }

    /**
     * How many stranded runs this row would have, treating $alsoTaken as sold.
     *
     * A run is a block of still-free seats bounded by taken seats, a gangway (`aisle_after`) or the
     * end of the row. A run at or under the configured gap is an orphan - the default gap of 1
     * means "never leave exactly one seat on its own".
     */
    private function orphanCount($row, array $alsoTaken, int $gap, ?Carbon $now): int
    {
        $runs = [];
        $run = 0;

        foreach ($row as $seat) {
            $taken = in_array($seat->id, $alsoTaken, true) || ! $seat->isAvailable($now);

            if ($taken) {
                if ($run > 0) {
                    $runs[] = $run;
                }
                $run = 0;

                continue;
            }

            $run++;

            // A gangway means the seats either side are not neighbours, so the run ends here even
            // though the next seat is free.
            if ($seat->aisle_after) {
                $runs[] = $run;
                $run = 0;
            }
        }

        if ($run > 0) {
            $runs[] = $run;
        }

        return count(array_filter($runs, fn ($len) => $len <= $gap));
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
