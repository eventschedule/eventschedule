<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventSeatingMap;
use App\Models\SeatingDecoration;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Models\SeatingTable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Turns a seating plan TEMPLATE into one occurrence's own seat map, and answers
 * availability questions about a materialized map.
 *
 * The snapshot is the whole point of the design. Once an occurrence has its own copy:
 *   - editing the template can never disturb an event that has already sold seats;
 *   - "Modify this date only" is just editing the copy, with no override or tombstone
 *     machinery;
 *   - a recurring event reuses one template across every date, which is what organizers
 *     actually want.
 *
 * Materialization is LAZY - first designer open, first guest view, first box-office view -
 * so a recurring event with 200 future dates costs nothing until a date is touched.
 */
class SeatingMapService
{
    /**
     * Resolve the occurrence key exactly as TicketController::assertLegTicketsAvailable()
     * does. A one-time event reaches the guest page with no date in hand; if this resolved
     * differently from checkout the map and the oversell guard would key on different
     * strings and the event would quietly acquire two maps.
     */
    public function resolveDate(Event $event, ?string $date = null): ?string
    {
        return $date ?: $event->saleEventDateFromStartsAt();
    }

    /**
     * Read an occurrence's map without creating one.
     */
    public function mapFor(Event $event, ?string $date = null): ?EventSeatingMap
    {
        $date = $this->resolveDate($event, $date);

        if (! $date) {
            return null;
        }

        return EventSeatingMap::where('event_id', $event->id)
            ->where('event_date', $date)
            ->first();
    }

    /**
     * Read the occurrence's map, snapshotting the template on first use.
     *
     * Returns null when the event sells no allocated seating, or when the occurrence
     * cannot be keyed to a date - never a half-built map.
     */
    public function materialize(Event $event, ?string $date = null): ?EventSeatingMap
    {
        $date = $this->resolveDate($event, $date);

        if (! $date || ! $event->seating_plan_id) {
            return null;
        }

        // Fast path: the overwhelmingly common case is an occurrence that already has its
        // map, and it must not pay for a transaction.
        if ($existing = $this->mapFor($event, $date)) {
            return $existing;
        }

        $plan = SeatingPlan::with(['levels', 'sections.tables', 'sections.seats'])
            ->where('is_deleted', false)
            ->find($event->seating_plan_id);

        if (! $plan) {
            return null;
        }

        try {
            return DB::transaction(function () use ($event, $date, $plan) {
                // The locking read takes a gap lock over the (event_id, event_date) unique
                // index under MySQL's default REPEATABLE READ, so a second request for the
                // same occurrence waits here instead of racing us. Whoever waits then finds
                // the finished map rather than a half-copied one, because the copy below
                // shares this transaction.
                $map = EventSeatingMap::where('event_id', $event->id)
                    ->where('event_date', $date)
                    ->lockForUpdate()
                    ->first();

                if ($map) {
                    return $map;
                }

                // Inherited, not defaulted: a venue that turned the single-seat rule off for the
                // room should not have it come back on for every new date.
                $map = EventSeatingMap::create([
                    'event_id' => $event->id,
                    'event_date' => $date,
                    'seating_plan_id' => $plan->id,
                ] + $plan->orphanRuleDefaults());

                $this->copyStructure($plan, $map);
                $this->applyBandMapping($event, $map);

                $map->forceFill(['materialized_at' => now()])->save();

                // Reload so the column DEFAULTS are on the model. create() only carries the
                // attributes it was handed, so a freshly-materialized map came back with
                // orphan_rule_enabled = null - silently disabling the orphan rule for exactly the
                // first buyer, and leaving version/min_gap/lift_pct null too.
                return $map->refresh();
            });
        } catch (QueryException $e) {
            // Backstop for the case the gap lock does not cover (a non-default isolation
            // level): the unique index rejected the insert, so another transaction has
            // already committed one. A fresh read must therefore find it.
            if ($map = $this->mapFor($event, $date)) {
                return $map;
            }

            throw $e;
        }
    }

    /**
     * Deep-copy levels, sections, tables and seats from a template onto a snapshot.
     *
     * Seats are bulk-inserted rather than saved one by one - a 2,000-seat house would
     * otherwise be 2,000 round trips on the first page view. That bypasses the
     * SeatingOwnable saving guard, which is safe here precisely because the owner columns
     * are stamped from $map->ownerAttributes() a few lines up rather than by a caller.
     */
    protected function copyStructure(SeatingPlan $plan, EventSeatingMap $map): void
    {
        $owner = $map->ownerAttributes();
        $now = now();

        $levelIds = [];
        foreach ($plan->levels as $level) {
            $copy = SeatingLevel::create(array_merge($owner, [
                'name' => $level->name,
                'position' => $level->position,
                'width' => $level->width,
                'height' => $level->height,
            ]));
            $levelIds[$level->id] = $copy->id;
        }

        // Copied with the rest of the room: the stage is what tells a buyer which way the seats
        // face, so a snapshot without it is a different map from the one the organizer drew.
        foreach (SeatingDecoration::where('seating_plan_id', $plan->id)->orderBy('position')->get() as $decoration) {
            if (! isset($levelIds[$decoration->seating_level_id])) {
                continue;
            }

            SeatingDecoration::create(array_merge($owner, [
                'seating_level_id' => $levelIds[$decoration->seating_level_id],
                'kind' => $decoration->kind,
                'label' => $decoration->label,
                'x' => $decoration->x,
                'y' => $decoration->y,
                'width' => $decoration->width,
                'height' => $decoration->height,
                'rotation' => $decoration->rotation,
                'position' => $decoration->position,
            ]));
        }

        $sectionIds = [];
        foreach ($plan->sections as $section) {
            // A section whose level is missing would be unreachable in the designer.
            if (! isset($levelIds[$section->seating_level_id])) {
                continue;
            }

            $copy = SeatingSection::create(array_merge($owner, [
                'seating_level_id' => $levelIds[$section->seating_level_id],
                'name' => $section->name,
                'color' => $section->color,
                'kind' => $section->kind,
                'capacity' => $section->capacity,
                'band' => $section->band,
                // ticket_id stays null: tickets belong to the event, and the band is mapped
                // to one when the plan is attached, not when it is copied.
                'accessibility_only' => $section->accessibility_only,
                'x' => $section->x,
                'y' => $section->y,
                'rotation' => $section->rotation,
                'position' => $section->position,
                'shape' => $section->shape,
            ]));
            $sectionIds[$section->id] = $copy->id;
        }

        $tableIds = [];
        foreach ($plan->sections as $section) {
            if (! isset($sectionIds[$section->id])) {
                continue;
            }

            foreach ($section->tables as $table) {
                $copy = SeatingTable::create([
                    'seating_section_id' => $sectionIds[$section->id],
                    'label' => $table->label,
                    'shape' => $table->shape,
                    'seat_count' => $table->seat_count,
                    'booking_mode' => $table->booking_mode,
                    'x' => $table->x,
                    'y' => $table->y,
                    'rotation' => $table->rotation,
                    'width' => $table->width,
                    'height' => $table->height,
                ]);
                $tableIds[$table->id] = $copy->id;
            }
        }

        $rows = [];
        foreach ($plan->sections as $section) {
            if (! isset($sectionIds[$section->id])) {
                continue;
            }

            foreach ($section->seats as $seat) {
                $rows[] = array_merge($owner, [
                    'seating_section_id' => $sectionIds[$section->id],
                    'seating_table_id' => $seat->seating_table_id ? ($tableIds[$seat->seating_table_id] ?? null) : null,
                    'row_label' => $seat->row_label,
                    'row_position' => $seat->row_position,
                    'seat_label' => $seat->seat_label,
                    'x' => $seat->x,
                    'y' => $seat->y,
                    'kind' => $seat->kind,
                    'aisle_after' => $seat->aisle_after,
                    'position' => $seat->position,
                    'source_seat_id' => $seat->id,
                    'status' => 'available',
                    'state_version' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            SeatingSeat::insert($chunk);
        }
    }

    /**
     * Point each snapshot section at the ticket that prices its band.
     *
     * Runs at materialization and again whenever the event's tickets are saved, so renaming a band
     * or adding a ticket reaches every date that has already been snapshotted - without that, a
     * recurring event's older maps would keep selling at the mapping they were copied under.
     *
     * One UPDATE per band rather than per section: a 200-date recurring event has 200 maps.
     * Passing $map scopes it to a single occurrence, which is what materialize() wants.
     */
    public function applyBandMapping(Event $event, ?EventSeatingMap $map = null): void
    {
        $mapIds = $map
            ? [$map->id]
            : EventSeatingMap::where('event_id', $event->id)->pluck('id')->all();

        if (! $mapIds) {
            return;
        }

        $tickets = $event->tickets()->whereNotNull('seating_band')->get();

        // Anything whose band no longer matches a ticket loses its mapping, so a deleted or
        // renamed band cannot leave a section quietly selling at the old price.
        SeatingSection::whereIn('event_seating_map_id', $mapIds)
            ->when($tickets->isNotEmpty(), fn ($q) => $q->whereNotIn('band', $tickets->pluck('seating_band')->all()))
            ->update(['ticket_id' => null]);

        foreach ($tickets as $ticket) {
            SeatingSection::whereIn('event_seating_map_id', $mapIds)
                ->where('band', $ticket->seating_band)
                ->update(['ticket_id' => $ticket->id]);
        }
    }

    /**
     * Follow a one-time event that has been moved to a different date.
     *
     * The map is keyed (event_id, event_date), so without this a rescheduled event strands its
     * snapshot - blocked seats, house holds and the sold seats themselves - on a date nothing will
     * ever ask for again, and materializes a blank map for the new one.
     *
     * If the destination somehow already has a map, the stale one is dropped rather than merged,
     * unless it holds sold seats: those rows are the only record of who is sitting where, so both
     * are left in place for a human to sort out.
     */
    public function rekeyOccurrence(Event $event, ?string $from, ?string $to): void
    {
        if (! $from || ! $to || $from === $to) {
            return;
        }

        $map = EventSeatingMap::where('event_id', $event->id)->where('event_date', $from)->first();
        if (! $map) {
            return;
        }

        if (EventSeatingMap::where('event_id', $event->id)->where('event_date', $to)->exists()) {
            if (! $map->seats()->where('status', 'sold')->exists()) {
                $map->delete();
            }

            return;
        }

        $map->update(['event_date' => $to]);
    }

    /**
     * Drop an occurrence's own map so it falls back to the template on next use.
     *
     * Refuses while any seat is sold: those rows carry the only record of who is sitting
     * where, and the sale itself would survive the delete pointing at nothing.
     */
    public function revertToTemplate(EventSeatingMap $map): bool
    {
        // Both guards and the delete have to sit in ONE transaction, reading under a row lock.
        //
        // Unguarded, these were consistent reads: under REPEATABLE READ they answer from a
        // snapshot taken when the statement began, so a seat-selling transaction still in flight
        // is INVISIBLE to them - and seating_seats.event_seating_map_id is cascadeOnDelete while
        // sale_id is only nullOnDelete, so the DELETE then blocks on that sale's row locks, waits
        // for it to commit, and takes the freshly sold seat with it. The next page view
        // re-snapshots the template with every seat available, so the buyer's seat goes back on
        // sale to somebody else while their ticket still names it.
        //
        // lockForUpdate() makes these CURRENT reads: they see the newest committed row and block
        // on an uncommitted one, which is exactly the serialization this needs. Guest checkout was
        // already safe (a seat cannot reach `sold` without a committed cart hold, which guard 2
        // sees), but bookSeats(), the pass claim and assignBestAvailableForSale() all go from
        // available straight to sold with nothing the old guards could observe.
        return DB::transaction(function () use ($map) {
            $seats = $map->seats()->orderBy('id')->lockForUpdate()->get();

            foreach ($seats as $seat) {
                if ($seat->status === 'sold') {
                    return false;
                }

                // A guest is midway through checkout with seats held. Deleting the map would drop
                // their selection with no signal at all. Staff blocks (no expiry) are NOT a reason
                // to refuse - discarding per-date customisation is the whole point of reverting.
                if ($seat->status === 'held'
                    && $seat->hold_kind === 'cart'
                    && $seat->hold_expires_at
                    && $seat->hold_expires_at->gte(now())) {
                    return false;
                }
            }

            $map->delete();

            return true;
        });
    }

    /**
     * Seats a guest could still take, optionally narrowed to one price band.
     *
     * Goes through SeatingSeat::scopeAvailable() rather than testing status directly, so
     * a lapsed cart hold counts as free here exactly as it does at the moment of acquire.
     */
    public function availableSeatCount(EventSeatingMap $map, ?int $ticketId = null): int
    {
        $query = $this->seatsForMap($map)->available();

        if ($ticketId !== null) {
            $query->whereIn('seating_section_id', SeatingSection::where('event_seating_map_id', $map->id)
                ->where('ticket_id', $ticketId)
                ->select('id'));
        }

        return $query->count();
    }

    /**
     * Total seats defined for the occurrence, ignoring state. The denominator for the
     * orphan rule's "nearly sold out" lift and for the box-office report.
     */
    public function totalSeatCount(EventSeatingMap $map): int
    {
        return $this->seatsForMap($map)->count();
    }

    /**
     * The one query every seat read for an occurrence should start from: this map's seats,
     * excluding any whose section has been removed. Seats of a soft-deleted section are kept on
     * purpose (a sold one must not lose its history) which is exactly why they have to be
     * filtered out of anything that decides what can still be sold.
     */
    public function seatsForMap(EventSeatingMap $map): \Illuminate\Database\Eloquent\Builder
    {
        return SeatingSeat::where('event_seating_map_id', $map->id)->inLiveSection();
    }
}
