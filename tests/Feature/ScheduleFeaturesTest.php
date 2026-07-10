<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class ScheduleFeaturesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

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

    public function test_promote_co_owned_acceptance_accepts_curator_events_on_owned_venue(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $venue = $this->createRole($owner, 'venue');

        // Accepted on the curator; the importer left the venue side unaccepted (false).
        $event = $this->createEvent($curator);
        $event->roles()->attach($venue->id, ['is_accepted' => false]);

        $this->artisan('app:promote-co-owned-acceptance', ['--curator' => $curator->subdomain])
            ->assertExitCode(0);

        $this->assertDatabaseHas('event_role', [
            'event_id' => $event->id, 'role_id' => $venue->id, 'is_accepted' => 1,
        ]);
    }

    public function test_promote_skips_venue_owned_by_someone_else(): void
    {
        $curatorOwner = $this->createOwner();
        $venueOwner = $this->createOwner();
        $curator = $this->createRole($curatorOwner, 'curator');
        $venue = $this->createRole($venueOwner, 'venue'); // different owner -> not co-owned

        $event = $this->createEvent($curator);
        $event->roles()->attach($venue->id, ['is_accepted' => false]);

        $this->artisan('app:promote-co-owned-acceptance', ['--curator' => $curator->subdomain])
            ->assertExitCode(0);

        // Unchanged: acceptance must not carry onto someone else's venue.
        $this->assertDatabaseHas('event_role', [
            'event_id' => $event->id, 'role_id' => $venue->id, 'is_accepted' => 0,
        ]);
    }

    public function test_merge_venues_promotes_acceptance_on_co_owned_target(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        // Same name + empty city/country => detected as duplicates by venueDuplicateGroups().
        $target = $this->createRole($owner, 'venue', ['name' => 'Dup Venue']);
        $source = $this->createRole($owner, 'venue', ['name' => 'Dup Venue', 'email_verified_at' => null]);
        $source->refresh();

        // Two future events accepted on the curator, one per venue, unaccepted on the venue side.
        $eventA = $this->createEvent($curator);
        $eventA->roles()->attach($source->id, ['is_accepted' => false]);
        $eventB = $this->createEvent($curator);
        $eventB->roles()->attach($target->id, ['is_accepted' => false]);

        $this->actingAs($owner)->post(route('role.merge_venues_group', ['subdomain' => $curator->subdomain]), [
            'target_id' => $target->id,
            'source_ids' => [$source->id],
        ]);

        // Source event moved onto the target, and both are now accepted on the target venue.
        $this->assertDatabaseHas('event_role', ['event_id' => $eventA->id, 'role_id' => $target->id, 'is_accepted' => 1]);
        $this->assertDatabaseHas('event_role', ['event_id' => $eventB->id, 'role_id' => $target->id, 'is_accepted' => 1]);
    }

    public function test_calendar_import_dedupes_venue_string_variants(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');

        $sync = new class
        {
            use \App\Services\Concerns\ConvertsLocationToVenue;

            public function run($role, $location)
            {
                return $this->convertLocationToVenue($role, $location);
            }
        };

        // Same place, two string variants (extra comma + spacing) must resolve to ONE venue.
        $a = $sync->run($curator, "Patrick's Caesarea");
        $b = $sync->run($curator, "Patrick's,  Caesarea");

        $this->assertNotNull($a);
        $this->assertEquals($a->id, $b->id);
        $this->assertEquals(1, \App\Models\Role::where('type', 'venue')->where('user_id', $owner->id)->count());
    }

    public function test_calendar_import_matches_users_existing_named_venue(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $existing = $this->createRole($owner, 'venue', ['name' => "Mike's Place"]);

        $sync = new class
        {
            use \App\Services\Concerns\ConvertsLocationToVenue;

            public function run($role, $location)
            {
                return $this->convertLocationToVenue($role, $location);
            }
        };

        // A "Venue, City" location must match the user's existing cleanly-named venue (no new record).
        $matched = $sync->run($curator, "Mike's Place, Tel Aviv");

        $this->assertEquals($existing->id, $matched->id);
        $this->assertEquals(1, \App\Models\Role::where('type', 'venue')->where('user_id', $owner->id)->count());
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
        // roles.require_account defaults to 1, and the guest_import.store validator then demands
        // account_name/email/password. This test covers the no-account guest path, so opt out of it.
        $role = $this->createRole($owner, 'venue', ['accept_requests' => true, 'require_account' => false]);

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
