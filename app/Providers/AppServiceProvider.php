<?php

namespace App\Providers;

use App\Listeners\LogSentMessage;
use App\Models\Setting;
use App\Support\Logging\LoggingConfigManager;
use App\Support\MailConfigManager;
use App\Support\UpdateConfigManager;
use App\Support\WalletConfigManager;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
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
        if ($this->isBrowserTestingEnvironment()) {
            config([
                'app.is_testing' => true,
                'app.browser_testing' => true,
                'app.debug' => true,
                'app.load_vite_assets' => false,
                'debugbar.enabled' => false,
            ]);

            if ($this->app->bound('debugbar')) {
                $this->app->make('debugbar')->disable();
            }
        }

        Event::listen(MessageSent::class, LogSentMessage::class);
        Event::listen(NotificationSent::class, LogSentMessage::class);

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

        if (Schema::hasTable('settings')) {
            $generalSettings = Setting::forGroup('general');

            UpdateConfigManager::apply($generalSettings['update_repository_url'] ?? null);

            if (!empty($generalSettings['public_url'])) {
                config(['app.url' => $generalSettings['public_url']]);
                URL::forceRootUrl($generalSettings['public_url']);
            }

            $mailSettings = Setting::forGroup('mail');

            if (!empty($mailSettings)) {
                MailConfigManager::apply($mailSettings);
            }

            $loggingSettings = Setting::forGroup('logging');

            if (!empty($loggingSettings)) {
                LoggingConfigManager::apply($loggingSettings);
            }

            $appleWalletSettings = Setting::forGroup('wallet.apple');

            if (!empty($appleWalletSettings)) {
                WalletConfigManager::applyApple($appleWalletSettings);
            }

            $googleWalletSettings = Setting::forGroup('wallet.google');

            if (!empty($googleWalletSettings)) {
                WalletConfigManager::applyGoogle($googleWalletSettings);
            }
        }
    }

    private function isBrowserTestingEnvironment(): bool
    {
        if (env('BROWSER_TESTING')) {
            return true;
        }

        $flagPath = storage_path('framework/browser-testing.flag');

        return is_string($flagPath) && is_file($flagPath);
    }
}
