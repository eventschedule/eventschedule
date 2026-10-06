<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\DemoService;
use App\Services\RealtimeDashboard;
use App\Services\ScheduleRealtime;
use App\Utils\RealtimeTracker;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * /realtime: a schedule owner's live view of their own guest pages.
 *
 * Most of this file is about what the page must NOT do, because the table it reads holds every
 * visit to every schedule on the install and, for visitors who accepted cookies, who they are.
 * Each test names the one-line change that turns it red.
 */
class ScheduleRealtimeTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Setting::set('realtime_enabled', '1');
        Setting::set('realtime_owner_view', '1');
    }

    /** A page view on a schedule's guest page, by a visitor the owner may list unless told otherwise. */
    private function hit(Role $role, array $attributes = []): void
    {
        $now = now();
        $attributes = array_map(fn ($value) => $value instanceof \DateTimeInterface ? RealtimeTracker::ts($value) : $value, $attributes);

        DB::table('realtime_hits')->insert(array_merge([
            'hit_key' => str_pad(dechex(++$this->sequence), 32, '0', STR_PAD_LEFT),
            'visitor_key' => str_pad(dechex($this->sequence), 16, 'a', STR_PAD_LEFT),
            'consented' => true,
            'owner_visible' => true,
            'surface' => 'gp',
            'path' => '/'.$role->subdomain,
            'role_id' => $role->id,
            'device' => 'desktop',
            'browser' => 'Chrome',
            'os' => 'macOS',
            'country' => 'US',
            'hb' => 60,
            'started_at' => RealtimeTracker::ts($now),
            'last_seen_at' => RealtimeTracker::ts($now),
            'engaged_at' => RealtimeTracker::ts($now),
        ], $attributes));
    }

    private function payload(User $user, ?Role $only = null, string $salt = 'salt'): array
    {
        return (new ScheduleRealtime($user, $user->manageableRoles()->pluck('id'), $salt))->payload($only?->id);
    }

    public function test_an_owner_gets_the_page_and_the_poll(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'The Vinyl Room']);
        $event = $this->createEvent($role, ['name' => 'Halloween Warehouse Party']);
        $this->hit($role, ['event_id' => $event->id, 'country' => 'GB', 'device' => 'mobile']);

        $this->actingAs($owner)->get('/realtime')
            ->assertOk()
            ->assertSee('id="schedule-realtime"', false);

        $this->actingAs($owner)->getJson('/realtime/data')
            ->assertOk()
            ->assertJsonPath('state', 'ok')
            ->assertJsonPath('overview.views_5m', 1)
            ->assertJsonPath('overview.visitors_now', 1)
            ->assertJsonPath('overview.views_30m', 1)
            ->assertJsonPath('visitors.now.0.page', 'Halloween Warehouse Party')
            ->assertJsonPath('visitors.now.0.kind', 'event')
            ->assertJsonPath('visitors.now.0.country', 'GB')
            ->assertJsonPath('visitors.now.0.device', 'mobile');
    }

    /**
     * The one that matters most. Mutation: drop `whereIn('role_id', ...)` from fetchRows().
     */
    public function test_another_schedules_visits_never_appear(): void
    {
        $owner = $this->createOwner();
        $mine = $this->createRole($owner, 'venue', ['name' => 'Mine']);
        $theirs = $this->createRole($this->createOwner(), 'venue', ['name' => 'Somebody Elses Room']);
        $secret = $this->createEvent($theirs, ['name' => 'Their Unannounced Show']);

        $this->hit($mine);
        $this->hit($theirs, ['event_id' => $secret->id, 'country' => 'DE']);
        $this->hit($theirs, ['consented' => false, 'visitor_key' => null, 'owner_visible' => false, 'country' => 'FR']);

        $response = $this->actingAs($owner)->getJson('/realtime/data')->assertOk();

        $response->assertJsonPath('overview.views_30m', 1)->assertJsonPath('overview.visitors_now', 1);
        $this->assertSame(['US'], array_column($response->json('breakdowns.countries'), 'key'));

        $raw = $response->getContent();
        $this->assertStringNotContainsString('Their Unannounced Show', $raw);
        $this->assertStringNotContainsString('Somebody Elses Room', $raw);
    }

    /** Mutation: answer a schedule that is not theirs with "all of yours" instead of refusing. */
    public function test_a_schedule_that_is_not_mine_is_refused_on_the_page_and_the_poll(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner);
        $theirs = $this->createRole($this->createOwner());
        $param = '?schedule='.UrlUtils::encodeId($theirs->id);

        $this->actingAs($owner)->get('/realtime'.$param)->assertForbidden();
        $this->actingAs($owner)->getJson('/realtime/data'.$param)->assertForbidden();
        $this->actingAs($owner)->getJson('/realtime/data?schedule=not-an-id')->assertForbidden();
    }

    public function test_the_picker_narrows_to_one_of_my_schedules(): void
    {
        $owner = $this->createOwner();
        $first = $this->createRole($owner, 'venue', ['name' => 'First']);
        $second = $this->createRole($owner, 'venue', ['name' => 'Second']);
        $this->hit($first);
        $this->hit($second);
        $this->hit($second);

        $this->actingAs($owner)->getJson('/realtime/data')->assertJsonPath('overview.views_30m', 3);
        $this->actingAs($owner)->getJson('/realtime/data?schedule='.UrlUtils::encodeId($second->id))
            ->assertOk()->assertJsonPath('overview.views_30m', 2);
    }

    /**
     * On hosted a team member reaches somebody else's schedule only while its plan includes a
     * team (User::manageableRoles(), what Sales uses). Mutation: scope by editor() instead.
     */
    public function test_a_schedule_my_plan_closes_to_me_is_left_out_and_refused(): void
    {
        $member = $this->createOwner();
        $own = $this->createRole($member, 'venue', ['name' => 'My Own']);

        $closed = $this->createFreeRole(null, 'venue', ['name' => 'Lapsed Room']);
        $closed->users()->attach($member->id, ['level' => 'admin']);
        $this->assertFalse($closed->fresh()->isEnterprise());

        $this->hit($own);
        $this->hit($closed);

        $this->actingAs($member)->getJson('/realtime/data')->assertOk()->assertJsonPath('overview.views_30m', 1);
        $this->actingAs($member)->getJson('/realtime/data?schedule='.UrlUtils::encodeId($closed->id))->assertForbidden();
    }

    /**
     * Nothing that says who a visitor is leaves the server, and a row is exactly these eight
     * fields. Mutation: add any column to COLUMNS and print it, or return the visitor key as id.
     */
    public function test_the_response_names_nobody(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $visitor = User::factory()->create(['name' => 'Dana Whitlock', 'email' => 'dana.whitlock@gmail.com']);

        $this->hit($role, [
            'hit_key' => str_repeat('c', 32),
            'visitor_key' => 'feedfacefeedface',
            'user_id' => $visitor->id,
            'path' => '/'.$role->subdomain.'/private-path-segment',
            'title' => 'A Title Only Admins See',
            'browser' => 'Vivaldi',
            'os' => 'Haiku',
            'source_channel' => 'campaign',
            'source_name' => null,
            'utm_campaign' => 'spring-relaunch',
            'is_entrance' => true,
        ]);

        $response = $this->actingAs($owner)->getJson('/realtime/data')->assertOk();
        $raw = $response->getContent();

        foreach ([
            'feedfacefeedface', str_repeat('c', 32), 'Dana Whitlock', 'dana.whitlock@gmail.com',
            UrlUtils::encodeId($visitor->id), 'private-path-segment', 'A Title Only Admins See',
            'Vivaldi', 'Haiku', 'spring-relaunch',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $raw);
        }

        $this->assertSame(
            ['id', 'country', 'device', 'page', 'kind', 'schedule', 'seconds', 'left_ago'],
            array_keys($response->json('visitors.now.0'))
        );
        $this->assertMatchesRegularExpression('/^[0-9a-f]{12}$/', $response->json('visitors.now.0.id'));
    }

    /**
     * Two owners of one schedule, or one owner in two sessions, cannot use a row's handle to work
     * out that they are one visitor. Mutation: leave the viewer or the salt out of handle().
     */
    public function test_a_handle_means_nothing_outside_the_session_that_is_looking(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $partner = $this->createOwner();
        $role->users()->attach($partner->id, ['level' => 'admin']);
        $this->hit($role);

        $id = fn (User $user, string $salt) => $this->payload($user, null, $salt)['visitors']['now'][0]['id'];

        $this->assertSame($id($owner, 'one'), $id($owner, 'one'), 'stable within a session, so a row keeps its place');
        $this->assertNotSame($id($owner, 'one'), $id($owner, 'two'));
        $this->assertNotSame($id($owner, 'one'), $id($partner, 'one'));
    }

    /**
     * Platform admins and the schedule's own team are not its audience. The team bit is signed at
     * render, so it holds for a member who declined cookies too.
     * Mutation: drop either `where('is_admin', false)` or `where('is_team', false)`.
     */
    public function test_admin_and_team_visits_are_left_out(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->hit($role);
        $this->hit($role, ['is_admin' => true]);
        $this->hit($role, ['is_team' => true]);
        $this->hit($role, ['is_team' => true, 'consented' => false, 'visitor_key' => null, 'owner_visible' => false]);

        $payload = $this->payload($owner);

        $this->assertSame(1, $payload['overview']['views_30m']);
        $this->assertSame(1, $payload['overview']['visitors_now']);
    }

    /**
     * A visitor who declined cookies, and one who accepted before the banner named the organizer
     * (owner_visible = 0), are both page views and never rows.
     * Mutation: list on `consented` alone.
     */
    public function test_only_a_visitor_who_agreed_to_the_organizer_seeing_is_listed(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->hit($role, ['country' => 'GB']);
        $this->hit($role, ['consented' => false, 'visitor_key' => null, 'owner_visible' => false, 'country' => 'DE']);
        $this->hit($role, ['owner_visible' => false, 'country' => 'FR']);

        $payload = $this->payload($owner);

        $this->assertSame(3, $payload['overview']['views_30m']);
        $this->assertSame(1, $payload['overview']['visitors_now']);
        $this->assertSame(['GB'], array_column($payload['visitors']['now'], 'country'));
        $this->assertSame(1, $payload['visitors']['now_total']);
    }

    /**
     * A later page view inherits the source of wherever the visit began, which may be another
     * schedule's page. Mutation: count every row with a source instead of entrances only.
     */
    public function test_sources_come_only_from_the_page_view_a_visit_began_on(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->hit($role, ['is_entrance' => true, 'source_channel' => 'social', 'source_name' => 'instagram.com']);
        $this->hit($role, ['is_entrance' => true, 'source_channel' => 'direct']);
        // Arrived on somebody else's page from a newsletter, then clicked through to mine.
        $this->hit($role, ['is_entrance' => false, 'source_channel' => 'email', 'source_name' => 'their-newsletter']);

        $sources = $this->payload($owner)['breakdowns']['sources'];

        $this->assertEqualsCanonicalizing(['instagram.com', __('messages.direct')], array_column($sources, 'label'));
        $this->assertSame(2, array_sum(array_column($sources, 'views')));
        $this->assertStringNotContainsString('their-newsletter', json_encode($sources));
    }

    /**
     * Top pages, countries and devices each add up to the page views above them: past the listed
     * rows, the rest is one "other" row. Mutation: cap the list without the fold.
     */
    public function test_each_page_view_breakdown_adds_up_to_the_total(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $events = collect(range(1, 8))->map(fn ($n) => $this->createEvent($role, ['name' => 'Event '.$n]));

        foreach (['US', 'GB', 'DE', 'FR', 'IE', 'NL', 'ES', 'SE'] as $index => $country) {
            $this->hit($role, ['country' => $country, 'event_id' => $events[$index]->id, 'device' => $index % 2 ? 'mobile' : 'tablet']);
        }
        $this->hit($role, ['country' => 'US', 'device' => 'unknown']);
        // A view whose country could not be found (a private address, a lookup that missed) is
        // still a view: it goes in the "other" row. Mutation: leave such views out of Countries.
        $this->hit($role, ['country' => null]);

        $payload = $this->payload($owner);

        $this->assertSame(10, $payload['overview']['views_30m']);
        foreach (['pages', 'countries', 'devices'] as $card) {
            $this->assertSame(10, array_sum(array_column($payload['breakdowns'][$card], 'views')), $card);
        }
        $this->assertCount(ScheduleRealtime::BREAKDOWN_ROWS + 1, $payload['breakdowns']['countries']);
        $this->assertTrue($payload['breakdowns']['countries'][ScheduleRealtime::BREAKDOWN_ROWS]['other']);
        // A device the detector could not name is "other", never a blank row.
        $this->assertContains('other', array_column($payload['breakdowns']['devices'], 'key'));
        $this->assertSame(array_sum($payload['minutes']), $payload['overview']['views_30m']);
    }

    /**
     * A calendar embedded on someone's own website is not a person on a schedule page: its views
     * have a row of their own and are in nothing else. Mutation: count embed rows as page views.
     */
    public function test_embedded_calendar_views_are_counted_apart(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->hit($role);
        $this->hit($role, ['surface' => 'embed', 'consented' => false, 'visitor_key' => null, 'owner_visible' => false]);
        $this->hit($role, ['surface' => 'ap', 'path' => '/'.$role->subdomain.'/schedule']);

        $overview = $this->payload($owner)['overview'];

        $this->assertSame(1, $overview['views_30m']);
        $this->assertSame(1, $overview['embed_views_30m']);
    }

    /**
     * Someone who opened a page forty minutes ago and is still on it is a visitor now and not a
     * page view of the last half hour; someone who has left is listed under Earlier with when.
     * Mutation: count a row as a page view by last_seen_at instead of started_at.
     */
    public function test_now_earlier_and_the_window_follow_the_shared_rules(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->hit($role, ['started_at' => now()->subMinutes(40), 'engaged_at' => now()->subMinutes(40), 'last_seen_at' => now()->subSeconds(20)]);
        $this->hit($role, ['started_at' => now()->subMinutes(12), 'engaged_at' => now()->subMinutes(12), 'last_seen_at' => now()->subMinutes(10), 'ended_at' => now()->subMinutes(10)]);

        $payload = $this->payload($owner);

        $this->assertSame(1, $payload['overview']['visitors_now']);
        $this->assertSame(1, $payload['overview']['views_30m'], 'only the page view that began in the half hour');
        $this->assertSame(0, $payload['overview']['views_5m']);
        $this->assertEqualsWithDelta(720, $payload['overview']['last_view_ago'], 5);
        $this->assertGreaterThanOrEqual(2400, $payload['visitors']['now'][0]['seconds']);
        $this->assertCount(1, $payload['visitors']['earlier']);
        $this->assertEqualsWithDelta(600, $payload['visitors']['earlier'][0]['left_ago'], 5);
        $this->assertNull($payload['visitors']['earlier'][0]['seconds']);
    }

    /**
     * The two Realtime pages read the same table through different queries; "now" and the minute
     * a page view belongs to must not come to mean two things. Mutation: change either rule in
     * one service instead of in RealtimeRows.
     */
    public function test_both_realtime_pages_agree_on_now_and_on_the_minutes(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        foreach ([0, 20, 95, 150, 151, 400, 1750, 1800, 1830] as $secondsAgo) {
            $at = now()->subSeconds($secondsAgo);
            $this->hit($role, ['started_at' => $at, 'engaged_at' => $at, 'last_seen_at' => $at]);
        }

        $mine = $this->payload($owner);
        $admin = (new RealtimeDashboard)->payload();

        $this->assertSame($admin['overview']['now'], $mine['overview']['visitors_now']);
        $this->assertSame(
            array_map(fn ($bucket) => $bucket['consented'] + $bucket['unidentified'], $admin['minutes']),
            $mine['minutes']
        );
    }

    /**
     * Either switch off, the shared demo account, or nobody's schedule to show: no page, no poll,
     * no tile data. Mutation: gate on RealtimeTracker::enabled() alone.
     */
    public function test_where_the_install_does_not_offer_it_there_is_nothing_to_reach(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner);
        $attendee = $this->createOwner();

        $reach = function (User $user): array {
            return [
                $this->actingAs($user)->get('/realtime')->status(),
                $this->actingAs($user)->getJson('/realtime/data')->status(),
                $this->actingAs($user)->getJson('/realtime/summary')->status(),
            ];
        };

        $this->assertSame([200, 200, 200], $reach($owner));
        $this->assertSame([404, 404, 404], $reach($attendee), 'someone who runs no schedule');

        Setting::set('realtime_owner_view', '0');
        $this->assertSame([404, 404, 404], $reach($owner), 'the owner switch is off');
        $this->assertTrue(RealtimeTracker::enabled(), 'and that did not switch Realtime itself off');

        Setting::set('realtime_owner_view', '1');
        Setting::set('realtime_enabled', '0');
        $this->assertSame([404, 404, 404], $reach($owner), 'Realtime is off');

        Setting::set('realtime_enabled', '1');
        $demo = User::factory()->create(['email' => DemoService::DEMO_EMAIL, 'email_verified_at' => now()]);
        $this->createRole($demo);
        $this->assertSame([404, 404, 404], $reach($demo), 'anyone can sign in as the demo account');
    }

    /**
     * On an install that is not eventschedule.com the owner view is off until its operator turns
     * it on: they switched Realtime on under a setting that said only administrators see it.
     * Mutation: default the second switch to the first.
     */
    public function test_off_the_nexus_the_owner_view_waits_for_its_own_switch(): void
    {
        config(['app.is_nexus' => false]);
        DB::table('settings')->where('key', 'realtime_owner_view')->delete();
        Cache::flush();
        Setting::set('realtime_enabled', '1');

        $this->assertTrue(RealtimeTracker::enabled());
        $this->assertFalse(RealtimeTracker::ownerViewEnabled());

        Setting::set('realtime_owner_view', '1');
        $this->assertTrue(RealtimeTracker::ownerViewEnabled());
    }

    /** The dashboard tile, and the "visitors now" column of the schedules list. */
    public function test_the_summary_is_the_same_numbers_by_schedule(): void
    {
        $owner = $this->createOwner();
        $first = $this->createRole($owner);
        $second = $this->createRole($owner);
        $this->hit($first);
        $this->hit($second);
        $this->hit($second);
        $this->hit($second, ['consented' => false, 'visitor_key' => null, 'owner_visible' => false]);

        $response = $this->actingAs($owner)->getJson('/realtime/summary')->assertOk();

        $response->assertJsonPath('views_5m', 4)->assertJsonPath('visitors_now', 3)->assertJsonPath('views_30m', 4);
        $this->assertSame(4, array_sum($response->json('minutes')));
        // Keyed by encoded id: a raw schedule id is never printed for a browser.
        $this->assertEquals(
            [UrlUtils::encodeId($first->id) => 1, UrlUtils::encodeId($second->id) => 2],
            $response->json('by_schedule')
        );
    }

    /**
     * A page that polls every fifteen seconds must not make its owner "active" every day they leave
     * a tab open. Mutation: count any 200 instead of a document the browser asked for.
     */
    public function test_the_poll_is_not_a_day_of_use(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner);

        $this->actingAs($owner)->getJson('/realtime/data')->assertOk();
        $this->actingAs($owner)->getJson('/realtime/summary')->assertOk();

        $this->assertSame(0, DB::table('user_active_days')->where('user_id', $owner->id)->count());
    }

    /**
     * /admin/realtime is about people across the whole install; none of its page belongs in a
     * document an organizer can open. Mutation: include an admin/realtime partial in the view.
     */
    public function test_the_owner_page_carries_none_of_the_admin_page(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner);

        $html = $this->actingAs($owner)->get('/realtime')->assertOk()->getContent();

        foreach (['realtime-app', __('messages.realtime_show_admins'), __('messages.realtime_signed_in'), __('messages.realtime_open_chat'), 'admin/realtime'] as $needle) {
            $this->assertStringNotContainsString($needle, $html);
        }
    }
}
