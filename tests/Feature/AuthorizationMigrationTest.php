<?php

namespace Tests\Feature;

use App\Models\SystemRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_system_roles_are_seeded_during_migrations(): void
    {
        $this->assertGreaterThanOrEqual(1, SystemRole::count());
    }
}
