<?php

namespace App\Jobs;

use App\Utils\ImageUtils;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

/**
 * Delete the files of gallery rows that GalleryUtils::purge() has already removed from the
 * database - the large-schedule path, where doing it inline would be thousands of storage calls
 * inside one request.
 */
class DeleteGalleryFiles implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  string[]  $filenames  Raw stored filenames.
     */
    public function __construct(public array $filenames) {}

    public function handle(): void
    {
        foreach ($this->filenames as $filename) {
            if (! is_string($filename) || $filename === '' || str_starts_with($filename, 'demo_')) {
                continue;
            }

            try {
                Storage::delete(ImageUtils::storagePathFor($filename));
            } catch (\Throwable $e) {
                report($e);
            }

            ImageUtils::deleteStoredVariants($filename);
        }
    }
}
