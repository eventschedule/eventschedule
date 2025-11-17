<?php

namespace App\Providers;

use App\Enums\ReleaseChannel;
use App\Listeners\HandleLogout;
use App\Listeners\HandleSuccessfulLogin;
use App\Listeners\LogSentMessage;
use App\Models\Image;
use App\Models\Setting;
use App\Policies\ImagePolicy;
use App\Services\Audit\AuditLogger;
use App\Services\Authorization\AuthorizationService;
use App\Support\BrandingManager;
use App\Support\Logging\LoggingConfigManager;
use App\Support\MailConfigManager;
use App\Support\ReleaseChannelManager;
use App\Support\UpdateConfigManager;
use App\Support\UrlUtilsConfigManager;
use App\Support\WalletConfigManager;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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
        $this->app->singleton(AuthorizationService::class, function ($app) {
            return new AuthorizationService();
        });

        $this->app->singleton(AuditLogger::class, function ($app) {
            return new AuditLogger();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Image::class, ImagePolicy::class);

        BrandingManager::apply();

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
        Event::listen(Login::class, HandleSuccessfulLogin::class);
        Event::listen(Logout::class, HandleLogout::class);

        if (! config('app.hosted') && empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
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

            $channel = ReleaseChannel::fromString($generalSettings['update_release_channel'] ?? config('self-update.release_channel'));
            ReleaseChannelManager::apply($channel);

            UpdateConfigManager::apply($generalSettings['update_repository_url'] ?? null);

            if (array_key_exists('url_utils_verify_ssl', $generalSettings)) {
                UrlUtilsConfigManager::apply($generalSettings['url_utils_verify_ssl']);
            }

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

            $brandingSettings = Setting::forGroup('branding');

            BrandingManager::apply($brandingSettings);
        }

        if (config('app.env') !== 'local') {
            $appUrl = config('app.url');

            if (is_string($appUrl) && parse_url($appUrl, PHP_URL_SCHEME) === 'https') {
                URL::forceScheme('https');
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
