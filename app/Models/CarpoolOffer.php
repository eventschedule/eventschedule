<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarpoolOffer extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'role_id',
        'event_date',
        'direction',
        'city',
        'departure_time',
        'meeting_point',
        'total_spots',
        'note',
        'status',
    ];

    protected $casts = [
        'event_date' => 'date',
        'total_spots' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function requests()
    {
        return $this->hasMany(CarpoolRequest::class);
    }

    public function approvedRequests()
    {
        return $this->hasMany(CarpoolRequest::class)->where('status', 'approved');
    }

    public function pendingRequests()
    {
        return $this->hasMany(CarpoolRequest::class)->where('status', 'pending');
    }

    public function reviews()
    {
        return $this->hasMany(CarpoolReview::class);
    }

    public function reports()
    {
        return $this->hasMany(CarpoolReport::class);
    }

    public function availableSpots()
    {
        return $this->total_spots - $this->approvedRequests->count();
    }

    public function isFull()
    {
        return $this->availableSpots() <= 0;
    }

    public function directionLabel()
    {
        return match ($this->direction) {
            'to_event' => __('messages.carpool_to_event'),
            'from_event' => __('messages.carpool_from_event'),
            'round_trip' => __('messages.carpool_round_trip'),
            default => $this->direction,
        };
    }
}
