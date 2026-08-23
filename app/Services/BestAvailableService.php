<?php

namespace App\Services;

use App\Models\EventSeatingMap;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Models\SeatingTable;
use App\Models\Ticket;
use Carbon\Carbon;

/**
 * Picks the seats a guest would most likely have picked themselves.
 *
 * This is the DEFAULT path, not a fallback: most buyers want four seats together at a fair price,
 * not a floor plan to study. It is also what makes the feature usable on a phone and without a
 * mouse, since choosing seats then needs no pointer at all.
 *
 * Also used for pass advance-bookings, which claim a real seat but give the holder no picker.
 */
class BestAvailableService
{
    /**
     * The best block of $qty seats for this ticket's bands, as seat ids.
     *
     * Preference order: earlier section, then earlier row, then closest to the centre of its row.
     * Returns fewer than $qty only when that many seats do not exist.
     */
    public function pick(EventSeatingMap $map, Ticket $ticket, int $qty, ?Carbon $now = null): array
    {
        if ($qty < 1) {
            return [];
        }

        $sections = SeatingSection::where('event_seating_map_id', $map->id)
            ->where('is_deleted', false)
            ->where('ticket_id', $ticket->id)
            ->pluck('position', 'id');

        if ($sections->isEmpty()) {
            return [];
        }

        $seats = SeatingSeat::where('event_seating_map_id', $map->id)
            ->whereIn('seating_section_id', $sections->keys())
            ->available($now)
            ->orderBy('seating_section_id')->orderBy('row_position')->orderBy('position')
            ->get();

        if ($seats->isEmpty()) {
            return [];
        }

        // A whole-table-only table is all or nothing, so it cannot take part in the ordinary
        // run-scoring below - that would hand back three chairs of a table of ten, which is
        // precisely what WholeTableRule now refuses. Split them out and offer them separately,
        // whole, if the party is big enough to take one.
        [$wholeTableSeats, $seats] = $this->splitWholeTables($seats);

        $best = null;

        foreach ($seats->groupBy(fn ($s) => $s->seating_section_id.'|'.$s->row_position) as $row) {
            $row = $row->values();
            $positions = $row->pluck('position');
            // Centre of what is still free in this row. Using the free seats rather than the row's
            // full width keeps the pick sensible as a row fills from the outside in.
            $rowCentre = ($positions->min() + $positions->max()) / 2;

            for ($i = 0; $i + $qty <= $row->count(); $i++) {
                $block = $row->slice($i, $qty)->values();

                if (! $this->isContiguous($block)) {
                    continue;
                }

                $centre = ($block->first()->position + $block->last()->position) / 2;
                $score = [
                    (int) ($sections[$block->first()->seating_section_id] ?? 0),
                    (int) $block->first()->row_position,
                    abs($centre - $rowCentre),
                ];

                // PHP compares equal-length arrays element by element, which is exactly the
                // section-then-row-then-centre precedence we want.
                if ($best === null || $score < $best['score']) {
                    $best = ['score' => $score, 'ids' => $block->pluck('id')->all()];
                }
            }
        }

        if ($best) {
            return $best['ids'];
        }

        // Nothing contiguous left. Handing back the best singles beats refusing the sale outright -
        // a party of four late to a busy show would rather sit apart than not come.
        $singles = $seats->take($qty)->pluck('id')->all();

        if (count($singles) >= $qty) {
            return $singles;
        }

        // Still short. A fully free whole-table is the last resort, and only when the party
        // actually fills it: a table of ten is not an answer for a party of two.
        foreach ($wholeTableSeats as $tableSeats) {
            if ($tableSeats->count() === $qty) {
                return $tableSeats->pluck('id')->all();
            }
        }

        return $singles;
    }

    /**
     * Separate the seats of whole-table-only tables from the rest.
     *
     * A table only qualifies if EVERY one of its seats is still free - a table with one seat gone
     * can no longer be sold whole, so its remaining seats are not offerable at all under this mode
     * and drop out of both lists.
     *
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection}
     */
    private function splitWholeTables($seats): array
    {
        $tableIds = $seats->pluck('seating_table_id')->filter()->unique();

        if ($tableIds->isEmpty()) {
            return [collect(), $seats];
        }

        $wholeIds = SeatingTable::whereIn('id', $tableIds)
            ->where('booking_mode', 'whole')
            ->pluck('id');

        if ($wholeIds->isEmpty()) {
            return [collect(), $seats];
        }

        $totals = SeatingSeat::whereIn('seating_table_id', $wholeIds)
            ->inLiveSection()
            ->selectRaw('seating_table_id, COUNT(*) as total')
            ->groupBy('seating_table_id')
            ->pluck('total', 'seating_table_id');

        $whole = collect();

        foreach ($seats->whereIn('seating_table_id', $wholeIds)->groupBy('seating_table_id') as $tableId => $group) {
            if ($group->count() === (int) ($totals[$tableId] ?? 0)) {
                $whole->push($group->values());
            }
        }

        return [$whole, $seats->whereNotIn('seating_table_id', $wholeIds)->values()];
    }

    /**
     * Adjacent in the same row, with no gangway between them.
     *
     * Positions are consecutive because a sold or held seat drops out of the available list and
     * leaves a hole; aisle_after marks a physical gangway, so seats either side of it are not
     * neighbours however close their numbers are.
     */
    private function isContiguous($block): bool
    {
        for ($k = 0, $n = $block->count(); $k < $n - 1; $k++) {
            if ($block[$k]->aisle_after) {
                return false;
            }

            if ((int) $block[$k + 1]->position !== (int) $block[$k]->position + 1) {
                return false;
            }
        }

        return true;
    }
}
