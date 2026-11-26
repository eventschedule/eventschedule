<?php

namespace App\Services\VendorMaps;

use App\Models\VendorMap;
use App\Models\VendorMapDraft;
use App\Models\VendorMapOccupant;
use App\Utils\ImageUtils;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Where a vendor map's pictures live.
 *
 * Photos for the AI map, the background traced in the designer and a vendor's photo before the
 * organizer approves it are PRIVATE: they show people at a market. They go on the default disk
 * with private visibility (the hosted bucket is public-read by default, so the visibility is
 * said on every write) under vendor-maps/, never under public/, and are served to the schedule's
 * editors by a controller. Only an approved vendor's photo becomes public, as a small new file.
 */
class VendorMapImages
{
    private const PRIVATE_DIR = 'vendor-maps';

    public static function disk(): Filesystem
    {
        return Storage::disk(config('filesystems.default'));
    }

    /** Store bytes privately; returns the path to keep. */
    public static function storePrivate(string $bytes, string $extension = 'jpg'): string
    {
        $extension = preg_replace('/[^a-z0-9]/', '', strtolower($extension)) ?: 'jpg';
        $path = self::PRIVATE_DIR.'/'.Str::lower(Str::random(40)).'.'.$extension;
        self::disk()->put($path, $bytes, ['visibility' => 'private']);

        return $path;
    }

    public static function readPrivate(?string $path): ?string
    {
        if (! self::isPrivatePath($path)) {
            return null;
        }

        $bytes = self::disk()->get($path);

        return is_string($bytes) && $bytes !== '' ? $bytes : null;
    }

    public static function deletePrivate(?string $path): void
    {
        if (self::isPrivatePath($path)) {
            self::disk()->delete($path);
        }
    }

    /** @param  string[]  $paths */
    public static function deletePaths(array $paths): void
    {
        foreach ($paths as $path) {
            self::deletePrivate($path);
        }
    }

    public static function size(?string $path): int
    {
        try {
            return self::isPrivatePath($path) ? (int) self::disk()->size($path) : 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    public static function isPrivatePath(?string $path): bool
    {
        return is_string($path) && preg_match('#^vendor-maps/[a-z0-9]{40}\.(jpg|jpeg|png|webp|pdf)$#', $path) === 1;
    }

    public static function mimeFor(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            default => 'image/jpeg',
        };
    }

    /**
     * A public, small copy of an approved vendor's photo (about 240px), as a new random name on
     * the public storage the rest of the app's images use. Null when the photo cannot be read.
     */
    public static function publishThumbnail(string $privatePath): ?string
    {
        $bytes = self::readPrivate($privatePath);
        if ($bytes === null || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        $size = @getimagesizefromstring($bytes);
        if (! $size || $size[0] * $size[1] > ImageUtils::VARIANT_MAX_PIXELS) {
            return null;
        }
        $image = @imagecreatefromstring($bytes);
        if (! $image) {
            return null;
        }

        $w = imagesx($image);
        $h = imagesy($image);
        $side = 240;
        $scale = min(1, $side / max($w, $h));
        $tw = max(1, (int) round($w * $scale));
        $th = max(1, (int) round($h * $scale));

        $thumb = imagecreatetruecolor($tw, $th);
        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $tw, $th, $w, $h);
        ob_start();
        imagejpeg($thumb, null, 82);
        $jpeg = (string) ob_get_clean();
        imagedestroy($image);
        imagedestroy($thumb);

        $filename = strtolower('vmthumb_'.Str::random(32).'.jpg');
        self::disk()->put(ImageUtils::storagePathFor($filename), $jpeg, ['visibility' => 'public']);

        return $filename;
    }

    /**
     * The public copy of a map's tracing picture, for shoppers when its owner chose to show it:
     * one name per private picture (keyed with the app key, so the private path cannot be read
     * back from it), served from the public store and its CDN, never through PHP.
     */
    public static function pictureName(string $privatePath): string
    {
        return 'vmpic_'.substr(hash_hmac('sha256', $privatePath, (string) config('app.key')), 0, 32).'.jpg';
    }

    /** Makes the public copy when there is none (at most 2400px, a JPEG). Null when it cannot. */
    public static function publishPicture(string $privatePath): ?string
    {
        $name = self::pictureName($privatePath);
        $disk = self::disk();
        if ($disk->exists(ImageUtils::storagePathFor($name))) {
            return $name;
        }
        $bytes = self::readPrivate($privatePath);
        $size = $bytes ? @getimagesizefromstring($bytes) : false;
        if (! $size || $size[0] * $size[1] > ImageUtils::VARIANT_MAX_PIXELS || ! function_exists('imagecreatefromstring')) {
            return null;
        }
        $image = @imagecreatefromstring($bytes);
        if (! $image) {
            return null;
        }
        $scale = min(1, 2400 / max($size[0], $size[1]));
        $w = max(1, (int) round($size[0] * $scale));
        $h = max(1, (int) round($size[1] * $scale));
        $out = imagecreatetruecolor($w, $h);
        imagecopyresampled($out, $image, 0, 0, 0, 0, $w, $h, $size[0], $size[1]);
        ob_start();
        imagejpeg($out, null, 84);
        $jpeg = (string) ob_get_clean();
        imagedestroy($image);
        imagedestroy($out);
        $disk->put(ImageUtils::storagePathFor($name), $jpeg, ['visibility' => 'public']);

        return $name;
    }

    /** A published thumbnail or map picture, by the name this class gave it and no other. */
    public static function deletePublic(?string $filename): void
    {
        if (is_string($filename) && preg_match('/^(vmthumb|vmpic)_[a-z0-9]{32}\.jpg$/', $filename) === 1) {
            self::disk()->delete(ImageUtils::storagePathFor($filename));
        }
    }

    /**
     * Every file the vendor maps keep for these schedules, deleted before their rows go: a
     * schedule's map, drafts and occupants go by a database cascade (roles -> vendor_maps ->
     * the rest), which runs no model event. As a market: its share card, its picture (both
     * copies), its drafts' photos and its vendors' photos. As a vendor: the photos on its own
     * rows on other markets' maps. A photo another row still shows (a one-day move copies the
     * thumbnail) is kept. The private files would be swept within a day anyway
     * (app:prune-vendor-map-drafts); the public ones would stay on the public store for good.
     *
     * Called by Role's `deleting` hook and by AccountDeletionService (an account's schedules
     * go by the roles.user_id cascade, which runs no hook either).
     *
     * @param  array<int, int|string>  $roleIds
     */
    public static function forgetSchedules(array $roleIds): void
    {
        $roleIds = array_values(array_filter(array_map('intval', $roleIds)));
        if ($roleIds === []) {
            return;
        }

        $maps = VendorMap::whereIn('role_id', $roleIds)->get(['id', 'share_image_path', 'background_path']);
        $mapIds = $maps->pluck('id')->all();

        foreach ($maps as $map) {
            if (is_string($map->share_image_path) && preg_match('/^vmshare_[a-z0-9]{32}\.png$/', $map->share_image_path) === 1) {
                self::disk()->delete(ImageUtils::storagePathFor($map->share_image_path));
            }
            if ($map->background_path) {
                self::deletePublic(self::pictureName($map->background_path));
                self::deletePrivate($map->background_path);
            }
        }

        if ($mapIds !== []) {
            foreach (VendorMapDraft::whereIn('vendor_map_id', $mapIds)->pluck('images') as $images) {
                self::deletePaths(array_filter((array) $images, 'is_string'));
            }
        }

        $going = VendorMapOccupant::query()
            ->where(fn ($q) => $q->whereIn('vendor_role_id', $roleIds)->when($mapIds !== [], fn ($q) => $q->orWhereIn('vendor_map_id', $mapIds)))
            ->where(fn ($q) => $q->whereNotNull('photo_path')->orWhereNotNull('photo_thumb_path'))
            ->get(['id', 'photo_path', 'photo_thumb_path']);

        foreach (['photo_path', 'photo_thumb_path'] as $column) {
            $paths = $going->pluck($column)->filter()->unique()->values();
            if ($paths->isEmpty()) {
                continue;
            }

            $kept = VendorMapOccupant::whereIn($column, $paths->all())
                ->whereNotIn('id', $going->pluck('id')->all())
                ->pluck($column)
                ->all();

            foreach ($paths->diff($kept) as $path) {
                $column === 'photo_path' ? self::deletePrivate($path) : self::deletePublic($path);
            }
        }
    }

    public static function publicUrl(?string $filename): ?string
    {
        return $filename ? ImageUtils::storedUrl($filename) : null;
    }
}
