<?php

namespace App\Models;

use App\Models\Event;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class MediaAssetUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_asset_id',
        'media_asset_variant_id',
        'usable_type',
        'usable_id',
        'context',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(MediaAssetVariant::class, 'media_asset_variant_id');
    }

    public function usable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function recordUsage(Model $model, string $context, MediaAsset $asset, ?MediaAssetVariant $variant = null): void
    {
        static::query()
            ->where('usable_type', $model::class)
            ->where('usable_id', $model->getKey())
            ->where('context', $context)
            ->delete();

        static::create([
            'media_asset_id' => $asset->getKey(),
            'media_asset_variant_id' => $variant?->getKey(),
            'usable_type' => $model::class,
            'usable_id' => $model->getKey(),
            'context' => $context,
        ]);
    }

    public static function clearUsage(Model $model, string $context): void
    {
        static::query()
            ->where('usable_type', $model::class)
            ->where('usable_id', $model->getKey())
            ->where('context', $context)
            ->delete();
    }

    public static function hasUsage(Model $model, string $context): bool
    {
        return static::query()
            ->where('usable_type', $model::class)
            ->where('usable_id', $model->getKey())
            ->where('context', $context)
            ->exists();
    }

    public static function clearForAsset(MediaAsset $asset): Collection
    {
        $asset->loadMissing('usages.usable');

        return $asset->usages->filter(function (MediaAssetUsage $usage): bool {
            return ! $usage->detachFromUsable();
        });
    }

    public function detachFromUsable(): bool
    {
        $usable = $this->usable;

        if (! $usable) {
            $this->delete();

            return true;
        }

        $changed = false;
        $handled = false;

        if ($usable instanceof Role) {
            if ($this->context === 'profile') {
                $usable->profile_image = null;
                $usable->profile_image_url = null;
                $usable->profile_image_id = null;
                $changed = true;
                $handled = true;
            } elseif ($this->context === 'header') {
                $usable->header_image = null;
                $usable->header_image_url = null;
                $usable->header_image_id = null;
                $changed = true;
                $handled = true;
            } elseif ($this->context === 'background') {
                $usable->background_image = null;
                $usable->background_image_url = null;
                $usable->background_image_id = null;
                $changed = true;
                $handled = true;
            }
        } elseif ($usable instanceof Event && $this->context === 'flyer') {
            $usable->flyer_image_url = null;
            $usable->flyer_image_id = null;
            $changed = true;
            $handled = true;
        }

        if (! $handled) {
            return false;
        }

        if ($changed) {
            $usable->save();
        }

        $this->delete();

        return true;
    }
}
