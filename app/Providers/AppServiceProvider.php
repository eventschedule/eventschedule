<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Role;
use App\Policies\EventPolicy;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
        //
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
                    ->whereIn('pivot.level', ['owner', 'admin']),
                'venues' => $allRoles
                    ->where('type', 'venue')
                    ->whereIn('pivot.level', ['owner', 'admin']),
                'curators' => $allRoles
                    ->where('type', 'curator')
                    ->whereIn('pivot.level', ['owner', 'admin']),
            ]);
        });

        View::composer('layouts.app-admin', function ($view) {
            $allRoles = app('userRoles');
            $upgradeRole = $allRoles
                ->where('pivot.level', 'owner')
                ->first(fn ($role) => $role->actualPlanTier() === 'free');
            $view->with('upgradeSubdomain', $upgradeRole?->subdomain);
        });

    }
}
