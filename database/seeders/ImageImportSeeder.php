<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Services\ImageService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use SplFileInfo;

class ImageImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = app(ImageService::class);
        $importPaths = config('filesystems.image_storage.import_paths', ['public/images']);
        $importPaths = array_values(array_filter(array_map('trim', $importPaths)));

        if (empty($importPaths)) {
            $this->command?->warn('No import paths configured for images.');
            return;
        }

        foreach ($importPaths as $relativePath) {
            $absolutePath = base_path($relativePath);

            if (!File::exists($absolutePath)) {
                $this->command?->warn("Skipping missing path: {$relativePath}");
                continue;
            }

            $this->command?->info("Scanning image directory: {$relativePath}");

            foreach (File::allFiles($absolutePath) as $file) {
                $this->importFile($service, $file, $relativePath);
            }
        }
    }

    protected function importFile(ImageService $service, SplFileInfo $file, string $root): void
    {
        if (!$this->isSupportedImage($file)) {
            return;
        }

        $checksum = hash_file('sha256', $file->getRealPath());

        if (Image::withTrashed()->where('checksum', $checksum)->exists()) {
            $this->command?->line("Skipping already imported image: {$file->getFilename()}");
            return;
        }

        $mimeType = File::mimeType($file->getRealPath()) ?: $this->guessMimeType($file);

        $uploaded = new UploadedFile(
            $file->getRealPath(),
            $file->getFilename(),
            $mimeType,
            $file->getSize(),
            true
        );

        $image = $service->upload($uploaded);
        $image->original_filename = $file->getFilename();
        $image->directory = trim($image->directory ?? '', '/');
        $image->save();

        $this->command?->info("Imported image {$image->id} from {$file->getFilename()} ({$root})");
    }

    protected function isSupportedImage(SplFileInfo $file): bool
    {
        $extension = strtolower($file->getExtension());

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);
    }

    protected function guessMimeType(SplFileInfo $file): string
    {
        return match (strtolower($file->getExtension())) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            default => 'application/octet-stream',
        };
    }
}
