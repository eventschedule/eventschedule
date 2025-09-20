<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EventRole extends Pivot
{
    protected $table = 'event_role';

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'role_id',
        'is_accepted',
        'group_id',
        'google_event_id',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
