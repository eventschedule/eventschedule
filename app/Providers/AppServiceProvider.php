<?php

namespace App\Providers;

use App\Listeners\LogSentMessage;
use App\Models\Setting;
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
                if (!empty($mailSettings['mailer'])) {
                    config(['mail.default' => $mailSettings['mailer']]);
                }

                if (array_key_exists('host', $mailSettings) && $mailSettings['host'] !== null) {
                    config(['mail.mailers.smtp.host' => $mailSettings['host']]);
                }

                if (array_key_exists('port', $mailSettings) && $mailSettings['port'] !== null) {
                    config(['mail.mailers.smtp.port' => (int) $mailSettings['port']]);
                }

                if (array_key_exists('username', $mailSettings) && $mailSettings['username'] !== null) {
                    config(['mail.mailers.smtp.username' => $mailSettings['username']]);
                }

                if (array_key_exists('password', $mailSettings) && $mailSettings['password'] !== null) {
                    config(['mail.mailers.smtp.password' => $mailSettings['password']]);
                }

                if (array_key_exists('encryption', $mailSettings)) {
                    config(['mail.mailers.smtp.encryption' => $mailSettings['encryption'] ?: null]);
                }

                if (!empty($mailSettings['from_address'])) {
                    config(['mail.from.address' => $mailSettings['from_address']]);
                }

                if (!empty($mailSettings['from_name'])) {
                    config(['mail.from.name' => $mailSettings['from_name']]);
                }
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
}
