<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\PreviewsBlogPosts;
use App\Models\BlogPost;
use App\Services\Blog\BlogWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateSubAudienceBlog extends Command
{
    use PreviewsBlogPosts;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-sub-audience-blog
                            {--audience= : Generate for specific audience only (e.g., musicians, bars)}
                            {--sub-audience= : Generate for specific sub-audience key only (e.g., solo-artists)}
                            {--dry-run : Show what would be generated without creating}
                            {--preview : Write the next post now, print each stage and save nothing}
                            {--all : Generate all missing posts (not just one)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate blog posts for sub-audiences defined in config/sub_audiences.php';

    /**
     * Execute the console command.
     */
    public function handle(BlogWriter $writer)
    {
        // The blog is the marketing site's, so it exists on the nexus only (see routes/web.php).
        if (! config('app.is_nexus')) {
            $this->info('Sub-audience blog generation only runs on eventschedule.com.');

            return 0;
        }

        $config = config('sub_audiences');

        if (empty($config)) {
            $this->error('No sub-audiences configured. Check config/sub_audiences.php');

            return 1;
        }

        $isDryRun = $this->option('dry-run');
        $generateAll = $this->option('all');
        $targetAudience = $this->option('audience');
        $targetSubAudience = $this->option('sub-audience');

        // Skip if any post was created in the last 12 hours (unless --all), whichever generator
        // or admin wrote it: together the two generators post at most once a day. The pairing
        // with app:generate-daily-blog-post's 25-hour window is explained there.
        if (! $generateAll && BlogPost::recentlyGenerated(12)) {
            $this->info('A blog post was already created in the last 12 hours. Skipping.');

            return 0;
        }

        $missing = [];
        $generated = 0;

        // Find all sub-audiences that are missing blog posts
        foreach ($config as $audienceKey => $audience) {
            // Skip if we're targeting a specific audience and this isn't it
            if ($targetAudience && $audienceKey !== $targetAudience) {
                continue;
            }

            foreach ($audience['sub_audiences'] as $subKey => $subAudience) {
                // Skip if we're targeting a specific sub-audience and this isn't it
                if ($targetSubAudience && $subKey !== $targetSubAudience) {
                    continue;
                }

                $slug = $subAudience['slug'];

                // Check if blog post already exists
                $exists = BlogPost::where('slug', $slug)->exists();

                if (! $exists) {
                    $missing[] = [
                        'audience' => $audienceKey,
                        'audience_title' => $audience['title'],
                        'sub_audience' => $subKey,
                        'name' => $subAudience['name'],
                        'slug' => $slug,
                        'topic' => $subAudience['blog_topic'],
                        'features' => $subAudience['features'] ?? [],
                        'page' => $audience['page'],
                    ];
                }
            }
        }

        if (empty($missing)) {
            $this->info('All sub-audience blog posts already exist.');

            return 0;
        }

        // Display missing posts
        $this->info('Found '.count($missing).' sub-audiences without blog posts:');
        $this->newLine();

        foreach ($missing as $item) {
            $this->line("  - [{$item['audience_title']}] {$item['name']} ({$item['slug']})");
        }

        $this->newLine();

        if ($isDryRun) {
            $this->info('Dry run - no posts will be created.');

            return 0;
        }

        if ($this->option('preview')) {
            shuffle($missing);
            $item = $missing[0];
            $this->info("Previewing: {$item['name']} ({$item['slug']})");

            return $this->preview($writer->write(['topic' => $item['topic'], 'audience' => $this->audience($item)]));
        }

        // Only generate posts ~70% of the time for a more natural posting pattern
        if (! $generateAll && rand(1, 100) > 70) {
            $this->info('Skipping generation this run (random cooldown for natural posting pattern).');

            return 0;
        }

        // A post takes three model calls and a minute or two, so the other generator (or an
        // admin) may be part-way through one whose row does not exist yet.
        if (! BlogWriter::claim()) {
            $this->info('Another blog post is being written. Skipping.');

            return 0;
        }

        // Randomize order so posts aren't generated linearly by audience
        shuffle($missing);

        // Generate posts (one by default, all if --all flag is set)
        $toGenerate = $generateAll ? $missing : [array_shift($missing)];

        try {
            foreach ($toGenerate as $item) {
                $this->info("Generating blog post for: {$item['name']}");
                $this->line("  Topic: {$item['topic']}");

                try {
                    $written = $writer->write(['topic' => $item['topic'], 'audience' => $this->audience($item)]);

                    if ($written['post'] === null) {
                        Log::warning("Sub-audience blog post for {$item['slug']}: nothing was written (".$written['error'].').');
                        $this->error("  Nothing was written for {$item['name']}: ".$written['error'].'.');

                        continue;
                    }

                    // The configured slug, not one made from the title: the audience's card on
                    // its marketing page links to it by this slug.
                    $post = $writer->store($written, BlogWriter::SOURCE_AUDIENCE, $item['slug']);

                    // Clear the cache for this slug so the "Learn More" link appears
                    Cache::forget('sub_audience_blog_'.$item['slug']);

                    if (! $post->is_published) {
                        Log::warning("Sub-audience blog post for {$item['slug']} held as a draft: ".$post->held_reason, ['id' => $post->id]);
                        $this->warn("  Held as a draft (ID: {$post->id}): ".str_replace("\n", '; ', (string) $post->held_reason));

                        continue;
                    }

                    $this->info("  Created blog post: {$post->title} (ID: {$post->id})");

                    $generated++;
                } catch (\Exception $e) {
                    report($e);
                    $this->error("  Could not write the post for {$item['name']}.");

                    continue;
                }
            }
        } finally {
            BlogWriter::release();
        }

        $this->newLine();
        $this->info("Generated {$generated} blog post(s).");

        if (! $generateAll && count($missing) > 0) {
            $this->info('Run with --all to generate all '.count($missing).' remaining posts.');
        }

        return 0;
    }

    /**
     * What the writer needs to know about the audience a post is for.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function audience(array $item): array
    {
        return [
            'slug' => $item['slug'],
            'name' => $item['name'],
            'page' => $item['page'],
            'title' => $item['audience_title'],
            'features' => $item['features'],
        ];
    }
}
