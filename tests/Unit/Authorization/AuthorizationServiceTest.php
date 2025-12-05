<?php

namespace Tests\Unit\Authorization;

use App\Models\Permission;
use App\Models\SystemRole;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_permissions_are_warmed_and_cached_per_user(): void
    {
        $permission = Permission::factory()->create(['key' => 'resources.manage']);
        $role = SystemRole::factory()->create(['slug' => 'admin']);
        $role->permissions()->attach($permission);

        $user = User::factory()->create();
        $user->systemRoles()->attach($role);

        $service = $this->app->make(AuthorizationService::class);

        $warmed = $service->warmUserPermissions($user);

        $this->assertContains('resources.manage', $warmed);
        $this->assertTrue($service->userHasPermission($user, 'resources.manage'));

        $service->forgetUserPermissions($user);
        $user->systemRoles()->detach($role);

        $this->assertFalse($service->userHasPermission($user, 'resources.manage'));
    }

    public function test_user_has_any_permission_accepts_empty_sets(): void
    {
        $user = User::factory()->create();
        $service = $this->app->make(AuthorizationService::class);

        $this->assertTrue($service->userHasAnyPermission($user, []));
    }
}
