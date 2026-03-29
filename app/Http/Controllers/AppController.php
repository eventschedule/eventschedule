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
            report($e);

            return redirect()->to(route('profile.edit').'#section-app')->with('error', __('messages.error'));
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

            try {
                (new \App\Jobs\ProcessScheduledNewsletters)();
            } catch (\Exception $e) {
                \Log::error('Scheduled command ProcessScheduledNewsletters failed: '.$e->getMessage());
                report($e);
            }

            // === EVERY 5 MINUTES ===
            if (! Cache::has('td_5min')) {
                Cache::put('td_5min', true, now()->addMinutes(5));

                if (config('app.hosted')) {
                    try {
                        \Artisan::call('app:sync-domain-statuses');
                    } catch (\Exception $e) {
                        \Log::error('Scheduled command app:sync-domain-statuses failed: '.$e->getMessage());
                        report($e);
                    }
                }
            }

            // === EVERY 15 MINUTES ===
            if (! Cache::has('td_15min')) {
                Cache::put('td_15min', true, now()->addMinutes(15));

                try {
                    \Artisan::call('caldav:sync');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command caldav:sync failed: '.$e->getMessage());
                    report($e);
                }

                if (\App\Services\MetaAdsService::isBoostConfigured()) {
                    try {
                        \Artisan::call('boost:sync');
                    } catch (\Exception $e) {
                        \Log::error('Scheduled command boost:sync failed: '.$e->getMessage());
                        report($e);
                    }
                }
                try {
                    \Artisan::call('boost:expire-pending');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command boost:expire-pending failed: '.$e->getMessage());
                    report($e);
                }
            }

            // === HOURLY ===
            if (! Cache::has('td_hourly')) {
                Cache::put('td_hourly', true, now()->addHour());

                try {
                    \Artisan::call('app:release-tickets');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command app:release-tickets failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:expire-waitlist');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command app:expire-waitlist failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:translate');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command app:translate failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:send-graphic-emails');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command app:send-graphic-emails failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:send-feedback-requests');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command app:send-feedback-requests failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:send-carpool-reminders');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command app:send-carpool-reminders failed: '.$e->getMessage());
                    report($e);
                }

                if (config('app.hosted')) {
                    try {
                        \Artisan::call('app:setup-demo');
                    } catch (\Exception $e) {
                        \Log::error('Scheduled command app:setup-demo failed: '.$e->getMessage());
                        report($e);
                    }
                }
            }

            // === DAILY ===
            if (! Cache::has('td_daily')) {
                Cache::put('td_daily', true, now()->endOfDay());

                try {
                    \Artisan::call('google:refresh-webhooks');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command google:refresh-webhooks failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('audit:prune');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command audit:prune failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:cleanup-webhook-deliveries');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command app:cleanup-webhook-deliveries failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:cleanup-backups');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command app:cleanup-backups failed: '.$e->getMessage());
                    report($e);
                }

                if (config('app.hosted')) {
                    try {
                        \Artisan::call('app:generate-sub-audience-blog');
                    } catch (\Exception $e) {
                        \Log::error('Scheduled command app:generate-sub-audience-blog failed: '.$e->getMessage());
                        report($e);
                    }
                    try {
                        \Artisan::call('app:generate-daily-blog-post');
                    } catch (\Exception $e) {
                        \Log::error('Scheduled command app:generate-daily-blog-post failed: '.$e->getMessage());
                        report($e);
                    }
                    try {
                        \Artisan::call('app:send-subscription-reminders');
                    } catch (\Exception $e) {
                        \Log::error('Scheduled command app:send-subscription-reminders failed: '.$e->getMessage());
                        report($e);
                    }
                    try {
                        \Artisan::call('app:process-referral-credits');
                    } catch (\Exception $e) {
                        \Log::error('Scheduled command app:process-referral-credits failed: '.$e->getMessage());
                        report($e);
                    }
                }

                if (! config('app.hosted')) {
                    try {
                        \Artisan::call('app:import-curator-events');
                    } catch (\Exception $e) {
                        \Log::error('Scheduled command app:import-curator-events failed: '.$e->getMessage());
                        report($e);
                    }
                }
            }

            // === DAILY AT 12:00 PM UTC ===
            if (now()->hour >= 12 && ! Cache::has('notified_pending_today')) {
                try {
                    \Artisan::call('app:notify-request-changes');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command app:notify-request-changes failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:notify-fan-content-changes');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command app:notify-fan-content-changes failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:notify-poll-option-changes');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command app:notify-poll-option-changes failed: '.$e->getMessage());
                    report($e);
                }
                Cache::put('notified_pending_today', true, now()->endOfDay());
            }

        } finally {
            $lock->release();
        }

        return response()->json(['success' => true]);
    }

    public function robots()
    {
        $disallowRules = "User-agent: *\nDisallow: /login\nDisallow: /sign_up\nDisallow: /reset-password\nDisallow: /update-password\nDisallow: /confirm-password\nDisallow: /verify-email\nDisallow: /two-factor-challenge\nDisallow: /auth/\nDisallow: /events\nDisallow: /settings\nDisallow: /checkout\nDisallow: /admin\n";

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
