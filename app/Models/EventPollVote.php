<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPollVote extends Model
{
    protected $fillable = [
        'event_poll_id',
        'user_id',
        'option_index',
    ];

    public function poll()
    {
        return $this->belongsTo(EventPoll::class, 'event_poll_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
