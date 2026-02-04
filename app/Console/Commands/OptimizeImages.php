<?php

namespace App\Console\Commands;

use App\Utils\ImageUtils;
use Illuminate\Console\Command;

class OptimizeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-images {--force : Regenerate all WebP files even if they exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate WebP variants for all PNG and JPG images';

    /**
     * Directories to scan for images (relative to public/images/)
     */
    private const DIRECTORIES = [
        '',
        'backgrounds',
        'backgrounds/thumbs',
        'headers',
        'headers/thumbs',
        'screenshots',
        'social',
        'demo',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        $totalCreated = 0;
        $totalSkipped = 0;
        $totalFailed = 0;

        foreach (self::DIRECTORIES as $dir) {
            $label = $dir ?: 'root';
            $fullPath = public_path('images'.($dir ? "/{$dir}" : ''));

            if (! is_dir($fullPath)) {
                continue;
            }

            $this->info("Processing {$label}...");

            $files = array_merge(
                glob("{$fullPath}/*.png") ?: [],
                glob("{$fullPath}/*.jpg") ?: [],
                glob("{$fullPath}/*.jpeg") ?: []
            );

            $created = 0;
            $skipped = 0;
            $failed = 0;

            foreach ($files as $sourcePath) {
                $webpPath = preg_replace('/\.(png|jpe?g)$/i', '.webp', $sourcePath);

                if (file_exists($webpPath) && ! $force) {
                    $skipped++;

                    continue;
                }

                $result = ImageUtils::generateWebP($sourcePath, $webpPath);

                if ($result) {
                    $created++;
                    $this->line('  Created: '.basename($webpPath));
                } else {
                    $failed++;
                    $this->error('  Failed: '.basename($sourcePath));
                }
            }

            $this->info("  {$label}: {$created} created, {$skipped} skipped, {$failed} failed");

            $totalCreated += $created;
            $totalSkipped += $skipped;
            $totalFailed += $failed;
        }

        $this->newLine();
        $this->info("Complete: {$totalCreated} created, {$totalSkipped} skipped, {$totalFailed} failed");
    }
}
