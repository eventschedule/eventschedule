<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\MicrosoftCalendarSync;
use App\Models\User;
use App\Services\MicrosoftCalendarService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class MicrosoftCalendarServiceTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.microsoft', [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-secret',
            'redirect' => 'https://app.test/microsoft-calendar/callback',
            'tenant' => 'common',
            'webhook_secret' => 'test-webhook-secret',
        ]);

        Http::preventStrayRequests();
    }

    private function connectedOwner(array $attrs = []): User
    {
        $user = $this->createOwner();
        $user->forceFill(array_merge([
            'microsoft_id' => 'ms-user-1',
            'microsoft_token' => 'valid-access-token',
            'microsoft_refresh_token' => 'valid-refresh-token',
            'microsoft_token_expires_at' => now()->addHour(),
        ], $attrs))->save();

        return $user->fresh();
    }

    private function service(): MicrosoftCalendarService
    {
        return app(MicrosoftCalendarService::class);
    }

    public function test_refresh_persists_rotated_refresh_token(): void
    {
        $user = $this->connectedOwner(['microsoft_token_expires_at' => now()->subMinutes(5)]);

        Http::fake([
            'login.microsoftonline.com/*' => Http::response([
                'access_token' => 'new-access-token',
                'refresh_token' => 'rotated-refresh-token',
                'expires_in' => 3600,
            ], 200),
        ]);

        $ok = $this->service()->ensureValidToken($user);

        $this->assertTrue($ok);
        $user->refresh();
        $this->assertSame('new-access-token', $user->microsoft_token);
        $this->assertSame('rotated-refresh-token', $user->microsoft_refresh_token);
    }

    public function test_create_event_maps_fields_to_graph_wall_clock(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue', ['timezone' => 'America/New_York']);
        $event = $this->createEvent($role, ['name' => 'Launch Party', 'is_private' => true]);

        Http::fake(['graph.microsoft.com/*' => Http::response(['id' => 'ms-event-123'], 201)]);

        $this->service()->ensureValidToken($user);
        $result = $this->service()->createEvent($event, $role);

        $this->assertSame('ms-event-123', $result['id']);

        $expectedDateTime = Carbon::parse($event->starts_at, 'UTC')->setTimezone('America/New_York')->format('Y-m-d\TH:i:s');

        Http::assertSent(function ($request) use ($expectedDateTime) {
            return $request->method() === 'POST'
                && str_contains($request->url(), '/me/events')
                && $request['subject'] === 'Launch Party'
                && $request['start']['timeZone'] === 'America/New_York'
                && $request['start']['dateTime'] === $expectedDateTime
                && $request['sensitivity'] === 'private';
        });
    }

    public function test_create_event_adds_teams_flags_and_writes_join_url_for_online_event(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'talent', [
            'timezone' => 'America/New_York',
            'microsoft_create_teams_meetings' => true,
        ]);
        // Online event: no venue attached.
        $event = $this->createEvent($role, ['name' => 'Webinar', 'event_url' => null]);

        Http::fake(['graph.microsoft.com/*' => Http::response([
            'id' => 'ms-event-teams',
            'onlineMeeting' => ['joinUrl' => 'https://teams.microsoft.com/l/meetup-join/abc'],
        ], 201)]);

        $this->service()->ensureValidToken($user);
        $this->service()->createEvent($event, $role);

        Http::assertSent(fn ($request) => ($request['isOnlineMeeting'] ?? null) === true
            && ($request['onlineMeetingProvider'] ?? null) === 'teamsForBusiness');

        $this->assertSame('https://teams.microsoft.com/l/meetup-join/abc', $event->fresh()->event_url);
    }

    public function test_create_event_retries_without_teams_flags_on_client_error(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'talent', ['microsoft_create_teams_meetings' => true]);
        $event = $this->createEvent($role, ['name' => 'Personal Account Event']);

        // First call (with Teams flags) 400s; retry without them succeeds.
        Http::fake(['graph.microsoft.com/*' => Http::sequence()
            ->push(['error' => ['code' => 'ErrorInvalidOnlineMeeting']], 400)
            ->push(['id' => 'ms-event-plain'], 201),
        ]);

        $this->service()->ensureValidToken($user);
        $result = $this->service()->createEvent($event, $role);

        $this->assertSame('ms-event-plain', $result['id']);
        // Two requests: the rejected Teams attempt and the plain retry.
        Http::assertSentCount(2);
    }

    public function test_create_event_all_day_uses_isallday_payload(): void
    {
        // Zero-duration (all-day) events must not produce end == start (Graph 400).
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue', ['timezone' => 'America/New_York']);
        $event = $this->createEvent($role, ['name' => 'Holiday', 'duration' => 0]);

        Http::fake(['graph.microsoft.com/*' => Http::response(['id' => 'ms-allday'], 201)]);

        $this->service()->ensureValidToken($user);
        $result = $this->service()->createEvent($event, $role);

        $this->assertSame('ms-allday', $result['id']);
        Http::assertSent(function ($request) {
            return ($request['isAllDay'] ?? null) === true
                && str_ends_with($request['start']['dateTime'], 'T00:00:00')
                && str_ends_with($request['end']['dateTime'], 'T00:00:00')
                && $request['start']['dateTime'] !== $request['end']['dateTime'];
        });
    }

    public function test_teams_retry_does_not_fire_on_401(): void
    {
        // A 401 must NOT trigger the Teams strip-and-retry (only 400/403 do).
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'talent', ['microsoft_create_teams_meetings' => true]);
        $event = $this->createEvent($role, ['name' => 'Auth Fail']);

        Http::fake(['graph.microsoft.com/*' => Http::sequence()
            ->push(['error' => ['code' => 'InvalidAuthenticationToken']], 401)
            ->push(['id' => 'should-not-reach'], 201),
        ]);

        $this->service()->ensureValidToken($user);
        $result = $this->service()->createEvent($event, $role);

        $this->assertNull($result);
        Http::assertSentCount(1);
    }

    public function test_renew_subscription_returns_null_on_404(): void
    {
        $user = $this->connectedOwner();
        Http::fake(['graph.microsoft.com/*' => Http::response('', 404)]);

        $this->assertNull($this->service()->renewSubscription($user, 'sub-gone'));
    }

    public function test_renew_subscription_throws_on_transient_error(): void
    {
        // A transient failure must throw so the caller retries rather than recreating a live sub.
        $user = $this->connectedOwner();
        Http::fake(['graph.microsoft.com/*' => Http::response(['error' => 'server'], 503)]);

        $this->expectException(\RuntimeException::class);
        $this->service()->renewSubscription($user, 'sub-live');
    }

    public function test_update_event_patches_graph_event(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue');
        $event = $this->createEvent($role, ['name' => 'Updated Name']);

        Http::fake(['graph.microsoft.com/*' => Http::response(['id' => 'ms-event-1'], 200)]);

        $this->service()->ensureValidToken($user);
        $this->service()->updateEvent($event, 'ms-event-1', $role);

        Http::assertSent(fn ($request) => $request->method() === 'PATCH'
            && str_contains($request->url(), '/me/events/ms-event-1'));
    }

    public function test_delete_event_treats_404_as_success(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue');

        Http::fake(['graph.microsoft.com/*' => Http::response(['error' => ['code' => 'ErrorItemNotFound']], 404)]);

        $this->service()->ensureValidToken($user);
        $this->assertTrue($this->service()->deleteEvent('missing-id', null, $role->id));
    }

    public function test_get_calendars_maps_graph_fields(): void
    {
        $user = $this->connectedOwner();

        Http::fake(['graph.microsoft.com/*' => Http::response([
            'value' => [
                ['id' => 'cal-1', 'name' => 'Calendar', 'isDefaultCalendar' => true],
                ['id' => 'cal-2', 'name' => 'Work', 'isDefaultCalendar' => false],
            ],
        ], 200)]);

        $calendars = $this->service()->getCalendars($user);

        $this->assertCount(2, $calendars);
        $this->assertSame(['id' => 'cal-1', 'summary' => 'Calendar', 'primary' => true], $calendars[0]);
        $this->assertSame('Work', $calendars[1]['summary']);
        $this->assertFalse($calendars[1]['primary']);
    }

    public function test_delta_sync_creates_event_and_persists_delta_link(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue', ['timezone' => 'America/New_York']);

        Http::fake(['graph.microsoft.com/*' => Http::response([
            'value' => [[
                'id' => 'remote-1',
                'subject' => 'Imported Event',
                'body' => ['contentType' => 'html', 'content' => '<p>Hello</p>'],
                'start' => ['dateTime' => '2099-01-15T18:00:00.0000000', 'timeZone' => 'UTC'],
                'end' => ['dateTime' => '2099-01-15T19:00:00.0000000', 'timeZone' => 'UTC'],
                'isAllDay' => false,
            ]],
            '@odata.deltaLink' => 'https://graph.microsoft.com/v1.0/me/calendarView/delta?$deltatoken=xyz',
        ], 200)]);

        $result = $this->service()->syncFromMicrosoftCalendar($user, $role, null);

        $this->assertSame(1, $result['created']);
        $this->assertDatabaseHas('microsoft_calendar_syncs', [
            'role_id' => $role->id,
            'microsoft_event_id' => 'remote-1',
        ]);
        $this->assertDatabaseHas('events', ['name' => 'Imported Event']);

        $role->refresh();
        $this->assertStringContainsString('$deltatoken=xyz', $role->microsoft_sync_token);
        $this->assertNotNull($role->microsoft_last_sync_at);
    }

    public function test_delta_removed_item_deletes_mapping_not_event(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue');
        $event = $this->createEvent($role, ['name' => 'Keep Me']);
        MicrosoftCalendarSync::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'role_id' => $role->id,
            'microsoft_event_id' => 'remote-gone',
        ]);

        Http::fake(['graph.microsoft.com/*' => Http::response([
            'value' => [['id' => 'remote-gone', '@removed' => ['reason' => 'deleted']]],
            '@odata.deltaLink' => 'https://graph.microsoft.com/v1.0/me/calendarView/delta?$deltatoken=next',
        ], 200)]);

        $this->service()->syncFromMicrosoftCalendar($user, $role, null);

        $this->assertDatabaseMissing('microsoft_calendar_syncs', ['microsoft_event_id' => 'remote-gone']);
        // The local event must survive - Google/Graph inbound never propagates deletions.
        $this->assertDatabaseHas('events', ['id' => $event->id, 'name' => 'Keep Me']);
    }

    public function test_delta_removed_item_with_changed_reason_is_not_a_deletion(): void
    {
        // Graph reports @removed with reason 'changed' when an event merely leaves the query window
        // (e.g. rescheduled beyond the sync horizon). That is NOT a deletion: even with the delete
        // policy, the still-existing event and its mapping must be left intact.
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue', ['calendar_delete_action' => 'delete']);
        $event = $this->createEvent($role, ['name' => 'Still Exists']);
        MicrosoftCalendarSync::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'role_id' => $role->id,
            'microsoft_event_id' => 'moved-out',
        ]);

        Http::fake(['graph.microsoft.com/*' => Http::response([
            'value' => [['id' => 'moved-out', '@removed' => ['reason' => 'changed']]],
            '@odata.deltaLink' => 'https://graph.microsoft.com/v1.0/me/calendarView/delta?$deltatoken=next',
        ], 200)]);

        $this->service()->syncFromMicrosoftCalendar($user, $role, null);

        $this->assertDatabaseHas('events', ['id' => $event->id, 'name' => 'Still Exists']);
        $this->assertDatabaseHas('microsoft_calendar_syncs', ['microsoft_event_id' => 'moved-out']);
    }

    public function test_delta_410_clears_token_and_restarts(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue');
        $role->forceFill(['microsoft_sync_token' => 'https://graph.microsoft.com/v1.0/me/calendarView/delta?$deltatoken=stale'])->save();

        // Stale deltaLink 410s; the restarted full sync returns a fresh deltaLink.
        Http::fake(['graph.microsoft.com/*' => Http::sequence()
            ->push(['error' => ['code' => 'resyncRequired']], 410)
            ->push(['value' => [], '@odata.deltaLink' => 'https://graph.microsoft.com/v1.0/me/calendarView/delta?$deltatoken=fresh'], 200),
        ]);

        $result = $this->service()->syncFromMicrosoftCalendar($user, $role, null);

        $this->assertSame(0, $result['errors']);
        $role->refresh();
        $this->assertStringContainsString('$deltatoken=fresh', $role->microsoft_sync_token);
    }
}
