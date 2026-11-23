<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Photos uploaded for the AI map and what the AI made of them. Kept on the server so a phone that
 * went to sleep comes back to "Your map is ready"; pruned after a day with its photos
 * (app:prune-vendor-map-drafts), because they show people at a market.
 */
class VendorMapDraft extends Model
{
    protected $fillable = ['vendor_map_id', 'user_id', 'images', 'result', 'status'];

    protected $casts = [
        'images' => 'array',
        'result' => 'array',
    ];

    public function map()
    {
        return $this->belongsTo(VendorMap::class, 'vendor_map_id');
    }
}
