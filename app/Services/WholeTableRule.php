<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\EventSeatingMap;
use App\Models\SeatingSeat;
use App\Models\SeatingTable;

/**
 * Makes a table's Booking setting mean something.
 *
 * `seating_tables.booking_mode` has three values, set in the designer: `seat` (single seats only),
 * `whole` (whole table only) and `either`. It was stored, copied into every occurrence snapshot,
 * shipped to the browser in the picker payload and drawn in the designer - and read by nothing.
 * "Whole table only" sold exactly like single seats, so a venue that set it got the opposite of
 * what it asked for: a fundraising dinner where one guest takes a single chair at a table of ten.
 *
 * Enforced on the SERVER, like the accessibility and orphan rules, because the picker is only an
 * affordance - a hand-posted seat id has to be refused too.
 *
 * Box office is exempt, the same convention the other two rules follow: staff can see the whole
 * room and seating one person at a reserved table is exactly their job.
 */
class WholeTableRule
{
    /**
     * @param  \Illuminate\Support\Collection<int,SeatingSeat>  $seats  the seats being taken
     */
    public function validate(EventSeatingMap $map, $seats): void
    {
        $tableIds = $seats->pluck('seating_table_id')->filter()->unique();

        if ($tableIds->isEmpty()) {
            return;
        }

        $tables = SeatingTable::whereIn('id', $tableIds)->get();

        foreach ($tables as $table) {
            if (! $table->requiresWholeTable()) {
                continue;
            }

            // Every seat of the table has to be in this selection. Seats already sold to somebody
            // else are counted as missing on purpose: a whole-table booking that is only half a
            // table is not a whole-table booking, whoever holds the other half.
            $wanted = $seats->where('seating_table_id', $table->id)->count();
            $total = SeatingSeat::where('seating_table_id', $table->id)->inLiveSection()->count();

            if ($wanted < $total) {
                throw new BusinessException(__('messages.seating_whole_table_only', [
                    'table' => $table->label ?: $table->id,
                    'count' => $total,
                ]));
            }
        }
    }
}
