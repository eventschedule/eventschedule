<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MicrosoftCalendarSync extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'role_id',
        'microsoft_event_id',
        'microsoft_calendar_id',
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
