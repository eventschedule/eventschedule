<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One thing a new account is refused for: an email address, an email domain, a network address
 * or a country, until expires_at when it has one. Read and written through
 * App\Services\Blocklist. See the migrations.
 */
class BlocklistEntry extends Model
{
    protected $guarded = [];

    protected $casts = [
        'refused_count' => 'integer',
        'last_refused_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Set by Blocklist::add() for an entry that was already there: 'renewed' when it had ended
     * and starts over, 'extended' when it was running and now lasts longer, 'kept' when a block
     * asked for longer than an entry the operator typed in and it kept its own end. Null otherwise.
     */
    public ?string $renewal = null;

    public function accountBlock()
    {
        return $this->belongsTo(AccountBlock::class);
    }

    /** The entries that still refuse: no end, or an end still to come. */
    public function scopeActive($query)
    {
        return $query->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function hasEnded(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
