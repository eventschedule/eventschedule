<?php

namespace App\Providers;

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
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }

        $this->app->singleton('userRoles', function () {
            if ($user = auth()->user()) {
                return $user->member()->get();
            }
            return collect();
        });
    
        View::composer('layouts.navigation', function ($view) {
            $allRoles = app('userRoles');
            $view->with([
                'venues' => $allRoles->where('type', 'venue'),
                'talent' => $allRoles->where('type', 'talent'),
                'vendors' => $allRoles->where('type', 'vendor'),
                'curators' => $allRoles->where('type', 'curator'),
            ]);
        });
        
    }
}
