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
                return $user->roles()->get();
            }
            return collect();
        });
    
        View::composer('layouts.navigation', function ($view) {
            $allRoles = app('userRoles');
            $view->with([
                'isFollowingVenues' => $allRoles->where('type', 'venue')->where('pivot.level', 'follower')->count(),    
                'isFollowingTalent' => $allRoles->where('type', 'talent')->where('pivot.level', 'follower')->count(),
                'isFollowingVendors' => $allRoles->where('type', 'vendor')->where('pivot.level', 'follower')->count(),
                'isFollowingCurators' => $allRoles->where('type', 'curator')->where('pivot.level', 'follower')->count(),
                'venues' => $allRoles->where('type', 'venue')->whereIn('pivot.level', ['owner', 'admin']),    
                'talent' => $allRoles->where('type', 'talent')->whereIn('pivot.level', ['owner', 'admin']),
                'vendors' => $allRoles->where('type', 'vendor')->whereIn('pivot.level', ['owner', 'admin']),
                'curators' => $allRoles->where('type', 'curator')->whereIn('pivot.level', ['owner', 'admin']),
            ]);
        });
        
    }
}
