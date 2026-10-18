<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * How a schedule's standing calendar syncs treat what they read in. Written only through
 * App\Services\CalendarArrival; no row reads as "arrive as drafts". See the migration
 * (2026_10_18_000000) for every column.
 */
class CalendarSyncSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'google_publishes' => 'boolean',
        'microsoft_publishes' => 'boolean',
        'caldav_publishes' => 'boolean',
        'google_publish_confirmed_at' => 'datetime',
        'microsoft_publish_confirmed_at' => 'datetime',
        'caldav_publish_confirmed_at' => 'datetime',
        'google_reread_at' => 'datetime',
        'microsoft_reread_at' => 'datetime',
        'caldav_reread_at' => 'datetime',
        'google_last_pull' => 'array',
        'microsoft_last_pull' => 'array',
        'caldav_last_pull' => 'array',
        'google_first_pull' => 'array',
        'microsoft_first_pull' => 'array',
        'caldav_first_pull' => 'array',
        'google_stopped_at' => 'datetime',
        'microsoft_stopped_at' => 'datetime',
        'caldav_stopped_at' => 'datetime',
        'taken_off_notified_at' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
