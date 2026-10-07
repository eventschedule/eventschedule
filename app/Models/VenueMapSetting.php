<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A schedule's venue map settings. One row per schedule that has ever switched its map on; no row
 * reads as off (Role::venueMapSetting() has a default). See the migration (2026_10_07_000005) for
 * why this is not three columns on `roles`.
 */
class VenueMapSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
        'starts_open' => 'boolean',
        'ready_at' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
