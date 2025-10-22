<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaAssetVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_asset_id',
        'disk',
        'path',
        'label',
        'width',
        'height',
        'size',
        'crop_meta',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'size' => 'integer',
        'crop_meta' => 'array',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }

    public function getUrlAttribute(): string
    {
        return storage_asset_url($this->path);
    }
}
