<?php

use App\Http\Middleware\CaptureUtmParameters;
use App\Http\Middleware\DemoAutoLogin;
use App\Http\Middleware\DetectTrailingSlash;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\EnsureSelfhostSetup;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\HandleBotTraffic;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\ResolveCustomDomain;
use App\Http\Middleware\SanitizeUserAgent;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetUserLanguage;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trusted proxies are configured in config/trustedproxy.php (read at runtime by
        // Laravel's TrustProxies middleware) so the setting survives `php artisan config:cache`.
        // Reading env() here would return null once the config is cached, dropping proxy
        // trust and causing infinite redirect loops behind a reverse proxy / Cloudflare.

        $middleware->validateCsrfTokens(except: [
            'google-calendar/webhook',
            'stripe/webhook',
            'stripe/subscription-webhook',
            'invoiceninja/webhook/*',
            'invoiceninja/purchase-webhook/*',
            'test_database',
            'nl/u/*',
            'webhooks/meta',
            'api/whatsapp/webhook',
        ]);

        // Resolve custom domains before routing (must be first)
        $middleware->prepend(ResolveCustomDomain::class);

        // Sanitize user agent before session middleware processes it
        $middleware->prepend(SanitizeUserAgent::class);

        // Detect trailing slash in URL before Laravel normalizes it
        $middleware->prepend(DetectTrailingSlash::class);

        $middleware->append(SecurityHeaders::class);

        $middleware->authenticateSessions();

        $middleware->web(prepend: [
            // Runs before route-model binding so a selfhost install with no migrated DB
            // is redirected to the setup wizard before anything touches the database.
            EnsureSelfhostSetup::class,
        ], append: [
            CaptureUtmParameters::class,
            SetUserLanguage::class,
            EnsureEmailIsVerified::class,
            HandleBotTraffic::class,
            DemoAutoLogin::class,
        ]);

        $middleware->alias([
            'guest' => RedirectIfAuthenticated::class,
            'admin' => EnsureUserIsAdmin::class,
            'throttle' => \App\Http\Middleware\ThrottleRequests::class,
            'app_subdomain' => \App\Http\Middleware\RedirectToAppSubdomain::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
    })->create();
