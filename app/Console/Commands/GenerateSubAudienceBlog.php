<?php

namespace App\Console\Commands;

use App\Mail\BlogPostReview;
use App\Models\BlogPost;
use App\Utils\GeminiUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class GenerateSubAudienceBlog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-sub-audience-blog
                            {--audience= : Generate for specific audience only (e.g., musicians, bars)}
                            {--sub-audience= : Generate for specific sub-audience key only (e.g., solo-artists)}
                            {--dry-run : Show what would be generated without creating}
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
    public function handle()
    {
        $config = config('sub_audiences');

        if (empty($config)) {
            $this->error('No sub-audiences configured. Check config/sub_audiences.php');

            return 1;
        }

        $isDryRun = $this->option('dry-run');
        $generateAll = $this->option('all');
        $targetAudience = $this->option('audience');
        $targetSubAudience = $this->option('sub-audience');

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

        // Only generate posts ~70% of the time for a more natural posting pattern
        if (! $generateAll && rand(1, 100) > 70) {
            $this->info('Skipping generation this run (random cooldown for natural posting pattern).');

            return 0;
        }

        // Generate posts (one by default, all if --all flag is set)
        $toGenerate = $generateAll ? $missing : [array_shift($missing)];

        foreach ($toGenerate as $item) {
            $this->info("Generating blog post for: {$item['name']}");
            $this->line("  Topic: {$item['topic']}");

            try {
                // Get the parent page from config for internal linking
                $parentPage = $config[$item['audience']]['page'];
                $parentTitle = $config[$item['audience']]['title'];

                // Generate the blog post content using Gemini
                $result = GeminiUtils::generateBlogPost($item['topic'], $parentPage, $parentTitle, $item['features']);

                if (empty($result) || empty($result['content'])) {
                    $this->error("  Failed to generate content for {$item['name']}");

                    continue;
                }

                // Create the blog post with the configured slug (not auto-generated)
                $post = BlogPost::create([
                    'title' => $result['title'],
                    'slug' => $item['slug'], // Use the configured slug
                    'content' => $result['content'],
                    'excerpt' => $result['excerpt'] ?? null,
                    'tags' => $result['tags'] ?? ['events', 'scheduling'],
                    'meta_title' => $result['meta_title'] ?? $result['title'],
                    'meta_description' => $result['meta_description'] ?? ($result['excerpt'] ?? null),
                    'featured_image' => $result['featured_image'] ?? null,
                    'author_name' => 'Event Schedule Team',
                    'is_published' => true,
                    'published_at' => now()->subSeconds(rand(0, 6 * 60 * 60)),
                ]);

                // Clear the cache for this slug so the "Learn More" link appears
                Cache::forget('sub_audience_blog_'.$item['slug']);

                $this->info("  Created blog post: {$post->title} (ID: {$post->id})");

                // Send review email
                $this->sendReviewEmail($post);

                $generated++;
            } catch (\Exception $e) {
                $this->error("  Error generating post for {$item['name']}: ".$e->getMessage());

                continue;
            }
        }

        $this->newLine();
        $this->info("Generated {$generated} blog post(s).");

        if (! $generateAll && count($missing) > 0) {
            $this->info('Run with --all to generate all '.count($missing).' remaining posts.');
        }

        return 0;
    }

    /**
     * Send review email to admin
     */
    protected function sendReviewEmail(BlogPost $post)
    {
        $adminEmail = config('mail.admin_email');

        if (! $adminEmail) {
            $this->warn('  No admin email configured - skipping review email');

            return;
        }

        try {
            Mail::to($adminEmail)->send(new BlogPostReview($post));
            $this->line('  Sent review email to '.$adminEmail);
        } catch (\Exception $e) {
            $this->warn('  Failed to send review email: '.$e->getMessage());
        }
    }
}
