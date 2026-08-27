<?php

namespace App\Models;

use App\Traits\SeatingOwnable;
use Illuminate\Database\Eloquent\Model;

/**
 * A pricing band and colour block within a level.
 *
 * kind drives everything downstream:
 *   seated   - has seats, sold per seat
 *   table    - has tables holding seats, sold per seat or per table
 *   standing - NO seats, just a capacity; maps to an ordinary quantity Ticket and rides
 *              the existing stock path untouched. This is how one event sells allocated
 *              seats alongside a standing area.
 *
 * ticket_id (the price band) is only ever set on a SNAPSHOT section - tickets belong to
 * an event and templates do not. band is the template-side label used to auto-match a
 * section to a ticket type of the same name when a plan is attached to an event.
 */
class SeatingSection extends Model
{
    use SeatingOwnable;

    protected $fillable = [
        'seating_level_id',
        'seating_plan_id',
        'event_seating_map_id',
        'name',
        'color',
        'kind',
        'capacity',
        'band',
        'ticket_id',
        // RESERVED, not wired. Whole-table PRICING - one unit for the table rather than N x the
        // band price - is a real cabaret ask, but it changes the arithmetic the books-balance guard
        // in TicketController::claimSeatsForLeg() depends on, so it is a feature in its own right
        // rather than a column to quietly start reading. Nothing sets it today.
        'table_ticket_id',
        'accessibility_only',
        'x',
        'y',
        'rotation',
        'position',
        // RESERVED, not wired. The migration describes it as a polygon outline for stadium tiers
        // and in-the-round, and nothing writes or draws it: the designer has no polygon tool and
        // every renderer derives a section's box from its seats. Kept because the column exists on
        // production and the shape is the right one for that feature; do not treat its presence as
        // evidence the feature works.
        'shape',
        'is_deleted',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'accessibility_only' => 'boolean',
        'x' => 'integer',
        'y' => 'integer',
        'rotation' => 'integer',
        'position' => 'integer',
        'shape' => 'array',
        'is_deleted' => 'boolean',
    ];

    public function level()
    {
        return $this->belongsTo(SeatingLevel::class, 'seating_level_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * A point in this section's own coordinates, expressed in canvas space.
     *
     * The PHP twin of toCanvasFrame() in resources/js/seat-map-geometry.js, for the printed report,
     * which resolves absolute seat positions server-side instead of drawing a transformed group.
     * Without it a rotated section printed straight while the same room rendered angled in the
     * designer, so the front-of-house sheet did not match the map staff had drawn.
     *
     * @return array{0: float, 1: float}
     */
    public function canvasPoint($x, $y): array
    {
        $deg = (int) $this->rotation;

        if (! $deg) {
            return [$this->x + $x, $this->y + $y];
        }

        $r = deg2rad($deg);
        $cos = cos($r);
        $sin = sin($r);

        return [
            $this->x + $x * $cos - $y * $sin,
            $this->y + $x * $sin + $y * $cos,
        ];
    }

    public function tableTicket()
    {
        return $this->belongsTo(Ticket::class, 'table_ticket_id');
    }

    public function tables()
    {
        return $this->hasMany(SeatingTable::class, 'seating_section_id');
    }

    public function seats()
    {
        // row_position, not row_label: a lexicographic sort puts row AA before row B.
        return $this->hasMany(SeatingSeat::class, 'seating_section_id')
            ->orderBy('row_position')
            ->orderBy('position');
    }

    public function isSeated(): bool
    {
        return $this->kind === 'seated';
    }

    public function isTableSection(): bool
    {
        return $this->kind === 'table';
    }

    /**
     * Standing sections hold no seat rows at all - their inventory is the ticket's
     * quantity, exactly as it was before allocated seating existed.
     */
    public function isStanding(): bool
    {
        return $this->kind === 'standing';
    }

    public function isAllocated(): bool
    {
        return ! $this->isStanding();
    }
}
