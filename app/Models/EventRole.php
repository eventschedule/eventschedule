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
    ];
}
