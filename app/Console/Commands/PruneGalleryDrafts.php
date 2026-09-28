<?php

namespace App\Console\Commands;

use App\Models\GalleryImage;
use App\Utils\GalleryUtils;
use Illuminate\Console\Command;

/**
 * Delete gallery photos that were uploaded from an edit form which was never saved.
 *
 * The editors upload each photo as a draft the moment it is added (GalleryController), and only
 * the form's Save commits it (GalleryUtils::sync()). An abandoned form leaves its drafts behind,
 * files and all. A week is long enough that a form left open overnight still saves what it
 * uploaded; a Save that does name a pruned draft tells the organizer so.
 */
class PruneGalleryDrafts extends Command
{
    protected $signature = 'app:prune-gallery-drafts {--days=7 : Keep drafts younger than this}';

    protected $description = 'Delete unsaved gallery uploads older than the given number of days';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        if ($days < 1) {
            $this->error('Days must be at least 1.');

            return Command::FAILURE;
        }

        $query = GalleryImage::whereNotNull('draft_token')->where('created_at', '<', now()->subDays($days));
        $count = (clone $query)->count();

        GalleryUtils::purge($query);

        $this->info("Deleted {$count} unsaved gallery uploads older than {$days} days.");

        return Command::SUCCESS;
    }
}
