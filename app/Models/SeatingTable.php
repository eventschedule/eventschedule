<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A round or rectangular dining table inside a kind=table section.
 *
 * Deliberately not a section: a room of twenty tables is ONE pricing band, so modelling
 * each table as a section would create twenty bands. booking_mode decides whether a
 * guest may take a single chair, must take the whole table, or may do either.
 */
class SeatingTable extends Model
{
    protected $fillable = [
        'seating_section_id',
        'label',
        'shape',
        'seat_count',
        'booking_mode',
        'x',
        'y',
        'rotation',
        'width',
        'height',
    ];

    protected $casts = [
        'seat_count' => 'integer',
        'x' => 'integer',
        'y' => 'integer',
        'rotation' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function section()
    {
        return $this->belongsTo(SeatingSection::class, 'seating_section_id');
    }

    public function seats()
    {
        return $this->hasMany(SeatingSeat::class, 'seating_table_id')->orderBy('position');
    }

    public function requiresWholeTable(): bool
    {
        return $this->booking_mode === 'whole';
    }

    public function allowsSingleSeat(): bool
    {
        return in_array($this->booking_mode, ['seat', 'either'], true);
    }
}
