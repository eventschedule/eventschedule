<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The owner's switch "Team members pick from these presets", one row per schedule that has ever set
 * it; no row reads as off (Role::ticketPresetSetting() has a default). Not a column on `roles`, which
 * is at MySQL's row limit (see 2026_10_16_000001). Read through App\Services\TicketPresets.
 */
class TicketPresetSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'team_uses_presets' => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
