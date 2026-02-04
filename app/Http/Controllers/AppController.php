<?php

namespace App\Http\Controllers;

use App\Mail\BlogPostReview;
use App\Models\BlogPost;
use App\Utils\GeminiUtils;
use Codedge\Updater\UpdaterManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AppController extends Controller
{
    public function update(UpdaterManager $updater)
    {
        if (config('app.hosted')) {
            return redirect()->to(route('profile.edit').'#section-app')->with('error', 'Not authorized');
        }

        try {
            if ($updater->source()->isNewVersionAvailable()) {
                $versionAvailable = $updater->source()->getVersionAvailable();

                $release = $updater->source()->fetch($versionAvailable);

                $updater->source()->update($release);

                Artisan::call('migrate', ['--force' => true]);
            } else {
                return redirect()->to(route('profile.edit').'#section-app')->with('error', __('messages.no_new_version_available'));
            }
        } catch (\Exception $e) {
            return redirect()->to(route('profile.edit').'#section-app')->with('error', $e->getMessage());
        }

        return redirect()->to(route('profile.edit').'#section-app')->with('message', __('messages.app_updated'));
    }

    public function setup()
    {
        return view('setup');
    }

    public function testDatabase(Request $request)
    {
        // This endpoint is only available for self-hosted setups (not on hosted platform)
        // and should only be used during initial configuration
        if (config('app.hosted')) {
            return response()->json(['success' => false, 'error' => 'Not available'], 403);
        }

        $host = $request->input('host');
        $port = $request->input('port');
        $database = $request->input('database');
        $username = $request->input('username');
        $password = $request->input('password');

        try {
            $connection = @mysqli_connect($host, $username, $password, $database, (int) $port);
            if (! $connection) {
                // Don't expose detailed MySQL errors - they can reveal server information
                \Log::warning('Database connection test failed', ['host' => $host, 'error' => mysqli_connect_error()]);

                return response()->json(['success' => false, 'error' => 'Unable to connect to database. Please check your credentials.']);
            }

            // After successful connection, check for existing users
            try {
                $result = mysqli_query($connection, 'SELECT COUNT(*) as count FROM users');
                if ($result) {
                    $row = mysqli_fetch_assoc($result);
                    if ($row && (int) $row['count'] > 0) {
                        mysqli_close($connection);

                        return response()->json(['success' => true, 'has_existing_user' => true]);
                    }
                }
            } catch (\Exception $e) {
                // Table doesn't exist - that's fine, it's a fresh database
            }

            mysqli_close($connection);
        } catch (\Exception $e) {
            \Log::warning('Database connection test exception', ['host' => $host, 'error' => $e->getMessage()]);

            return response()->json(['success' => false, 'error' => 'Unable to connect to database. Please check your credentials.']);
        }

        return response()->json(['success' => true]);
    }

    public function translateData()
    {
        $requestSecret = request()->get('secret');
        $serverSecret = config('app.cron_secret');

        if (! $serverSecret || ! $requestSecret || ! hash_equals($serverSecret, $requestSecret)) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        $lock = Cache::lock('translate_data_lock', 300);
        if (! $lock->get()) {
            return response()->json(['message' => 'Already running'], 200);
        }

        try {
            try {
                // Process queued jobs (emails, etc.)
                \Artisan::call('queue:work', [
                    '--stop-when-empty' => true,
                    '--max-time' => 120,
                    '--tries' => 3,
                ]);

                // Retry failed jobs (capped at 50 to prevent infinite loops)
                $failedCount = DB::table('failed_jobs')->count();
                if ($failedCount > 0) {
                    \Log::warning("Found {$failedCount} failed jobs, retrying up to 50");
                    $failedIds = DB::table('failed_jobs')->orderBy('failed_at')->limit(50)->pluck('uuid');
                    foreach ($failedIds as $uuid) {
                        \Artisan::call('queue:retry', ['id' => [$uuid]]);
                    }

                    // Process retried jobs
                    \Artisan::call('queue:work', [
                        '--stop-when-empty' => true,
                        '--max-time' => 60,
                        '--tries' => 3,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Queue processing failed: ' . $e->getMessage());
            }

            \Artisan::call('app:translate');
            \Artisan::call('app:send-graphic-emails');
            \Artisan::call('google:refresh-webhooks');

            // Send pending notification emails (once per day at 12:00 PM UTC)
            if (now()->hour >= 12 && ! Cache::has('notified_pending_today')) {
                \Artisan::call('app:notify-request-changes');
                \Artisan::call('app:notify-fan-content-changes');
                Cache::put('notified_pending_today', true, now()->endOfDay());
            }

            if (config('app.hosted')) {
                \Artisan::call('app:generate-sub-audience-blog');
            }

            if (! config('app.hosted')) {
                \Artisan::call('app:import-curator-events');
            }

            // Auto-generate daily blog post (once per day)
            if (config('app.hosted')) {
                $this->generateDailyBlogPost();
            }
        } finally {
            $lock->release();
        }

        return response()->json(['success' => true]);
    }

    private function generateDailyBlogPost()
    {
        // Check if we already created a post today
        $todayStart = now()->startOfDay();
        $existsToday = BlogPost::where('created_at', '>=', $todayStart)->exists();

        if ($existsToday) {
            return; // Already created today's post
        }

        // Only create posts ~70% of days for more natural posting pattern
        if (rand(1, 100) > 70) {
            return;
        }

        // Get recent titles for context
        $recentTitles = BlogPost::orderBy('created_at', 'desc')
            ->limit(15)
            ->pluck('title')
            ->toArray();

        // Generate topic based on recent posts
        $topic = GeminiUtils::generateBlogTopic($recentTitles);

        if (! $topic) {
            return; // API error, will retry next cron run
        }

        // Generate full blog post
        $postData = GeminiUtils::generateBlogPost($topic);

        if (! $postData) {
            return;
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

        // Send email notification to admin for review
        try {
            $supportEmail = config('app.support_email');
            if ($supportEmail) {
                Mail::to($supportEmail)->send(new BlogPostReview($blogPost));
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send blog post review email: '.$e->getMessage());
        }
    }
}
