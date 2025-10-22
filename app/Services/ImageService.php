<?php

namespace App\Services;

use App\Models\Image;
use App\Utils\ImageUtils;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ReflectionFunction;
use RuntimeException;

class ImageService
{
    protected string $disk;

    protected string $baseDirectory;

    protected string $variantDirectory;

    public function __construct(?string $disk = null)
    {
        $this->disk = $disk ?: config('filesystems.image_storage.disk', 'images');
        $this->baseDirectory = trim(config('filesystems.image_storage.directory', 'images/originals'), '/');
        $this->variantDirectory = trim(config('filesystems.image_storage.variants_directory', 'images/variants'), '/');
    }

    public function upload(UploadedFile $file, array $variantDefinitions = []): Image
    {
        ImageUtils::validateUploadedFile($file);

        $filesystem = $this->filesystem();
        $stored = $this->storeFile($filesystem, $file, null);

        $image = new Image([
            'disk' => $this->disk,
            'directory' => $stored['directory'],
            'filename' => $stored['filename'],
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $stored['mime_type'],
            'size' => $stored['size'],
            'path' => $stored['path'],
            'checksum' => $stored['checksum'],
            'variants' => [],
        ]);

        DB::transaction(function () use ($image, $variantDefinitions): void {
            $image->save();

            if (!empty($variantDefinitions)) {
                $variants = $this->generateVariants($image, $variantDefinitions);
                $image->variants = $variants;
                $image->save();
            }
        });

        return $image->refresh();
    }

    public function replace(Image $image, UploadedFile $file, array $variantDefinitions = []): Image
    {
        ImageUtils::validateUploadedFile($file);

        $filesystem = $this->filesystem($image);
        $previousPath = $image->path;
        $previousVariants = $image->variants ?? [];

        $stored = $this->storeFile($filesystem, $file, $image);

        DB::transaction(function () use ($image, $variantDefinitions, $stored, $previousVariants, $file): void {
            $image->fill([
                'directory' => $stored['directory'],
                'filename' => $stored['filename'],
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $stored['mime_type'],
                'size' => $stored['size'],
                'path' => $stored['path'],
                'checksum' => $stored['checksum'],
            ]);

            $image->save();

            $this->removeVariantFiles($previousVariants, $image);

            if (!empty($variantDefinitions)) {
                $variants = $this->generateVariants($image, $variantDefinitions);
                $image->variants = $variants;
            } else {
                $image->variants = [];
            }

            $image->save();
        });

        if ($previousPath !== $stored['path']) {
            $filesystem->delete($previousPath);
        }

        return $image->refresh();
    }

    public function canDelete(Image $image): bool
    {
        return ($image->reference_count ?? 0) === 0;
    }

    public function delete(Image $image, bool $force = false): bool
    {
        if (!$force && !$this->canDelete($image)) {
            return false;
        }

        $filesystem = $this->filesystem($image);

        $filesystem->delete($image->path);
        $this->removeVariantFiles($image->variants ?? [], $image, $filesystem);

        return $force ? (bool) $image->forceDelete() : (bool) $image->delete();
    }

    public function incrementReference(Image $image, int $amount = 1): Image
    {
        $image->reference_count += max(1, $amount);
        $image->save();

        return $image->refresh();
    }

    public function decrementReference(Image $image, int $amount = 1): Image
    {
        $image->reference_count = max(0, $image->reference_count - max(1, $amount));
        $image->save();

        return $image->refresh();
    }

    public function generateVariants(Image $image, array $variantDefinitions): array
    {
        if (empty($variantDefinitions)) {
            return [];
        }

        $filesystem = $this->filesystem($image);
        $variants = [];

        foreach ($variantDefinitions as $name => $definition) {
            $variantName = (string) $name;
            $options = is_array($definition) ? $definition : [];
            $variantPath = $this->buildVariantPath($image, $variantName, $options);
            $visibility = $options['visibility'] ?? null;

            $directoryName = trim(str_replace('\\', '/', dirname($variantPath)), '/');

            if ($directoryName !== '' && !$filesystem->exists($directoryName)) {
                $filesystem->makeDirectory($directoryName);
            }

            $callback = $this->resolveVariantCallback($definition);
            $this->invokeVariantCallback($callback, $filesystem, $image, $variantPath, $options);

            if ($filesystem->exists($variantPath)) {
                if ($visibility) {
                    $filesystem->setVisibility($variantPath, $visibility);
                }

                $variants[$variantName] = [
                    'path' => $variantPath,
                    'disk' => $image->disk,
                    'size' => $filesystem->size($variantPath),
                    'last_modified' => $filesystem->lastModified($variantPath),
                ];
            }
        }

        return $variants;
    }

    public function importFile(UploadedFile $file, array $variantDefinitions = []): Image
    {
        $existing = $this->findByChecksum(hash_file('sha256', $file->getRealPath()));

        if ($existing) {
            return $existing;
        }

        return $this->upload($file, $variantDefinitions);
    }

    public function findByChecksum(string $checksum): ?Image
    {
        return Image::where('checksum', $checksum)->first();
    }

    protected function filesystem(?Image $image = null): FilesystemAdapter
    {
        $diskName = $image?->disk ?: $this->disk;

        return Storage::disk($diskName);
    }

    protected function storeFile(FilesystemAdapter $filesystem, UploadedFile $file, ?Image $context): array
    {
        $directory = $context?->directory;

        if ($directory === null || $directory === '') {
            $directory = $this->buildStorageDirectory();
        }

        $directory = trim((string) $directory, '/');
        $filename = $this->generateFilename($file);
        $storedPath = $filesystem->putFileAs($directory, $file, $filename);

        if ($storedPath === false) {
            throw new RuntimeException('Unable to store image file on the configured disk.');
        }

        $path = is_string($storedPath) && $storedPath !== ''
            ? $storedPath
            : ($directory !== '' ? $directory . '/' . $filename : $filename);

        return [
            'directory' => $directory,
            'filename' => $filename,
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'checksum' => hash_file('sha256', $file->getRealPath()),
        ];
    }

    protected function buildStorageDirectory(): string
    {
        $dateSegment = Carbon::now()->format('Y/m/d');
        $segments = array_filter([$this->baseDirectory, $dateSegment]);

        return implode('/', $segments);
    }

    protected function buildVariantDirectory(Image $image): string
    {
        $segments = array_filter([$this->variantDirectory, $image->uuid]);

        return implode('/', $segments);
    }

    protected function buildVariantPath(Image $image, string $variantName, array $options = []): string
    {
        $directory = trim((string) ($options['path'] ?? $this->buildVariantDirectory($image)), '/');
        $extension = $options['extension'] ?? pathinfo($image->filename, PATHINFO_EXTENSION) ?: 'bin';
        $filename = Str::slug($variantName) . '.' . ltrim($extension, '.');

        return $directory !== '' ? $directory . '/' . $filename : $filename;
    }

    protected function resolveVariantCallback(mixed $definition): callable
    {
        if (is_callable($definition)) {
            return $definition;
        }

        if (is_array($definition)) {
            if (isset($definition['generator']) && is_callable($definition['generator'])) {
                return $definition['generator'];
            }

            if (isset($definition['callback']) && is_callable($definition['callback'])) {
                return $definition['callback'];
            }
        }

        if (is_string($definition) && method_exists($this, $definition)) {
            return [$this, $definition];
        }

        return function (Filesystem $filesystem, Image $image, string $variantPath, array $options = []): void {
            $filesystem->copy($image->path, $variantPath);
        };
    }

    protected function invokeVariantCallback(
        callable $callback,
        FilesystemAdapter $filesystem,
        Image $image,
        string $variantPath,
        array $options
    ): void {
        $closure = \Closure::fromCallable($callback);
        $reflection = new ReflectionFunction($closure);
        $parameterCount = $reflection->getNumberOfParameters();

        $arguments = match (true) {
            $parameterCount >= 4 => [$filesystem, $image, $variantPath, $options],
            $parameterCount === 3 => [$filesystem, $image, $variantPath],
            $parameterCount === 2 => [$filesystem, $image],
            $parameterCount === 1 => [$filesystem],
            default => [],
        };

        $closure(...$arguments);
    }

    protected function removeVariantFiles(array $variants, ?Image $image = null, ?FilesystemAdapter $filesystem = null): void
    {
        if (empty($variants)) {
            return;
        }

        $filesystem ??= $this->filesystem($image);

        foreach ($variants as $variant) {
            $path = is_array($variant) ? ($variant['path'] ?? null) : $variant;

            if ($path) {
                $filesystem->delete($path);
            }
        }
    }

    protected function generateFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'bin'));

        return Str::uuid()->toString() . '.' . $extension;
    }
}
