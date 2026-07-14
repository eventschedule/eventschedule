<?php

namespace Tests\Feature;

use App\Models\MicrosoftCalendarSync;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class MicrosoftCalendarEventFlowTest extends TestCase
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

    private function connectedOwner(): User
    {
        $user = $this->createOwner();
        $user->forceFill([
            'microsoft_token' => 'valid-access-token',
            'microsoft_refresh_token' => 'valid-refresh-token',
            'microsoft_token_expires_at' => now()->addHour(),
        ])->save();

        return $user->fresh();
    }

    public function test_sync_to_microsoft_creates_remote_event_and_mapping(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue', ['microsoft_sync_direction' => 'to']);
        $event = $this->createEvent($role, ['name' => 'Outbound Event']);

        Http::fake(['graph.microsoft.com/*' => Http::response(['id' => 'ms-out-1'], 201)]);

        $event->syncToMicrosoftCalendar('create');

        Http::assertSent(fn ($request) => $request->method() === 'POST' && str_contains($request->url(), '/me/events'));
        $this->assertDatabaseHas('microsoft_calendar_syncs', [
            'user_id' => $user->id,
            'event_id' => $event->id,
            'role_id' => $role->id,
            'microsoft_event_id' => 'ms-out-1',
        ]);
    }

    public function test_sync_to_microsoft_delete_removes_remote_event_and_mapping(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue', ['microsoft_sync_direction' => 'to']);
        $event = $this->createEvent($role, ['name' => 'To Delete']);
        MicrosoftCalendarSync::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'role_id' => $role->id,
            'microsoft_event_id' => 'ms-del-1',
        ]);

        Http::fake(['graph.microsoft.com/*' => Http::response('', 204)]);

        $event->syncToMicrosoftCalendar('delete');

        Http::assertSent(fn ($request) => $request->method() === 'DELETE' && str_contains($request->url(), '/me/events/ms-del-1'));
        $this->assertDatabaseMissing('microsoft_calendar_syncs', ['microsoft_event_id' => 'ms-del-1']);
    }

    public function test_no_sync_when_direction_disabled(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue', ['microsoft_sync_direction' => null]);
        $event = $this->createEvent($role, ['name' => 'Not Synced']);

        $event->syncToMicrosoftCalendar('create');

        Http::assertNothingSent();
        $this->assertDatabaseMissing('microsoft_calendar_syncs', ['event_id' => $event->id]);
    }

    public function test_no_sync_when_owner_not_connected(): void
    {
        $user = $this->createOwner(); // no microsoft_token
        $role = $this->createRole($user, 'venue', ['microsoft_sync_direction' => 'to']);
        $event = $this->createEvent($role, ['name' => 'Owner Offline']);

        $event->syncToMicrosoftCalendar('create');

        Http::assertNothingSent();
    }

    public function test_per_event_sync_endpoint_creates_mapping(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue', ['microsoft_sync_direction' => 'to']);
        $event = $this->createEvent($role, ['name' => 'Manual Sync']);

        Http::fake(['graph.microsoft.com/*' => Http::response(['id' => 'ms-manual-1'], 201)]);

        $response = $this->actingAs($user)->withServerVariables(['subdomain' => $role->subdomain])
            ->post('/microsoft-calendar/sync-event/'.$role->subdomain.'/'.UrlUtils::encodeId($event->id));

        $response->assertOk();
        $response->assertJsonPath('microsoft_event_id', 'ms-manual-1');
        $this->assertDatabaseHas('microsoft_calendar_syncs', [
            'event_id' => $event->id,
            'microsoft_event_id' => 'ms-manual-1',
        ]);
    }

    public function test_per_event_sync_endpoint_forbidden_for_stranger(): void
    {
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner, 'venue', ['microsoft_sync_direction' => 'to']);
        $event = $this->createEvent($role, ['name' => 'Private Event']);

        $stranger = $this->connectedOwner();

        $response = $this->actingAs($stranger)
            ->post('/microsoft-calendar/sync-event/'.$role->subdomain.'/'.UrlUtils::encodeId($event->id));

        $response->assertStatus(403);
        Http::assertNothingSent();
    }
}
