<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    protected $fillable = [
        'visibility',
        'starts_at',
        'duration',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    
    public function venue()
    {
        return $this->belongsTo(Role::class, 'venue_id');
    }

    public function getStartTimeAttribute($value)
    {
        if (! $value) {
            return '';
        }

        $timezone = auth()->user()->timezone;

        return Carbon::createFromFormat('Y-m-d H:i', $value, 'UTC')
                    ->setTimezone($timezone)
                    ->format('Y-m-d H:i');
    }
}