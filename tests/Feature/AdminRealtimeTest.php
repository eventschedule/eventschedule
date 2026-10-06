<?php

namespace Tests\Feature;

use App\Models\RealtimeHit;
use App\Models\Setting;
use App\Models\User;
use App\Services\AuditService;
use App\Services\RealtimeDashboard;
use App\Utils\RealtimeTracker;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * /admin/realtime: access, the JSON payload's definitions, the settings switch, and the people
 * rules that decide who is listed.
 */
class AdminRealtimeTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Setting::set('realtime_enabled', '1');
    }

    private function admin(): User
    {
        return $this->createOwner(true);
    }

    private function actingAsAdmin(User $admin)
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
    }

    public function test_an_admin_gets_the_page_and_the_json(): void
    {
        $admin = $this->admin();
        $this->hit(['visitor_key' => 'aaaaaaaaaaaaaaaa', 'path' => '/pricing', 'title' => 'Pricing']);

        $this->actingAsAdmin($admin)->get('/admin/realtime')
            ->assertOk()
            ->assertSee('id="realtime-app"', false);

        $this->actingAsAdmin($admin)->getJson('/admin/realtime/data')
            ->assertOk()
            ->assertJsonPath('state', 'ok')
            ->assertJsonPath('overview.now', 1)
            ->assertJsonPath('visitors.now.0.page.label', 'Pricing');
    }

    public function test_the_admin_page_itself_never_carries_the_beacon(): void
    {
        $this->actingAsAdmin($this->admin())->get('/admin/realtime')
            ->assertOk()
            ->assertDontSee('api\/realtime', false);
    }

    public function test_a_non_admin_cannot_reach_it(): void
    {
        $user = $this->createOwner();

        $this->actingAs($user)->get('/admin/realtime')->assertRedirect();
        $this->actingAs($user)->getJson('/admin/realtime/data')->assertStatus(403);

        // Nor switch it off (which would also purge every row).
        $this->actingAs($user)->post(route('admin.settings.update_realtime'), ['realtime_enabled' => '0'])->assertRedirect();
        $this->assertSame('1', Setting::get('realtime_enabled'));
    }

    public function test_a_lapsed_reauth_window_answers_the_poll_with_423(): void
    {
        $this->actingAs($this->admin())->getJson('/admin/realtime/data')->assertStatus(423);

        // The page itself sends the admin to confirm their password, not to the dashboard.
        $this->actingAs($this->admin())->get('/admin/realtime')->assertRedirect(route('admin.password.confirm.show'));
    }

    public function test_invalid_filter_values_are_dropped_not_refused(): void
    {
        $this->actingAsAdmin($this->admin())
            ->getJson('/admin/realtime/data?surface=nope&country=Germany&page=x&who=everyone&expand=bad')
            ->assertOk()
            ->assertJsonPath('filters', [])
            ->assertJsonPath('who', 'all');
    }

    public function test_the_payload_never_carries_an_ip_or_a_raw_user_id(): void
    {
        $user = $this->createOwner();

        // Through the real endpoint, from a known address, so the assertion below could fail.
        $context = ['s' => 'ap', 'p' => '/home', 'pt' => '/home', 'r' => '', 'e' => '', 'u' => UrlUtils::encodeId($user->id),
            'a' => 0, 'd' => 0, 'em' => 0, 'hb' => 60, 't' => RealtimeTracker::now()->timestamp];
        $context['sig'] = RealtimeTracker::sign($context);
        $this->call('POST', '/api/realtime', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US',
            'REMOTE_ADDR' => '203.0.113.77',
        ], json_encode(['t' => 'pv', 'm' => 'f', 'k' => str_repeat('b', 32), 'c' => $context]))->assertNoContent();
        $this->assertSame(1, RealtimeHit::count());
        DB::table('realtime_hits')->update(['engaged_at' => RealtimeTracker::ts(now())]);

        // The Sign-ups card reads audit_logs, which does keep an address: it must not travel either.
        DB::table('audit_logs')->insert(['user_id' => $user->id, 'action' => AuditService::AUTH_REGISTER, 'model_type' => 'User', 'model_id' => $user->id, 'ip_address' => '203.0.113.77', 'user_agent' => 'Mozilla/5.0', 'created_at' => now()]);

        $json = $this->actingAsAdmin($this->admin())->getJson('/admin/realtime/data')->assertOk()->getContent();

        $this->assertStringContainsString('"signup:', $json, 'the sign-up row is in the payload this checks');
        $this->assertStringNotContainsString('203.0.113.77', $json);
        $this->assertStringNotContainsString('"user_id"', $json);
        $this->assertStringContainsString('u:'.UrlUtils::encodeId($user->id), $json);
    }

    public function test_tracking_off_shows_the_off_state(): void
    {
        Setting::set('realtime_enabled', '0');

        $this->actingAsAdmin($this->admin())->getJson('/admin/realtime/data')->assertOk()->assertExactJson(['state' => 'off']);
    }

    public function test_switching_off_saves_zero_on_the_nexus_purges_rows_and_leaves_header_code_alone(): void
    {
        config(['app.is_nexus' => true]);
        Setting::set('custom_header_code', '<!-- keep me -->');
        $this->hit(['visitor_key' => 'cccccccccccccccc']);

        $this->actingAsAdmin($this->admin())->post(route('admin.settings.update_realtime'), ['realtime_enabled' => '0'])
            ->assertRedirect();

        $this->assertSame('0', Setting::get('realtime_enabled'));
        $this->assertFalse(RealtimeTracker::enabled());
        $this->assertSame(0, RealtimeHit::count());
        $this->assertSame('<!-- keep me -->', Setting::get('custom_header_code'));
    }

    public function test_switching_on_stamps_when_it_started(): void
    {
        Setting::set('realtime_enabled', '0');

        $this->actingAsAdmin($this->admin())->post(route('admin.settings.update_realtime'), ['realtime_enabled' => '1']);

        $this->assertSame('1', Setting::get('realtime_enabled'));
        $this->assertNotNull(Setting::get('realtime_enabled_at'));
        $this->assertSame('waiting', (new RealtimeDashboard)->payload()['state']);
    }

    public function test_count_only_views_are_counted_but_never_listed(): void
    {
        $this->hit(['visitor_key' => 'dddddddddddddddd', 'path' => '/pricing']);
        $this->hit(['consented' => false, 'visitor_key' => null, 'path' => '/pricing']);
        $this->hit(['consented' => false, 'visitor_key' => null, 'path' => '/features']);

        $payload = (new RealtimeDashboard)->payload();

        $this->assertSame(1, $payload['overview']['now']);
        $this->assertSame(1, $payload['counts']['all']);
        $this->assertSame(1, $payload['overview']['win_views']);
        $this->assertSame(2, $payload['overview']['unidentified_views']);
        $this->assertSame(33, $payload['overview']['consent_share']);

        // The total first, then the split, each phrase with its own unit.
        $this->assertSame('3 page views', $payload['overview']['phrases']['views_total']);
        $this->assertSame('1 page view · 1 visitor', $payload['overview']['phrases']['accepted']);
        $this->assertSame('2 page views', $payload['overview']['phrases']['not_accepted']);
        $this->assertSame(3, array_sum(array_map(fn ($m) => $m['consented'] + $m['unidentified'], $payload['minutes'])));
        $this->assertSame(2, collect($payload['breakdowns']['pages'])->firstWhere('key', 'p:wp:/pricing')['views']);
    }

    public function test_a_new_users_pre_sign_up_pages_join_their_account_but_an_admins_private_window_does_not(): void
    {
        $user = $this->createOwner();
        $admin = $this->admin();

        $this->hit(['visitor_key' => '1111111111111111', 'path' => '/pricing', 'started_at' => now()->subMinutes(5)]);
        $this->hit(['visitor_key' => '1111111111111111', 'user_id' => $user->id, 'surface' => 'ap', 'path' => '/home']);

        $this->hit(['visitor_key' => '2222222222222222', 'user_id' => $admin->id, 'is_admin' => true, 'surface' => 'ap', 'path' => '/home']);
        $this->hit(['visitor_key' => '2222222222222222', 'path' => '/pricing']);

        $people = collect((new RealtimeDashboard)->payload()['visitors'])->only(['now', 'earlier'])->flatten(1);

        $merged = $people->firstWhere('id', 'u:'.UrlUtils::encodeId($user->id));
        $this->assertSame(2, $merged['views']);
        $this->assertContains('v:1111111111111111', $merged['aliases']);

        $this->assertNotNull($people->firstWhere('id', 'v:2222222222222222'), "the admin's private window stays visible");
        $this->assertCount(2, $people, 'the admin is hidden by default');

        // With admins shown, the admin is listed - and still never absorbs the anonymous browsing
        // that shares their IP and browser.
        $shown = collect((new RealtimeDashboard(admins: true))->payload()['visitors'])->only(['now', 'earlier'])->flatten(1);
        $this->assertNotNull($shown->firstWhere('id', 'v:2222222222222222'));
        $this->assertSame(1, $shown->firstWhere('id', 'u:'.UrlUtils::encodeId($admin->id))['views']);
    }

    public function test_a_deleted_account_is_anonymous(): void
    {
        $this->hit(['visitor_key' => '3333333333333333', 'user_id' => 999999, 'surface' => 'ap', 'path' => '/home']);

        $person = (new RealtimeDashboard)->payload()['visitors']['now'][0];

        $this->assertSame('anon', $person['kind']);
        $this->assertSame('v:3333333333333333', $person['id']);
    }

    public function test_right_now_follows_each_rows_own_heartbeat_and_ended_rows_drop_out(): void
    {
        $this->hit(['visitor_key' => '4444444444444444', 'hb' => 120, 'last_seen_at' => now()->subSeconds(200)]);
        $this->hit(['visitor_key' => '5555555555555555', 'hb' => 60, 'last_seen_at' => now()->subSeconds(200)]);
        $this->hit(['visitor_key' => '6666666666666666', 'ended_at' => now()]);

        $payload = (new RealtimeDashboard)->payload();

        $this->assertSame(['v:4444444444444444'], array_column($payload['visitors']['now'], 'id'));
        $this->assertSame(3, $payload['counts']['all']);
    }

    public function test_filters_match_page_views_and_the_current_page_decides_right_now(): void
    {
        $this->hit(['visitor_key' => '7777777777777777', 'country' => 'DE', 'path' => '/pricing', 'started_at' => now()->subMinutes(10), 'last_seen_at' => now()->subMinutes(9), 'ended_at' => now()->subMinutes(9)]);
        $this->hit(['visitor_key' => '7777777777777777', 'country' => 'DE', 'path' => '/features']);
        $this->hit(['visitor_key' => '8888888888888888', 'country' => 'FR', 'path' => '/pricing']);

        $payload = RealtimeDashboard::fromRequest(Request::create('/', 'GET', ['page' => 'p:wp:/pricing']))->payload();

        $this->assertSame(2, $payload['counts']['all'], 'both visitors viewed /pricing in the window');
        $this->assertSame(1, $payload['overview']['now'], 'only the French visitor is on /pricing now');
        $this->assertSame(2, $payload['overview']['now_total']);
    }

    public function test_activity_reads_the_audit_log_with_its_traps_handled(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Jazz Nights']);
        $event = $this->createEvent($role, ['name' => 'Salsa Night']);
        $sale = $this->createSale($event, $role, ['payment_amount' => 40, 'status' => 'paid']);
        DB::table('events')->where('id', $event->id)->update(['ticket_currency_code' => 'EUR']);

        AuditService::log(AuditService::AUTH_REGISTER, $owner->id, 'User', $owner->id);
        AuditService::log(AuditService::AUTH_REGISTER, $owner->id); // a follower account: left out
        AuditService::log(AuditService::SCHEDULE_CREATE, $owner->id, 'Role', $role->id);
        AuditService::log(AuditService::SALE_PAID, null, 'Sale', $sale->id, ['status' => 'unpaid'], ['status' => 'paid']);
        AuditService::log(AuditService::SALE_PAID, null, 'Sale', $sale->id, ['status' => 'unpaid'], ['status' => 'amount_mismatch']);
        DB::table('audit_logs')->insert(['user_id' => $owner->id, 'action' => AuditService::EVENT_CREATE, 'model_type' => 'Event', 'model_id' => $event->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Symfony', 'created_at' => now()]);

        $activity = (new RealtimeDashboard)->payload()['activity'];
        $types = array_column($activity['items'], 'type');

        $this->assertSame(1, count(array_keys($types, 'signup')));
        $this->assertSame(1, count(array_keys($types, 'order')), 'amount_mismatch is not an order');
        $this->assertNotContains('events', $types, 'scheduler imports are not people adding events');
        $order = collect($activity['items'])->firstWhere('type', 'order');
        $this->assertStringContainsString('Salsa Night', $order['text']);
        $this->assertStringContainsString('40', $order['text']);
        $this->assertSame(
            ['signup' => 1, 'schedule' => 1, 'events' => 0, 'order' => 1],
            collect($activity['stats'])->pluck('count', 'type')->all(),
            'the count buttons; no Upgrades button on a day without one'
        );
    }

    public function test_the_sign_ups_card_lists_who_the_button_counts_and_follows_the_funnels_steps(): void
    {
        $ticketed = $this->signUp(['name' => 'Tina Ticket']);
        $this->createTicket($this->createEvent($this->createRole($ticketed, 'venue', ['name' => 'Ticket Hall'])));

        // A deleted schedule still counts as saved, as it does in Insights, so the step after it
        // can never be the larger one. It is counted, not linked.
        $withEvent = $this->signUp(['name' => 'Eve Event']);
        $gone = $this->createRole($withEvent, 'venue', ['name' => 'Gone Hall']);
        $this->createEvent($gone);
        DB::table('roles')->where('id', $gone->id)->update(['is_deleted' => true]);

        // An anonymous guest submission is not their own event, and an add-on is not a ticket type.
        $withSchedule = $this->signUp(['name' => 'Sam Schedule']);
        $submitted = $this->createEvent($this->createRole($withSchedule, 'venue', ['name' => 'Sam Hall']), ['is_guest_submission' => true]);
        $this->createTicket($submitted, ['is_addon' => true]);

        $this->signUp(['name' => 'Nora New']);
        $this->signUp(['name' => 'Fay Follow', 'signup_intent' => 'follow']);

        // The follower door (no model type): an account made by following a schedule is not a sign-up.
        AuditService::log(AuditService::AUTH_REGISTER, $this->createOwner()->id);

        $activity = (new RealtimeDashboard)->payload()['activity'];
        $signups = $activity['signups'];
        $rows = collect($signups['rows'])->keyBy('name');

        $this->assertSame(5, collect($activity['stats'])->firstWhere('type', 'signup')['count']);
        $this->assertSame(5, $signups['total']);
        $this->assertCount(5, $signups['rows'], 'the card lists exactly the people the button counts');
        $this->assertSame([5, 3, 2, 1], array_column($signups['steps'], 'count'), 'each step counts the people who reached it');
        $this->assertSame(5, array_sum($signups['hours']));
        $this->assertArrayNotHasKey('marks', $signups, 'the chart marks travel in minutes, not twice');

        $this->assertSame(3, $rows['Tina Ticket']['stage']);
        $this->assertSame('Ticket Hall', $rows['Tina Ticket']['schedule']['name']);
        $this->assertSame(2, $rows['Eve Event']['stage']);
        $this->assertNull($rows['Eve Event']['schedule']);
        $this->assertSame(1, $rows['Sam Schedule']['stage']);
        $this->assertSame(0, $rows['Nora New']['stage']);
        $this->assertNull($rows['Nora New']['intent_label'], 'an organizer with nothing yet shows an empty meter');
        $this->assertSame('follow', $rows['Fay Follow']['intent_label'], 'someone who came to follow is labelled, not shown as stalled');

        // Its feed item shares the row's key, which is what lets one highlight cover both.
        $this->assertContains($rows['Nora New']['key'], array_column($activity['items'], 'key'));
    }

    public function test_an_admins_own_sign_up_is_hidden_until_admins_are_shown(): void
    {
        $admin = $this->admin();
        AuditService::log(AuditService::AUTH_REGISTER, $admin->id, 'User', $admin->id);

        $hidden = (new RealtimeDashboard)->payload()['activity']['signups'];
        $this->assertSame(0, $hidden['total']);
        $this->assertSame([], $hidden['rows']);

        $this->assertSame(1, (new RealtimeDashboard(admins: true))->payload()['activity']['signups']['total']);
    }

    public function test_a_count_button_lists_everything_it_counted_even_past_the_newest_twenty(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Jazz Nights']);
        $sale = $this->createSale($this->createEvent($role, ['name' => 'Salsa Night']), $role, ['payment_amount' => 40, 'status' => 'paid']);

        // One order this morning, then 25 newer items of another kind.
        AuditService::log(AuditService::SALE_PAID, null, 'Sale', $sale->id, ['status' => 'unpaid'], ['status' => 'paid']);
        DB::table('audit_logs')->update(['created_at' => now()->subHours(10)]);
        for ($i = 0; $i < 25; $i++) {
            AuditService::log(AuditService::SCHEDULE_CREATE, $owner->id, 'Role', $role->id);
        }

        $everything = (new RealtimeDashboard)->payload()['activity'];
        $this->assertCount(20, $everything['items']);
        $this->assertNotContains('order', array_column($everything['items'], 'type'), 'the order is older than the newest 20');
        $this->assertSame(1, collect($everything['stats'])->firstWhere('type', 'order')['count']);

        $orders = RealtimeDashboard::fromRequest(Request::create('/', 'GET', ['feed' => 'order']))->payload()['activity'];
        $this->assertSame('order', $orders['feed']);
        $this->assertSame(['order'], array_column($orders['items'], 'type'), 'filtered before the cap, so the button lists what it counted');

        // An unknown kind is dropped, like every other query value.
        $this->assertNull(RealtimeDashboard::fromRequest(Request::create('/', 'GET', ['feed' => 'nope']))->payload()['activity']['feed']);
    }

    public function test_the_area_buttons_count_the_people_who_are_in_each_part_of_the_site_right_now(): void
    {
        config(['app.is_nexus' => true]);
        $inApp = $this->createOwner();

        $this->hit(['visitor_key' => 'a1a1a1a1a1a1a1a1', 'user_id' => $inApp->id, 'surface' => 'ap', 'path' => '/home']);
        $this->hit(['visitor_key' => 'a2a2a2a2a2a2a2a2', 'surface' => 'wp', 'path' => '/pricing']);
        $this->hit(['visitor_key' => 'a3a3a3a3a3a3a3a3', 'surface' => 'wp', 'path' => '/features']);
        // Read the marketing site, now on a schedule page: a person is where their CURRENT page is.
        $this->hit(['visitor_key' => 'a4a4a4a4a4a4a4a4', 'surface' => 'wp', 'path' => '/', 'started_at' => now()->subMinutes(5), 'last_seen_at' => now()->subMinutes(4), 'ended_at' => now()->subMinutes(4)]);
        $this->hit(['visitor_key' => 'a4a4a4a4a4a4a4a4', 'surface' => 'gp', 'path' => '/jazz']);
        // Left: counted in the 30 minutes, not in "right now".
        $this->hit(['visitor_key' => 'a5a5a5a5a5a5a5a5', 'surface' => 'ap', 'path' => '/home', 'ended_at' => now()]);
        // Has not accepted cookies: a page view, never a person.
        $this->hit(['consented' => false, 'visitor_key' => null, 'surface' => 'ap', 'path' => '/home']);

        $areas = fn (array $query = []) => collect(RealtimeDashboard::fromRequest(Request::create('/', 'GET', $query))->payload()['overview']['now_by_surface'])
            ->pluck('count', 'key')->all();

        $this->assertSame(['wp' => 2, 'gp' => 1, 'ap' => 1], $areas(), 'fixed order, and no Sign up & log in while nobody is on it');

        // Pressing one area must not zero the others: the buttons are how you get to them.
        $this->assertSame(['wp' => 2, 'gp' => 1, 'ap' => 1], $areas(['surface' => 'ap']));
        $this->assertSame(1, RealtimeDashboard::fromRequest(Request::create('/', 'GET', ['surface' => 'ap']))->payload()['overview']['now']);

        // The other filters still narrow them.
        $this->assertSame(['wp' => 1, 'gp' => 0, 'ap' => 0], $areas(['page' => 'p:wp:/pricing']));

        // Someone on the sign-up page gets a button for as long as they are there.
        $this->hit(['visitor_key' => 'a6a6a6a6a6a6a6a6', 'surface' => 'auth', 'path' => '/sign_up']);
        $this->assertSame(['wp' => 2, 'gp' => 1, 'ap' => 1, 'auth' => 1], $areas());

        // An install without the marketing site has no button for it.
        config(['app.is_nexus' => false]);
        $this->assertSame(['gp', 'ap', 'auth'], array_keys($areas()));
    }

    public function test_a_sign_up_carries_the_area_the_person_is_in_only_while_they_are_on_the_site(): void
    {
        $here = $this->signUp(['name' => 'Hana Here']);
        $gone = $this->signUp(['name' => 'Gil Gone']);
        $this->hit(['visitor_key' => 'b1b1b1b1b1b1b1b1', 'user_id' => $here->id, 'surface' => 'ap', 'path' => '/home']);
        $this->hit(['visitor_key' => 'b2b2b2b2b2b2b2b2', 'user_id' => $gone->id, 'surface' => 'ap', 'path' => '/home', 'ended_at' => now()]);

        $activity = (new RealtimeDashboard)->payload()['activity'];
        $rows = collect($activity['signups']['rows'])->keyBy('name');

        $this->assertSame('now', $rows['Hana Here']['on_site']);
        $this->assertSame('ap', $rows['Hana Here']['surface']);
        $this->assertSame('App', $rows['Hana Here']['surface_label']);
        $this->assertSame('ap', collect($activity['items'])->firstWhere('key', $rows['Hana Here']['key'])['surface'], 'the feed item says it too');

        $this->assertSame('recent', $rows['Gil Gone']['on_site']);
        $this->assertNull($rows['Gil Gone']['surface'], 'where someone was before they left says nothing about now');
    }

    public function test_a_sign_up_is_marked_on_the_minute_it_happened_in(): void
    {
        $recent = $this->signUp(['name' => 'Rae Recent']);
        $earlier = $this->signUp(['name' => 'Ed Earlier']);
        DB::table('audit_logs')->where('user_id', $recent->id)->update(['created_at' => now()->subMinutes(12)]);
        DB::table('audit_logs')->where('user_id', $earlier->id)->update(['created_at' => now()->subMinutes(40)]);

        $payload = (new RealtimeDashboard)->payload();
        $marked = array_filter(array_column($payload['minutes'], 'signups'));

        $this->assertCount(1, $marked, 'only the sign-up inside the 30-minute chart is marked');
        $this->assertStringContainsString('Rae Recent', reset($marked)[0]);
        // 30 buckets ending on the current minute: 12 minutes ago is bucket 17 (16 if a minute
        // turned over between the fixture and the read).
        $this->assertContains(array_key_first($marked), [16, 17]);
        $this->assertSame(2, $payload['activity']['signups']['total'], 'both are still sign-ups of the last 24 hours');
    }

    public function test_expanding_a_visitor_returns_their_last_hour_including_the_sign_up(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue', ['name' => 'Jazz Nights']);
        $id = 'u:'.UrlUtils::encodeId($user->id);

        $this->hit(['visitor_key' => 'f1f1f1f1f1f1f1f1', 'path' => '/pricing', 'title' => 'Pricing', 'started_at' => now()->subMinutes(50), 'last_seen_at' => now()->subMinutes(48), 'ended_at' => now()->subMinutes(48)]);
        $this->hit(['visitor_key' => 'f1f1f1f1f1f1f1f1', 'user_id' => $user->id, 'surface' => 'ap', 'path' => '/dashboard', 'title' => 'Dashboard']);
        $this->hit(['visitor_key' => 'f2f2f2f2f2f2f2f2', 'path' => '/features', 'title' => 'Someone else']);

        $expanded = RealtimeDashboard::fromRequest(Request::create('/', 'GET', ['expand' => $id]))->payload()['expanded'];

        $this->assertSame($id, $expanded['person_id']);
        $this->assertSame(['Pricing', 'Signed up', 'Dashboard'], array_column($expanded['timeline'], 'label'),
            'the pre-sign-up page from 50 minutes ago is outside the 30-minute list but inside the hour');
        $this->assertSame($user->email, $expanded['email']);
        $this->assertSame('Jazz Nights', $expanded['schedules'][0]['name']);
    }

    public function test_an_expanded_timeline_never_takes_anonymous_rows_from_a_shared_key_or_an_admins_window(): void
    {
        $alice = $this->createOwner();
        $bob = $this->createOwner();
        $admin = $this->admin();

        // Alice and Bob share one network and browser; an anonymous page on that key could be either.
        $this->hit(['visitor_key' => 'abababababababab', 'user_id' => $alice->id, 'surface' => 'ap', 'path' => '/home', 'title' => 'Alice home']);
        $this->hit(['visitor_key' => 'abababababababab', 'user_id' => $bob->id, 'surface' => 'ap', 'path' => '/home', 'title' => 'Bob home']);
        $this->hit(['visitor_key' => 'abababababababab', 'path' => '/pricing', 'title' => 'Whose pricing']);

        // The admin's own private window shares their key.
        $this->hit(['visitor_key' => 'cdcdcdcdcdcdcdcd', 'user_id' => $admin->id, 'is_admin' => true, 'surface' => 'ap', 'path' => '/home', 'title' => 'Admin home']);
        $this->hit(['visitor_key' => 'cdcdcdcdcdcdcdcd', 'path' => '/features', 'title' => 'Private window']);

        // Page entries only: the accounts were created moments ago, so a "Signed up" marker joins them.
        $labels = fn (User $user) => array_column(array_filter(
            (new RealtimeDashboard(admins: true, expand: 'u:'.UrlUtils::encodeId($user->id)))->payload()['expanded']['timeline'] ?? [],
            fn ($entry) => $entry['marker'] === null
        ), 'label');

        $this->assertSame(['Alice home'], $labels($alice));
        $this->assertSame(['Admin home'], $labels($admin));
    }

    public function test_browsing_after_signing_out_is_its_own_anonymous_person_with_its_own_timeline(): void
    {
        $user = $this->createOwner();
        $key = '5151515151515151';
        $userId = 'u:'.UrlUtils::encodeId($user->id);

        $this->hit(['visitor_key' => $key, 'path' => '/pricing', 'title' => 'Before sign-in', 'started_at' => now()->subMinutes(20), 'last_seen_at' => now()->subMinutes(19), 'ended_at' => now()->subMinutes(19)]);
        // A tab from before signing out, still open and heartbeating with its signed-in context. Seen
        // most recently, so the user is listed first: an alias match would find them for v:{key}.
        $this->hit(['visitor_key' => $key, 'user_id' => $user->id, 'surface' => 'ap', 'path' => '/home', 'title' => 'Signed in', 'started_at' => now()->subMinutes(15)]);
        // Signed out, or the next person on a shared laptop: same browser and network, so same key.
        $this->hit(['visitor_key' => $key, 'path' => '/features', 'title' => 'After sign-out', 'started_at' => now()->subMinutes(5), 'last_seen_at' => now()->subMinutes(4), 'ended_at' => now()->subMinutes(4)]);

        $people = collect((new RealtimeDashboard)->payload()['visitors'])->only(['now', 'earlier'])->flatten(1);
        $this->assertSame(2, $people->firstWhere('id', $userId)['views'], 'the page before signing in joins the account');
        $this->assertSame(1, $people->firstWhere('id', 'v:'.$key)['views'], 'the page after signing out does not');

        $labels = fn (string $expand) => array_column(array_filter(
            RealtimeDashboard::fromRequest(Request::create('/', 'GET', ['expand' => $expand]))->payload()['expanded']['timeline'] ?? [],
            fn ($entry) => $entry['marker'] === null
        ), 'label');

        $this->assertSame(['Before sign-in', 'Signed in'], $labels($userId));

        // The user keeps v:{key} as an alias, so the exact id must win, and the anonymous person's
        // timeline must not repeat the account's pre-sign-in page (which would link the two).
        $anonymous = RealtimeDashboard::fromRequest(Request::create('/', 'GET', ['expand' => 'v:'.$key]))->payload()['expanded'];
        $this->assertSame('v:'.$key, $anonymous['person_id']);
        $this->assertSame(['After sign-out'], $labels('v:'.$key));
    }

    public function test_the_dashboard_teaser_shows_only_when_tracking_is_on(): void
    {
        $admin = $this->admin();
        $this->hit(['visitor_key' => '9999999999999999']);

        $this->actingAsAdmin($admin)->get('/admin/dashboard')->assertSee('1 page view in the last 5 minutes');

        Setting::set('realtime_enabled', '0');
        $this->actingAsAdmin($admin)->get('/admin/dashboard')->assertOk()->assertDontSee('in the last 5 minutes');
    }

    public function test_deleting_an_account_deletes_its_realtime_rows(): void
    {
        $user = $this->createOwner();
        $this->hit(['visitor_key' => 'eeeeeeeeeeeeeeee', 'user_id' => $user->id]);

        $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'password'])->assertRedirect();

        $this->assertNull(User::find($user->id), 'the account was really deleted');
        $this->assertSame(0, RealtimeHit::where('user_id', $user->id)->count());
    }

    /**
     * An account created through the sign-up form, with its audit row.
     */
    private function signUp(array $attributes = []): User
    {
        $user = $this->createOwner();
        if ($attributes) {
            $user->forceFill($attributes)->save();
        }

        AuditService::log(AuditService::AUTH_REGISTER, $user->id, 'User', $user->id);

        return $user;
    }

    private function hit(array $attributes): void
    {
        $now = now();
        $attributes = array_map(fn ($value) => $value instanceof \DateTimeInterface ? RealtimeTracker::ts($value) : $value, $attributes);

        DB::table('realtime_hits')->insert(array_merge([
            'hit_key' => str_pad(dechex(++$this->sequence), 32, '0', STR_PAD_LEFT),
            'consented' => true,
            'surface' => 'wp',
            'path' => '/',
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
}
