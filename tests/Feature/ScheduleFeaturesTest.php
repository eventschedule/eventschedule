<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Services\AuditService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class ScheduleFeaturesTest extends TestCase
{
    use RefreshDatabase;
    use CreatesScheduleData;

    public function test_schedule_merge_moves_events_to_target(): void
    {
        $owner = $this->createOwner();
        $source = $this->createRole($owner, 'venue');
        $target = $this->createRole($owner, 'venue');
        // Merge only allows an unclaimed source (canMergeRoles guards verified records).
        \App\Models\Role::where('id', $source->id)->update(['email_verified_at' => null]);
        $source->refresh();
        $event = $this->createEvent($source);

        $this->actingAs($owner)->post(route('role.merge', ['subdomain' => $source->subdomain]), [
            'target_subdomain' => $target->subdomain,
        ]);

        $this->assertDatabaseHas('event_role', ['event_id' => $event->id, 'role_id' => $target->id]);
        $this->assertDatabaseMissing('event_role', ['event_id' => $event->id, 'role_id' => $source->id]);
    }

    public function test_save_youtube_video_for_talent(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $talent = $this->createRole($owner, 'talent', ['youtube_links' => json_encode([])]);

        $event = $this->createEvent($curator);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $this->actingAs($owner)->post(route('role.save_video', ['subdomain' => $curator->subdomain]), [
            'role_id' => $talent->id,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'video_title' => 'Live Set',
        ]);

        $links = json_decode($talent->fresh()->youtube_links, true) ?? [];
        $this->assertNotEmpty($links);
        $this->assertStringContainsString('dQw4w9WgXcQ', json_encode($links));
    }

    public function test_owner_can_view_audit_log(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        // Simulate a logged action via the real audit path.
        AuditService::log('category.added', $owner->id, 'Role', $role->id, null, ['name' => 'New Category']);

        $this->assertDatabaseHas('audit_logs', ['model_type' => 'Role', 'model_id' => $role->id]);

        $this->actingAs($owner)->get(route('role.audit_log', ['subdomain' => $role->subdomain]))
            ->assertOk();
    }

    public function test_white_label_hides_branding_for_pro(): void
    {
        $owner = $this->createOwner();
        $freeRole = $this->createRole($owner, 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
        ]);
        $proRole = $this->createRole($owner); // enterprise by default

        // Free tier shows the "Powered by" branding; Pro/Enterprise is white-labeled.
        $this->assertTrue($freeRole->showBranding());
        $this->assertFalse($proRole->showBranding());

        // The guest event page footer shows branding only for the free schedule.
        $freeEvent = $this->createEvent($freeRole);
        $proEvent = $this->createEvent($proRole);
        $this->get($this->guestEventUrl($freeRole, $freeEvent))->assertOk()->assertSee('Invoice Ninja');
        $this->get($this->guestEventUrl($proRole, $proEvent))->assertOk()->assertDontSee('Invoice Ninja');
    }

    public function test_guest_event_submission(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['accept_requests' => true]);

        $this->post(route('event.guest_import.store', ['subdomain' => $role->subdomain]), [
            'name' => 'Guest Submitted Event',
            'description' => 'From a guest',
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'duration' => 2,
        ]);

        $event = Event::where('name', 'Guest Submitted Event')->first();
        $this->assertNotNull($event);
        $this->assertTrue($role->events()->where('events.id', $event->id)->exists());
    }
}
