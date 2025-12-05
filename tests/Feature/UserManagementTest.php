<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\SystemRole;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function createManagerWithPermission(string $permissionKey = 'users.manage'): User
    {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role = SystemRole::factory()->create(['slug' => 'admin']);
        $role->permissions()->attach($permission);

        $user = User::factory()->create();
        $user->systemRoles()->attach($role);

        // Ensure cached permissions are populated for middleware checks
        app(AuthorizationService::class)->warmUserPermissions($user);

        return $user;
    }

    public function test_admin_can_create_user_through_management_endpoints(): void
    {
        $admin = $this->createManagerWithPermission();

        $response = $this->actingAs($admin)->post(route('settings.users.store'), [
            'name' => 'Managed User',
            'email' => 'managed@example.com',
            'password_mode' => 'set',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'timezone' => 'America/New_York',
            'language_code' => 'en',
            'status' => 'active',
            'roles' => [$admin->systemRoles->first()->id],
            'venue_scope' => 'all',
            'curator_scope' => 'all',
            'talent_scope' => 'all',
        ]);

        $response->assertRedirect(route('settings.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'managed@example.com',
            'status' => 'active',
        ]);

        $managedUser = User::where('email', 'managed@example.com')->first();
        $this->assertNotNull($managedUser);
        $this->assertTrue($managedUser->systemRoles()->where('auth_roles.id', $admin->systemRoles->first()->id)->exists());
    }

    public function test_unauthorized_users_cannot_access_user_management(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('settings.users.index'));

        $response->assertForbidden();
    }
}
