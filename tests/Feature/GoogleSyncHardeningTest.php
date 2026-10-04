<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule's Google sync belongs to its owner: the calendar id sits on the owner's pivot and
 * the standing sync (google:sync, the webhook) runs with the owner's token. Three places used to
 * let somebody else's account in:
 *
 *   1. POST /google-calendar/sync/{subdomain} authorised on Role::users(), which includes
 *      followers. A follower with a Google connection could change the schedule's sync direction
 *      and pull with their own token, and the default calendar id 'primary' resolves against the
 *      caller's account - so their own calendar landed on someone else's public schedule.
 *   2. Saving the schedule's settings read google_calendar_id unguarded. A save that never
 *      rendered the Google tab cleared the owner's pivot, and the sync fell back to 'primary'.
 *   3. The webhook pulled as Role::users()->first(): whoever sorts first by name.
 */
class GoogleSyncHardeningTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function connect(User $user): User
    {
        $user->forceFill([
            'google_token' => 'token-'.$user->id,
            'google_refresh_token' => 'refresh-'.$user->id,
            'google_token_expires_at' => now()->addHour(),
        ])->save();

        return $user->fresh();
    }

    /** A schedule pulling from the owner's calendar 'cal-owner'. */
    private function syncedSchedule(User $owner, string $direction = 'from'): Role
    {
        $role = $this->createRole($owner, 'venue', ['sync_direction' => $direction]);
        $role->forceFill(['sync_direction' => $direction])->save();
        RoleUser::where('role_id', $role->id)->where('user_id', $owner->id)
            ->update(['google_calendar_id' => 'cal-owner']);

        return $role->fresh();
    }

    private function ownerCalendar(Role $role): ?string
    {
        return RoleUser::where('role_id', $role->id)->where('user_id', $role->user_id)->first()->google_calendar_id;
    }

    private function payload(Role $role, array $overrides = []): array
    {
        return array_merge([
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'new_subdomain' => $role->subdomain,
        ], $overrides);
    }

    /** Any call that reaches Google fails the test. */
    private function googleMustNotBeCalled(): void
    {
        $this->mock(GoogleCalendarService::class, function ($mock) {
            $mock->shouldNotReceive('syncFromGoogleCalendar');
            $mock->shouldNotReceive('syncUserEvents');
            $mock->shouldNotReceive('createWebhook');
            $mock->shouldNotReceive('deleteWebhook');
            $mock->shouldReceive('ensureValidToken')->andReturn(true);
            $mock->shouldReceive('setAccessToken');
        });
    }

    public function test_a_follower_cannot_drive_a_schedules_sync(): void
    {
        $owner = $this->connect($this->createOwner());
        $role = $this->syncedSchedule($owner, 'to');
        $follower = $this->connect($this->createOwner());
        $this->followRole($follower, $role);
        $this->googleMustNotBeCalled();

        $response = $this->actingAs($follower)->postJson(
            route('google.calendar.sync', ['subdomain' => $role->subdomain]),
            ['sync_direction' => 'from']
        );

        $response->assertStatus(403);
        $this->assertSame('to', $role->fresh()->sync_direction);
    }

    public function test_an_admin_who_is_not_the_owner_cannot_either(): void
    {
        $owner = $this->connect($this->createOwner());
        $role = $this->syncedSchedule($owner, 'to');
        $admin = $this->connect($this->createOwner());
        $this->followRole($admin, $role, 'admin');
        $this->googleMustNotBeCalled();

        $this->actingAs($admin)->postJson(
            route('google.calendar.sync', ['subdomain' => $role->subdomain]),
            ['sync_direction' => 'both']
        )->assertStatus(403);

        $this->assertSame('to', $role->fresh()->sync_direction);
    }

    public function test_the_owner_still_syncs(): void
    {
        $owner = $this->connect($this->createOwner());
        $role = $this->syncedSchedule($owner, 'from');

        $this->mock(GoogleCalendarService::class, function ($mock) use ($owner) {
            $mock->shouldReceive('ensureValidToken')->andReturn(true);
            $mock->shouldReceive('syncFromGoogleCalendar')
                ->once()
                ->with(Mockery::on(fn ($user) => $user->id === $owner->id), Mockery::any(), 'cal-owner')
                ->andReturn(['created' => 0]);
        });

        $this->actingAs($owner)->postJson(
            route('google.calendar.sync', ['subdomain' => $role->subdomain])
        )->assertOk();
    }

    public function test_the_sync_menu_item_is_the_owners(): void
    {
        $owner = $this->connect($this->createOwner());
        $role = $this->syncedSchedule($owner);
        $admin = $this->connect($this->createOwner());
        $this->followRole($admin, $role, 'admin');

        $this->actingAs($owner)->get('/'.$role->subdomain.'/schedule')
            ->assertOk()
            ->assertSee('id="sync-events-link"', false);

        $this->actingAs($admin)->get('/'.$role->subdomain.'/schedule')
            ->assertOk()
            ->assertDontSee('id="sync-events-link"', false);
    }

    public function test_a_save_that_did_not_render_the_google_controls_keeps_the_calendar(): void
    {
        // The owner, with Google disconnected since: the tab shows a connect link and no inputs.
        $owner = $this->createOwner();
        $role = $this->syncedSchedule($owner);
        $this->googleMustNotBeCalled();

        $this->actingAs($owner)->put(
            route('role.update', ['subdomain' => $role->subdomain]),
            $this->payload($role, ['name' => 'Renamed'])
        )->assertRedirect();

        $this->assertSame('Renamed', $role->fresh()->name);
        $this->assertSame('cal-owner', $this->ownerCalendar($role));
        $this->assertSame('from', $role->fresh()->sync_direction);
    }

    public function test_an_editor_cannot_repoint_the_owners_sync(): void
    {
        $owner = $this->connect($this->createOwner());
        $role = $this->syncedSchedule($owner);
        $admin = $this->connect($this->createOwner());
        $this->followRole($admin, $role, 'admin');
        $this->googleMustNotBeCalled();

        // A hand-built POST: the marker and both inputs, from an account that is not the owner.
        $this->actingAs($admin)->put(
            route('role.update', ['subdomain' => $role->subdomain]),
            $this->payload($role, [
                'google_integration_submitted' => '1',
                'google_calendar_id' => 'cal-of-the-editor',
                'sync_direction' => 'both',
            ])
        )->assertRedirect();

        $this->assertSame('cal-owner', $this->ownerCalendar($role));
        $this->assertSame('from', $role->fresh()->sync_direction);
    }

    public function test_an_unloaded_select_does_not_clear_the_calendar(): void
    {
        $owner = $this->connect($this->createOwner());
        $role = $this->syncedSchedule($owner);
        $this->mock(GoogleCalendarService::class, fn ($mock) => $mock->shouldIgnoreMissing());

        // The select posts '' while its options are still being fetched.
        $this->actingAs($owner)->put(
            route('role.update', ['subdomain' => $role->subdomain]),
            $this->payload($role, [
                'google_integration_submitted' => '1',
                'google_calendar_id' => '',
                'sync_direction' => 'from',
            ])
        )->assertRedirect();

        $this->assertSame('cal-owner', $this->ownerCalendar($role));
    }

    public function test_the_owner_still_changes_calendar_and_direction(): void
    {
        $owner = $this->connect($this->createOwner());
        $role = $this->syncedSchedule($owner);
        $this->mock(GoogleCalendarService::class, fn ($mock) => $mock->shouldIgnoreMissing());

        $this->actingAs($owner)->put(
            route('role.update', ['subdomain' => $role->subdomain]),
            $this->payload($role, [
                'google_integration_submitted' => '1',
                'google_calendar_id' => 'cal-new',
                'sync_direction' => 'to',
            ])
        )->assertRedirect();

        $this->assertSame('cal-new', $this->ownerCalendar($role));
        $this->assertSame('to', $role->fresh()->sync_direction);
    }

    public function test_the_settings_controls_render_for_the_owner_only(): void
    {
        $owner = $this->connect($this->createOwner());
        $role = $this->syncedSchedule($owner);
        $admin = $this->connect($this->createOwner());
        $this->followRole($admin, $role, 'admin');

        $this->actingAs($owner)->get('/'.$role->subdomain.'/edit')
            ->assertOk()
            ->assertSee('name="google_integration_submitted"', false)
            ->assertSee('id="google-calendar-select"', false);

        // An editor keeps their own "sync to my calendar" section, and nothing schedule-level.
        $this->actingAs($admin)->get('/'.$role->subdomain.'/edit')
            ->assertOk()
            ->assertDontSee('name="google_integration_submitted"', false)
            ->assertDontSee('id="google-calendar-select"', false)
            ->assertSee('id="member-calendar-select"', false);
    }

    public function test_the_webhook_pulls_as_the_owner(): void
    {
        config(['services.google.webhook_secret' => 'hook-secret']);

        $owner = $this->createOwner();
        $owner->forceFill(['name' => 'Zed Owner'])->save();
        $owner = $this->connect($owner);
        $role = $this->syncedSchedule($owner);
        $role->forceFill(['google_webhook_id' => 'chan-1', 'google_webhook_resource_id' => 'res-1'])->save();

        // Sorts first by name, which is who Role::users()->first() used to hand the pull to.
        $follower = $this->createOwner();
        $follower->forceFill(['name' => 'Aaron Follower'])->save();
        $follower = $this->connect($follower);
        $this->followRole($follower, $role);

        $this->mock(GoogleCalendarService::class, function ($mock) use ($owner) {
            $isOwner = Mockery::on(fn ($user) => $user->id === $owner->id);
            $mock->shouldReceive('ensureValidToken')->with($isOwner)->andReturn(true);
            $mock->shouldReceive('setAccessToken')
                ->with(Mockery::on(fn ($token) => $token['access_token'] === 'token-'.$owner->id));
            $mock->shouldReceive('syncFromGoogleCalendar')
                ->once()
                ->with($isOwner, Mockery::any(), 'cal-owner')
                ->andReturn([]);
        });

        $this->post('/google-calendar/webhook', [], [
            'X-Goog-Channel-Token' => 'hook-secret',
            'X-Goog-Channel-ID' => 'chan-1',
            'X-Goog-Resource-ID' => 'res-1',
            'X-Goog-Resource-State' => 'exists',
        ])->assertOk();
    }
}
