<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'start_time',
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
}