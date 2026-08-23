<?php

namespace App\Models;

use App\Traits\SeatingOwnable;
use Illuminate\Database\Eloquent\Model;

/**
 * A floor or tier with its own drawing canvas (Stalls, Circle, Balcony). Belongs to
 * exactly one of a SeatingPlan or an EventSeatingMap - see SeatingOwnable.
 */
class SeatingLevel extends Model
{
    use SeatingOwnable;

    protected $fillable = [
        'seating_plan_id',
        'event_seating_map_id',
        'name',
        'position',
        'width',
        'height',
    ];

    protected $casts = [
        'position' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function sections()
    {
        return $this->hasMany(SeatingSection::class)->where('is_deleted', false)->orderBy('position');
    }
}
