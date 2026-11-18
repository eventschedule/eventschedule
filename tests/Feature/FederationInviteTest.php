<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\FederatedInstance;
use App\Models\FederationClicksDaily;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\FederationService;
use App\Utils\SetupGuide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The invitation to put a selfhost install's events on eventschedule.com, now and then: a modal on
 * the dashboard at most three times, thirty days apart, to an admin whose account is a week old,
 * who is not in the middle of the setup guide and owes nothing in "Needs attention"; never to an
 * install that shares or ever connected. The schedule page keeps its banner on the same clock.
 * Then, once joined: the one-time "you're live" card, and the visits the network sent back.
 */
class FederationInviteTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const MODAL = 'id="federation-invite"';

    protected function setUp(): void
    {
        parent::setUp();

        // Federation is an instance-side feature; the suite runs as the nexus.
        config(['app.is_nexus' => false]);
        Cache::flush();
    }

    private function service(): FederationService
    {
        return app(FederationService::class);
    }

    private function admin(int $days = 10): User
    {
        $admin = $this->createOwner(true);
        $admin->forceFill(['created_at' => now()->subDays($days)])->saveQuietly();

        return $admin->fresh();
    }

    /** @return array{0: User, 1: Role, 2: Event} */
    private function invitedAdmin(int $days = 10): array
    {
        $admin = $this->admin($days);
        $role = $this->createRole($admin, 'venue');
        // Far enough ahead to still be coming after the clock has travelled a few months.
        $event = $this->createEvent($role, [
            'name' => 'Jazz on the Roof',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $role->id,
            'starts_at' => now()->addDays(300)->setTime(20, 0)->format('Y-m-d H:i:s'),
        ]);

        return [$admin, $role, $event];
    }

    private function dashboard(User $user)
    {
        return $this->actingAs($user)->get(route('home'))->assertOk();
    }

    // Who

    public function test_an_admin_a_week_old_with_something_to_share_is_invited(): void
    {
        [$admin] = $this->invitedAdmin();

        $this->dashboard($admin)
            ->assertSee(self::MODAL, false)
            ->assertSee(__('messages.federation_invite_choose'), false)
            ->assertSee(route('admin.settings', ['card' => 'federation', 'from' => 'invite']))
            ->assertSee(route('home.federation_prompt_dismiss'), false);
    }

    public function test_an_account_younger_than_a_week_is_not_invited(): void
    {
        [$admin] = $this->invitedAdmin(3);

        $this->dashboard($admin)->assertDontSee(self::MODAL, false);
        $this->assertSame(0, (int) $admin->fresh()->federation_invite_count);
    }

    public function test_someone_who_is_not_an_admin_is_not_invited(): void
    {
        [$admin] = $this->invitedAdmin();
        $admin->is_admin = false;
        $admin->saveQuietly();

        $this->dashboard($admin->fresh())->assertDontSee(self::MODAL, false);
    }

    public function test_an_install_that_already_shares_is_never_invited(): void
    {
        [$admin] = $this->invitedAdmin();
        Setting::set('federation_enabled', '1');

        $this->dashboard($admin)->assertDontSee(self::MODAL, false);
    }

    /** Turned on once and back off: the user's explicit requirement is that it never asks again. */
    public function test_an_install_that_ever_connected_is_never_invited(): void
    {
        [$admin] = $this->invitedAdmin();
        Setting::set('federation_instance_id', 'a-previous-identity');
        Setting::set('federation_enabled', null);

        $this->dashboard($admin)->assertDontSee(self::MODAL, false);
        $this->assertFalse($this->service()->shouldInvite($admin));
    }

    public function test_the_nexus_never_invites(): void
    {
        [$admin] = $this->invitedAdmin();
        config(['app.is_nexus' => true]);

        $this->dashboard($admin)->assertDontSee(self::MODAL, false);
    }

    public function test_nobody_is_invited_in_the_middle_of_the_setup_guide(): void
    {
        [$admin, $role] = $this->invitedAdmin();
        SetupGuide::start($admin, $role);

        $this->dashboard($admin->fresh())->assertDontSee(self::MODAL, false);
        $this->assertSame(0, (int) $admin->fresh()->federation_invite_count);
    }

    public function test_a_hidden_setup_guide_does_not_hold_it_back(): void
    {
        [$admin, $role] = $this->invitedAdmin();
        SetupGuide::start($admin, $role);
        DB::table('users')->where('id', $admin->id)->update([
            'setup_guide' => json_encode(['role_id' => $role->id, 'started_at' => now()->toIso8601String(), 'dismissed_at' => now()->toIso8601String()]),
        ]);
        SetupGuide::flush();

        $this->dashboard($admin->fresh())->assertSee(self::MODAL, false);
    }

    public function test_nobody_is_invited_while_something_needs_attention(): void
    {
        [$admin, $role] = $this->invitedAdmin();

        // An event request waiting on the schedule: a row in "Needs attention".
        $other = $this->createRole($this->createOwner(), 'talent');
        $this->createEvent($other, ['name' => 'Asking to play', 'is_accepted' => true])
            ->roles()->attach($role->id, ['is_accepted' => null]);

        $this->dashboard($admin)->assertDontSee(self::MODAL, false);
        $this->assertSame(0, (int) $admin->fresh()->federation_invite_count);
    }

    // When

    public function test_it_comes_back_three_times_thirty_days_apart_and_then_never(): void
    {
        [$admin] = $this->invitedAdmin();

        $this->dashboard($admin)->assertSee(self::MODAL, false);
        $fresh = $admin->fresh();
        $this->assertSame(1, $fresh->federation_invite_count);
        $this->assertNotNull($fresh->federation_invite_at);

        // The same window: counted once, and not shown again.
        $this->dashboard($admin->fresh())->assertDontSee(self::MODAL, false);
        $this->travel(29)->days();
        $this->dashboard($admin->fresh())->assertDontSee(self::MODAL, false);
        $this->assertSame(1, $admin->fresh()->federation_invite_count);

        $this->travel(2)->days();
        $this->dashboard($admin->fresh())->assertSee(self::MODAL, false);
        $this->assertSame(2, $admin->fresh()->federation_invite_count);

        $this->travel(31)->days();
        $this->dashboard($admin->fresh())->assertSee(self::MODAL, false);
        $this->assertSame(3, $admin->fresh()->federation_invite_count);

        $this->travel(31)->days();
        $this->dashboard($admin->fresh())->assertDontSee(self::MODAL, false);
        $this->travel(365)->days();
        $this->dashboard($admin->fresh())->assertDontSee(self::MODAL, false);
        $this->assertSame(3, $admin->fresh()->federation_invite_count);
    }

    public function test_the_clock_is_each_admins_own(): void
    {
        [$admin] = $this->invitedAdmin();
        $other = $this->admin();
        $this->createRole($other, 'talent');

        $this->dashboard($admin)->assertSee(self::MODAL, false);
        $this->dashboard($other)->assertSee(self::MODAL, false);
    }

    public function test_counting_does_not_move_updated_at(): void
    {
        [$admin] = $this->invitedAdmin();
        $before = $admin->fresh()->updated_at;

        $this->travel(1)->minutes();
        $this->dashboard($admin);

        $this->assertEquals($before, $admin->fresh()->updated_at);
    }

    public function test_an_old_dismissal_counts_as_one_not_now(): void
    {
        [$admin] = $this->invitedAdmin();
        $admin->federation_prompt_dismissed = true;
        $admin->saveQuietly();

        $migration = require database_path('migrations/2026_11_18_120000_add_federation_invite_to_users_table.php');
        $migration->carryOverDismissals();

        $fresh = $admin->fresh();
        $this->assertFalse($fresh->federation_prompt_dismissed);
        $this->assertSame(1, $fresh->federation_invite_count);
        $this->assertNotNull($fresh->federation_invite_at);

        $this->dashboard($fresh)->assertDontSee(self::MODAL, false);
        $this->travel(31)->days();
        $this->dashboard($admin->fresh())->assertSee(self::MODAL, false);
        $this->assertSame(2, $admin->fresh()->federation_invite_count);
    }

    public function test_dont_ask_again_ends_it(): void
    {
        [$admin] = $this->invitedAdmin();
        $this->dashboard($admin)->assertSee(self::MODAL, false);

        $this->post(route('home.federation_prompt_dismiss'))->assertRedirect();

        $this->travel(31)->days();
        $this->dashboard($admin->fresh())->assertDontSee(self::MODAL, false);
        $this->assertFalse($this->service()->shouldInvite($admin->fresh()));
    }

    // One ask per visit

    public function test_the_modal_replaces_the_banner_and_the_other_asks_wait(): void
    {
        config(['app.hosted' => false]);
        [$admin] = $this->invitedAdmin();

        $response = $this->dashboard($admin)
            ->assertSee(self::MODAL, false)
            ->assertDontSee(__('messages.federation_prompt_title'));

        $this->assertNull($response->viewData('homeSchedulePrompt'));
        $this->assertTrue($response->viewData('nextStepItems')->isEmpty());
        $this->assertTrue($response->viewData('federationListingSchedules')->isEmpty());

        // The next visit, with the invitation counted: the home-schedule question is back.
        $next = $this->dashboard($admin->fresh())->assertDontSee(self::MODAL, false);
        $this->assertNotNull($next->viewData('homeSchedulePrompt'));
    }

    public function test_the_modal_shows_their_own_next_event_with_their_site_on_it(): void
    {
        [$admin] = $this->invitedAdmin();
        config(['app.url' => 'https://toms-site.org']);

        $poster = $this->service()->invitePoster($admin);

        $this->assertSame('Jazz on the Roof', $poster['name']);
        $this->assertSame('toms-site.org', $poster['source']);
        $this->assertNotEmpty($poster['image']);
        $this->assertNotEmpty($poster['when']);
    }

    public function test_an_event_without_a_picture_draws_no_poster(): void
    {
        $admin = $this->admin();
        $role = $this->createRole($admin, 'venue');
        $this->createEvent($role, ['name' => 'No picture', 'creator_role_id' => $role->id]);

        $this->assertNull($this->service()->invitePoster($admin));
    }

    public function test_the_schedule_page_banner_says_not_now_on_the_same_clock(): void
    {
        [$admin, $role] = $this->invitedAdmin();
        $page = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']);

        $this->actingAs($admin)->get($page)->assertOk()
            ->assertSee(__('messages.federation_prompt_title'))
            ->assertSee(route('home.federation_prompt_later'), false)
            ->assertSee(__('messages.not_now'))
            ->assertDontSee('messages.', false);

        $this->post(route('home.federation_prompt_later'))->assertRedirect();
        $this->assertSame(1, $admin->fresh()->federation_invite_count);

        // Gone from both pages for thirty days.
        $this->actingAs($admin->fresh())->get($page)->assertOk()->assertDontSee(__('messages.federation_prompt_title'));
        $this->dashboard($admin->fresh())->assertDontSee(self::MODAL, false);

        $this->travel(31)->days();
        $this->actingAs($admin->fresh())->get($page)->assertOk()->assertSee(__('messages.federation_prompt_title'));
    }

    /** Not now changes something, so it is never a GET (StateChangingAddressTest's rule). */
    public function test_not_now_is_never_a_get(): void
    {
        [$admin] = $this->invitedAdmin();

        $status = $this->actingAs($admin)->get('/dashboard/federation-prompt/later')->status();

        $this->assertContains($status, [404, 405]);
        $this->assertSame(0, (int) $admin->fresh()->federation_invite_count);
    }

    // The way in

    public function test_choose_what_to_share_lands_on_the_card_with_what_happens_next(): void
    {
        [$admin] = $this->invitedAdmin();
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        $this->get(route('admin.settings', ['card' => 'federation', 'from' => 'invite']))
            ->assertRedirect(route('admin.settings').'#federation');

        $this->get(route('admin.settings'))->assertOk()
            ->assertSee('scroll-mt-24 sys-invited', false)
            ->assertSee(__('messages.federation_invite_next_steps', ['site' => 'eventschedule.com']));

        // Once: the next load is the plain card.
        $this->get(route('admin.settings'))->assertOk()->assertDontSee('scroll-mt-24 sys-invited', false);
    }

    public function test_the_password_confirm_says_why_when_it_comes_from_the_invitation(): void
    {
        [$admin] = $this->invitedAdmin();

        $this->actingAs($admin)
            ->get(route('admin.settings', ['card' => 'federation', 'from' => 'invite']))
            ->assertRedirect(route('admin.password.confirm.show'));

        $this->get(route('admin.password.confirm.show'))->assertOk()
            ->assertSee(__('messages.federation_invite_confirm_context', ['site' => 'eventschedule.com']));
    }

    // After joining

    public function test_the_first_approved_sync_with_events_sent_is_told_once(): void
    {
        [$admin, $role, $event] = $this->invitedAdmin();
        $role->forceFill(['federation_enabled' => true])->saveQuietly();
        Setting::set('federation_enabled', '1');
        Setting::set('federation_instance_id', 'joined');
        Setting::set('federation_status', 'approved');
        Setting::set('federation_listings_url', 'https://eventschedule.com/browse?show=network&instance=abc');

        // Nothing sent yet: nothing to say.
        $this->service()->noteLive();
        $this->assertNull(Setting::get('federation_live_at'));

        $event->forceFill(['federated_at' => now()])->saveQuietly();
        $this->service()->noteLive();
        $this->assertSame('1', (string) Setting::get('federation_live_count'));

        $title = trans_choice('messages.federation_live_title', 1, ['count' => 1]);
        $this->dashboard($admin)
            ->assertSee($title)
            ->assertSee('https://eventschedule.com/browse?show=network&amp;instance=abc', false);
        $this->dashboard($admin->fresh())->assertDontSee($title);

        // Another admin is told too, once.
        $other = $this->admin();
        $this->createRole($other, 'talent');
        $this->dashboard($other)->assertSee($title);
    }

    public function test_an_install_already_live_before_this_update_is_not_told(): void
    {
        [$admin, , $event] = $this->invitedAdmin();
        Setting::set('federation_enabled', '1');
        Setting::set('federation_instance_id', 'joined');
        Setting::set('federation_status', 'approved');
        $event->forceFill(['federated_at' => now()])->saveQuietly();

        $migration = require database_path('migrations/2026_11_18_120000_add_federation_invite_to_users_table.php');
        $migration->markAlreadyLive();
        $this->service()->noteLive();

        $this->assertSame('0', (string) Setting::get('federation_live_count'));
        $this->assertNull($this->service()->takeLiveCard($admin));
    }

    // Visits, told back

    public function test_the_network_answers_with_the_visits_of_the_last_30_days(): void
    {
        $instance = FederatedInstance::create([
            'instance_id' => 'visits-test',
            'site_url' => 'https://toms-site.org',
            'name' => 'Tom',
            'secret' => str_repeat('a', 64),
            'status' => 'approved',
        ]);

        foreach ([0 => 5, 10 => 7, 29 => 40, 30 => 100, 90 => 1000] as $daysAgo => $clicks) {
            DB::table('federation_clicks_daily')->insert([
                'federated_instance_id' => $instance->id,
                'date' => now()->subDays($daysAgo)->toDateString(),
                'clicks' => $clicks,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->assertSame(52, $instance->apiState()['visits_30d']);
        $this->assertSame(52, $instance->visitsLast30Days());
        $this->assertSame(1152, FederationClicksDaily::totalForInstance($instance->id));
    }

    public function test_the_settings_card_shows_the_visits_beside_the_listings_link(): void
    {
        [$admin, $role, $event] = $this->invitedAdmin();
        $role->forceFill(['federation_enabled' => true])->saveQuietly();
        Setting::set('federation_enabled', '1');
        Setting::set('federation_instance_id', 'joined');
        Setting::set('federation_contact_email', 'admin@gmail.com');
        $event->forceFill(['federated_at' => now()])->saveQuietly();

        Http::fake(['*' => Http::response([
            'status' => 'approved',
            'listings_url' => 'https://eventschedule.com/browse?show=network&instance=abc',
            'visits_30d' => 52,
        ])]);
        config(['app.nexus_url' => 'https://eventschedule.com']);

        $this->service()->register();
        $this->assertSame('52', (string) Setting::get('federation_visits_30d'));

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin)
            ->get(route('admin.settings'))->assertOk()
            ->assertSee(trans_choice('messages.federation_visits_30d', 52, ['count' => 52]))
            ->assertSee(__('messages.federation_see_listings'));
    }

    public function test_the_visits_line_handles_zero_and_one(): void
    {
        $this->assertSame('No visits sent to you in the last 30 days', trans_choice('messages.federation_visits_30d', 0, ['count' => 0]));
        $this->assertSame('1 visit sent to you in the last 30 days', trans_choice('messages.federation_visits_30d', 1, ['count' => 1]));
        $this->assertSame('52 visits sent to you in the last 30 days', trans_choice('messages.federation_visits_30d', 52, ['count' => 52]));
    }
}
