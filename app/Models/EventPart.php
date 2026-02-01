<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPart extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'description',
        'start_time',
        'end_time',
        'sort_order',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function videos()
    {
        return $this->hasMany(EventVideo::class);
    }

    public function approvedVideos()
    {
        return $this->hasMany(EventVideo::class)->where('is_approved', true);
    }

    public function comments()
    {
        return $this->hasMany(EventComment::class);
    }

    public function approvedComments()
    {
        return $this->hasMany(EventComment::class)->where('is_approved', true);
    }
}
