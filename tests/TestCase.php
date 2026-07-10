<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Must run BEFORE parent::setUp(): booting the app fires RefreshDatabase
        // (migrate:fresh), which would wipe whatever database is configured.
        if (! str_ends_with((string) getenv('DB_DATABASE'), '_test')) {
            self::fail('Refusing to run: DB_DATABASE must be a dedicated *_test database (see phpunit.xml). Got: '.getenv('DB_DATABASE'));
        }

        parent::setUp();

        $this->withoutVite();
    }
}
