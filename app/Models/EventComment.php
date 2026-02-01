<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventComment extends Model
{
    protected $fillable = [
        'event_id',
        'event_part_id',
        'event_date',
        'user_id',
        'comment',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function eventPart()
    {
        return $this->belongsTo(EventPart::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
