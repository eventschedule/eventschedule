<?php

namespace App\Console\Commands;

use App\Models\VendorMap;
use App\Models\VendorMapDraft;
use App\Models\VendorMapOccupant;
use App\Services\VendorMaps\VendorMapImages;
use Illuminate\Console\Command;

/**
 * Deletes the vendor map's private pictures nobody needs any more: the photos uploaded for the AI
 * map (and what the AI made of them) after a day, and a declined vendor's photo after a month.
 * Both show people at a market. The privacy policy says the AI photos are deleted within a day.
 *
 * Hourly on both cron rails (routes/console.php and AppController::translateData()).
 */
class PruneVendorMapDrafts extends Command
{
    protected $signature = 'app:prune-vendor-map-drafts';

    protected $description = 'Delete vendor map AI drafts older than a day and declined vendor photos older than 30 days';

    public const DRAFT_HOURS = 24;

    public const DECLINED_PHOTO_DAYS = 30;

    /**
     * Private files nothing points at any more (a photo picked on the vendor link and never
     * sent, a batch refused part way): kept a day, as the privacy policy says, then deleted.
     */
    private function pruneOrphans(): int
    {
        $disk = VendorMapImages::disk();
        $used = collect()
            ->merge(VendorMapDraft::pluck('images')->flatten())
            ->merge(VendorMapOccupant::whereNotNull('photo_path')->pluck('photo_path'))
            ->merge(VendorMap::whereNotNull('background_path')->pluck('background_path'))
            ->filter()->flip();
        $cutoff = now()->subHours(self::DRAFT_HOURS)->getTimestamp();
        $deleted = 0;

        foreach ($disk->files('vendor-maps') as $path) {
            if (isset($used[$path]) || ! VendorMapImages::isPrivatePath($path)) {
                continue;
            }
            try {
                if ($disk->lastModified($path) >= $cutoff) {
                    continue;
                }
            } catch (\Throwable) {
                continue;
            }
            $disk->delete($path);
            $deleted++;
        }

        return $deleted;
    }

    public function handle(): int
    {
        $drafts = 0;
        $photos = 0;

        VendorMapDraft::where('created_at', '<', now()->subHours(self::DRAFT_HOURS))
            ->orderBy('id')
            ->chunkById(200, function ($chunk) use (&$drafts) {
                // A background traced in the designer is its own copy (VendorMapController), but
                // a path a map still points at is never deleted with a draft.
                $kept = VendorMap::whereIn('id', $chunk->pluck('vendor_map_id')->unique()->all())
                    ->whereNotNull('background_path')
                    ->pluck('background_path')
                    ->all();

                foreach ($chunk as $draft) {
                    foreach ((array) $draft->images as $path) {
                        if (is_string($path) && ! in_array($path, $kept, true)) {
                            VendorMapImages::deletePrivate($path);
                        }
                    }

                    $draft->delete();
                    $drafts++;
                }
            });

        VendorMapOccupant::where('status', 'declined')
            ->whereNotNull('photo_path')
            ->where('decided_at', '<', now()->subDays(self::DECLINED_PHOTO_DAYS))
            ->orderBy('id')
            ->chunkById(200, function ($chunk) use (&$photos) {
                foreach ($chunk as $row) {
                    VendorMapImages::deletePrivate($row->photo_path);
                    $row->forceFill(['photo_path' => null])->save();
                    $photos++;
                }
            });

        // Rows an approval stood aside, kept a day for Undo when the vendor was never mailed.
        VendorMapOccupant::where('status', 'replaced')
            ->where('updated_at', '<', now()->subDay())
            ->orderBy('id')
            ->chunkById(200, fn ($chunk) => $chunk->each->delete());

        $orphans = $this->pruneOrphans();

        $this->info("Deleted {$drafts} vendor map drafts, {$photos} declined vendor photos and {$orphans} unused files.");

        return Command::SUCCESS;
    }
}
