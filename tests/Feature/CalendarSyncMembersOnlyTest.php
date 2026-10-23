<?php

namespace Tests\Feature;

use App\Services\GoogleCalendarService;
use App\Services\MicrosoftCalendarService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Calendar sync is for a schedule's own people, and a follower is not one of them.
 *
 * Role::users() is every link to a schedule, followers included, and three sync gates asked it:
 * Outlook's schedule sync (which changes the sync direction and pulls with the CALLER's token),
 * and "send this event to my calendar" for both providers, which copied a draft, an unlisted or
 * password event or a booking to anybody following a schedule the event is on. Google's schedule
 * sync had already been made owner-only for this reason.
 *
 *   - schedule sync: the owner, on both providers;
 *   - one event to my calendar, and a member's standing sync: owner, admin or viewer.
 *
 * The services are mocked: a test must not reach either provider.
 */
class CalendarSyncMembersOnlyTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function test_a_follower_cannot_run_outlook_sync_on_a_schedule(): void
    {
        $this->mock(MicrosoftCalendarService::class, fn ($mock) => $mock->shouldIgnoreMissing());
        $role = $this->createRole($this->createOwner(), 'venue');
        $follower = $this->createOwner();
        $follower->forceFill(['microsoft_token' => 'token'])->save();
        $this->followRole($follower, $role);

        $response = $this->actingAs($follower->fresh())->postJson(route('microsoft.calendar.sync', ['subdomain' => $role->subdomain]), ['sync_direction' => 'from']);

        $response->assertStatus(403);
        $this->assertNull($role->fresh()->microsoft_sync_direction);
    }

    public function test_a_follower_cannot_copy_a_draft_into_their_own_calendar(): void
    {
        $this->mock(GoogleCalendarService::class, fn ($mock) => $mock->shouldIgnoreMissing());
        $role = $this->createRole($this->createOwner(), 'venue');
        $draft = $this->createEvent($role, ['is_draft' => true, 'creator_role_id' => $role->id]);
        $follower = $this->createOwner();
        $follower->forceFill(['google_token' => 'token', 'google_token_scopes' => 'https://www.googleapis.com/auth/calendar'])->save();
        $this->followRole($follower, $role);

        $response = $this->actingAs($follower->fresh())->postJson(route('google.calendar.sync_event', ['subdomain' => $role->subdomain, 'eventId' => UrlUtils::encodeId($draft->id)]));

        $this->assertContains($response->status(), [403, 422], 'refused before anything is sent');
        $this->assertSame(0, DB::table('calendar_syncs')->where('event_id', $draft->id)->count());
    }

    /**
     * Sync Now follows the save's rule: only an event guests can see goes to a calendar. It used
     * to push whatever it was given, so one press copied a draft, an internal event or a cancelled
     * one into the owner's calendar. Mutation: drop the calendarSyncRefusal() check from either
     * controller.
     */
    public function test_sync_now_does_not_copy_a_draft_internal_or_cancelled_event_to_google(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['google_token' => 'token', 'google_token_scopes' => 'https://www.googleapis.com/auth/calendar'])->save();
        $role = $this->createRole($owner, 'venue');
        $hidden = [
            'draft' => $this->createEvent($role, ['is_draft' => true, 'creator_role_id' => $role->id]),
            'internal' => $this->createEvent($role, ['is_draft' => true, 'is_internal' => true, 'creator_role_id' => $role->id]),
            'cancelled' => $this->createEvent($role, ['is_cancelled' => true, 'creator_role_id' => $role->id]),
        ];
        $published = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $this->mock(GoogleCalendarService::class, function ($mock) use ($published) {
            $mock->shouldReceive('ensureValidToken')->andReturn(true);
            $mock->shouldReceive('createEvent')->once()
                ->withArgs(fn ($event) => (int) $event->id === (int) $published->id)
                ->andReturn(new \Google\Service\Calendar\Event(['id' => 'google-1']));
        });

        foreach ($hidden as $state => $event) {
            $this->actingAs($owner->fresh())
                ->postJson(route('google.calendar.sync_event', ['subdomain' => $role->subdomain, 'eventId' => UrlUtils::encodeId($event->id)]))
                ->assertStatus(422);
            $this->assertSame(0, DB::table('calendar_syncs')->where('event_id', $event->id)->count(), "a {$state} event is not copied");
        }

        // Positive control: a published event still goes.
        $this->actingAs($owner->fresh())
            ->postJson(route('google.calendar.sync_event', ['subdomain' => $role->subdomain, 'eventId' => UrlUtils::encodeId($published->id)]))
            ->assertOk();
    }

    public function test_sync_now_does_not_copy_a_draft_internal_or_cancelled_event_to_outlook(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['microsoft_token' => 'token'])->save();
        $role = $this->createRole($owner, 'venue');
        $hidden = [
            'draft' => $this->createEvent($role, ['is_draft' => true, 'creator_role_id' => $role->id]),
            'internal' => $this->createEvent($role, ['is_draft' => true, 'is_internal' => true, 'creator_role_id' => $role->id]),
            'cancelled' => $this->createEvent($role, ['is_cancelled' => true, 'creator_role_id' => $role->id]),
        ];
        $published = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $this->mock(MicrosoftCalendarService::class, function ($mock) use ($published) {
            $mock->shouldReceive('ensureValidToken')->andReturn(true);
            $mock->shouldReceive('createEvent')->once()
                ->withArgs(fn ($event) => (int) $event->id === (int) $published->id)
                ->andReturn(['id' => 'outlook-1']);
        });

        foreach ($hidden as $state => $event) {
            $this->actingAs($owner->fresh())
                ->postJson(route('microsoft.calendar.sync_event', ['subdomain' => $role->subdomain, 'eventId' => UrlUtils::encodeId($event->id)]))
                ->assertStatus(422);
            $this->assertSame(0, DB::table('microsoft_calendar_syncs')->where('event_id', $event->id)->count(), "a {$state} event is not copied");
        }

        $this->actingAs($owner->fresh())
            ->postJson(route('microsoft.calendar.sync_event', ['subdomain' => $role->subdomain, 'eventId' => UrlUtils::encodeId($published->id)]))
            ->assertOk();
    }
}
