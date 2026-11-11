<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A promoter's link on a schedule (the Promoters tab, Pro). Read and written through
 * App\Services\PromoterLinks, which also captures `?via=CODE` and stamps the sales it leads to.
 *
 * Archived, never deleted: sales.promoter_link_id keeps pointing at it, and the owner still owes
 * the commission on what it sold.
 */
class PromoterLink extends Model
{
    protected $fillable = ['role_id', 'name', 'code', 'commission_percent'];

    protected $casts = [
        'commission_percent' => 'float',
        'clicks' => 'integer',
        'first_click_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /** Once somebody has followed it, the code is out in the world and may not change. */
    public function codeLocked(): bool
    {
        return $this->first_click_at !== null || $this->clicks > 0;
    }
}
