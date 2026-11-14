<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A site that may be serving a copy of the marketing pages. See App\Services\MarketingCopyWatch. */
class MarketingCopyHost extends Model
{
    protected $guarded = [];

    protected $casts = [
        'markers' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'checked_at' => 'datetime',
        'copy_found_at' => 'datetime',
        'decided_at' => 'datetime',
    ];
}
