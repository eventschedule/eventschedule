<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class MediaAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'size',
        'width',
        'height',
        'uploaded_by',
        'folder',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (MediaAsset $asset): void {
            if (empty($asset->uuid)) {
                $asset->uuid = (string) Str::uuid();
            }
        });

        static::deleting(function (MediaAsset $asset): void {
            $disk = $asset->disk ?: storage_public_disk();

            if ($asset->path) {
                try {
                    Storage::disk($disk)->delete($asset->path);
                } catch (Throwable $exception) {
                    report($exception);
                }
            }

            $asset->variants()->get()->each(function (MediaAssetVariant $variant): void {
                $variant->delete();
            });
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MediaAssetVariant::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(MediaTag::class, 'media_asset_tag')->withTimestamps();
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaAssetUsage::class);
    }

    public function getUrlAttribute(): string
    {
        return storage_asset_url($this->path);
    }
}
