<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class MicrosoftCalendarUiRenderTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

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

    public function test_role_edit_renders_outlook_tab_for_connected_owner(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue', ['microsoft_sync_direction' => 'both']);

        $response = $this->actingAs($user)->get('/'.$role->subdomain.'/edit');

        $response->assertOk();
        $response->assertSee('integration-tab-microsoft', false);
        $response->assertSee('microsoft-calendar-select', false);
        $response->assertSee('microsoft_sync_direction', false);
        $response->assertSee('microsoft_create_teams_meetings', false);
        $response->assertSee('microsoft_integration_submitted', false);
    }

    public function test_profile_settings_renders_outlook_section(): void
    {
        // With a client id configured, the connect button (not the "not configured" note) renders.
        config()->set('services.microsoft.client_id', 'test-client-id');
        $user = $this->createOwner();

        $response = $this->actingAs($user)->get('/settings');

        $response->assertOk();
        $response->assertSee('section-microsoft-calendar', false);
        $response->assertSee('microsoft-calendar/redirect', false);
    }

    public function test_profile_settings_shows_not_configured_note_without_client_id(): void
    {
        config()->set('services.microsoft.client_id', null);
        $user = $this->createOwner();

        $response = $this->actingAs($user)->get('/settings');

        $response->assertOk();
        $response->assertSee('section-microsoft-calendar', false);
        // No dead connect button when the server is not configured for Outlook.
        $response->assertDontSee('microsoft-calendar/redirect', false);
    }
}
