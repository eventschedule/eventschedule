<?php

namespace Tests\Feature;

use App\Models\MicrosoftCalendarSync;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class MicrosoftCalendarControllerTest extends TestCase
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
    }

    private function connectedOwner(): User
    {
        $user = $this->createOwner();
        $user->forceFill([
            'microsoft_id' => 'ms-user-1',
            'microsoft_token' => 'valid-access-token',
            'microsoft_refresh_token' => 'valid-refresh-token',
            'microsoft_token_expires_at' => now()->addHour(),
        ])->save();

        return $user->fresh();
    }

    private function fakeIdToken(string $sub = 'ms-user-1'): string
    {
        $b64 = fn ($data) => rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');

        return $b64(['alg' => 'none']).'.'.$b64(['sub' => $sub]).'.sig';
    }

    public function test_redirect_starts_oauth_and_stores_state(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->get('/microsoft-calendar/redirect');

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('login.microsoftonline.com', $location);
        $this->assertStringContainsString('Calendars.ReadWrite', urldecode($location));
        $this->assertStringContainsString('offline_access', urldecode($location));
        $this->assertNotEmpty(session('microsoft_oauth_state'));
    }

    public function test_callback_persists_tokens(): void
    {
        $user = $this->createOwner();

        Http::fake([
            'login.microsoftonline.com/*' => Http::response([
                'access_token' => 'the-access-token',
                'refresh_token' => 'the-refresh-token',
                'expires_in' => 3600,
                'id_token' => $this->fakeIdToken('ms-oid-42'),
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['microsoft_oauth_state' => 'state-123'])
            ->get('/microsoft-calendar/callback?code=auth-code&state=state-123');

        $response->assertRedirect();
        $user->refresh();
        $this->assertSame('the-access-token', $user->microsoft_token);
        $this->assertSame('the-refresh-token', $user->microsoft_refresh_token);
        $this->assertSame('ms-oid-42', $user->microsoft_id);
        $this->assertNotNull($user->microsoft_token_expires_at);
    }

    public function test_callback_rejects_state_mismatch(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)
            ->withSession(['microsoft_oauth_state' => 'expected-state'])
            ->get('/microsoft-calendar/callback?code=auth-code&state=wrong-state');

        $response->assertRedirect();
        $user->refresh();
        $this->assertNull($user->microsoft_token);
    }

    public function test_disconnect_cleans_up_everything(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue', [
            'microsoft_sync_direction' => 'both',
            'microsoft_webhook_id' => 'sub-1',
            'microsoft_webhook_expires_at' => now()->addDay(),
            'microsoft_sync_token' => 'delta-token',
        ]);
        $event = $this->createEvent($role, ['name' => 'Synced Event']);
        MicrosoftCalendarSync::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'role_id' => $role->id,
            'microsoft_event_id' => 'ms-ev-1',
        ]);
        RoleUser::where('role_id', $role->id)->where('user_id', $user->id)
            ->update(['microsoft_calendar_id' => 'cal-1']);

        Http::fake(['graph.microsoft.com/*' => Http::response('', 204)]);

        $response = $this->actingAs($user)->get('/microsoft-calendar/disconnect');

        $response->assertRedirect();

        $user->refresh();
        $this->assertNull($user->microsoft_token);
        $this->assertNull($user->microsoft_refresh_token);
        $this->assertNull($user->microsoft_id);

        $role->refresh();
        $this->assertNull($role->microsoft_sync_direction);
        $this->assertNull($role->microsoft_webhook_id);
        $this->assertNull($role->microsoft_sync_token);

        $this->assertDatabaseMissing('microsoft_calendar_syncs', ['event_id' => $event->id]);
        $this->assertNull(RoleUser::where('role_id', $role->id)->where('user_id', $user->id)->first()->microsoft_calendar_id);
    }

    public function test_get_calendars_endpoint_returns_json(): void
    {
        $user = $this->connectedOwner();

        Http::fake(['graph.microsoft.com/*' => Http::response([
            'value' => [['id' => 'cal-1', 'name' => 'Calendar', 'isDefaultCalendar' => true]],
        ], 200)]);

        $response = $this->actingAs($user)->get('/microsoft-calendar/calendars');

        $response->assertOk();
        $response->assertJsonPath('calendars.0.summary', 'Calendar');
    }

    public function test_webhook_echoes_validation_token_without_csrf(): void
    {
        $response = $this->post('/microsoft-calendar/webhook?validationToken=abc%20123');

        $response->assertOk();
        $response->assertSee('abc 123');
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));
    }

    public function test_webhook_rejects_wrong_client_state(): void
    {
        Http::preventStrayRequests();
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue', [
            'microsoft_sync_direction' => 'from',
            'microsoft_webhook_id' => 'sub-9',
        ]);

        $response = $this->postJson('/microsoft-calendar/webhook', [
            'value' => [['subscriptionId' => 'sub-9', 'clientState' => 'WRONG']],
        ]);

        $response->assertStatus(401);
        Http::assertNothingSent();
    }

    public function test_webhook_valid_notification_triggers_delta_sync(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue', [
            'timezone' => 'America/New_York',
            'microsoft_sync_direction' => 'from',
            'microsoft_webhook_id' => 'sub-live',
        ]);

        Http::fake(['graph.microsoft.com/*' => Http::response([
            'value' => [[
                'id' => 'pushed-1',
                'subject' => 'Pushed Event',
                'body' => ['contentType' => 'text', 'content' => 'x'],
                'start' => ['dateTime' => '2099-03-01T10:00:00.0000000', 'timeZone' => 'UTC'],
                'end' => ['dateTime' => '2099-03-01T11:00:00.0000000', 'timeZone' => 'UTC'],
                'isAllDay' => false,
            ]],
            '@odata.deltaLink' => 'https://graph.microsoft.com/v1.0/me/calendarView/delta?$deltatoken=abc',
        ], 200)]);

        $response = $this->postJson('/microsoft-calendar/webhook', [
            'value' => [['subscriptionId' => 'sub-live', 'clientState' => 'test-webhook-secret']],
        ]);

        $response->assertStatus(202);
        $this->assertDatabaseHas('events', ['name' => 'Pushed Event']);
        $this->assertDatabaseHas('microsoft_calendar_syncs', [
            'role_id' => $role->id,
            'microsoft_event_id' => 'pushed-1',
        ]);
    }
}
