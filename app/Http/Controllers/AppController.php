<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Utils\UrlUtils;
use Codedge\Updater\UpdaterManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AppController extends Controller
{
    public function update(UpdaterManager $updater)
    {
        if (config('app.is_nexus')) {
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

        // The default max_execution_time is far shorter than this chain needs, so raise it to match
        // the lock below. Deliberately not 0: many PHP-FPM pools leave request_terminate_timeout
        // unset, so "no limit" really would mean a stuck request pinning a worker forever. This is
        // only a backstop either way - PHP-FPM and the web server's proxy read timeout can still cut
        // the request short, which is why every long-running command carries its own budget.
        @set_time_limit(900);

        // Must outlive the slowest tier below (app:translate is budgeted at 240s) or the next
        // minute's cron tick acquires the lock and runs a second copy alongside this one.
        $lock = Cache::lock('translate_data_lock', 900);
        if (! $lock->get()) {
            return response()->json(['message' => 'Already running'], 200);
        }

        try {
            // === EVERY CALL (every minute) ===

            // Process scheduled newsletters BEFORE queue:work so newly dispatched
            // SendNewsletterBatch jobs get processed by the queue:work below
            try {
                (new \App\Jobs\ProcessScheduledNewsletters)();
            } catch (\Exception $e) {
                \Log::error('Scheduled command ProcessScheduledNewsletters failed: '.$e->getMessage());
                report($e);
            }

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

                try {
                    \Artisan::call('app:sync-curator-sources');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command app:sync-curator-sources failed: '.$e->getMessage());
                    report($e);
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

                try {
                    \Artisan::call('microsoft:sync');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command microsoft:sync failed: '.$e->getMessage());
                    report($e);
                }

                try {
                    \Artisan::call('google:sync');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command google:sync failed: '.$e->getMessage());
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

                // Gated separately from boost:sync: an operator can run the on-network
                // promotions engine without configuring Meta at all.
                //
                // The gate is the deploy-time master switch, NOT PromotionService::isEnabled().
                // The command settles and refunds prepaid campaigns, so gating it on "is the
                // network serving" would let switching the network off strand advertisers'
                // money. SyncPromotions::handle() reads isEnabled() itself to decide which
                // steps still apply.
                if (\App\Services\AdsService::isEnabled()) {
                    try {
                        \Artisan::call('promo:sync');
                    } catch (\Exception $e) {
                        \Log::error('Scheduled command promo:sync failed: '.$e->getMessage());
                        report($e);
                    }
                }
            }

            // === HOURLY ===
            if (! Cache::has('td_hourly')) {
                Cache::put('td_hourly', true, now()->addHour());

                // Catch \Throwable (not just \Exception) so a fatal \Error in one
                // command cannot abort the rest of the hourly block. td_hourly is set
                // up-front, so a mid-chain kill skips everything after it until the next
                // hour - which is why the slow external-API-bound commands run last, and
                // why app:translate has its own tier below rather than sitting in here.
                try {
                    \Artisan::call('app:release-tickets');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:release-tickets failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:expire-waitlist');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:expire-waitlist failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:send-graphic-emails');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:send-graphic-emails failed: '.$e->getMessage());
                    report($e);
                }
                // In the hourly block, not the daily one: the first nudge is due an hour
                // after signup and the window it targets closes fast.
                try {
                    \Artisan::call('app:send-onboarding-nudges', ['--apply' => true]);
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:send-onboarding-nudges failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:send-feedback-requests');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:send-feedback-requests failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:send-carpool-reminders');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:send-carpool-reminders failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:send-appointment-reminders');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:send-appointment-reminders failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('federation:push');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command federation:push failed: '.$e->getMessage());
                    report($e);
                }

                if (config('app.hosted')) {
                    try {
                        \Artisan::call('app:setup-demo');
                    } catch (\Throwable $e) {
                        \Log::error('Scheduled command app:setup-demo failed: '.$e->getMessage());
                        report($e);
                    }
                }

                // Run the slow one last: federation:maintain downloads images and deletes
                // from object storage, so a timeout in it must not starve the commands above.
                try {
                    \Artisan::call('federation:maintain');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command federation:maintain failed: '.$e->getMessage());
                    report($e);
                }
            }

            // === TRANSLATION (every 15 minutes) ===
            // Its own tier, not part of the hourly block: app:translate is the slowest command
            // here (an AI call plus a cooldown per row) and it used to sit near the end of the
            // hourly chain, so any request killed earlier in that chain silently skipped
            // translation for a whole hour - long enough that newer schedules were never reached.
            //
            // Claim a short window up-front so concurrent minute-ticks cannot double-run, then
            // extend it to the full interval only once the command actually returns. A run killed
            // mid-flight therefore retries on the next tick instead of losing the interval, and
            // because the command orders rows longest-waiting first, each partial run still
            // advances the queue.
            if (! Cache::has('td_translate')) {
                Cache::put('td_translate', true, now()->addMinutes(5));

                try {
                    \Artisan::call('app:translate', ['--max-seconds' => config('usage.translation_max_seconds', 240)]);
                    Cache::put('td_translate', true, now()->addMinutes(15));
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:translate failed: '.$e->getMessage());
                    report($e);
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
                    \Artisan::call('microsoft:refresh-webhooks');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command microsoft:refresh-webhooks failed: '.$e->getMessage());
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

            // === MONTHLY ===
            if (! Cache::has('td_monthly')) {
                Cache::put('td_monthly', true, now()->addMonth());

                try {
                    \Artisan::call('app:update-geoip');
                } catch (\Exception $e) {
                    \Log::error('Scheduled command app:update-geoip failed: '.$e->getMessage());
                    report($e);
                }
            }

        } finally {
            $lock->release();
        }

        return response()->json(['success' => true]);
    }

    public function robots()
    {
        // /appointment/ carries the booking secret in the path, so it must never be crawled or indexed.
        // The pages themselves also send noindex; this stops the URL being fetched at all.
        // /promo/ is a click-counting redirect, so crawling it would spend advertisers'
        // budgets on traffic that was never a person.
        $disallowRules = "User-agent: *\nDisallow: /login\nDisallow: /sign_up\nDisallow: /reset-password\nDisallow: /update-password\nDisallow: /confirm-password\nDisallow: /verify-email\nDisallow: /two-factor-challenge\nDisallow: /auth/\nDisallow: /events\nDisallow: /settings\nDisallow: /checkout\nDisallow: /appointment/\nDisallow: /promo/\nDisallow: /admin\n";

        $isAppSubdomain = config('app.hosted') && str_starts_with(request()->getHost(), 'app.');

        // sitemap_url() is host-aware: a custom domain points at its own sitemap, because Google
        // rejects a third-party host inside the global one ("URL not allowed") whatever robots.txt
        // says. The same helper builds the <link rel="sitemap"> tag in layouts/app.blade.php, so
        // the two can never disagree on a page served from this host.
        $content = $isAppSubdomain
            ? $disallowRules
            : $disallowRules."\nSitemap: ".sitemap_url()."\n# AI/LLM-friendly docs: ".config('app.url')."/llms.txt\n";

        return response($content, 200)->header('Content-Type', 'text/plain');
    }

    public function mapImage(Request $request)
    {
        $id = $request->route('id');

        $apiKey = config('services.google.backend');
        if (! $apiKey) {
            abort(404);
        }

        $roleId = UrlUtils::decodeIdOrFail($id);
        $role = Role::find($roleId);

        if (! $role || ! $role->geo_lat || ! $role->geo_lon) {
            abort(404);
        }

        $allowedSizes = ['600x200', '600x400'];
        $size = request('size', '600x200');
        if (! in_array($size, $allowedSizes)) {
            $size = '600x200';
        }

        $cacheDir = storage_path('app/map_cache');
        $cachePath = $cacheDir.'/'.$roleId.'_'.$size.'.png';
        $cacheTtl = 30 * 24 * 60 * 60; // 30 days

        if (file_exists($cachePath) && (time() - filemtime($cachePath)) < $cacheTtl) {
            return response()->file($cachePath, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        $url = 'https://maps.googleapis.com/maps/api/staticmap?'.http_build_query([
            'center' => $role->geo_lat.','.$role->geo_lon,
            'zoom' => 15,
            'size' => $size,
            'scale' => 2,
            'markers' => 'color:red|'.$role->geo_lat.','.$role->geo_lon,
            'key' => $apiKey,
        ]);

        try {
            $response = Http::timeout(10)->get($url);

            if (! $response->successful()) {
                abort(404);
            }

            if (! is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }

            file_put_contents($cachePath, $response->body());

            return response($response->body(), 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        } catch (\Exception $e) {
            report($e);
            abort(404);
        }
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
