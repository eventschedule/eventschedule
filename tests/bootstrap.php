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
    'MAP_GEOCODER_URL',
    'MAP_TILE_URL',
    'SESSION_LIFETIME',
    'GROWTH_DATA_TOKEN',
    'BACKEND_GOOGLE_KEY',
    'DO_API_TOKEN',
    'DO_APP_ID',
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

// On is not enough: opcache needs room. One that has filled up keeps what it holds and caches
// nothing more, without a word, so every file first required after that is compiled again each
// time it is required, and that is the same leak by a quieter road. PHPUnit loads every test file
// before it runs one, and those alone take ~104 MB of the default 128, so a full run (or any
// --filter, which loads them all as well) fills it within the first feature tests. On CI in
// 2026-10 routes/web.php stopped fitting and the run died at about test 5,150 with an OOM "in
// resources/lang/en/messages.php". Whether it filled is only known at the end, and the sizes are
// PHP_INI_SYSTEM like the switch above, so this says so then, after an OOM too: a shutdown
// function still runs. TestEnvironmentTest fails CI before it gets this far.
register_shutdown_function(static function (): void {
    $status = function_exists('opcache_get_status') ? @opcache_get_status(false) : false;

    if (! is_array($status) || empty($status['opcache_enabled']) || empty($status['cache_full'])) {
        return;
    }

    fwrite(STDERR, sprintf(
        "\nWarning: opcache filled up during this run (opcache.memory_consumption=%s MB: %d scripts kept, %d compiles), so files were compiled again every time they were required, which is slower and leaks toward memory_limit. Set opcache.memory_consumption=512 and opcache.interned_strings_buffer=64 in php.ini (CI: setup-php's ini-values in .github/workflows/test.yml) - see CLAUDE.md.\n",
        ini_get('opcache.memory_consumption'),
        $status['opcache_statistics']['num_cached_scripts'] ?? 0,
        $status['opcache_statistics']['misses'] ?? 0
    ));
});

// Must run here, not in TestCase: PHPUnit loads this after applying phpunit.xml's <env> block but
// before any test boots the app, which is the only window where DB_DATABASE can still be redirected.
Tests\TestDatabase::bootstrap(__DIR__.'/..');
