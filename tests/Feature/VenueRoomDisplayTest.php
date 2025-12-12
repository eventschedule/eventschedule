<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VenueRoomDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_venue_room_displays_in_guest_event_view()
    {
        $user = User::factory()->create();
        $venue = Role::factory()->create([
            'type' => 'venue',
            'user_id' => $user->id,
            'name' => 'Test Venue',
            'address1' => '123 Main St',
            'address2' => 'Room 501',
            'city' => 'New York',
            'state' => 'NY',
        ]);

        $event = Event::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Event',
        ]);

        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $response = $this->get(route('event.view_guest', [
            'subdomain' => $venue->subdomain,
            'slug' => $event->slug
        ]));

        $response->assertStatus(200);
        $response->assertSee('Room 501');
    }

    public function test_venue_room_displays_in_admin_event_view()
    {
        $user = User::factory()->create();
        $venue = Role::factory()->create([
            'type' => 'venue',
            'user_id' => $user->id,
            'name' => 'Test Venue',
            'address1' => '123 Main St',
            'address2' => 'Suite 200',
            'city' => 'Los Angeles',
            'state' => 'CA',
        ]);

        $event = Event::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Event',
        ]);

        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $response = $this->actingAs($user)
            ->get(route('event.view', [
                'hash' => \App\Utils\UrlUtils::encodeId($event->id)
            ]));

        $response->assertStatus(200);
        $response->assertSee('Suite 200');
    }

    public function test_event_without_venue_room_does_not_show_room()
    {
        $user = User::factory()->create();
        $venue = Role::factory()->create([
            'type' => 'venue',
            'user_id' => $user->id,
            'name' => 'Test Venue',
            'address1' => '123 Main St',
            'address2' => null, // No room
            'city' => 'Chicago',
            'state' => 'IL',
        ]);

        $event = Event::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Event',
        ]);

        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $response = $this->get(route('event.view_guest', [
            'subdomain' => $venue->subdomain,
            'slug' => $event->slug
        ]));

        $response->assertStatus(200);
        // Should not have an extra line for room since it's not set
        $content = $response->getContent();
        $this->assertStringNotContainsString('text-gray-500 dark:text-gray-400', $content);
    }

    public function test_translated_venue_room_displays()
    {
        $user = User::factory()->create();
        $venue = Role::factory()->create([
            'type' => 'venue',
            'user_id' => $user->id,
            'name' => 'Test Venue',
            'address1' => '123 Main St',
            'address2' => 'Salle 42',
            'address2_en' => 'Room 42',
            'city' => 'Montreal',
            'state' => 'QC',
            'language_code' => 'fr',
        ]);

        $event = Event::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Event',
        ]);

        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        // Test with English translation
        $response = $this->withSession(['translate' => true])
            ->get(route('event.view_guest', [
                'subdomain' => $venue->subdomain,
                'slug' => $event->slug
            ]));

        $response->assertStatus(200);
        $response->assertSee('Room 42');
    }
}
