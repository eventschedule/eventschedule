<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\PreviewsBlogPosts;
use App\Models\BlogPost;
use App\Services\Blog\BlogWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateDailyBlogPost extends Command
{
    use PreviewsBlogPosts;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-daily-blog-post
                            {--preview : Write a post now, print each stage and save nothing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Write a blog post on a topic the planner chooses';

    /**
     * Execute the console command.
     */
    public function handle(BlogWriter $writer)
    {
        // The blog is the marketing site's, so it exists on the nexus only (see routes/web.php).
        if (! config('app.is_nexus')) {
            $this->info('Daily blog post generation only runs on eventschedule.com.');

            return 0;
        }

        if ($this->option('preview')) {
            return $this->preview($writer->write());
        }

        // One post a day across BOTH generators (and the admin), not one per generator: up to two
        // formulaic AI posts a day is the pattern Google's scaled-content policy targets.
        //
        // The two generators use different windows on purpose. app:generate-sub-audience-blog
        // (03:00) skips only when a post was created in the last 12 hours, and this command
        // (00:00) skips when one was created in the last 25. So on the scheduler rail a
        // sub-audience post at 03:00 is still inside the window at the next midnight (21 hours
        // later), which leaves the targeted sub-audience posts running daily and this command
        // filling only the days they skip. When this command does post at 00:00, that post is 3
        // hours old when the sub-audience command looks at 03:00 and is still inside this
        // command's own window at the next midnight, so the day after goes back to the
        // sub-audience command. Neither order ever yields two posts in one day. On the HTTP rail
        // (/translate_data) the sub-audience command runs first in the same tick, and this one
        // then sees its post and skips.
        //
        // 25 rather than 24 so a tick that lands a few seconds late still sees yesterday's post.
        $recentlyPublished = BlogPost::recentlyGenerated(25);

        if ($recentlyPublished) {
            $this->info('A blog post was already published in the last day.');

            return 0;
        }

        // Only create posts ~70% of days for more natural posting pattern
        if (rand(1, 100) > 70) {
            $this->info('Skipping generation this run (random cooldown for natural posting pattern).');

            return 0;
        }

        // A post takes three model calls and a minute or two, so the other generator (or an
        // admin) may be part-way through one whose row does not exist yet.
        if (! BlogWriter::claim()) {
            $this->info('Another blog post is being written. Skipping.');

            return 0;
        }

        try {
            $written = $writer->write();

            if ($written['post'] === null) {
                Log::warning('Daily blog post: nothing was written ('.$written['error'].').');
                $this->error('Nothing was written: '.$written['error'].'.');

                return 1;
            }

            $post = $writer->store($written, BlogWriter::SOURCE_DAILY);
        } finally {
            BlogWriter::release();
        }

        if (! $post->is_published) {
            Log::warning('Daily blog post held as a draft: '.$post->held_reason, ['id' => $post->id, 'title' => $post->title]);
            $this->warn("Held as a draft (ID: {$post->id}): ".str_replace("\n", '; ', (string) $post->held_reason));

            return 0;
        }

        $this->info("Created blog post: {$post->title} (ID: {$post->id})");

        return 0;
    }
}
