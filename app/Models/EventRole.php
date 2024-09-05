<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EventRole extends Pivot
{
    protected $table = 'event_role';

    protected $fillable = [
        'event_id',
        'role_id',
    ];
}
