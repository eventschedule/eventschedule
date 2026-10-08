<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A blocked account: who blocked it, the operator's note and the schedules the block took down.
 * Written and removed only by App\Services\AccountBlockService. See the migration.
 */
class AccountBlock extends Model
{
    protected $guarded = [];

    protected $casts = [
        'role_ids' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function entries()
    {
        return $this->hasMany(BlocklistEntry::class);
    }
}
