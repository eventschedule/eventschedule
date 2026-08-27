<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\EventSeatingMap;
use App\Models\SeatingDecoration;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Models\SeatingTable;
use Illuminate\Support\Facades\DB;

/**
 * Reads and writes a whole seating structure (levels -> sections -> tables -> seats) for either
 * a template or one occurrence's snapshot.
 *
 * Owner-agnostic on purpose. The designer edits a SeatingPlan, "Modify this date only" edits an
 * EventSeatingMap, and the box office edits the same map - one save path for all three means the
 * sold-seat guard and the id-ownership checks cannot drift between them.
 *
 * GEOMETRY: every coordinate is relative to its IMMEDIATE parent. A section's x/y is relative to
 * its level, a table's and a loose seat's to their section, and a table seat's to its table. So
 * dragging a section moves its rows, and dragging a table moves its chairs, with no cascade.
 */
class SeatingStructureService
{
    /** Guard rails against a runaway or hostile payload. A 2,000-seat house is a big theatre. */
    public const MAX_LEVELS = 12;

    public const MAX_SECTIONS = 200;

    public const MAX_SEATS = 6000;

    public const MAX_TABLES = 500;

    /** Generous: a stage plus a handful of labels per level is the realistic use. */
    public const MAX_DECORATIONS = 200;

    /**
     * The whole structure as the designer consumes it. Ids are raw integers: this never leaves
     * an authenticated owner-scoped endpoint, and the save path re-checks every id against the
     * owner anyway.
     */
    public function toArray(SeatingPlan|EventSeatingMap $owner): array
    {
        $levels = SeatingLevel::forOwner($owner)->orderBy('position')->get();
        $sections = SeatingSection::forOwner($owner)->where('is_deleted', false)->orderBy('position')->get();
        $tables = SeatingTable::whereIn('seating_section_id', $sections->pluck('id'))->get()->groupBy('seating_section_id');
        $seats = SeatingSeat::forOwner($owner)
            ->whereIn('seating_section_id', $sections->pluck('id'))
            ->orderBy('row_position')->orderBy('position')
            ->get()->groupBy('seating_section_id');
        $decorations = SeatingDecoration::forOwner($owner)
            ->orderBy('position')->get()->groupBy('seating_level_id');

        return [
            'levels' => $levels->map(fn (SeatingLevel $level) => [
                'id' => $level->id,
                'name' => $level->name,
                'position' => $level->position,
                'width' => $level->width,
                'height' => $level->height,
                'decorations' => ($decorations[$level->id] ?? collect())->values()
                    ->map(fn (SeatingDecoration $d) => [
                        'id' => $d->id,
                        'kind' => $d->kind,
                        'label' => $d->label,
                        'x' => $d->x, 'y' => $d->y,
                        'width' => $d->width, 'height' => $d->height,
                        'rotation' => $d->rotation,
                        'position' => $d->position,
                    ])->all(),
                'sections' => $sections->where('seating_level_id', $level->id)->values()
                    ->map(fn (SeatingSection $section) => [
                        'id' => $section->id,
                        'name' => $section->name,
                        'color' => $section->color,
                        'kind' => $section->kind,
                        'capacity' => $section->capacity,
                        'band' => $section->band,
                        'accessibility_only' => (bool) $section->accessibility_only,
                        'x' => $section->x,
                        'y' => $section->y,
                        'rotation' => $section->rotation,
                        'position' => $section->position,
                        'tables' => ($tables[$section->id] ?? collect())->values()
                            ->map(fn (SeatingTable $t) => [
                                'id' => $t->id,
                                'label' => $t->label,
                                'shape' => $t->shape,
                                'seat_count' => $t->seat_count,
                                'booking_mode' => $t->booking_mode,
                                'x' => $t->x, 'y' => $t->y, 'rotation' => $t->rotation,
                                'width' => $t->width, 'height' => $t->height,
                            ])->all(),
                        'seats' => ($seats[$section->id] ?? collect())->values()
                            ->map(fn (SeatingSeat $s) => [
                                'id' => $s->id,
                                'table_id' => $s->seating_table_id,
                                'row_label' => $s->row_label,
                                'row_position' => $s->row_position,
                                'seat_label' => $s->seat_label,
                                'x' => $s->x, 'y' => $s->y,
                                'kind' => $s->kind,
                                'aisle_after' => (bool) $s->aisle_after,
                                'position' => $s->position,
                                // Read-only for the designer, but it needs them to grey out and
                                // refuse to delete anything already sold on an occurrence map.
                                'status' => $s->status,
                                'locked' => $s->status === 'sold',
                            ])->all(),
                    ])->all(),
            ])->all(),
        ];
    }

    /**
     * Replace the structure with what the designer posted.
     *
     * Rows carrying an id the owner actually has are updated; anything else is created (the client
     * sends temporary ids for new rows, and a real id belonging to somebody else must never be
     * adoptable). Rows the payload omits are removed - sections soft, everything else hard.
     *
     * Refuses outright if the edit would remove a SOLD seat. That is the customer's "you cannot
     * delete seats or rows that already have bookings" rule, and it has to live here rather than
     * in the UI, because the box office and the per-date editor post to this same method.
     */
    public function save(SeatingPlan|EventSeatingMap $owner, array $data): void
    {
        // Here rather than only in the controller: duplicate() calls save() directly, and so will
        // the per-date editor and the box office. Cheap enough to run twice.
        $this->assertWithinLimits($data);

        $ownerAttrs = $owner->ownerAttributes();

        DB::transaction(function () use ($owner, $ownerAttrs, $data) {
            $existingLevels = SeatingLevel::forOwner($owner)->get()->keyBy('id');
            $existingSections = SeatingSection::forOwner($owner)->where('is_deleted', false)->get()->keyBy('id');
            // lockForUpdate: the sold-seat guard below reads this snapshot, but the DELETE is a
            // current read. Without the lock a checkout committing between the two could have its
            // paid seat deleted out from under it - and seating_seats.sale_id is on the deleted
            // side, so no foreign key would stop it.
            //
            // Scoped to the LIVE sections, matching toArray() - which is what builds the payload
            // this is diffed against. Unscoped, a seat whose section is already soft-deleted can
            // never appear in the post and so always lands in $droppedSeats: if it is sold, every
            // later save of this plan throws seating_cannot_remove_sold_seat naming a seat the
            // designer cannot see or restore, and the designer is bricked with no way back.
            // removeMissing() below makes that state unreachable through the app today (it deletes
            // a section's seats in the same save that soft-deletes the section, and refuses
            // outright if any is sold), but BackupService::importSeatingStructure() restores
            // sections and seats independently, so an archive can still carry it.
            //
            // whereIn on the keys already fetched, not inLiveSection(): that scope is a correlated
            // whereHas, and under FOR UPDATE it would take locks on seating_sections too.
            $existingSeats = SeatingSeat::forOwner($owner)
                ->whereIn('seating_section_id', $existingSections->keys())
                ->lockForUpdate()->get()->keyBy('id');
            $existingTables = SeatingTable::whereIn('seating_section_id', $existingSections->keys())->get()->keyBy('id');
            $existingDecorations = SeatingDecoration::forOwner($owner)->get()->keyBy('id');

            $keptLevels = [];
            $keptSections = [];
            $keptSeats = [];
            $keptTables = [];
            $keptDecorations = [];
            // Whether the client SAID anything about decorations at all. Collecting only what was
            // posted and hard-deleting the rest means a payload that simply omits the key wipes
            // every stage marker and label on the plan - the opposite of the "not posted means
            // leave alone" rule the orphan-rule columns follow.
            $decorationsDeclared = false;

            foreach (array_values($data['levels'] ?? []) as $levelIndex => $levelData) {
                $level = $this->upsert(
                    $existingLevels, $levelData['id'] ?? null,
                    fn () => new SeatingLevel($ownerAttrs),
                    [
                        'name' => $this->str($levelData['name'] ?? '', 100) ?: __('messages.seating_level'),
                        'position' => $levelIndex,
                        // Persisted for the API's benefit only. The client stopped reading these
                        // when the viewBox began tracking the rendered element rather than the
                        // level, so they are a constant on both sides - kept round-tripping so an
                        // API-built plan is not lossy.
                        'width' => $this->int($levelData['width'] ?? 1200, 200, 20000),
                        'height' => $this->int($levelData['height'] ?? 800, 200, 20000),
                    ]
                );
                $keptLevels[] = $level->id;

                $decorationsDeclared = $decorationsDeclared || array_key_exists('decorations', $levelData);

                foreach (array_values($levelData['decorations'] ?? []) as $decorationIndex => $decorationData) {
                    $decoration = $this->upsert(
                        $existingDecorations, $decorationData['id'] ?? null,
                        fn () => new SeatingDecoration($ownerAttrs),
                        [
                            'seating_level_id' => $level->id,
                            'kind' => ($decorationData['kind'] ?? 'stage') === 'text' ? 'text' : 'stage',
                            'label' => $this->str($decorationData['label'] ?? null, 100),
                            'x' => $this->int($decorationData['x'] ?? 0, -20000, 20000),
                            'y' => $this->int($decorationData['y'] ?? 0, -20000, 20000),
                            'width' => $this->int($decorationData['width'] ?? 320, 10, 20000),
                            'height' => $this->int($decorationData['height'] ?? 40, 10, 20000),
                            'rotation' => $this->int($decorationData['rotation'] ?? 0, -360, 360),
                            'position' => $decorationIndex,
                        ]
                    );
                    $keptDecorations[] = $decoration->id;
                }

                foreach (array_values($levelData['sections'] ?? []) as $sectionIndex => $sectionData) {
                    $kind = in_array($sectionData['kind'] ?? '', ['seated', 'table', 'standing'], true)
                        ? $sectionData['kind'] : 'seated';

                    $section = $this->upsert(
                        $existingSections, $sectionData['id'] ?? null,
                        fn () => new SeatingSection($ownerAttrs),
                        [
                            'seating_level_id' => $level->id,
                            'name' => $this->str($sectionData['name'] ?? '', 100) ?: __('messages.seating_section'),
                            'color' => $this->color($sectionData['color'] ?? null),
                            'kind' => $kind,
                            'capacity' => $kind === 'standing' ? $this->int($sectionData['capacity'] ?? 0, 0, 65535) : null,
                            'band' => $this->str($sectionData['band'] ?? null, 100),
                            'accessibility_only' => (bool) ($sectionData['accessibility_only'] ?? false),
                            'x' => $this->int($sectionData['x'] ?? 0, -20000, 20000),
                            'y' => $this->int($sectionData['y'] ?? 0, -20000, 20000),
                            'rotation' => $this->int($sectionData['rotation'] ?? 0, -360, 360),
                            'position' => $sectionIndex,
                            'is_deleted' => false,
                        ]
                    );
                    $keptSections[] = $section->id;

                    $tableIds = [];
                    foreach (array_values($sectionData['tables'] ?? []) as $tableData) {
                        $table = $this->upsert(
                            $existingTables, $tableData['id'] ?? null,
                            fn () => new SeatingTable,
                            [
                                'seating_section_id' => $section->id,
                                'label' => $this->str($tableData['label'] ?? '', 50) ?: '?',
                                'shape' => ($tableData['shape'] ?? 'round') === 'rect' ? 'rect' : 'round',
                                'seat_count' => $this->int($tableData['seat_count'] ?? 0, 0, 64),
                                'booking_mode' => in_array($tableData['booking_mode'] ?? '', ['seat', 'whole', 'either'], true)
                                    ? $tableData['booking_mode'] : 'seat',
                                'x' => $this->int($tableData['x'] ?? 0, -20000, 20000),
                                'y' => $this->int($tableData['y'] ?? 0, -20000, 20000),
                                'rotation' => $this->int($tableData['rotation'] ?? 0, -360, 360),
                                'width' => $this->int($tableData['width'] ?? 120, 10, 5000),
                                'height' => $this->int($tableData['height'] ?? 120, 10, 5000),
                            ]
                        );
                        $keptTables[] = $table->id;
                        // The client references a table by whatever id it sent, which for a new
                        // table is a temporary one - map it to the real row for the seats below.
                        $tableIds[(string) ($tableData['id'] ?? '')] = $table->id;
                    }

                    foreach (array_values($sectionData['seats'] ?? []) as $seatIndex => $seatData) {
                        $rawTableId = (string) ($seatData['table_id'] ?? '');

                        $seat = $this->upsert(
                            $existingSeats, $seatData['id'] ?? null,
                            fn () => new SeatingSeat($ownerAttrs),
                            [
                                'seating_section_id' => $section->id,
                                'seating_table_id' => $tableIds[$rawTableId] ?? null,
                                'row_label' => $this->str($seatData['row_label'] ?? null, 10),
                                'row_position' => $this->int($seatData['row_position'] ?? 0, 0, 65535),
                                'seat_label' => $this->str($seatData['seat_label'] ?? null, 10),
                                'x' => $this->int($seatData['x'] ?? 0, -20000, 20000),
                                'y' => $this->int($seatData['y'] ?? 0, -20000, 20000),
                                'kind' => in_array($seatData['kind'] ?? '', ['standard', 'wheelchair', 'companion', 'restricted_view'], true)
                                    ? $seatData['kind'] : 'standard',
                                'aisle_after' => (bool) ($seatData['aisle_after'] ?? false),
                                // The client's own number when it sent one: generateRows() sets the
                                // seat's index WITHIN its row and generateTables() the chair's index
                                // at its table. Overwriting both with the flat array index left a
                                // table's chairs numbered 40, 41, 42 - ordering still worked, but
                                // the orphan rule's notion of adjacency within a row, and any
                                // "seat 3 at table 4" reference, lose the only value that means it.
                                'position' => isset($seatData['position'])
                                    ? $this->int($seatData['position'], 0, 65535)
                                    : $seatIndex,
                            ]
                        );
                        $keptSeats[] = $seat->id;
                    }
                }
            }

            $this->removeMissing($existingSeats, $existingSections, $existingTables, $existingLevels,
                $keptSeats, $keptSections, $keptTables, $keptLevels,
                $decorationsDeclared ? $existingDecorations : null, $keptDecorations);
        });
    }

    /**
     * Update the row the id names, but ONLY if it is one of the owner's own. An id the owner does
     * not have is treated as a brand-new row rather than adopted, so a hand-edited payload cannot
     * reach across into another schedule's plan.
     */
    private function upsert($existing, $id, callable $make, array $attributes)
    {
        $model = ($id !== null && $existing->has($id)) ? $existing->get($id) : $make();
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    private function removeMissing($seats, $sections, $tables, $levels, array $keptSeats, array $keptSections, array $keptTables, array $keptLevels, $decorations = null, array $keptDecorations = []): void
    {
        $droppedSeats = $seats->keys()->diff($keptSeats);

        // The customer's "you cannot delete seats or rows that already have bookings" rule. It
        // lives here, not in the UI, because the per-date editor and the box office post to this
        // same method - and removing a section is caught by the same check, since its seats drop
        // out of the payload with it.
        $dropped = $seats->only($droppedSeats->all());

        $sold = $dropped->firstWhere('status', 'sold');
        if ($sold) {
            throw new BusinessException(__('messages.seating_cannot_remove_sold_seat', [
                'seat' => $sold->fullLabel() ?: $sold->id,
            ]));
        }

        // A live cart hold is somebody mid-checkout. Deleting their seat leaves them to fail the
        // books-balance check at the payment step for something they did nothing to cause.
        $held = $dropped->first(fn ($seat) => $seat->status === 'held'
            && $seat->hold_kind === 'cart'
            && $seat->hold_expires_at !== null
            && $seat->hold_expires_at->isFuture());

        if ($held) {
            throw new BusinessException(__('messages.seating_cannot_remove_held_seat', [
                'seat' => $held->fullLabel() ?: $held->id,
            ]));
        }

        SeatingSeat::whereIn('id', $droppedSeats)->delete();
        SeatingTable::whereIn('id', $tables->keys()->diff($keptTables))->delete();
        // Sections are soft-deleted: a removed one may still be named by a sold seat's history.
        SeatingSection::whereIn('id', $sections->keys()->diff($keptSections))->update(['is_deleted' => true]);
        SeatingLevel::whereIn('id', $levels->keys()->diff($keptLevels))->delete();

        // Hard-deleted, unlike a section: nothing else ever references a decoration, so there is no
        // sold seat whose history could still name one.
        if ($decorations) {
            SeatingDecoration::whereIn('id', $decorations->keys()->diff($keptDecorations))->delete();
        }
    }

    private function str($value, int $max): ?string
    {
        $value = is_scalar($value) ? trim((string) $value) : '';

        return $value === '' ? null : mb_substr($value, 0, $max);
    }

    private function int($value, int $min, int $max): int
    {
        return max($min, min($max, (int) $value));
    }

    private function color($value): string
    {
        $value = is_string($value) ? trim($value) : '';

        return preg_match('/^#[0-9a-fA-F]{6}([0-9a-fA-F]{2})?$/', $value) ? $value : '#4E81FA';
    }

    /**
     * Cheap size check before any of the above runs, so a hostile payload is rejected rather
     * than half-written and rolled back.
     */
    public function assertWithinLimits(array $data): void
    {
        $levels = $data['levels'] ?? [];
        $sections = 0;
        $seats = 0;
        $tables = 0;
        $decorations = 0;

        foreach ($levels as $level) {
            $decorations += count($level['decorations'] ?? []);

            foreach ($level['sections'] ?? [] as $section) {
                $sections++;
                $seats += count($section['seats'] ?? []);
                $tables += count($section['tables'] ?? []);
            }
        }

        // Name the limit actually hit. Reporting the seat cap for a plan that has too many LEVELS
        // sends the organizer off deleting rows that were never the problem.
        $breached = match (true) {
            count($levels) > self::MAX_LEVELS => ['messages.seating_too_many_levels', self::MAX_LEVELS],
            $sections > self::MAX_SECTIONS => ['messages.seating_too_many_sections', self::MAX_SECTIONS],
            $tables > self::MAX_TABLES => ['messages.seating_too_many_tables', self::MAX_TABLES],
            $decorations > self::MAX_DECORATIONS => ['messages.seating_too_many_decorations', self::MAX_DECORATIONS],
            $seats > self::MAX_SEATS => ['messages.seating_plan_too_large', self::MAX_SEATS],
            default => null,
        };

        if ($breached) {
            throw new BusinessException(__($breached[0], ['max' => $breached[1]]));
        }
    }
}
