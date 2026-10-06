<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One page view on /admin/realtime, kept about an hour. See the create_realtime_hits_table
 * migration for what it may and may not hold.
 *
 * Writes go through RealtimeBeaconController and reads through RealtimeDashboard, both with the
 * query builder; this model exists for the occasional Eloquent-shaped delete (account deletion,
 * tests).
 */
class RealtimeHit extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'consented' => 'boolean',
            'is_admin' => 'boolean',
            'is_demo' => 'boolean',
            'is_team' => 'boolean',
            'owner_visible' => 'boolean',
            'is_entrance' => 'boolean',
            'started_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'engaged_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }
}
