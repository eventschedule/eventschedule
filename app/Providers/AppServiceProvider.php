<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Role;
use App\Policies\EventPolicy;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
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
            \App\Models\LegalDocument::flush();
        });

        View::composer('marketing.partials.header', function ($view) {
            $view->with('githubStars', \App\Utils\GitHubUtils::getStars());
        });

        // Single source for every plan price the marketing site quotes. These used to be
        // hardcoded in ~140 places across 50-odd pages, so changing STRIPE_PRICE_*_AMOUNT
        // moved /pricing and left the rest of the site advertising the old number. The
        // guard test in tests/Feature/MarketingPriceTest.php keeps it that way.
        // referral.index is here for the same reason: it is the one AP view that hardcoded
        // the credit values rather than reading config.
        View::composer(['marketing.*', 'referral.index'], function ($view) {
            $view->with([
                'proMonthly' => (int) config('services.stripe_platform.price_monthly_amount', 9),
                'proYearly' => (int) config('services.stripe_platform.price_yearly_amount', 90),
                'entMonthly' => (int) config('services.stripe_platform.enterprise_price_monthly_amount', 29),
                'entYearly' => (int) config('services.stripe_platform.enterprise_price_yearly_amount', 290),
            ]);
        });

    }
}
