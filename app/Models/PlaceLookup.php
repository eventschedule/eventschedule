<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One address the venue map asked its address search about, and the answer. See the migration
 * (2026_10_07_000004) and App\Services\PlaceLookupService, the only writer.
 */
class PlaceLookup extends Model
{
    protected $guarded = [];

    protected $casts = [
        'lat' => 'float',
        'lon' => 'float',
        'attempts' => 'integer',
        'try_after' => 'datetime',
        'looked_up_at' => 'datetime',
    ];
}
