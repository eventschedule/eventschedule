<?php

use App\Http\Middleware\CaptureUtmParameters;
use App\Http\Middleware\DemoAutoLogin;
use App\Http\Middleware\DetectTrailingSlash;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\HandleBotTraffic;
use App\Http\Middleware\RedirectIfAuthenticated;
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
        $middleware->validateCsrfTokens(except: [
            'google-calendar/webhook',
            'stripe/webhook',
            'invoiceninja/webhook/*',
            'test_database',
        ]);

        // Sanitize user agent before session middleware processes it
        $middleware->prepend(SanitizeUserAgent::class);

        // Detect trailing slash in URL before Laravel normalizes it
        $middleware->prepend(DetectTrailingSlash::class);

        $middleware->append(SecurityHeaders::class);

        $middleware->web(append: [
            CaptureUtmParameters::class,
            SetUserLanguage::class,
            EnsureEmailIsVerified::class,
            HandleBotTraffic::class,
            DemoAutoLogin::class,
        ]);

        $middleware->alias([
            'guest' => RedirectIfAuthenticated::class,
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
    })->create();
