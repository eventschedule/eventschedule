<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One stall, table or booth on a vendor map. Never deleted while a vendor has stood on it:
 * retired instead (VendorMapStructure), so its id stays put and a past date still resolves.
 */
class VendorMapSpot extends Model
{
    protected $fillable = ['vendor_map_id', 'label', 'area_id', 'x', 'y', 'w', 'h', 'rotation', 'shape', 'attrs'];

    protected $casts = [
        'x' => 'integer',
        'y' => 'integer',
        'w' => 'integer',
        'h' => 'integer',
        'rotation' => 'integer',
        'shape' => 'array',
        'attrs' => 'array',
        'retired_at' => 'datetime',
    ];

    public function map()
    {
        return $this->belongsTo(VendorMap::class, 'vendor_map_id');
    }

    public function area()
    {
        return $this->belongsTo(VendorMapArea::class, 'area_id');
    }
}
