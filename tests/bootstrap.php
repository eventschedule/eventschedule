<?php

// require_once, not require: PHPUnit's Composer binary has already loaded the autoloader.
require_once __DIR__.'/../vendor/autoload.php';

// Must run here, not in TestCase: PHPUnit loads this after applying phpunit.xml's <env> block but
// before any test boots the app, which is the only window where DB_DATABASE can still be redirected.
Tests\TestDatabase::bootstrap(__DIR__.'/..');
