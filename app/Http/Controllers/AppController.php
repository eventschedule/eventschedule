<?php

namespace App\Http\Controllers;

use Codedge\Updater\UpdaterManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

        // Require authentication unless this is the initial setup (no users exist yet)
        if (! auth()->check()) {
            try {
                $hasUsers = \App\Models\User::exists();
            } catch (\Exception $e) {
                // Table may not exist yet during initial setup
                $hasUsers = false;
            }
            if ($hasUsers) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }
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
            $startTime = microtime(true);
            \Log::info('translateData started');

            // === EVERY CALL (every minute) ===

            // Process queued jobs (emails, etc.)
            try {
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
                \Log::warning('Queue processing failed: '.$e->getMessage());
            }

            (new \App\Jobs\ProcessScheduledNewsletters)();

            // === EVERY 5 MINUTES ===
            if (! Cache::has('td_5min')) {
                Cache::put('td_5min', true, now()->addMinutes(5));

                if (config('app.hosted')) {
                    \Artisan::call('app:sync-domain-statuses');
                }
            }

            // === EVERY 15 MINUTES ===
            if (! Cache::has('td_15min')) {
                Cache::put('td_15min', true, now()->addMinutes(15));

                \Artisan::call('caldav:sync');

                if (\App\Services\MetaAdsService::isBoostConfigured()) {
                    \Artisan::call('boost:sync');
                }
                \Artisan::call('boost:expire-pending');
            }

            // === HOURLY ===
            if (! Cache::has('td_hourly')) {
                Cache::put('td_hourly', true, now()->addHour());

                \Artisan::call('app:release-tickets');
                \Artisan::call('app:expire-waitlist');
                \Artisan::call('app:translate');
                \Artisan::call('app:send-graphic-emails');

                if (config('app.hosted')) {
                    \Artisan::call('app:setup-demo');
                }
            }

            // === DAILY ===
            if (! Cache::has('td_daily')) {
                Cache::put('td_daily', true, now()->endOfDay());

                \Artisan::call('google:refresh-webhooks');
                \Artisan::call('audit:prune');

                if (config('app.hosted')) {
                    \Artisan::call('app:generate-sub-audience-blog');
                    \Artisan::call('app:generate-daily-blog-post');
                    \Artisan::call('app:send-subscription-reminders');
                }

                if (! config('app.hosted')) {
                    \Artisan::call('app:import-curator-events');
                }
            }

            // === DAILY AT 12:00 PM UTC ===
            if (now()->hour >= 12 && ! Cache::has('notified_pending_today')) {
                \Artisan::call('app:notify-request-changes');
                \Artisan::call('app:notify-fan-content-changes');
                Cache::put('notified_pending_today', true, now()->endOfDay());
            }

            $duration = round(microtime(true) - $startTime, 2);
            \Log::info("translateData finished in {$duration}s");
        } finally {
            $lock->release();
        }

        return response()->json(['success' => true]);
    }

    public function robots()
    {
        $disallowRules = "User-agent: *\nDisallow: /login\nDisallow: /register\nDisallow: /password\nDisallow: /checkout\nDisallow: /home\nDisallow: /admin\n";

        $isAppSubdomain = config('app.hosted') && str_starts_with(request()->getHost(), 'app.');
        $content = $isAppSubdomain
            ? $disallowRules
            : $disallowRules."\nSitemap: ".config('app.url')."/sitemap.xml\n";

        return response($content, 200)->header('Content-Type', 'text/plain');
    }

    public function tempEventImage($filename = null)
    {
        if (! $filename) {
            abort(404);
        }

        $filename = basename($filename);

        if (! preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
            abort(404);
        }

        if (! str_starts_with($filename, 'event_')) {
            abort(404);
        }

        $path = storage_path('app/temp/'.$filename);

        if (file_exists($path)) {
            return response()->file($path);
        }

        abort(404);
    }

}
