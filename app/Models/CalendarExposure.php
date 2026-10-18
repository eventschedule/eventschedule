<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An event a calendar import published that looks like somebody's private calendar. Written only
 * by App\Services\CalendarExposureWatch. See the migration (2026_10_18_000002).
 */
class CalendarExposure extends Model
{
    protected $guarded = [];

    protected $casts = [
        'kinds' => 'array',
        'matched_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
