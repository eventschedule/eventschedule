<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What the operator did about a schedule /admin/private-calendars listed. See the migration
 * (2026_10_18_000003).
 */
class CalendarExposureReview extends Model
{
    protected $guarded = [];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'hidden_at' => 'datetime',
        'paused_at' => 'datetime',
        'emailed_at' => 'datetime',
        'emailed_kinds' => 'array',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function hider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hidden_by');
    }

    public function pauser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paused_by');
    }

    public function emailer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emailed_by');
    }
}
