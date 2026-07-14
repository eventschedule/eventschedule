<?php

namespace Tests\Feature;

use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class MicrosoftCalendarRoleUpdateTest extends TestCase
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
            'microsoft_token' => 'owner-token',
            'microsoft_refresh_token' => 'owner-refresh',
            'microsoft_token_expires_at' => now()->addHour(),
        ])->save();

        return $user->fresh();
    }

    private function payload($role, array $overrides = []): array
    {
        return array_merge([
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'new_subdomain' => $role->subdomain,
        ], $overrides);
    }

    public function test_non_owner_editor_save_preserves_owner_outlook_config(): void
    {
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner, 'venue', [
            'microsoft_sync_direction' => 'both',
            'microsoft_webhook_id' => 'sub-owner',
            'microsoft_webhook_expires_at' => now()->addDay(),
        ]);
        RoleUser::where('role_id', $role->id)->where('user_id', $owner->id)
            ->update(['microsoft_calendar_id' => 'cal-owner']);

        // A non-owner admin editor (with no Outlook connection) saves an unrelated setting.
        // The Outlook tab does not render for them, so no microsoft_* inputs are submitted.
        $editor = $this->createOwner();
        $this->followRole($editor, $role, 'admin');

        Http::fake();

        $response = $this->actingAs($editor)->put(
            route('role.update', ['subdomain' => $role->subdomain]),
            $this->payload($role, ['name' => 'Renamed By Editor'])
        );

        $response->assertRedirect();

        // Owner's calendar selection, direction, and subscription must be untouched...
        $this->assertSame('cal-owner', RoleUser::where('role_id', $role->id)->where('user_id', $owner->id)->first()->microsoft_calendar_id);
        $role->refresh();
        $this->assertSame('both', $role->microsoft_sync_direction);
        $this->assertSame('sub-owner', $role->microsoft_webhook_id);
        // ...and no Graph subscription deletion may be attempted (with the wrong token).
        Http::assertNothingSent();
    }

    public function test_owner_switch_to_no_sync_tears_down_subscription(): void
    {
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner, 'venue', [
            'microsoft_sync_direction' => 'both',
            'microsoft_webhook_id' => 'sub-owner',
            'microsoft_webhook_expires_at' => now()->addDay(),
        ]);
        RoleUser::where('role_id', $role->id)->where('user_id', $owner->id)
            ->update(['microsoft_calendar_id' => 'cal-owner']);

        Http::fake(['graph.microsoft.com/*' => Http::response('', 204)]);

        $response = $this->actingAs($owner)->put(
            route('role.update', ['subdomain' => $role->subdomain]),
            $this->payload($role, [
                'microsoft_integration_submitted' => '1',
                'microsoft_sync_direction' => '', // "no sync"
                'microsoft_calendar_id' => 'cal-owner', // unchanged calendar
            ])
        );

        $response->assertRedirect();

        // Switching to no-sync must delete the Graph subscription and clear its state.
        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && str_contains($request->url(), '/subscriptions/sub-owner'));
        $role->refresh();
        $this->assertNull($role->microsoft_webhook_id);
        $this->assertNull($role->microsoft_sync_direction);
    }
}
