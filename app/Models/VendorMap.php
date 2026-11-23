<?php

namespace App\Models;

use App\Services\VendorMaps\VendorMapKinds;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A schedule's vendor map: the plan of a market, fair or expo, whose spots vendors take date by
 * date. One per schedule; the row is the switch. See the migration for why vendors are not
 * event participants.
 */
class VendorMap extends Model
{
    protected $fillable = ['role_id', 'enabled', 'kind', 'event_id', 'width', 'height', 'claims_open', 'here_x', 'here_y'];

    protected $casts = [
        'enabled' => 'boolean',
        'claims_open' => 'boolean',
        'background_public' => 'boolean',
        'width' => 'integer',
        'height' => 'integer',
        'revision' => 'integer',
        'here_x' => 'integer',
        'here_y' => 'integer',
        'cancelled_days' => 'array',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function spots()
    {
        return $this->hasMany(VendorMapSpot::class)->whereNull('retired_at');
    }

    public function allSpots()
    {
        return $this->hasMany(VendorMapSpot::class);
    }

    public function areas()
    {
        return $this->hasMany(VendorMapArea::class)->orderBy('sort_order')->orderBy('id');
    }

    public function landmarks()
    {
        return $this->hasMany(VendorMapLandmark::class);
    }

    public function occupants()
    {
        return $this->hasMany(VendorMapOccupant::class);
    }

    public function drafts()
    {
        return $this->hasMany(VendorMapDraft::class);
    }

    public function kind(): string
    {
        return VendorMapKinds::normalize($this->kind);
    }

    /** The vendor link's token, made the first time it is asked for. */
    public function ensureClaimToken(): string
    {
        if (! $this->claim_token) {
            $this->forceFill(['claim_token' => Str::random(32)])->save();
        }

        return $this->claim_token;
    }

    public function rotateClaimToken(): string
    {
        $this->forceFill(['claim_token' => Str::random(32)])->save();

        return $this->claim_token;
    }

    /**
     * Vendors can join through the link: the map is on, the link is open, the schedule's plan
     * includes it, and it serves an event that is not a draft, cancelled or over.
     */
    public function takesClaims(): bool
    {
        return $this->enabled
            && $this->claims_open
            && $this->claim_token
            && $this->role
            && $this->role->isPro()
            && $this->eventTakesVendors();
    }

    public function eventTakesVendors(): bool
    {
        $event = $this->event;

        if (! $event || $event->is_draft || $event->is_internal || $event->is_cancelled) {
            return false;
        }

        return $event->nextOccurrenceFrom() !== null;
    }
}
