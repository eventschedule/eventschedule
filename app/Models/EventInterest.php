<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Somebody who asked to hear about one event: when its tickets go on sale, and if it moves or is
 * called off. See the migration for why this is not a role_subscribers row.
 *
 * There is no unsubscribed_at and no shared suppression list here, unlike RoleSubscriber. An
 * unsubscribe DELETES the row, which is both simpler and more honest: the relationship is bounded
 * by one event, so once somebody opts out there is nothing left to suppress, and a deletion is a
 * real erasure rather than a retained address on a list. It also means the row cannot be revived by
 * a later import the way a suppressed subscriber can.
 */
class EventInterest extends Model
{
    protected $fillable = [
        'event_id',
        'event_date',
        'email',
        'name',
        'locale',
        'source',
        'confirmed_at',
        'token',
        'confirm_token',
        'tickets_notified_at',
        'reminder_sent_at',
        'ip_address',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'tickets_notified_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    /**
     * Lowercased and trimmed on the way in, so the (event_id, event_date, email) unique index
     * actually catches a resubmission that differs only in case. Same reasoning as
     * RoleSubscriber::setEmailAttribute().
     */
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = strtolower(trim((string) $value));
    }

    /**
     * '' rather than null for an undated event: the unique index has to catch a resubmission, and
     * MySQL treats NULLs as distinct, so a nullable column would let one address take unlimited
     * rows on the same event.
     */
    public function setEventDateAttribute($value): void
    {
        $this->attributes['event_date'] = is_string($value) ? trim($value) : '';
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /** Only confirmed rows are ever mailed, matching AudienceResolver's rule. */
    public function scopeConfirmed($query)
    {
        return $query->whereNotNull('confirmed_at');
    }

    public function isConfirmed(): bool
    {
        return ! is_null($this->confirmed_at);
    }

    public static function newToken(): string
    {
        return Str::random(64);
    }
}
