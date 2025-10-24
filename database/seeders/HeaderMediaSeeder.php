<?php

namespace Database\Seeders;

use App\Models\MediaAsset;
use App\Models\MediaTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HeaderMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sourceDirectory = public_path('images/headers');

        if (! File::isDirectory($sourceDirectory)) {
            $this->command?->warn('Skipping header media import: public/images/headers not found.');
            return;
        }

        $disk = storage_public_disk();
        $storage = Storage::disk($disk);
        $tag = MediaTag::firstOrCreate(
            ['slug' => 'header'],
            ['name' => 'Header images']
        );

        foreach (File::files($sourceDirectory) as $file) {
            $extension = strtolower($file->getExtension());

            if (! in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
                continue;
            }

            $originalFilename = $file->getFilename();
            $existing = MediaAsset::where('folder', 'headers')
                ->where('original_filename', $originalFilename)
                ->first();

            if ($existing) {
                if (! $storage->exists($existing->path)) {
                    $storage->put($existing->path, File::get($file->getRealPath()));
                }

                $existing->tags()->syncWithoutDetaching([$tag->id]);
                continue;
            }

            $baseName = pathinfo($originalFilename, PATHINFO_FILENAME);
            $slug = Str::slug($baseName);

            if ($slug === '') {
                $slug = Str::random(16);
            }

            $directory = 'media/headers';
            $fileName = $slug . '.' . $extension;
            $path = $directory . '/' . $fileName;
            $counter = 1;

            while ($storage->exists($path)) {
                $fileName = $slug . '-' . $counter . '.' . $extension;
                $path = $directory . '/' . $fileName;
                $counter++;
            }

            $storage->put($path, File::get($file->getRealPath()));

            $dimensions = @getimagesize($file->getRealPath());

            $asset = MediaAsset::create([
                'disk' => $disk,
                'path' => $path,
                'original_filename' => $originalFilename,
                'mime_type' => File::mimeType($file->getRealPath()) ?: 'image/' . $extension,
                'size' => File::size($file->getRealPath()),
                'width' => $dimensions[0] ?? null,
                'height' => $dimensions[1] ?? null,
                'folder' => 'headers',
            ]);

            $asset->tags()->sync([$tag->id]);

            $this->command?->info("Imported header image: {$asset->original_filename}");
        }
    }
}
