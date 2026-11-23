<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A named part of a vendor map ("Hangar", "Orchard"), drawn as a coloured outline. */
class VendorMapArea extends Model
{
    protected $fillable = ['vendor_map_id', 'name', 'color', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];
}
