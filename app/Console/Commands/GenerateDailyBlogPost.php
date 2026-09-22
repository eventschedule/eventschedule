<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Utils\GeminiUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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

        // One post a day across BOTH generators (and the admin), not one per generator: up to two
        // formulaic AI posts a day is the pattern Google's scaled-content policy targets.
        //
        // created_at, not published_at: both generators backdate published_at by up to 6 hours
        // at random, so it says little about when a post actually appeared. And 23 hours, not
        // 24: this command runs at 00:00 and app:generate-sub-audience-blog at 03:00, so the
        // window must reach back past 03:00 yesterday (21 hours) to see that post, while a
        // full 24 would catch this command's own post from the previous midnight whenever
        // tonight's tick lands a few seconds earlier than last night's.
        $recentlyPublished = BlogPost::where('is_published', true)
            ->where('created_at', '>=', now()->subHours(23))
            ->exists();

        if ($recentlyPublished) {
            $this->info('A blog post was already published in the last day.');

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

        $rejection = BlogPost::qualityGateFailure($postData);

        if ($rejection !== null) {
            Log::warning('Daily blog post rejected by the quality gate: '.$rejection, ['title' => $postData['title'] ?? null]);
            $this->warn('Rejected by the quality gate: '.$rejection);

            return 0;
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

        return 0;
    }
}
