<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Must run BEFORE parent::setUp(): booting the app fires RefreshDatabase
        // (migrate:fresh), which would wipe whatever database is configured.
        // Accepts the per-session schema tests/bootstrap.php hands out
        // (eventschedule_test_<token>); the dev database still fails. The predicate
        // lives in TestDatabase so TestDatabaseSchemaTest can pin it.
        if (! TestDatabase::isDedicatedTestSchema((string) getenv('DB_DATABASE'))) {
            self::fail('Refusing to run: DB_DATABASE must be a dedicated *_test database (see phpunit.xml and tests/bootstrap.php). Got: '.getenv('DB_DATABASE'));
        }

        parent::setUp();

        $this->withoutVite();

        // Its counts are memoized for the request; tests share a process, so a
        // previous test's totals would otherwise carry over.
        \App\Services\AdminAlertService::flush();
    }
}
