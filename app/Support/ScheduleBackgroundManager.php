<?php

namespace App\Support;

use App\Models\MediaAsset;
use App\Models\MediaTag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ScheduleBackgroundManager
{
    private const STORAGE_FOLDER = 'media/schedule/backgrounds';
    private const TAG_SLUG = 'schedule-backgrounds';
    private const TAG_NAME = 'Schedule backgrounds';
    private const FILE_GLOB_PATTERN = 'images/backgrounds/*.png';

    /**
     * Retrieve all schedule background assets from the media library, importing bundled
     * backgrounds if they have not yet been added.
     */
    public static function backgroundAssets(): Collection
    {
        self::importBundledBackgrounds();

        return MediaAsset::query()
            ->with('variants')
            ->where('folder', self::STORAGE_FOLDER)
            ->orderBy('original_filename')
            ->get();
    }

    /**
     * Locate a bundled background asset by its original filename (with or without extension).
     */
    public static function findBackgroundByIdentifier(?string $identifier): ?MediaAsset
    {
        if (! is_string($identifier) || trim($identifier) === '') {
            return null;
        }

        $normalized = trim($identifier);
        if (! Str::endsWith(Str::lower($normalized), '.png')) {
            $normalized .= '.png';
        }

        return MediaAsset::query()
            ->where('folder', self::STORAGE_FOLDER)
            ->where('original_filename', $normalized)
            ->first();
    }

    /**
     * Retrieve a random bundled background asset from the media library.
     */
    public static function randomBackgroundAsset(): ?MediaAsset
    {
        $assets = self::backgroundAssets();

        if ($assets->isEmpty()) {
            return null;
        }

        return $assets->random();
    }

    /**
     * Ensure the bundled schedule backgrounds are imported into the media library.
     */
    private static function importBundledBackgrounds(): void
    {
        $publicPath = public_path(self::FILE_GLOB_PATTERN);
        $paths = glob($publicPath) ?: [];

        if (empty($paths)) {
            return;
        }

        $disk = storage_public_disk();
        $storage = Storage::disk($disk);
        $tag = self::resolveBackgroundTag();

        foreach ($paths as $path) {
            if (! is_string($path) || ! is_file($path) || ! is_readable($path)) {
                continue;
            }

            $basename = pathinfo($path, PATHINFO_BASENAME);
            if (! is_string($basename) || $basename === '') {
                continue;
            }

            $relativePath = trim(self::STORAGE_FOLDER . '/' . $basename, '/');

            try {
                $contents = file_get_contents($path);
            } catch (Throwable $exception) {
                Log::warning('Failed to read bundled background for import', [
                    'path' => $path,
                    'exception' => $exception->getMessage(),
                ]);
                continue;
            }

            if ($contents === false) {
                continue;
            }

            try {
                if (! $storage->exists($relativePath)) {
                    $storage->put($relativePath, $contents, ['visibility' => 'public']);
                }
            } catch (Throwable $exception) {
                Log::warning('Failed to store bundled background in media library', [
                    'disk' => $disk,
                    'relative_path' => $relativePath,
                    'exception' => $exception->getMessage(),
                ]);
                continue;
            }

            $dimensions = @getimagesize($path) ?: [null, null];
            $size = @filesize($path);

            $attributes = [
                'disk' => $disk,
                'path' => $relativePath,
                'folder' => self::STORAGE_FOLDER,
                'original_filename' => $basename,
                'mime_type' => 'image/png',
                'size' => is_int($size) ? $size : null,
                'width' => $dimensions[0] ?? null,
                'height' => $dimensions[1] ?? null,
            ];

            $asset = MediaAsset::query()
                ->where('folder', self::STORAGE_FOLDER)
                ->where('original_filename', $basename)
                ->first();

            if ($asset) {
                $asset->fill($attributes);
                if ($asset->isDirty()) {
                    $asset->save();
                }
            } else {
                $asset = MediaAsset::create($attributes);
            }

            if ($asset) {
                $asset->tags()->syncWithoutDetaching([$tag->id]);
            }
        }
    }

    private static function resolveBackgroundTag(): MediaTag
    {
        return MediaTag::firstOrCreate(
            ['slug' => self::TAG_SLUG],
            ['name' => self::TAG_NAME]
        );
    }
}

