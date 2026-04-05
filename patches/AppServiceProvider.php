<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Schema::defaultStringLength(191);

        if (env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        if ($this->app->runningInConsole()) {
            return;
        }

        if (!class_exists(\App\Models\Setting::class)) {
            return;
        }

        try {
            if (Schema::hasTable('settings')) {
                $settings = \App\Models\Setting::query()
                    ->get(['name', 'value'])
                    ->pluck('value', 'name')
                    ->toArray();

                foreach ($settings as $key => $value) {
                    config(["settings.$key" => $value]);
                }

                View::share('globalSettings', $settings);
            }
        } catch (\Throwable $e) {
            // During image build we do not have a database, so swallow errors.
            return;
        }
    }
}
