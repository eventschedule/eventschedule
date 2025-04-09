<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SetUserLanguage;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\HandleBotTraffic;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            SetUserLanguage::class,
            EnsureEmailIsVerified::class,
            HandleBotTraffic::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
    })->create();
