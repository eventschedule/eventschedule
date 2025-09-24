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

    public function test_contacts_page_displays_role_contacts(): void
    {
        $user = User::factory()->create();

        $venue = new Role([
            'type' => 'venue',
            'name' => 'Concert Hall',
            'email' => 'venue@example.com',
        ]);
        $venue->subdomain = 'concert-hall';
        $venue->user_id = $user->id;
        $venue->contacts = [
            ['name' => 'Venue Contact', 'email' => 'venue-contact@example.com', 'phone' => '555-1000'],
        ];
        $venue->save();
        $venue->users()->attach($user->id, ['level' => 'owner']);

        $curator = new Role([
            'type' => 'curator',
            'name' => 'Event Collective',
            'email' => 'curator@example.com',
        ]);
        $curator->subdomain = 'event-collective';
        $curator->user_id = $user->id;
        $curator->contacts = [
            ['name' => 'Curator Contact', 'email' => 'curator-contact@example.com'],
        ];
        $curator->save();
        $curator->users()->attach($user->id, ['level' => 'owner']);

        $talent = new Role([
            'type' => 'talent',
            'name' => 'Headline Artist',
            'email' => 'talent@example.com',
        ]);
        $talent->subdomain = 'headline-artist';
        $talent->user_id = $user->id;
        $talent->contacts = [
            ['name' => 'Talent Contact', 'phone' => '555-2000'],
        ];
        $talent->save();
        $talent->users()->attach($user->id, ['level' => 'owner']);

        $this->actingAs($user);

        $response = $this->get(route('role.contacts'));

        $response->assertOk();
        $response->assertSeeText(__('messages.venues'));
        $response->assertSeeText(__('messages.curators'));
        $response->assertSeeText(__('messages.contacts'));
        $response->assertSeeText('Venue Contact');
        $response->assertSee('venue-contact@example.com');
        $response->assertSee('555-1000');
        $response->assertSeeText('Curator Contact');
        $response->assertSeeText('Talent Contact');
        $response->assertSee('555-2000');
    }
}
