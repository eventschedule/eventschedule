<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Utils\ImageUtils;

class GenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-thumbnails {--force : Regenerate all thumbnails even if they exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate thumbnails for header and background images';

    /**
     * Thumbnail specifications
     */
    private const THUMBNAIL_CONFIG = [
        'headers' => [
            'width' => 384,
            'height' => 192,
            'quality' => 80,
        ],
        'backgrounds' => [
            'width' => 232,
            'height' => 308,
            'quality' => 80,
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        foreach (self::THUMBNAIL_CONFIG as $type => $config) {
            $this->info("Processing {$type}...");
            $this->processDirectory($type, $config, $force);
        }

        $this->info('Thumbnail generation complete!');
    }

    /**
     * Process a directory of images and generate thumbnails
     */
    private function processDirectory(string $type, array $config, bool $force): void
    {
        $sourceDir = public_path("images/{$type}");
        $thumbDir = public_path("images/{$type}/thumbs");

        if (!is_dir($sourceDir)) {
            $this->warn("Source directory not found: {$sourceDir}");
            return;
        }

        // Create thumbs directory if it doesn't exist
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
            $this->info("Created directory: {$thumbDir}");
        }

        // Get all PNG files in the source directory
        $files = glob("{$sourceDir}/*.png");
        $processed = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($files as $sourcePath) {
            $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
            $thumbPath = "{$thumbDir}/{$filename}.jpg";

            // Skip if thumbnail exists and force is not set
            if (file_exists($thumbPath) && !$force) {
                $skipped++;
                continue;
            }

            $result = ImageUtils::generateThumbnail(
                $sourcePath,
                $thumbPath,
                $config['width'],
                $config['height'],
                $config['quality']
            );

            if ($result) {
                $processed++;
                $this->line("  Created: {$filename}.jpg");
            } else {
                $failed++;
                $this->error("  Failed: {$filename}");
            }
        }

        $this->info("  {$type}: {$processed} created, {$skipped} skipped, {$failed} failed");
    }
}
