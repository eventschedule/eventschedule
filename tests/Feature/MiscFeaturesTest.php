<?php

namespace Tests\Feature;

use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class MiscFeaturesTest extends TestCase
{
    use RefreshDatabase;
    use CreatesScheduleData;

    public function test_customization_and_banner_saved(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'new_subdomain' => $role->subdomain,
            'custom_css' => 'body { background: #111; }',
            'accent_color' => '#4E81FA',
            'font_color' => '#ffffff',
            'font_family' => 'Inter',
            'banner_enabled' => true,
            'banner_message' => 'Welcome to our shows!',
        ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'custom_css' => 'body { background: #111; }',
            'accent_color' => '#4E81FA',
        ]);
    }

    public function test_dashboard_config_saved(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner);

        $this->actingAs($owner)->post(route('home.save_config'), [
            'panels' => [
                ['id' => 'upcoming_count', 'visible' => true, 'size' => 1],
                ['id' => 'followers', 'visible' => false, 'size' => 1],
            ],
        ])->assertOk();

        $this->assertNotNull($owner->fresh()->dashboard_config);
    }

    public function test_ical_feed(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['name' => 'Feed Event']);

        $response = $this->get(route('feed.ical', ['subdomain' => $role->subdomain]));
        $response->assertOk();
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
        $this->assertStringContainsString('Feed Event', $response->getContent());
    }

    public function test_rss_feed(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['name' => 'RSS Event']);

        $response = $this->get(route('feed.rss', ['subdomain' => $role->subdomain]));
        $response->assertOk();
        $this->assertStringContainsString('<rss', $response->getContent());
    }

    public function test_search_events(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['name' => 'Summer Festival 2026']);

        $response = $this->actingAs($owner)->get(route('role.search_events', ['subdomain' => $role->subdomain]) . '?q=Summer');
        $response->assertOk();
        $this->assertStringContainsString('Summer Festival', $response->getContent());
    }

    public function test_past_events_listing(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role, [
            'name' => 'Last Year Show',
            'starts_at' => now()->subDays(30)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);

        $response = $this->get(route('role.list_past_events', ['subdomain' => $role->subdomain]) . '?before=' . now()->addDay()->toDateString());
        $response->assertOk();
        $this->assertStringContainsString('Last Year Show', $response->getContent());
    }

    public function test_team_add_member(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $member = User::factory()->create(['email' => 'member@example.com']);

        $this->actingAs($owner)->post(route('role.store_member', ['subdomain' => $role->subdomain]), [
            'email' => 'member@example.com',
            'name' => 'New Member',
            'level' => 'admin',
        ]);

        $this->assertDatabaseHas('role_user', [
            'role_id' => $role->id,
            'user_id' => $member->id,
            'level' => 'admin',
        ]);
    }

    public function test_team_update_member_level(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $member = User::factory()->create();
        $role->users()->attach($member->id, ['level' => 'admin']);

        $this->actingAs($owner)->patch(route('role.update_member_level', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($member->id),
        ]), ['level' => 'viewer']);

        $this->assertDatabaseHas('role_user', [
            'role_id' => $role->id,
            'user_id' => $member->id,
            'level' => 'viewer',
        ]);
    }

    public function test_team_remove_member(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $member = User::factory()->create();
        $role->users()->attach($member->id, ['level' => 'admin']);

        $this->actingAs($owner)->delete(route('role.remove_member', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($member->id),
        ]));

        $this->assertDatabaseMissing('role_user', [
            'role_id' => $role->id,
            'user_id' => $member->id,
        ]);
    }
}
