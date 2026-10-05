<?php

// require_once, not require: PHPUnit's Composer binary has already loaded the autoloader.
require_once __DIR__.'/../vendor/autoload.php';

// phpunit.xml's force="true" rewrites getenv() and $_ENV, but NOT $_SERVER - and Laravel's Env reads
// $_SERVER first, so an exported shell variable silently outranks a pinned one and a forced <env> is
// not actually forced. It does already beat a value in .env: the pin makes Env::getRepository()->has()
// true, and Laravel loads .env through an immutable writer that skips anything already set.
//
// Mirror the pinned values across so both routes are covered. Same window as the note below - after
// phpunit.xml is applied, before the app boots. DB_DATABASE is deliberately absent: TestDatabase
// below owns it and must have the last word.
foreach ([
    'APP_URL',
    'STRIPE_KEY',
    'STRIPE_PLATFORM_SECRET',
    'PAYFAST_MERCHANT_ID',
    'PAYFAST_MERCHANT_KEY',
    'PAYFAST_PASSPHRASE',
    'PAYPAL_CLIENT_ID',
    'PAYPAL_CLIENT_SECRET',
    'PAYPAL_WEBHOOK_ID',
    'DEFAULT_PAYMENT_METHOD',
    'MARKETING_WALL_CACHE_SECONDS',
    'SESSION_LIFETIME',
    'GROWTH_DATA_TOKEN',
    'BACKEND_GOOGLE_KEY',
    'ADMIN_REQUIRE_2FA',
] as $pinned) {
    if (array_key_exists($pinned, $_ENV)) {
        $_SERVER[$pinned] = $_ENV[$pinned];
    }
}

// Without opcache, PHP keeps part of every closure it compiles for the rest of the process, and each
// test's app boot re-requires routes/web.php and its ~450 route closures - ~130KB leaked per test.
// The full suite then dies near the end with an OOM "in routes/web.php", which points nowhere near
// the cause. opcache.enable_cli is PHP_INI_SYSTEM, so this can only warn; see CLAUDE.md.
if (! filter_var(ini_get('opcache.enable_cli'), FILTER_VALIDATE_BOOLEAN)) {
    fwrite(STDERR, "Warning: opcache.enable_cli is off, so the full suite will exhaust memory_limit (~130KB leaked per test). Set opcache.enable_cli=1 in php.ini - see CLAUDE.md.\n");
}

// Must run here, not in TestCase: PHPUnit loads this after applying phpunit.xml's <env> block but
// before any test boots the app, which is the only window where DB_DATABASE can still be redirected.
Tests\TestDatabase::bootstrap(__DIR__.'/..');
