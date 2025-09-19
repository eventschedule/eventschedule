<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleContactsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.is_testing' => true,
            'app.hosted' => false,
        ]);
    }

    public function test_role_creation_stores_multiple_contacts(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('role.store'), [
            'type' => 'venue',
            'name' => 'Contact Venue',
            'email' => 'venue@example.com',
            'contacts' => [
                ['name' => 'Alice Example', 'email' => 'ALICE@example.com', 'phone' => '555-1000'],
                ['name' => '', 'email' => '', 'phone' => ''],
                ['name' => 'Bob Example', 'email' => 'bob@example.com'],
            ],
        ]);

        $role = Role::firstWhere('name', 'Contact Venue');

        $this->assertNotNull($role);

        $response->assertRedirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']));

        $this->assertCount(2, $role->contacts);
        $this->assertSame('alice@example.com', $role->contacts[0]['email']);
        $this->assertSame('555-1000', $role->contacts[0]['phone']);
        $this->assertSame('Bob Example', $role->contacts[1]['name']);
        $this->assertSame('bob@example.com', $role->contacts[1]['email']);
    }

    public function test_role_update_can_manage_contacts(): void
    {
        $user = User::factory()->create();

        $role = new Role([
            'type' => 'talent',
            'name' => 'Existing Role',
            'email' => 'role@example.com',
        ]);
        $role->subdomain = 'existing-role';
        $role->user_id = $user->id;
        $role->contacts = [
            ['name' => 'Original Contact', 'email' => 'original@example.com'],
        ];
        $role->save();

        $role->users()->attach($user->id, ['level' => 'owner']);

        $this->actingAs($user);

        $response = $this->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'type' => 'talent',
            'name' => 'Existing Role',
            'email' => 'role@example.com',
            'new_subdomain' => $role->subdomain,
            'contacts' => [
                ['name' => 'New Contact', 'email' => 'new@example.com', 'phone' => '555-2000'],
            ],
        ]);

        $response->assertRedirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']));

        $role->refresh();

        $this->assertCount(1, $role->contacts);
        $this->assertSame('new@example.com', $role->contacts[0]['email']);
        $this->assertSame('555-2000', $role->contacts[0]['phone']);

        $response = $this->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'type' => 'talent',
            'name' => 'Existing Role',
            'email' => 'role@example.com',
            'new_subdomain' => $role->subdomain,
            'contacts' => [],
        ]);

        $response->assertRedirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']));

        $role->refresh();

        $this->assertSame([], $role->contacts);
    }
}
