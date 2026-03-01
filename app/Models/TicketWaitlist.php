<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketWaitlist extends Model
{
    protected $fillable = [
        'event_id',
        'event_date',
        'name',
        'email',
        'subdomain',
        'status',
        'locale',
        'notified_at',
        'expires_at',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeForEventDate($query, $eventId, $date)
    {
        return $query->where('event_id', $eventId)->where('event_date', $date);
    }
}
