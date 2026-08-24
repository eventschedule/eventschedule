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
    'STRIPE_KEY',
    'STRIPE_PLATFORM_SECRET',
    'PAYFAST_MERCHANT_ID',
    'PAYFAST_MERCHANT_KEY',
    'PAYFAST_PASSPHRASE',
    'DEFAULT_PAYMENT_METHOD',
] as $pinned) {
    if (array_key_exists($pinned, $_ENV)) {
        $_SERVER[$pinned] = $_ENV[$pinned];
    }
}

// Must run here, not in TestCase: PHPUnit loads this after applying phpunit.xml's <env> block but
// before any test boots the app, which is the only window where DB_DATABASE can still be redirected.
Tests\TestDatabase::bootstrap(__DIR__.'/..');
