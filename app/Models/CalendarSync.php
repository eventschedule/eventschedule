<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarSync extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'role_id',
        'google_event_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
