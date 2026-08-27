<?php

namespace App\Models;

use App\Traits\SeatingOwnable;
use Illuminate\Database\Eloquent\Model;

/**
 * A stage or a text label drawn on one level of a seat map.
 *
 * Never sellable and never interactive: the picker, the box office console and the printed report
 * all draw these with pointer-events disabled, so a decoration can never compete with a seat for a
 * press. The designer is the only surface that lets you move one.
 */
class SeatingDecoration extends Model
{
    use SeatingOwnable;

    protected $fillable = [
        'seating_level_id',
        'seating_plan_id',
        'event_seating_map_id',
        'kind',
        'label',
        'x',
        'y',
        'width',
        'height',
        'rotation',
        'position',
    ];

    protected $casts = [
        'x' => 'integer',
        'y' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'rotation' => 'integer',
        'position' => 'integer',
    ];

    public function level()
    {
        return $this->belongsTo(SeatingLevel::class, 'seating_level_id');
    }

    /** The four corners in level space, so a rotated stage still frames correctly. */
    public function bounds(): array
    {
        $deg = (int) $this->rotation;
        $w = (int) $this->width;
        $h = (int) $this->height;

        if (! $deg) {
            return [$this->x, $this->y, $this->x + $w, $this->y + $h];
        }

        $r = deg2rad($deg);
        $cos = cos($r);
        $sin = sin($r);
        $xs = [];
        $ys = [];

        foreach ([[0, 0], [$w, 0], [$w, $h], [0, $h]] as [$px, $py]) {
            $xs[] = $this->x + $px * $cos - $py * $sin;
            $ys[] = $this->y + $px * $sin + $py * $cos;
        }

        return [min($xs), min($ys), max($xs), max($ys)];
    }
}
