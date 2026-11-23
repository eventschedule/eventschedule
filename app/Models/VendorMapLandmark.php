<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Something on a vendor map that is not a spot: the entrance, restrooms, parking, a stage, a label. */
class VendorMapLandmark extends Model
{
    public const KINDS = ['entrance', 'restroom', 'parking', 'stage', 'text'];

    protected $fillable = ['vendor_map_id', 'kind', 'label', 'x', 'y', 'w', 'h', 'rotation'];

    protected $casts = [
        'x' => 'integer',
        'y' => 'integer',
        'w' => 'integer',
        'h' => 'integer',
        'rotation' => 'integer',
    ];
}
