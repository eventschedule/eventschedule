<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A schedule a platform admin has hidden from discovery (the "Hide schedule" pill on the homepage
 * rail and /browse). One row per hidden schedule; no row means it is shown. Which events that takes
 * out is Event::constrainFromSchedule(). See the migration (2026_10_18_000004) for why this is not
 * a column on `roles`.
 */
class DiscoveryHiddenSchedule extends Model
{
    protected $guarded = [];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
