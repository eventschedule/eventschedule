<?php

namespace App\Console\Commands;

use App\Utils\ImageUtils;
use Illuminate\Console\Command;

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
    protected $description = 'Generate thumbnails and social-preview derivatives for header and background images';

    /**
     * Derivative specifications, keyed by the public/images subdirectory they read.
     *
     * Each source directory can produce several sizes; `dir` is the subdirectory the output
     * lands in. The `social` size on headers exists because the originals are 1536x768 PNGs
     * weighing about 1.9 MB, and WhatsApp renders no link preview at all above roughly 300 KB,
     * while the 384px thumbnail is far too small for an og:image. 1200x600 is the widely
     * accepted preview width at the 2:1 ratio the originals already use.
     */
    private const THUMBNAIL_CONFIG = [
        'headers' => [
            [
                'dir' => 'thumbs',
                'width' => 384,
                'height' => 192,
                'quality' => 80,
            ],
            [
                'dir' => 'social',
                'width' => 1200,
                'height' => 600,
                'quality' => 82,
            ],
        ],
        'backgrounds' => [
            [
                'dir' => 'thumbs',
                'width' => 232,
                'height' => 308,
                'quality' => 80,
            ],
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        foreach (self::THUMBNAIL_CONFIG as $type => $sizes) {
            foreach ($sizes as $config) {
                $this->info("Processing {$type}/{$config['dir']}...");
                $this->processDirectory($type, $config, $force);
            }
        }

        $this->info('Thumbnail generation complete!');
    }

    /**
     * Process a directory of images and generate thumbnails
     */
    private function processDirectory(string $type, array $config, bool $force): void
    {
        $sourceDir = public_path("images/{$type}");
        $thumbDir = public_path("images/{$type}/{$config['dir']}");

        if (! is_dir($sourceDir)) {
            $this->warn("Source directory not found: {$sourceDir}");

            return;
        }

        // Create thumbs directory if it doesn't exist
        if (! is_dir($thumbDir)) {
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
            if (file_exists($thumbPath) && ! $force) {
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

        $this->info("  {$type}/{$config['dir']}: {$processed} created, {$skipped} skipped, {$failed} failed");
    }
}
