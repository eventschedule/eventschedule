<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Role;
use App\Policies\EventPolicy;
use App\Policies\RolePolicy;
use App\Services\ScheduledTaskRecorder;
use App\Support\SafeTranslationLoader;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\GeoIpService::class);

        // Singleton so the driver instances and the config read behind
        // PaymentGatewayManager::all() are shared across a request. The sales table asks it about
        // every row on the page.
        $this->app->singleton(\App\Services\Payments\PaymentGatewayManager::class);

        // The override directory is operator-writable and the selfhost docs invite hand-edited
        // files into it, so a typo there would otherwise throw out of the bare require inside
        // FileLoader on the first __() of every request - including the admin translations page
        // that is the only way to fix the file. SafeTranslationLoader skips the unreadable file
        // instead. Extending rather than re-binding because 'translation.loader' is a DEFERRED
        // binding: TranslationServiceProvider registers it on demand and would overwrite a
        // straight bind(), while Container::dropStaleInstances() never clears extenders.
        $this->app->extend('translation.loader', function ($loader, $app) {
            return SafeTranslationLoader::wrap($loader, $app['files']);
        });

        $this->callAfterResolving('translator', function ($translator) {
            $translator->getLoader()->addPath(config('app.lang_overrides_path'));
        });
    }

    /**
     * Hosted mode spans multiple subdomains (app., schedule subdomains), so the
     * session cookie must cover the whole base domain. Default it when
     * SESSION_DOMAIN is not explicitly configured. ResolveCustomDomain overrides
     * this to null per-request for custom-domain requests.
     */
    public static function defaultHostedSessionDomain(): void
    {
        if (config('app.hosted')
            && config('app.env') !== 'local'
            && ! config('app.is_testing')
            && ! config('session.domain')
            && str_contains(_base_domain(), '.')
            && ! filter_var(_base_domain(), FILTER_VALIDATE_IP)) {
            config(['session.domain' => '.'._base_domain()]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! config('app.hosted') && empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        static::defaultHostedSessionDomain();

        // Register authorization policies
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        // Configure Cashier to use Role model for subscriptions
        Cashier::useCustomerModel(\App\Models\Role::class);

        $this->app->singleton('userRoles', function () {
            if ($user = auth()->user()) {
                return $user->roles()->get();
            }

            return collect();
        });

        if (config('app.is_testing')) {
            DB::enableQueryLog();

            $this->app->terminating(function () {
                $queries = DB::getQueryLog();
                $count = count($queries);
                $totalTime = array_sum(array_column($queries, 'time'));
                $method = request()->method();
                $url = request()->fullUrl();
                $timestamp = now()->format('Y-m-d H:i:s');

                $line = "[{$timestamp}] {$method} {$url} — {$count} queries ({$totalTime}ms)".PHP_EOL;
                file_put_contents(storage_path('logs/queries.log'), $line, FILE_APPEND);
            });
        }

        View::composer(['layouts.navigation', 'home'], function ($view) {
            $allRoles = app('userRoles');
            $view->with([
                'schedules' => $allRoles
                    ->where('type', 'talent')
                    ->whereIn('pivot.level', ['owner', 'admin', 'viewer']),
                'venues' => $allRoles
                    ->where('type', 'venue')
                    ->whereIn('pivot.level', ['owner', 'admin', 'viewer']),
                'curators' => $allRoles
                    ->where('type', 'curator')
                    ->whereIn('pivot.level', ['owner', 'admin', 'viewer']),
                'hasCarpoolActivity' => auth()->check() && (function () {
                    if (session()->has('has_carpool_activity')) {
                        return session('has_carpool_activity');
                    }
                    $has = \App\Models\CarpoolOffer::where('user_id', auth()->id())->exists()
                        || \App\Models\CarpoolRequest::where('user_id', auth()->id())->exists();
                    session(['has_carpool_activity' => $has]);

                    return $has;
                })(),
            ]);
        });

        View::composer('layouts.app-admin', function ($view) {
            $allRoles = app('userRoles');
            $upgradeRole = $allRoles
                ->where('pivot.level', 'owner')
                ->first(fn ($role) => $role->actualPlanTier() === 'free');
            $view->with([
                'upgradeSubdomain' => $upgradeRole?->subdomain,
                'githubStars' => \App\Utils\GitHubUtils::getStars(),
            ]);
        });

        // Badge counts for every /admin queue waiting on an admin. The service gates
        // each count by install type (nexus / hosted) and memoizes for the request, so
        // the nav and the admin dashboard share one pass.
        View::composer('admin.partials._navigation', function ($view) {
            if (auth()->check() && auth()->user()->isAdmin()) {
                $view->with('adminAlertBadges', \App\Services\AdminAlertService::badges());
            }
        });

        // PlatformCurrency memoizes for the process. That is right for a web request, but a
        // queue worker lives for days: without this it would keep serving the currency that was
        // set when it booted, long after an admin changed it. Setting::set() clears the shared
        // cache across processes; only this static needs help.
        Queue::looping(function () {
            \App\Utils\PlatformCurrency::flush();
            \App\Utils\PlatformPricing::flush();
            \App\Models\LegalDocument::flush();
        });

        // Unsubscribe limiters keyed on the TOKEN, not the IP.
        //
        // ThrottleRequests::resolveRequestSignature() falls back to sha1(domain|ip) for a guest, so
        // an unprefixed per-IP limit on an unsubscribe route counts the mail PROVIDER, not the
        // reader: RFC 8058 one-click unsubscribes are POSTed by Gmail's and Outlook's egress hosts,
        // so the nth reader to press Unsubscribe within the window got a 429. A 429 on an
        // unsubscribe is what produces a spam complaint, which is the exact outcome both routes'
        // own comments say to avoid.
        //
        // The token is single-purpose and already unguessable, so keying on it is both tighter
        // (one person cannot burn anyone else's budget) and looser where it matters (a shared
        // egress IP is no longer a shared budget). Kept generous: repeating your own unsubscribe
        // is not abuse, and the write is idempotent.
        foreach (['audience_unsubscribe', 'newsletter_unsubscribe'] as $limiter) {
            RateLimiter::for($limiter, function ($request) {
                return Limit::perMinutes(2, 10)->by((string) $request->route('token'));
            });
        }

        // Scheduler heartbeat, read by AdminAlertService's scheduler_stalled row.
        //
        // CommandFinished rather than ScheduledTaskFinished: this has to tick even on the minutes
        // when nothing was due, or a quiet stretch would look identical to a dead scheduler. That
        // is the whole point of a dead-man's switch.
        //
        // A cache key rather than a Setting row because Setting::set() calls
        // Cache::forget('site_settings'), and busting the settings map once a minute to record a
        // liveness ping would cost far more than the ping is worth. It does mean the alert needs a
        // cache store shared between the worker and the web containers - CACHE_STORE=database on
        // hosted. On the file default the worker writes a key the web container cannot see, so the
        // alert would fire constantly; that is why the env var is a prerequisite, not a nicety.
        //
        // AppController::translateData() writes the same key, so an install on either rail - or
        // mid-cutover on both - feeds one signal.
        EventFacade::listen(CommandFinished::class, function (CommandFinished $event) {
            if ($event->command !== 'schedule:run') {
                return;
            }

            Cache::put('scheduler.last_run_at', now()->timestamp, now()->addDay());

            // A SECOND key, per rail, rather than adding a "via" to the one above.
            //
            // The aggregate answers "is anything running" and three readers already treat it as a
            // bare int, so its shape stays put. But during the cutover both rails tick every
            // minute, so a single "which rail was last" field would just flap - and if the worker
            // died while the HTTP cron kept going, the aggregate would stay green and the stall
            // alert would never fire. That is the exact blindness this is meant to remove, so each
            // rail has to age independently.
            //
            // A week, not a day: a rail retired last month should disappear from the card, but one
            // that died yesterday must still be visible AS stale rather than silently absent.
            Cache::put('scheduler.last_run_at.'.config('app.scheduler_rail', 'cron'), now()->timestamp, now()->addDays(7));
        });

        // Per-task health for /admin/queue. Every handler is internally guarded - see the
        // ScheduledTaskRecorder docblock: the starting event is dispatched outside
        // ScheduleRunCommand's try/catch, so a throw here would kill the whole minute's run.
        EventFacade::listen(ScheduledTaskStarting::class, [ScheduledTaskRecorder::class, 'starting']);
        EventFacade::listen(ScheduledTaskFinished::class, [ScheduledTaskRecorder::class, 'finished']);
        EventFacade::listen(ScheduledTaskFailed::class, [ScheduledTaskRecorder::class, 'failed']);
        EventFacade::listen(ScheduledTaskSkipped::class, [ScheduledTaskRecorder::class, 'skipped']);

        View::composer('marketing.partials.header', function ($view) {
            $view->with('githubStars', \App\Utils\GitHubUtils::getStars());
        });

        // Single source for every plan price the marketing site quotes. These used to be
        // hardcoded in ~140 places across 50-odd pages, so changing STRIPE_PRICE_*_AMOUNT
        // moved /pricing and left the rest of the site advertising the old number. The
        // guard test in tests/Feature/MarketingPriceTest.php keeps it that way.
        // PlatformPricing, not config: a super-admin sets these at /admin/settings now, and
        // reading config here would move the panel's number everywhere except this composer.
        // referral.index is here for the same reason: it is the one AP view that hardcoded
        // the credit values rather than reading config.
        View::composer(['marketing.*', 'referral.index'], function ($view) {
            $view->with(\App\Utils\PlatformPricing::all());
        });

    }
}
