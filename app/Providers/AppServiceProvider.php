<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
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

        $this->app->singleton('userRoles', function () {
            if ($user = auth()->user()) {
                return $user->roles()->get();
            }
            return collect();
        });
    
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
        
    }
}
