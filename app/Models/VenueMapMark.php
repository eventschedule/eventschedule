<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A schedule owner's decision about one venue on their venue map: off the map, or a position set
 * by hand. See the migration, and App\Services\VenueMap::venues(), the only reader.
 */
class VenueMapMark extends Model
{
    protected $guarded = [];

    protected $casts = [
        'hidden' => 'boolean',
        'lat' => 'float',
        'lon' => 'float',
    ];
}
