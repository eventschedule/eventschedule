<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\EventSeatingMap;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;

/**
 * Keeps accessible seating for the people it is there for.
 *
 * Two rules, both enforced on the SERVER. Until now `accessibility_only` and the `wheelchair`
 * seat kind were drawn on the map and nothing else - a guest could hand-post a wheelchair space's
 * seat id and buy it with an ordinary ticket, which is the one failure this feature really cannot
 * afford given it is sold on accessible seating.
 *
 * 1. A wheelchair space is only sellable through a section flagged `accessibility_only`. An
 *    organizer who marks a wheelchair seat inside an ordinary section has not made it bookable by
 *    the people who need it, so it is not bookable at all until they flag the section.
 *
 * 2. A companion seat is held for whoever takes the wheelchair space beside it. It cannot be taken
 *    on its own while that space is still free - only together with it, or once it is gone.
 */
class AccessibleSeatingRule
{
    /**
     * @param  \Illuminate\Support\Collection<int,SeatingSeat>  $seats  the seats being taken
     */
    public function validate(EventSeatingMap $map, $seats): void
    {
        if ($seats->isEmpty()) {
            return;
        }

        $sections = SeatingSection::whereIn('id', $seats->pluck('seating_section_id')->unique())
            ->get()->keyBy('id');

        foreach ($seats as $seat) {
            if ($seat->kind !== 'wheelchair') {
                continue;
            }

            if (! ($sections[$seat->seating_section_id]->accessibility_only ?? false)) {
                throw new BusinessException(__('messages.seating_wheelchair_not_bookable', [
                    'seat' => $seat->fullLabel() ?: $seat->id,
                ]));
            }
        }

        $this->assertCompanionsNotOrphaned($map, $seats);
    }

    /**
     * A companion seat may only go with its wheelchair space, or after it.
     *
     * "Beside it" means the nearest wheelchair seat in the same row that is not separated by a
     * gangway - `aisle_after` marks a physical break, so a companion across the aisle is an
     * ordinary seat.
     */
    private function assertCompanionsNotOrphaned(EventSeatingMap $map, $seats): void
    {
        $companions = $seats->where('kind', 'companion');

        if ($companions->isEmpty()) {
            return;
        }

        $takenIds = $seats->pluck('id')->all();

        foreach ($companions as $companion) {
            $row = SeatingSeat::where('event_seating_map_id', $map->id)
                ->where('seating_section_id', $companion->seating_section_id)
                ->where('row_position', $companion->row_position)
                ->orderBy('position')
                ->get();

            $partner = $this->wheelchairBeside($row, $companion);

            // No wheelchair space next to it - an ordinary seat that merely carries the label.
            if (! $partner) {
                continue;
            }

            // Free and not being taken in the same breath: it is still being held for its user.
            if ($partner->isAvailable() && ! in_array($partner->id, $takenIds, true)) {
                throw new BusinessException(__('messages.seating_companion_reserved', [
                    'seat' => $companion->fullLabel() ?: $companion->id,
                ]));
            }
        }
    }

    private function wheelchairBeside($row, SeatingSeat $companion): ?SeatingSeat
    {
        $index = $row->search(fn ($s) => $s->id === $companion->id);

        if ($index === false) {
            return null;
        }

        foreach ([-1, 1] as $step) {
            $i = $index + $step;
            $neighbour = $row[$i] ?? null;

            if (! $neighbour) {
                continue;
            }

            // A gangway between them means they are not neighbours.
            $blocked = $step === 1 ? $companion->aisle_after : (bool) $neighbour->aisle_after;

            if (! $blocked && $neighbour->kind === 'wheelchair') {
                return $neighbour;
            }
        }

        return null;
    }
}
