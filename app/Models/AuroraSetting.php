<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A schedule's Aurora background: its palette, arrangement and motion. One row per schedule that
 * has saved one; no row reads as Aurora from the accent (Role::auroraSetting() has a default).
 * See App\Utils\Aurora, and the migration (2026_10_22_000001) for why this is not on `roles`.
 */
class AuroraSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'seed' => 'integer',
        'motion' => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
