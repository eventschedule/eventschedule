<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An event a calendar sync (or the operator) is keeping off the public page, and why. Written
 * only through App\Services\CalendarArrival. See the migration (2026_10_18_000001).
 */
class CalendarImportHold extends Model
{
    protected $guarded = [];

    protected $casts = [
        'first_pull' => 'boolean',
        'decided_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** Still waiting for the owner. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('decided_at');
    }

    public function isOpen(): bool
    {
        return $this->decided_at === null;
    }
}
