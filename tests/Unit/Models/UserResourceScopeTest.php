<?php

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use App\Models\SystemRole;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserResourceScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function createScopedManager(array $attributes = []): User
    {
        $permission = Permission::factory()->create(['key' => 'resources.manage']);
        $systemRole = SystemRole::factory()->create(['slug' => 'admin']);
        $systemRole->permissions()->attach($permission);

        $user = User::factory()->create($attributes);
        $user->systemRoles()->attach($systemRole);

        app(AuthorizationService::class)->warmUserPermissions($user);

        return $user;
    }

    public function test_user_can_manage_resources_within_scope(): void
    {
        $allowedVenue = Role::factory()->ofType('venue')->create();
        $blockedVenue = Role::factory()->ofType('venue')->create();

        $user = $this->createScopedManager([
            'venue_scope' => 'selected',
            'venue_ids' => [$allowedVenue->id],
        ]);

        $this->assertTrue($user->canManageResource($allowedVenue));
        $this->assertFalse($user->canManageResource($blockedVenue));
    }

    public function test_visible_roles_query_respects_scope_and_membership(): void
    {
        $visible = Role::factory()->ofType('talent')->create();
        $hidden = Role::factory()->ofType('talent')->create();

        $user = $this->createScopedManager([
            'talent_scope' => 'selected',
            'talent_ids' => [$visible->id],
        ]);

        $roleIds = $user->visibleRolesQuery('talent')->pluck('id');

        $this->assertTrue($roleIds->contains($visible->id));
        $this->assertFalse($roleIds->contains($hidden->id));
    }
}
