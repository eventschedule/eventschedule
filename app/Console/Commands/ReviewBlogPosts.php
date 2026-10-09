<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Services\Blog\BlogReview;
use Illuminate\Console\Command;

class ReviewBlogPosts extends Command
{
    protected $signature = 'app:review-blog-posts
                            {--claims=0 : Also ask the model to check the product claims of this many posts that have not been checked}';

    protected $description = 'Review the published blog posts and store what is proposed for each. Changes no post.';

    public function handle()
    {
        if (! config('app.is_nexus')) {
            $this->info('The blog exists on eventschedule.com only.');

            return 0;
        }

        $this->info('Reviewed '.BlogReview::run().' posts.');

        $claims = (int) $this->option('claims');
        $checked = 0;

        if ($claims > 0) {
            $posts = BlogPost::published()->orderByDesc('view_count')->get()
                ->filter(fn ($post) => ! isset($post->review['unsupported']))
                ->take($claims);

            foreach ($posts as $post) {
                $checked += BlogReview::checkClaims($post) ? 1 : 0;
            }

            $this->info("Checked the claims of {$checked} posts.");
        }

        $proposed = BlogPost::published()->get()->countBy(fn ($post) => $post->review['action'] ?? 'not reviewed');
        foreach ($proposed as $action => $count) {
            $this->line("  {$action}: {$count}");
        }

        return 0;
    }
}
