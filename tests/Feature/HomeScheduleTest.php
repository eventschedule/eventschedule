<?php

namespace Tests\Feature;

use App\Http\Middleware\ServeHomeSchedule;
use App\Models\DismissedNextStep;
use App\Models\Role;
use App\Models\Setting;
use App\Support\HomeSchedule;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A selfhosted install showing one schedule at its own address (App\Support\HomeSchedule): the
 * root serves the page, the schedule's old address keeps working, every other address stays
 * where it was, and the address handed out for the schedule is the root.
 */
class HomeScheduleTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // A plain selfhost install. The suite loads the path-based routes already; the nexus flag
        // only decides what the root is, which the middleware reads at request time.
        config(['app.hosted' => false, 'app.is_nexus' => false]);
    }

    private function schedule(string $name = 'Example Hall'): Role
    {
        return $this->createRole($this->createOwner(), 'venue', ['name' => $name]);
    }

    public function test_the_root_shows_the_home_schedule_and_its_old_address_still_works(): void
    {
        $role = $this->schedule();
        HomeSchedule::set($role->id);

        $this->get('/')->assertOk()->assertSee('Example Hall');
        // The page sends its own language to the clean address: that is the root here, not the
        // schedule's path the request was read as.
        $this->get('/?lang=en&month=5')->assertRedirect(url('/').'/?month=5');

        // No redirect either way: links already shared keep answering with the page itself.
        $this->get('/'.$role->subdomain)->assertOk()->assertSee('Example Hall');
    }

    public function test_the_address_handed_out_is_the_root_and_only_for_that_schedule(): void
    {
        $home = $this->schedule();
        $other = $this->schedule('Other Room');
        HomeSchedule::set($home->id);

        $this->assertSame(url('/'), $home->fresh()->getGuestUrl());
        $this->assertSame(url('/'), $home->fresh()->getCanonicalUrl());
        $this->assertSame(route('role.view_guest', ['subdomain' => $other->subdomain]), $other->fresh()->getGuestUrl());

        // The page says so in its canonical tag.
        $this->get('/')->assertSee('<link rel="canonical" href="'.url('/'), false);
    }

    /**
     * Short links and sub-schedules hang off the schedule's path, never the root: site/facebook
     * would be another schedule's address, or nobody's.
     */
    public function test_short_links_and_sub_schedules_stay_under_the_schedules_path(): void
    {
        $role = $this->schedule();
        $role->forceFill([
            'social_links' => json_encode([['url' => 'https://www.facebook.com/example-hall']]),
        ])->saveQuietly();
        HomeSchedule::set($role->id);

        $role = $role->fresh();
        $path = route('role.view_guest', ['subdomain' => $role->subdomain]);
        $link = $role->decodeLinks('social_links')[0];
        $slug = $role->shortLinkSlugs()[0] ?? '';

        $this->assertNotSame('', $slug, 'fixture: the link has a short address');
        $this->assertSame($path.'/'.$slug, $role->socialLinkHref($link, 0));
        $this->assertSame($path, $role->guestPathBase());
        $this->assertSame(url('/'), $role->getGuestUrl());
    }

    /** Only a GET or HEAD of the root is read as the schedule: nothing posted to "/" moves. */
    public function test_only_a_get_or_head_of_the_root_is_the_schedule(): void
    {
        $role = $this->schedule();
        HomeSchedule::set($role->id);

        $seen = function (string $method, string $uri) {
            $path = null;
            (new ServeHomeSchedule)->handle(
                Request::create($uri, $method),
                function ($request) use (&$path) {
                    $path = $request->getPathInfo();

                    return response('');
                }
            );

            return $path;
        };

        $this->assertSame('/'.$role->subdomain, $seen('GET', '/'));
        $this->assertSame('/'.$role->subdomain, $seen('HEAD', '/'));
        $this->assertSame('/', $seen('POST', '/'));
        $this->assertSame('/login', $seen('GET', '/login'));
    }

    /** Events keep their addresses under the schedule's path. */
    public function test_event_addresses_do_not_move(): void
    {
        $role = $this->schedule();
        $event = $this->createEvent($role, ['name' => 'Opening Night', 'creator_role_id' => $role->id]);
        $before = $event->getGuestUrl();

        HomeSchedule::set($role->id);

        $this->assertSame($before, $event->fresh()->getGuestUrl());
        $this->assertStringContainsString('/'.$role->subdomain.'/', $before);
    }

    public function test_without_a_choice_the_root_does_not_show_a_schedule(): void
    {
        $this->schedule();

        $this->get('/')->assertDontSee('Example Hall');
    }

    /** A home schedule that was deleted gives the root back. */
    public function test_a_deleted_home_schedule_gives_the_root_back(): void
    {
        $role = $this->schedule();
        HomeSchedule::set($role->id);
        $role->forceFill(['is_deleted' => true])->saveQuietly();

        $this->get('/')->assertDontSee('Example Hall');
    }

    /** Never on a hosted install, which has hosts and custom domains for this. */
    public function test_a_hosted_install_ignores_the_setting(): void
    {
        $role = $this->schedule();
        Setting::set(HomeSchedule::SETTING, (string) $role->id);
        config(['app.hosted' => true]);

        $this->assertNull(HomeSchedule::id());
        $this->assertFalse(HomeSchedule::is($role->fresh()));
    }

    // ------------------------------------------------------------------ settings card

    private function actingAsAdmin()
    {
        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        return $admin;
    }

    public function test_an_admin_chooses_the_home_schedule_and_can_take_it_back(): void
    {
        $role = $this->schedule();
        $this->actingAsAdmin();

        $this->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('id="home-schedule"', false)
            ->assertSee(e(__('messages.home_schedule_login_note', ['url' => route('login')])), false);

        $this->post(route('admin.settings.update_home_schedule'), ['home_schedule' => UrlUtils::encodeId($role->id)])
            ->assertRedirect(route('admin.settings').'#home-schedule');
        $this->assertSame($role->id, HomeSchedule::id());

        $this->post(route('admin.settings.update_home_schedule'), ['home_schedule' => ''])->assertRedirect();
        $this->assertNull(HomeSchedule::id());
    }

    /** Only a schedule the card offered: a deleted one, or a made-up id, is refused. */
    public function test_a_schedule_the_card_did_not_offer_is_refused(): void
    {
        $role = $this->schedule();
        $role->forceFill(['is_deleted' => true])->saveQuietly();
        $this->actingAsAdmin();

        $this->post(route('admin.settings.update_home_schedule'), ['home_schedule' => UrlUtils::encodeId($role->id)])
            ->assertSessionHasErrors('home_schedule');
        $this->assertNull(HomeSchedule::id());
    }

    public function test_someone_who_is_not_an_admin_cannot_choose(): void
    {
        $role = $this->schedule();
        $this->actingAs($this->createOwner());

        $this->post(route('admin.settings.update_home_schedule'), ['home_schedule' => UrlUtils::encodeId($role->id)]);

        $this->assertNull(HomeSchedule::id());
    }

    /** The schedule's address is part of its network listing, so its events go again. */
    public function test_choosing_resends_the_schedules_events_to_the_network(): void
    {
        $role = $this->schedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'federated_at' => now()]);

        HomeSchedule::set($role->id);

        $this->assertNull($event->fresh()->federated_at);
    }

    // ------------------------------------------------------------------ dashboard banner

    public function test_an_admin_with_one_schedule_is_told_about_it_once(): void
    {
        $admin = $this->createOwner(true);
        $role = $this->createRole($admin, 'venue', ['name' => 'Example Hall']);
        $this->actingAs($admin);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(e(__('messages.home_schedule_prompt_title', ['name' => 'Example Hall'])), false)
            ->assertSee(e(__('messages.home_schedule_login_note', ['url' => route('login')])), false);

        $this->post(route('home.home_schedule_prompt_dismiss'))->assertRedirect();

        $this->assertTrue(DismissedNextStep::where('user_id', $admin->id)
            ->where('role_id', $role->id)
            ->where('step_type', DismissedNextStep::HOME_SCHEDULE)
            ->exists());

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee(e(__('messages.home_schedule_prompt_title', ['name' => 'Example Hall'])), false);
    }

    public function test_no_banner_with_two_schedules_once_chosen_or_for_a_non_admin(): void
    {
        $admin = $this->createOwner(true);
        $role = $this->createRole($admin, 'venue', ['name' => 'Example Hall']);

        $this->assertNotNull(HomeSchedule::promptFor($admin));
        $this->assertNull(HomeSchedule::promptFor($this->createOwner()));

        HomeSchedule::set($role->id);
        $this->assertNull(HomeSchedule::promptFor($admin));

        HomeSchedule::set(null);
        $this->createRole($admin, 'talent', ['name' => 'Second']);
        $this->assertNull(HomeSchedule::promptFor($admin));
    }
}
