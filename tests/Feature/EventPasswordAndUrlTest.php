<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EventPasswordAndUrlTest extends TestCase
{
    public function test_guest_gets_password_prompt_for_protected_event()
    {
        $user = User::factory()->create();

        $role = Role::factory()->create(['type' => 'venue']);
        $role->user_id = $user->id;
        $role->save();

        $event = Event::factory()->create([
            'user_id' => $user->id,
            'slug' => Str::slug('test-event'),
        ]);

        $event->event_password_hash = Hash::make('plain-text');
        $event->save();

        $response = $this->get('/' . $role->subdomain . '/' . $event->slug);

        $response->assertStatus(200);
        $response->assertSeeText(__('messages.event_password_required'));
    }

    public function test_online_event_shows_watch_online_link_even_with_venue()
    {
        $user = User::factory()->create();

        $role = Role::factory()->create(['type' => 'venue']);
        $role->user_id = $user->id;
        $role->save();

        $event = Event::factory()->create([
            'user_id' => $user->id,
            'slug' => Str::slug('online-event'),
            'event_url' => 'https://example.test/stream',
        ]);

        $event->roles()->attach($role->id, ['is_accepted' => true]);

        $response = $this->get('/' . $role->subdomain . '/' . $event->slug);

        $response->assertStatus(200);
        $response->assertSeeText(__('messages.watch_online'));
    }

    public function test_edit_page_shows_password_set_and_owner_can_update_without_password()
    {
        $user = User::factory()->create();

        $role = Role::factory()->create(['type' => 'venue']);
        $role->save();

        // ensure the user is a role member so they can edit
        $user->roles()->attach($role->id, ['level' => 'owner', 'created_at' => now()]);

        $event = Event::factory()->create([
            'user_id' => $user->id,
            'slug' => Str::slug('edit-event'),
        ]);

        $event->event_password_hash = Hash::make('plain-text');
        $event->save();

        $response = $this->actingAs($user)->get('/' . $role->subdomain . '/edit-event/' . \App\Utils\UrlUtils::encodeId($event->id));

        $response->assertStatus(200);
        $response->assertSeeText(__('messages.password_set'));

        $payload = [
            'timezone' => 'UTC',
            'name' => 'Updated name',
            'event_private' => '1',
        ];

        $updateResponse = $this->actingAs($user)->put('/' . $role->subdomain . '/update-event/' . \App\Utils\UrlUtils::encodeId($event->id), $payload);

        $updateResponse->assertStatus(302);

        $event->refresh();
        $this->assertNotNull($event->event_password_hash);
        $this->assertTrue(Hash::check('plain-text', $event->event_password_hash));
    }
}
