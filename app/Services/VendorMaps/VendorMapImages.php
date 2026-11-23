<?php

namespace App\Services\VendorMaps;

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
        if (! $size || $size[0] * $size[1] > 40_000_000) {
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

    /** A published thumbnail, by the name publishThumbnail() gave it and no other. */
    public static function deletePublic(?string $filename): void
    {
        if (is_string($filename) && preg_match('/^vmthumb_[a-z0-9]{32}\.jpg$/', $filename) === 1) {
            self::disk()->delete(ImageUtils::storagePathFor($filename));
        }
    }

    public static function publicUrl(?string $filename): ?string
    {
        return $filename ? ImageUtils::storedUrl($filename) : null;
    }
}
