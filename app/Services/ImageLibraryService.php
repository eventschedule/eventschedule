<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageLibraryService
{
    public const DISK = 'public';
    public const BASE_DIRECTORY = 'admin/images';
    public const META_DIRECTORY = 'admin/images/meta';

    /**
     * Retrieve a filtered list of images from the library.
     */
    public function list(?string $search = null, ?string $type = null): array
    {
        $this->ensureDirectories();

        $items = $this->collectImages();

        $availableTypes = array_values(array_unique(array_filter(
            array_map(fn (array $item) => $item['extension'] ?? null, $items)
        )));

        if ($search) {
            $search = Str::lower($search);

            $items = array_values(array_filter($items, function (array $item) use ($search) {
                $haystack = Str::lower(($item['original_name'] ?? '') . ' ' . ($item['filename'] ?? ''));

                return Str::contains($haystack, $search);
            }));
        }

        if ($type) {
            $type = Str::lower($type);

            $items = array_values(array_filter($items, function (array $item) use ($type) {
                return Str::lower($item['extension'] ?? '') === $type;
            }));
        }

        usort($items, function (array $a, array $b) {
            $aTime = $a['updated_at'] ?? $a['uploaded_at'] ?? $a['last_modified'] ?? null;
            $bTime = $b['updated_at'] ?? $b['uploaded_at'] ?? $b['last_modified'] ?? null;

            if ($aTime === $bTime) {
                return 0;
            }

            return strcmp($bTime ?? '', $aTime ?? '');
        });

        return [
            'items' => $items,
            'available_types' => $availableTypes,
        ];
    }

    /**
     * Store a newly uploaded image.
     */
    public function store(UploadedFile $file): array
    {
        $this->ensureDirectories();

        $id = (string) Str::uuid();
        $extension = $this->detectExtension($file);
        $filename = $id . '.' . $extension;

        $path = $file->storeAs(self::BASE_DIRECTORY, $filename, self::DISK);

        $metadata = [
            'id' => $id,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'extension' => $extension,
            'uploaded_at' => Carbon::now()->toIso8601String(),
        ];

        $this->writeMetadata($id, $metadata);

        return $this->buildImageData($metadata, $path);
    }

    /**
     * Replace an existing image with a new upload.
     */
    public function replace(string $id, UploadedFile $file): array
    {
        $this->ensureDirectories();

        $metadata = $this->readMetadata($id);

        if (! $metadata) {
            throw new NotFoundHttpException('Image not found.');
        }

        $previousPath = self::BASE_DIRECTORY . '/' . $metadata['filename'];
        if (Storage::disk(self::DISK)->exists($previousPath)) {
            Storage::disk(self::DISK)->delete($previousPath);
        }

        $extension = $this->detectExtension($file);
        $filename = $id . '.' . $extension;

        $path = $file->storeAs(self::BASE_DIRECTORY, $filename, self::DISK);

        $metadata = array_merge($metadata, [
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'extension' => $extension,
            'updated_at' => Carbon::now()->toIso8601String(),
        ]);

        $this->writeMetadata($id, $metadata);

        return $this->buildImageData($metadata, $path);
    }

    /**
     * Delete an image from the library.
     */
    public function delete(string $id): void
    {
        $this->ensureDirectories();

        $metadata = $this->readMetadata($id);

        if (! $metadata) {
            throw new NotFoundHttpException('Image not found.');
        }

        $path = self::BASE_DIRECTORY . '/' . $metadata['filename'];
        $disk = Storage::disk(self::DISK);

        if ($disk->exists($path)) {
            $disk->delete($path);
        }

        $disk->delete($this->metadataPath($id));
    }

    protected function ensureDirectories(): void
    {
        $disk = Storage::disk(self::DISK);
        $disk->makeDirectory(self::BASE_DIRECTORY);
        $disk->makeDirectory(self::META_DIRECTORY);
    }

    protected function collectImages(): array
    {
        $disk = Storage::disk(self::DISK);
        $metaFiles = $disk->files(self::META_DIRECTORY);

        $items = [];

        foreach ($metaFiles as $metaFile) {
            $metadata = $this->decodeMetadata($disk->get($metaFile));

            if (! $metadata || empty($metadata['id'])) {
                continue;
            }

            $item = $this->buildImageData($metadata);

            if ($item) {
                $items[] = $item;
            }
        }

        return $items;
    }

    protected function buildImageData(array $metadata, ?string $storedPath = null): ?array
    {
        if (empty($metadata['filename'])) {
            return null;
        }

        $disk = Storage::disk(self::DISK);
        $path = $storedPath ?? self::BASE_DIRECTORY . '/' . $metadata['filename'];

        if (! $disk->exists($path)) {
            return null;
        }

        $size = $disk->size($path);
        $lastModified = Carbon::createFromTimestamp($disk->lastModified($path));
        $mimeType = $disk->mimeType($path);
        $dimensions = $this->probeDimensions($path);

        $uploadedAt = ! empty($metadata['uploaded_at'])
            ? Carbon::parse($metadata['uploaded_at'])
            : $lastModified;
        $updatedAt = ! empty($metadata['updated_at']) ? Carbon::parse($metadata['updated_at']) : null;

        return [
            'id' => $metadata['id'],
            'filename' => $metadata['filename'],
            'display_name' => $metadata['original_name'] ?? $metadata['filename'],
            'original_name' => $metadata['original_name'] ?? null,
            'extension' => Str::lower(pathinfo($metadata['filename'], PATHINFO_EXTENSION)),
            'url' => $disk->url($path),
            'size_bytes' => $size,
            'size_human' => $this->formatBytes($size),
            'mime_type' => $mimeType,
            'dimensions' => $dimensions,
            'dimensions_label' => $dimensions
                ? $dimensions['width'] . '×' . $dimensions['height']
                : null,
            'uploaded_at' => $uploadedAt->toIso8601String(),
            'uploaded_at_human' => $uploadedAt->diffForHumans(),
            'updated_at' => $updatedAt?->toIso8601String(),
            'updated_at_human' => $updatedAt?->diffForHumans(),
            'last_modified' => $lastModified->toIso8601String(),
            'last_modified_human' => $lastModified->diffForHumans(),
        ];
    }

    protected function detectExtension(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->extension();

        if (! $extension) {
            $extension = match ($file->getMimeType()) {
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'image/svg+xml' => 'svg',
                default => 'jpg',
            };
        }

        return Str::lower($extension);
    }

    protected function writeMetadata(string $id, array $metadata): void
    {
        Storage::disk(self::DISK)->put(
            $this->metadataPath($id),
            json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    protected function readMetadata(string $id): ?array
    {
        $disk = Storage::disk(self::DISK);
        $path = $this->metadataPath($id);

        if (! $disk->exists($path)) {
            return null;
        }

        return $this->decodeMetadata($disk->get($path));
    }

    protected function decodeMetadata(string $json): ?array
    {
        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    protected function metadataPath(string $id): string
    {
        return self::META_DIRECTORY . '/' . $id . '.json';
    }

    protected function probeDimensions(string $path): ?array
    {
        $disk = Storage::disk(self::DISK);
        $absolutePath = $disk->path($path);

        if (! is_file($absolutePath)) {
            return null;
        }

        try {
            $info = getimagesize($absolutePath);
        } catch (\Throwable) {
            $info = false;
        }

        if (! $info || ! isset($info[0], $info[1])) {
            return null;
        }

        return [
            'width' => $info[0],
            'height' => $info[1],
        ];
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $bytesFloat = $bytes / 1024;
        $unitIndex = 0;

        while ($bytesFloat >= 1024 && $unitIndex < count($units) - 1) {
            $bytesFloat /= 1024;
            $unitIndex++;
        }

        return number_format($bytesFloat, 1) . ' ' . $units[$unitIndex];
    }
}
