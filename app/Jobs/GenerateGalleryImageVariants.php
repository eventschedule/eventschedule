<?php

namespace App\Jobs;

use App\Models\GalleryImage;
use Illuminate\Database\Eloquent\Model;

/**
 * Build the resized WebP derivatives of one gallery photo. The work itself, and its failure
 * handling, is in GenerateImageVariants.
 *
 * Dispatched from GalleryImage's created hook. A gallery row's file never changes after upload
 * (a replaced photo is a new row), so there is no updated counterpart. BackupService's restore
 * writes rows without events; `php artisan images:backfill-variants --gallery` covers those.
 */
class GenerateGalleryImageVariants extends GenerateImageVariants
{
    /**
     * @param  int  $galleryImageId  The gallery row whose photo to resize.
     * @param  string  $filename  The raw stored filename at dispatch time.
     */
    public function __construct(public int $galleryImageId, public string $filename) {}

    protected function findModel(): ?Model
    {
        return GalleryImage::find($this->galleryImageId);
    }

    protected function storedName(): string
    {
        return $this->filename;
    }

    protected function subject(): string
    {
        return 'gallery image '.$this->galleryImageId;
    }
}
