<?php

namespace App\Models;

use App\Services\VendorMaps\VendorMapImages;
use Illuminate\Database\Eloquent\Model;

/**
 * Who is on a spot: for the whole season (event_date '*') or one date. `kind` absent means the
 * season's vendor is not there that date. A row with no vendor_role_id is a name only.
 *
 * Read through VendorMapOccupancy, never directly: a season row, an absent row and a dated
 * row have to be combined, and only that class does it.
 */
class VendorMapOccupant extends Model
{
    public const SEASON = '*';

    protected $fillable = [
        'vendor_map_id', 'spot_id', 'event_date', 'kind', 'status', 'vendor_role_id', 'placeholder_name',
        'sells', 'categories', 'phone', 'photo_path', 'photo_thumb_path', 'user_id', 'note',
    ];

    protected $casts = [
        'categories' => 'array',
        'decided_at' => 'datetime',
        'notified_at' => 'datetime',
        'vendor_notified_at' => 'datetime',
    ];

    /**
     * A row's photos go with it (the private original and the public thumbnail), unless another
     * row still shows them (a one-day move copies them).
     */
    protected static function booted(): void
    {
        static::deleted(function (VendorMapOccupant $row) {
            foreach (['photo_path', 'photo_thumb_path'] as $column) {
                $path = $row->getAttribute($column);
                if (! $path || static::where($column, $path)->exists()) {
                    continue;
                }
                $column === 'photo_path'
                    ? VendorMapImages::deletePrivate($path)
                    : VendorMapImages::deletePublic($path);
            }
        });
    }

    public function map()
    {
        return $this->belongsTo(VendorMap::class, 'vendor_map_id');
    }

    public function spot()
    {
        return $this->belongsTo(VendorMapSpot::class, 'spot_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Role::class, 'vendor_role_id');
    }

    public function isSeason(): bool
    {
        return $this->event_date === self::SEASON;
    }

    public function isPlaceholder(): bool
    {
        return $this->vendor_role_id === null;
    }

    /** The name shoppers read: the vendor page's name, or the name typed on the spot. */
    public function displayName(): string
    {
        if ($this->vendor_role_id && $this->vendor) {
            return (string) $this->vendor->translatedName();
        }

        return (string) $this->placeholder_name;
    }

    /**
     * A short hash of what this row says. The "Not coming" link carries it, so a link from a
     * mail about a stall the vendor no longer has stops working instead of acting on a stranger.
     */
    public function stateHash(): string
    {
        return substr(hash_hmac('sha256', implode('|', [$this->id, $this->spot_id, $this->vendor_role_id, $this->status, $this->kind]), (string) config('app.key')), 0, 16);
    }
}
