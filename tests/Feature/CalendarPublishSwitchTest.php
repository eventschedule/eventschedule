<?php

namespace Tests\Feature;

use App\Models\CalendarSyncSetting;
use App\Models\Role;
use App\Models\User;
use App\Services\CalendarArrival;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The schedule form's calendar rows (role/edit, Integrations): a sync that starts reading needs a
 * calendar the owner chose, and "Publish new events automatically" is the owner's word, saved
 * behind each provider's marker, bound to the calendar it was said for (CalendarArrival).
 */
class CalendarPublishSwitchTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['app.hosted' => false]);
    }

    private function save(User $user, Role $role, array $fields = []): TestResponse
    {
        return $this->actingAs($user)
            ->from(route('role.edit', ['subdomain' => $role->subdomain]))
            ->put(route('role.update', ['subdomain' => $role->subdomain]), array_merge([
                'name' => $role->name,
                'email' => $role->email,
                'timezone' => $role->timezone,
                'new_subdomain' => $role->subdomain,
            ], $fields));
    }

    private function caldavSchedule(User $owner, ?string $direction = 'from'): Role
    {
        $role = $this->createRole($owner, 'talent');
        $role->forceFill([
            'caldav_settings' => ['server_url' => 'https://dav.example.org', 'username' => 'me', 'password' => 'p', 'calendar_url' => 'https://dav.example.org/cal'],
            'caldav_sync_direction' => $direction,
        ])->save();

        return $role->fresh();
    }

    public function test_starting_to_read_google_without_choosing_a_calendar_is_refused(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->save($owner, $role, ['google_integration_submitted' => 1, 'sync_direction' => 'from', 'google_calendar_id' => ''])
            ->assertRedirect(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertSessionHasErrors('google_calendar_id');

        $this->assertNull($role->fresh()->sync_direction, 'it used to read the account\'s main calendar');
    }

    public function test_the_owners_switch_is_saved_and_taken_back(): void
    {
        $owner = $this->createOwner();
        $role = $this->caldavSchedule($owner);

        $this->save($owner, $role, ['caldav_integration_submitted' => 1, 'caldav_sync_direction' => 'from', 'caldav_publish_on_arrival' => 1])->assertRedirect();
        $this->assertTrue(CalendarArrival::publishes($role->fresh(), CalendarArrival::CALDAV));
        $this->assertNotNull(CalendarSyncSetting::where('role_id', $role->id)->value('caldav_publish_confirmed_at'));

        $this->save($owner, $role, ['caldav_integration_submitted' => 1, 'caldav_sync_direction' => 'from', 'caldav_publish_on_arrival' => 0])->assertRedirect();
        $this->assertFalse(CalendarArrival::publishes($role->fresh(), CalendarArrival::CALDAV));
    }

    public function test_a_team_member_cannot_say_it_for_the_owner(): void
    {
        $owner = $this->createOwner();
        $role = $this->caldavSchedule($owner);
        $admin = $this->createOwner();
        $role->users()->attach($admin->id, ['level' => 'admin']);

        $this->save($admin, $role, ['caldav_integration_submitted' => 1, 'caldav_publish_on_arrival' => 1])->assertRedirect();

        $this->assertFalse(CalendarArrival::publishes($role->fresh(), CalendarArrival::CALDAV));
    }

    public function test_the_save_that_starts_reading_says_where_the_events_will_turn_up(): void
    {
        $owner = $this->createOwner();
        $role = $this->caldavSchedule($owner, 'to');

        $this->save($owner, $role, ['caldav_integration_submitted' => 1, 'caldav_sync_direction' => 'from'])
            ->assertSessionHas('calendar_reading_started', CalendarArrival::CALDAV);

        $this->actingAs($owner)
            ->withSession(['calendar_reading_started' => CalendarArrival::CALDAV])
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertSee(__('messages.calendar_reading_started', ['provider' => __('messages.caldav_calendar')]));
    }

    public function test_the_row_offers_the_switch_while_the_sync_reads_and_warns_while_it_is_on(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['google_token' => 'tok', 'google_refresh_token' => 'ref'])->save();
        $role = $this->createRole($owner, 'talent', ['sync_direction' => 'from']);
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);

        $html = $this->actingAs($owner->fresh())->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/data-calendar-publish="google"\s*>/', $html, 'shown, since the sync reads');
        $this->assertStringContainsString('name="google_publish_on_arrival"', $html);
        $this->assertMatchesRegularExpression('/data-calendar-publish-warning="google"\s*>/', $html, 'the warning shows while it is on');
        $this->assertStringContainsString('data-main-calendar-panel="google" hidden', $html);
        $this->assertStringContainsString(__('messages.google_main_calendar_warning'), $html);
    }
}
