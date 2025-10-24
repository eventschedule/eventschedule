<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Throwable;

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

    protected static function booted(): void
    {
        static::deleting(function (MediaAssetVariant $variant): void {
            $disk = $variant->disk ?: storage_public_disk();

            if ($variant->path) {
                try {
                    Storage::disk($disk)->delete($variant->path);
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        });
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }

    public function getUrlAttribute(): string
    {
        return storage_asset_url($this->path);
    }
}
