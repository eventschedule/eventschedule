<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class MicrosoftSyncCommandTest extends TestCase
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
            'microsoft_token' => 'valid-access-token',
            'microsoft_refresh_token' => 'valid-refresh-token',
            'microsoft_token_expires_at' => now()->addHour(),
        ])->save();

        return $user->fresh();
    }

    public function test_command_syncs_inbound_roles_only(): void
    {
        $user = $this->connectedOwner();
        // 'from' role should sync
        $fromRole = $this->createRole($user, 'venue', ['microsoft_sync_direction' => 'from']);
        // 'to' role should be skipped (outbound only)
        $toRole = $this->createRole($user, 'venue', ['microsoft_sync_direction' => 'to']);

        Http::fake(['graph.microsoft.com/*' => Http::response([
            'value' => [[
                'id' => 'cmd-remote-1',
                'subject' => 'Command Imported',
                'body' => ['contentType' => 'text', 'content' => 'x'],
                'start' => ['dateTime' => '2099-05-01T10:00:00.0000000', 'timeZone' => 'UTC'],
                'end' => ['dateTime' => '2099-05-01T11:00:00.0000000', 'timeZone' => 'UTC'],
                'isAllDay' => false,
            ]],
            '@odata.deltaLink' => 'https://graph.microsoft.com/v1.0/me/calendarView/delta?$deltatoken=cmd',
        ], 200)]);

        $this->artisan('microsoft:sync')->assertExitCode(0);

        $this->assertDatabaseHas('microsoft_calendar_syncs', [
            'role_id' => $fromRole->id,
            'microsoft_event_id' => 'cmd-remote-1',
        ]);
        // The outbound-only role must not have imported anything.
        $this->assertDatabaseMissing('microsoft_calendar_syncs', ['role_id' => $toRole->id]);
    }

    public function test_command_skips_deleted_roles(): void
    {
        Http::preventStrayRequests();
        $user = $this->connectedOwner();
        $this->createRole($user, 'venue', ['microsoft_sync_direction' => 'from', 'is_deleted' => true]);

        $this->artisan('microsoft:sync')->assertExitCode(0);

        // A deleted role is filtered out, so no Graph request is made.
        Http::assertNothingSent();
    }
}
