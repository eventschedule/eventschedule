<?php

namespace App\Models;

use App\Traits\SeatingOwnable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The seat itself - and, on a snapshot, its live state.
 *
 * NAMING: this is NOT sale_tickets.seats. That column is a JSON map of admission slot
 * number to check-in timestamp and carries no location. Seat ASSIGNMENT is this table's
 * sale_id / sale_ticket_id, read through Sale::seatAssignments().
 */
class SeatingSeat extends Model
{
    use SeatingOwnable;

    protected $fillable = [
        'seating_section_id',
        'seating_table_id',
        'seating_plan_id',
        'event_seating_map_id',
        'row_label',
        'row_position',
        'seat_label',
        'x',
        'y',
        'kind',
        'aisle_after',
        'position',
        'source_seat_id',
        'status',
        'hold_kind',
        'hold_note',
        'hold_token',
        'hold_expires_at',
        'sale_id',
        'sale_ticket_id',
        'state_version',
    ];

    protected $casts = [
        'row_position' => 'integer',
        'x' => 'integer',
        'y' => 'integer',
        'aisle_after' => 'boolean',
        'position' => 'integer',
        'hold_expires_at' => 'datetime',
        'state_version' => 'integer',
    ];

    public function section()
    {
        return $this->belongsTo(SeatingSection::class, 'seating_section_id');
    }

    /**
     * Named seatingTable(), not table(): Eloquent's Model already owns a $table property
     * holding the table NAME, so $seat->table would silently return the string
     * "seating_seats" instead of this relation.
     */
    public function seatingTable()
    {
        return $this->belongsTo(SeatingTable::class, 'seating_table_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function saleTicket()
    {
        return $this->belongsTo(SaleTicket::class);
    }

    /**
     * THE availability predicate. Every read, every count and every acquire must go
     * through this scope or its isAvailable() twin - two definitions that drift is how
     * a seat map oversells.
     *
     * A lapsed cart hold IS available. Expiry is evaluated here, at read time, rather
     * than by a sweeper, because selfhost ships QUEUE_CONNECTION=sync and a scheduler
     * that only ticks once a minute; correctness cannot wait on cron. A hold with a NULL
     * hold_expires_at is a box-office or house hold and never lapses.
     */
    public function scopeAvailable(Builder $query, ?Carbon $now = null): Builder
    {
        $now = $now ?: now();

        return $query->where(function (Builder $q) use ($now) {
            $q->where('status', 'available')
                ->orWhere(function (Builder $held) use ($now) {
                    $held->where('status', 'held')
                        ->whereNotNull('hold_expires_at')
                        ->where('hold_expires_at', '<', $now);
                });
        });
    }

    /**
     * Seats whose section is still live.
     *
     * A section is soft-deleted (is_deleted) rather than removed, because a sold seat may still
     * name it. Every count and every availability read must therefore filter on this, or a section
     * removed from one date's map goes on offering its seats - and the template and its own
     * snapshot start reporting different totals, because copyStructure() already skips deleted
     * sections.
     *
     * Note what the designer actually does, which this docblock used to overstate:
     * SeatingStructureService::removeMissing() DELETES a section's seats in the same save that
     * soft-deletes the section, and refuses the save outright if any of them is sold. So through
     * the app a soft-deleted section is left with no seats at all. Seats can still outlive one via
     * BackupService::importSeatingStructure(), which restores sections and seats independently -
     * which is why this scope has to exist regardless.
     */
    public function scopeInLiveSection(Builder $query): Builder
    {
        return $query->whereHas('section', fn (Builder $q) => $q->where('is_deleted', false));
    }

    /**
     * Held by this cart token and not yet lapsed - the seats a buyer may carry into
     * checkout. Kept beside scopeAvailable() so the two predicates stay in step.
     */
    public function scopeHeldByToken(Builder $query, string $token, ?Carbon $now = null): Builder
    {
        $now = $now ?: now();

        return $query->where('status', 'held')
            ->where('hold_token', $token)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('hold_expires_at')->orWhere('hold_expires_at', '>=', $now);
            });
    }

    /**
     * Row-level twin of scopeAvailable(). Must agree with it exactly.
     */
    public function isAvailable(?Carbon $now = null): bool
    {
        if ($this->status === 'available') {
            return true;
        }

        if ($this->status !== 'held') {
            return false;
        }

        return $this->hold_expires_at !== null
            && $this->hold_expires_at->lt($now ?: now());
    }

    public function isSold(): bool
    {
        return $this->status === 'sold';
    }

    /**
     * Blocked by staff: a hold that never lapses (house seats, production holds,
     * accessibility reservations, box-office holds).
     */
    public function isBlocked(): bool
    {
        return $this->status === 'held' && $this->hold_expires_at === null;
    }

    public function isWheelchairSpace(): bool
    {
        return $this->kind === 'wheelchair';
    }

    /**
     * Human seat reference for tickets, emails, the scanner and the box office -
     * "Row C, Seat 14", "Table 4, Seat 2", or just "Table 4" when the table is sold
     * without numbered chairs.
     */
    public function label(): string
    {
        $parts = [];

        // loadMissing rather than a relationLoaded() guard: a table seat has a NULL row_label, so
        // skipping the table when the relation happens to be cold rendered an EMPTY label on a
        // ticket, a confirmation email or the scanner. A lazy query is the cheaper mistake than
        // silently wrong output; list call sites eager-load to keep it off the N+1 path.
        if ($this->seating_table_id) {
            $this->loadMissing('seatingTable');
        }

        if ($this->seating_table_id && $this->seatingTable) {
            $parts[] = $this->seatingTable->label;
        } elseif ($this->row_label) {
            $parts[] = __('messages.seat_row_label', ['row' => $this->row_label]);
        }

        if ($this->seat_label) {
            $parts[] = __('messages.seat_number_label', ['seat' => $this->seat_label]);
        }

        return implode(', ', $parts);
    }

    /**
     * Fully qualified reference including the section, for surfaces where the seat
     * appears without its map around it (confirmation email, CSV export, scanner).
     */
    public function fullLabel(): string
    {
        $this->loadMissing('section');
        $section = $this->section?->name;
        $label = $this->label();

        return $section ? trim($section.($label ? ', '.$label : '')) : $label;
    }
}
