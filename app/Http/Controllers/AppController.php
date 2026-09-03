<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\AppUpdateService;
use App\Utils\UrlUtils;
use Codedge\Updater\UpdaterManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AppController extends Controller
{
    /**
     * The Settings > App Update action.
     *
     * The route is registered on every install and authorized here instead, so a route-cached
     * selfhost cannot freeze a stale registration and so the section stays renderable under
     * phpunit (which pins IS_NEXUS=true). On a multi-tenant self-hosted SaaS the route also
     * carries the 'admin' middleware.
     *
     * Every exit lands back on Settings. The admin panel has its own action,
     * AdminController::appUpdateRun(), because an operator who started in /admin should not be
     * thrown out to their profile.
     */
    public function update(UpdaterManager $updater, AppUpdateService $appUpdate)
    {
        if (! can_self_update()) {
            abort(404);
        }

        if (is_demo_mode()) {
            return redirect()->to(route('profile.edit').'#section-app')->with('error', __('messages.demo_mode_restriction'));
        }

        $result = $appUpdate->performUpdate($updater);

        return redirect()->to(route('profile.edit').'#section-app')
            ->with($result['status'] === 'updated' ? 'message' : 'error', $result['message']);
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

        // is_string matters: request()->get() hands back whatever the query string parsed to, so
        // ?secret[]=x yields an ARRAY. An array is truthy, so it sails past the emptiness check and
        // hash_equals() then throws a TypeError - an uncaught 500, from anyone, no secret needed.
        if (! $serverSecret || ! is_string($requestSecret) || $requestSecret === ''
            || ! hash_equals($serverSecret, $requestSecret)) {
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
            // Every catch below is \Throwable, not \Exception.
            //
            // Each tier claims its cache key BEFORE running its commands, so an escaping error does
            // not just fail one command - it skips every command below it in that tier until the
            // key expires. A fatal \Error (a TypeError from a malformed API response, say) is
            // deterministic, so on the daily tier that means the same block silently skipped every
            // day, forever. It would also escape past the heartbeat at the end of this method,
            // which is stamped last precisely so it means "the whole chain ran".
            //
            // === EVERY CALL (every minute) ===

            // Process scheduled newsletters BEFORE queue:work so newly dispatched
            // SendNewsletterBatch jobs get processed by the queue:work below
            try {
                (new \App\Jobs\ProcessScheduledNewsletters)();
            } catch (\Throwable $e) {
                \Log::error('Scheduled command ProcessScheduledNewsletters failed: '.$e->getMessage());
                report($e);
            }

            // Process queued jobs (emails, etc.). --sleep=0: Worker::daemon() sleeps BEFORE
            // stopIfNecessary() evaluates stopWhenEmpty, so the default of 3 burns three seconds of
            // every cron request on an empty queue. Keep in sync with routes/console.php.
            try {
                \Artisan::call('queue:work', [
                    '--stop-when-empty' => true,
                    '--sleep' => 0,
                    '--max-time' => 120,
                    '--tries' => 3,
                ]);

                // Retry failed jobs. The per-job cap, the cooldown and the per-job error
                // handling all live in the command, shared with the crontab rail in
                // routes/console.php, so this rail's one-minute cadence and that rail's
                // five-minute cadence cannot produce two different retry budgets. Keep the
                // two callers in sync. The command runs its own queue:work when it actually
                // pushed something.
                \Artisan::call('app:retry-failed-jobs');
            } catch (\Throwable $e) {
                // error + report, like every other catch on this rail. This one covers the two
                // commands that run every single minute, so a persistent failure here is the most
                // consequential of the lot - and at warning level with no report() it never
                // reached Sentry at all.
                \Log::error('Queue processing failed: '.$e->getMessage());
                report($e);
            }

            // === EVERY 5 MINUTES ===
            if (! Cache::has('td_5min')) {
                Cache::put('td_5min', true, now()->addMinutes(5));

                if (config('app.hosted')) {
                    try {
                        \Artisan::call('app:sync-domain-statuses');
                    } catch (\Throwable $e) {
                        \Log::error('Scheduled command app:sync-domain-statuses failed: '.$e->getMessage());
                        report($e);
                    }
                }

                try {
                    \Artisan::call('app:sync-curator-sources');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:sync-curator-sources failed: '.$e->getMessage());
                    report($e);
                }
            }

            // === EVERY 15 MINUTES ===
            if (! Cache::has('td_15min')) {
                Cache::put('td_15min', true, now()->addMinutes(15));

                try {
                    \Artisan::call('caldav:sync');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command caldav:sync failed: '.$e->getMessage());
                    report($e);
                }

                try {
                    \Artisan::call('microsoft:sync');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command microsoft:sync failed: '.$e->getMessage());
                    report($e);
                }

                try {
                    \Artisan::call('google:sync');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command google:sync failed: '.$e->getMessage());
                    report($e);
                }

                if (\App\Services\MetaAdsService::isBoostConfigured()) {
                    try {
                        \Artisan::call('boost:sync');
                    } catch (\Throwable $e) {
                        \Log::error('Scheduled command boost:sync failed: '.$e->getMessage());
                        report($e);
                    }
                }
                try {
                    \Artisan::call('boost:expire-pending');
                } catch (\Throwable $e) {
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
                    } catch (\Throwable $e) {
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
                //
                // Hosted-gated to match routes/console.php. Without the gate a plain selfhost
                // driving cron through this endpoint mails onboarding nudges that the crontab rail
                // deliberately suppresses - and mail cannot be recalled.
                try {
                    if (config('app.hosted')) {
                        \Artisan::call('app:send-onboarding-nudges', ['--apply' => true]);
                    }
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
                // Keep in sync with routes/console.php. Not gated on config('app.hosted'): this
                // keeps a promise made to a guest, and on selfhost the subscribe panel is the only
                // capture surface a signed-out visitor sees. The per-schedule cadence floor
                // (usage.audience_announcement_min_hours) is what bounds how often anyone is
                // mailed; this tier only decides how soon a same-day publish is noticed.
                try {
                    \Artisan::call('app:send-event-announcements', ['--apply' => true]);
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:send-event-announcements failed: '.$e->getMessage());
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
                    // Shared with the crontab scheduler in routes/console.php. That entry point
                    // has withoutOverlapping(), but it only serialises the scheduler against
                    // itself - an install running both rails would otherwise have two charge
                    // loops against the same cards at once. Deliberately not translate_data_lock,
                    // which this method already holds around its whole chain.
                    $chargeLock = Cache::lock('app_charge_installments_lock', 300);

                    if ($chargeLock->get()) {
                        try {
                            // Bounded explicitly: this whole hourly block shares one 900s budget,
                            // and a synchronous run of Stripe charges is the one thing here that
                            // can consume it all and starve the commands queued behind. It
                            // resumes next hour.
                            // Budget comes from the signature's own {--max-seconds=120}, on both
                            // rails. Passing it here too was the one argument that differed.
                            \Artisan::call('app:charge-installments');
                        } finally {
                            $chargeLock->release();
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:charge-installments failed: '.$e->getMessage());
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

                // A lock scoped to app:translate ALONE, shared with the crontab scheduler in
                // routes/console.php. An install that runs both entry points otherwise buys every
                // translation in the overlap twice, because both order rows longest-waiting first.
                // Deliberately not translate_data_lock, which this method already holds around
                // its whole once-a-minute chain: borrowing that one would let a 240s translation
                // run block newsletters, queue:work and every reminder.
                $translateLock = Cache::lock('app_translate_lock', 600);

                if ($translateLock->get()) {
                    try {
                        \Artisan::call('app:translate', ['--max-seconds' => config('usage.translation_max_seconds', 240)]);
                        Cache::put('td_translate', true, now()->addMinutes(15));
                    } catch (\Throwable $e) {
                        \Log::error('Scheduled command app:translate failed: '.$e->getMessage());
                        report($e);
                    } finally {
                        $translateLock->release();
                    }
                }
            }

            // === DAILY ===
            if (! Cache::has('td_daily')) {
                Cache::put('td_daily', true, now()->endOfDay());

                // Keep in sync with routes/console.php. No-ops on nexus, where there is
                // nothing to self-update; it matters on a self-hosted SaaS that drives its
                // cron through this endpoint rather than through schedule:run.
                try {
                    \Artisan::call('app:check-version');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:check-version failed: '.$e->getMessage());
                    report($e);
                }

                // Republish the admin translation manager's overrides from the database onto this
                // container's disk. config('app.lang_overrides_path') defaults to storage/app/lang, which is
                // per-container and does not survive a deploy - so on any host with more than one container,
                // or any host that redeploys, a process that never republishes serves the base English strings
                // instead of the operator's edits. Cheap and idempotent: it rewrites files from rows.
                // Keep in sync with routes/console.php.
                // Passes --no-prune: publishAll()'s prune deletes any managed file not backed by a DB row, and
                // adoptFileOverrides() swallows a parse error and creates none - so a hand-made override file
                // with one typo would be deleted by cron within a day. Unattended runs write, never delete.
                try {
                    \Artisan::call('translations:publish', ['--no-prune' => true]);
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command translations:publish failed: '.$e->getMessage());
                    report($e);
                }
                // app:send-activation-nudges is deliberately NOT called here, and not in
                // routes/console.php either - the two rails stay in sync, and here that means
                // absent from both. It is hand-run until a real pass has been read; see the
                // note in routes/console.php.
                try {
                    \Artisan::call('google:refresh-webhooks');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command google:refresh-webhooks failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('microsoft:refresh-webhooks');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command microsoft:refresh-webhooks failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('audit:prune');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command audit:prune failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:cleanup-webhook-deliveries');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:cleanup-webhook-deliveries failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:cleanup-backups');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:cleanup-backups failed: '.$e->getMessage());
                    report($e);
                }
                // Not hosted-gated: a selfhost install with a YouTube key gets the same rot.
                try {
                    \Artisan::call('app:recheck-video-embeds');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:recheck-video-embeds failed: '.$e->getMessage());
                    report($e);
                }

                if (config('app.hosted')) {
                    try {
                        \Artisan::call('app:generate-sub-audience-blog');
                    } catch (\Throwable $e) {
                        \Log::error('Scheduled command app:generate-sub-audience-blog failed: '.$e->getMessage());
                        report($e);
                    }
                    try {
                        \Artisan::call('app:generate-daily-blog-post');
                    } catch (\Throwable $e) {
                        \Log::error('Scheduled command app:generate-daily-blog-post failed: '.$e->getMessage());
                        report($e);
                    }
                    try {
                        \Artisan::call('app:send-subscription-reminders');
                    } catch (\Throwable $e) {
                        \Log::error('Scheduled command app:send-subscription-reminders failed: '.$e->getMessage());
                        report($e);
                    }
                    try {
                        \Artisan::call('app:process-referral-credits');
                    } catch (\Throwable $e) {
                        \Log::error('Scheduled command app:process-referral-credits failed: '.$e->getMessage());
                        report($e);
                    }
                }

                if (! config('app.hosted')) {
                    try {
                        \Artisan::call('app:import-curator-events');
                    } catch (\Throwable $e) {
                        \Log::error('Scheduled command app:import-curator-events failed: '.$e->getMessage());
                        report($e);
                    }
                }
            }

            // === DAILY AT 12:00 PM UTC ===
            if (now()->hour >= 12 && ! Cache::has('notified_pending_today')) {
                try {
                    \Artisan::call('app:notify-request-changes');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:notify-request-changes failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:notify-fan-content-changes');
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:notify-fan-content-changes failed: '.$e->getMessage());
                    report($e);
                }
                try {
                    \Artisan::call('app:notify-poll-option-changes');
                } catch (\Throwable $e) {
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
                } catch (\Throwable $e) {
                    \Log::error('Scheduled command app:update-geoip failed: '.$e->getMessage());
                    report($e);
                }
            }

            // Scheduler heartbeat, the same key AppServiceProvider writes on every schedule:run
            // tick, so AdminAlertService's scheduler_stalled row reads one signal whichever rail
            // an install is on - or both, mid-cutover.
            //
            // Deliberately the last statement of the try, NOT the finally. The try opens right
            // after the lock is taken, so a finally would stamp "the scheduler is alive" for a
            // request that died on its very first statement - which is precisely the outage a
            // dead-man's switch exists to report. Reaching this line means the whole chain ran.
            Cache::put('scheduler.last_run_at', now()->timestamp, now()->addDay());

            // Per-rail sibling key, so /admin can tell a live worker from a live HTTP cron. This
            // rail is always 'http'; the scheduler rail names itself via config('app.scheduler_rail').
            Cache::put('scheduler.last_run_at.http', now()->timestamp, now()->addDays(7));
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

    /**
     * The web app manifest, which is per host rather than per install.
     *
     * Served from a route rather than as public/manifest.webmanifest because it cannot be one
     * file: layouts/app.blade.php is the shell for the guest portal as well as the admin portal,
     * so a single static manifest naming "Event Schedule" with our logo turned every schedule's
     * site into an installable app branded as OURS. Android honours that - once a visitor adds
     * the schedule to their home screen, every link they open on that host is handed to the
     * installed app, which shows its launch splash first: our 512px logo on white, for a couple
     * of seconds, every time. A schedule owner's audience has no idea who we are.
     *
     * The historic /manifest.webmanifest path is kept on purpose. Home-screen apps installed
     * while the static file was live keep polling that URL, so they re-brand themselves to the
     * schedule on their next update check instead of carrying our logo indefinitely. A new path
     * would leave every existing install exactly as it is today.
     *
     * $subdomain is bound by the guest routes only. A custom domain reaches them too, because
     * ResolveCustomDomain rewrites the host to {subdomain}.{base} before routing.
     */
    public function manifest(?string $subdomain = null)
    {
        $manifest = $subdomain
            ? $this->scheduleManifest($subdomain)
            : $this->platformManifest();

        return response()->json($manifest, 200, [
            // Set before JsonResponse would apply its own, so this wins.
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * The manifest for one schedule's guest portal.
     *
     * start_url and scope are relative so the same document works on all three URL shapes
     * without knowing which it is on: resolved against the manifest's own URL they give "/" on a
     * subdomain and on a custom domain, and "/{subdomain}/" on a path-routed selfhost install.
     * An absolute URL would have to be rewritten per host, and ResolveCustomDomain only rewrites
     * text/html and application/json bodies - not application/manifest+json.
     */
    private function scheduleManifest(string $subdomain): array
    {
        // Filtered like its siblings in this route group: SitemapController uses
        // claimed()->where('is_deleted', false) and RoleController::viewGuest redirects an
        // unclaimed schedule away. Unfiltered, a placeholder a curator created - which has no
        // guest page at all - still answered 200 with its name and logo URL, cached publicly
        // for an hour and enumerable by subdomain.
        $role = Role::subdomain($subdomain)
            ->claimed()
            ->where('is_deleted', false)
            ->first();

        if (! $role) {
            abort(404);
        }

        $name = $role->translatedName() ?: $subdomain;

        $themeColor = $role->manifestThemeColor();

        $manifest = [
            'name' => $name,
            'short_name' => Str::limit($name, 12, ''),
            'start_url' => './',
            'scope' => './',
            // Advertised but deliberately NOT installable. Chrome only offers an install, and
            // only mints a WebAPK, when the effective display mode is fullscreen, standalone,
            // minimal-ui or window-controls-overlay - and it resolves that from the first
            // supported entry in display_override BEFORE it looks at display. 'browser' there
            // fails the check, so no home-screen app is created, which is what actually matters:
            // a WebAPK claims every link on the host and shows its launch splash before the page.
            // Chrome's plain "Add to Home screen" shortcut still works and opens in a tab.
            //
            // display stays 'standalone' because Safari ignores display_override and reads this:
            // iOS only exposes the Push API inside a Home Screen web app, and the ticket
            // confirmation page offers exactly that opt-in (partials/push-optin). Setting
            // display itself to 'browser' would silently kill push for iPhone ticket buyers,
            // and iOS was never the problem - it does no link capturing, which is why the splash
            // only ever showed on Android.
            //
            // Nothing may add another entry to display_override: a 'minimal-ui' in front of this
            // would re-open the hole with display untouched. GuestManifestTest pins both halves.
            'display' => 'standalone',
            'display_override' => ['browser'],
            // The ground a launch splash paints its icon on. That reaches stale Android WebAPKs
            // from before v1.0.124, and new iOS installs, which the display above deliberately
            // still allows. White is what made it read as a blank page that had failed to load,
            // which is how the owner who reported this described it.
            //
            // Coloured only when the icon on it is the SCHEDULE's. A schedule with no logo
            // advertises no icons at all (see below), so a WebAPK minted while the static
            // manifest was live has nothing to re-brand to and keeps OUR mark - and our mark on
            // their accent is a stranger artifact than our mark on white. accent_color is also
            // NOT NULL with a '#007bff' default, so it is a colour they chose only when they
            // chose one; this is not a claim that every schedule's splash is now theirs.
            'background_color' => ($role->profile_image_url && $themeColor) ? $themeColor : '#ffffff',
            'lang' => $role->language_code,
            'dir' => $role->isRtl() ? 'rtl' : 'ltr',
        ];

        // Omitted rather than defaulted: falling back to our brand blue would tint the mobile
        // address bar on a page that is meant to carry no branding of ours. The matching
        // <meta name="theme-color"> in partials/web-app-manifest.blade.php reads the same
        // accessor, so the tag and this document cannot disagree - they used to.
        if ($themeColor) {
            $manifest['theme_color'] = $themeColor;
        }

        // The schedule's own logo or nothing at all - never /images/logo.png, which is the whole
        // bug. Uploads are stored as-is (RoleController::update does no resizing), so the real
        // dimensions are unknown and "any" is the only honest value. A schedule with no logo
        // advertises no icons for the same reason. Do not rely on "any" to prevent an install:
        // Chromium treats it as satisfying every size requirement, so the earlier hope here that a
        // browser would "decline to treat the page as installable" was never true - display_override
        // is what does that now. These fields are still worth serving because a WebAPK minted while
        // the static manifest was live re-brands itself off them on its next update check, which is
        // the only reason the historic path is still served at all.
        if ($role->profile_image_url) {
            $manifest['icons'] = [[
                'src' => $role->profile_image_url,
                'sizes' => 'any',
                'purpose' => 'any',
            ]];
        }

        return $manifest;
    }

    /**
     * The manifest for the platform's own surfaces - the admin portal, and the apex.
     *
     * This is where the Event Schedule identity belongs. On a selfhost or a self-hosted SaaS the
     * install is the operator's, so it carries their name and logo from config, the same way the
     * rest of the app does.
     */
    private function platformManifest(): array
    {
        $isNexus = config('app.is_nexus');

        return [
            'name' => $isNexus ? 'Event Schedule' : config('app.name'),
            'short_name' => Str::limit($isNexus ? 'Schedule' : config('app.name'), 12, ''),
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#4E81FA',
            'icons' => $isNexus
                ? [
                    ['src' => '/images/apple-touch-icon.png', 'sizes' => '180x180', 'type' => 'image/png', 'purpose' => 'any'],
                    ['src' => '/images/logo.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                    ['src' => '/images/logo.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ]
                : [
                    ['src' => config('app.logo_light'), 'sizes' => 'any', 'purpose' => 'any'],
                ],
        ];
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
