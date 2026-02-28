<?php

namespace App\Console\Commands;

use App\Mail\BlogPostReview;
use App\Models\BlogPost;
use App\Utils\GeminiUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class GenerateDailyBlogPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-daily-blog-post';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a daily blog post using AI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! config('app.hosted')) {
            $this->info('Daily blog post generation is only available in hosted mode.');

            return 0;
        }

        // Check if we already created a post today
        $todayStart = now()->startOfDay();
        $existsToday = BlogPost::where('created_at', '>=', $todayStart)->exists();

        if ($existsToday) {
            $this->info('Already created a blog post today.');

            return 0;
        }

        // Only create posts ~70% of days for more natural posting pattern
        if (rand(1, 100) > 70) {
            $this->info('Skipping generation this run (random cooldown for natural posting pattern).');

            return 0;
        }

        // Get recent titles for context
        $recentTitles = BlogPost::orderBy('created_at', 'desc')
            ->limit(15)
            ->pluck('title')
            ->toArray();

        // Generate topic based on recent posts
        $topic = GeminiUtils::generateBlogTopic($recentTitles);

        if (! $topic) {
            $this->error('Failed to generate blog topic.');

            return 1;
        }

        // Generate full blog post
        $postData = GeminiUtils::generateBlogPost($topic);

        if (! $postData) {
            $this->error('Failed to generate blog post.');

            return 1;
        }

        // Create the blog post with randomized timestamp (up to 6 hours earlier)
        $randomSeconds = rand(0, 6 * 60 * 60);
        $blogPost = BlogPost::create([
            'title' => $postData['title'],
            'content' => $postData['content'],
            'excerpt' => $postData['excerpt'] ?? null,
            'tags' => $postData['tags'] ?? [],
            'meta_title' => $postData['meta_title'] ?? null,
            'meta_description' => $postData['meta_description'] ?? null,
            'featured_image' => $postData['featured_image'] ?? null,
            'is_published' => true,
            'published_at' => now()->subSeconds($randomSeconds),
        ]);

        $this->info("Created blog post: {$blogPost->title} (ID: {$blogPost->id})");

        // Send email notification to admin for review
        try {
            $supportEmail = config('app.support_email');
            if ($supportEmail) {
                Mail::to($supportEmail)->send(new BlogPostReview($blogPost));
                $this->line("Sent review email to {$supportEmail}");
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to send blog post review email: '.$e->getMessage(), ['exception' => $e]);
            $this->warn('Failed to send review email: '.$e->getMessage());
        }

        return 0;
    }
}
