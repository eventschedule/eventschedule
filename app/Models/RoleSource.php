<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A talent/venue schedule whose events a curator pulls in automatically.
 *
 * The rows this produces on event_role are marked is_auto_sourced so they can be
 * removed again without touching anything the curator added by hand. See
 * App\Services\CuratorSourceService.
 */
class RoleSource extends Model
{
    protected $fillable = [
        'role_id',
        'source_role_id',
        'group_id',
    ];

    /** The curator. */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /** The talent/venue being followed. */
    public function sourceRole()
    {
        return $this->belongsTo(Role::class, 'source_role_id');
    }

    /** Optional sub-schedule on the curator to file this source's events under. */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
