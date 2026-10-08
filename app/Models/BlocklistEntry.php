<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One thing a new account is refused for: an email address, an email domain or a network
 * address. Read and written through App\Services\Blocklist. See the migration.
 */
class BlocklistEntry extends Model
{
    protected $guarded = [];

    protected $casts = [
        'refused_count' => 'integer',
        'last_refused_at' => 'datetime',
    ];

    public function accountBlock()
    {
        return $this->belongsTo(AccountBlock::class);
    }
}
