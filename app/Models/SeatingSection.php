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
        'table_ticket_id',
        'accessibility_only',
        'x',
        'y',
        'rotation',
        'position',
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
