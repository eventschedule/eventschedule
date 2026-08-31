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
use App\Http\Middleware\TrackMarketingVisit;
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

        // cookie-consent.js writes this one from the browser, so Laravel has nothing to
        // decrypt; without the exemption $request->cookie('cookie_consent') is always null
        // and CaptureUtmParameters can never see that consent was granted. The value is a
        // public 'granted'/'denied' enum, so leaving it in the clear costs nothing.
        $middleware->encryptCookies(except: [
            'cookie_consent',
        ]);

        $middleware->validateCsrfTokens(except: [
            'google-calendar/webhook',
            'microsoft-calendar/webhook',
            'stripe/webhook',
            'stripe/subscription-webhook',
            'invoiceninja/webhook/*',
            'invoiceninja/purchase-webhook/*',
            'invoiceninja/event-purchase-webhook/*',
            // One exemption covering every gateway, present and future. The return/cancel paths are
            // here too because a gateway that posts the buyer back cannot carry our CSRF token; both
            // handlers verify the sale out of the signed URL rather than trusting the request.
            // Both shapes: the sale segment is optional, for gateways whose callback is registered
            // once per merchant account rather than per payment.
            'payments/*/webhook',
            'payments/*/webhook/*',
            'payments/*/return/*',
            'payments/*/cancel/*',
            'test_database',
            'nl/u/*',
            // RFC 8058 one-click unsubscribe: a mail client's POST carries no session and no token.
            'sub/u/*',
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
            TrackMarketingVisit::class,
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
