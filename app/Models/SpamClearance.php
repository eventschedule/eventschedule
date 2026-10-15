<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An account an operator marked not spam on /admin/blocked. Written and removed only by
 * App\Services\SpamWatch. See the migration.
 */
class SpamClearance extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clearer()
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }
}
