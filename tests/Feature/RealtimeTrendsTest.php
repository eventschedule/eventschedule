<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\AccountBlockService;
use App\Services\AuditService;
use App\Services\RealtimeDashboard;
use App\Services\RealtimeRollup;
use App\Services\RealtimeTrends;
use App\Utils\RealtimeTracker;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The refreshed /admin/realtime: the Right now board, and the Trends card with the hourly counts
 * behind it (RealtimeRollup, RealtimeTrends).
 *
 * What is held: every page view is counted once, in the hour it started, whichever path prunes it
 * and however late it is engaged; the counts leave out what the live page leaves out; the activity
 * tiles count what the Activity rail counts; a comparison the counts cannot make is not made; and
 * the board puts each person on the page they are on now.
 */
class RealtimeTrendsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->travelTo(now()->utc()->setTime(12, 30));
        Setting::set('realtime_enabled', '1');
    }

    // ---------------------------------------------------------------- the hourly counts

    /** Mutation: drop the is_admin, the surface or the blocked line from RealtimeRollup::run(). */
    public function test_the_rollup_counts_what_the_live_page_counts_by_the_hour_it_started_in(): void
    {
        $spammer = $this->createOwner();
        app(AccountBlockService::class)->block($spammer, $this->createOwner(true));

        $this->hit(['surface' => 'gp', 'is_entrance' => true, 'started_at' => now()->subMinutes(40)]);
        $this->hit(['surface' => 'gp', 'started_at' => now()->subMinutes(35)]);
        $this->hit(['surface' => 'wp', 'is_entrance' => true, 'started_at' => now()->subMinutes(10)]);
        $this->hit(['surface' => 'ap', 'is_admin' => true, 'started_at' => now()->subMinutes(10)]);
        $this->hit(['surface' => 'embed', 'started_at' => now()->subMinutes(10)]);
        $this->hit(['surface' => 'ap', 'user_id' => $spammer->id, 'started_at' => now()->subMinutes(10)]);
        // Opened and never engaged: not a page view, here or on the live page.
        $this->hit(['surface' => 'wp', 'engaged_at' => null, 'started_at' => now()->subMinutes(5)]);

        RealtimeRollup::run();

        $this->assertSame([
            ['hour' => now()->subHour()->format('Y-m-d H:00:00'), 'surface' => 'gp', 'page_views' => 2, 'visits' => 1],
            ['hour' => now()->format('Y-m-d H:00:00'), 'surface' => 'wp', 'page_views' => 1, 'visits' => 1],
        ], $this->hourly());
    }

    /** Mutation: move the cursor over started_at, or drop it. */
    public function test_each_page_view_is_counted_once_however_late_it_is_engaged(): void
    {
        $this->hit(['surface' => 'gp', 'started_at' => now()->subMinutes(20)]);
        $late = $this->hit(['surface' => 'gp', 'engaged_at' => null, 'started_at' => now()->subMinutes(20)]);

        RealtimeRollup::run();
        RealtimeRollup::run();
        $this->assertSame(1, $this->hourly()[0]['page_views'], 'a second run counts nothing twice');

        // Engaged a quarter of an hour after it started: counted now, in the hour it started.
        $this->travel(15)->minutes();
        DB::table('realtime_hits')->where('id', $late)->update(['engaged_at' => RealtimeTracker::ts(now()->subMinute()), 'last_seen_at' => RealtimeTracker::ts(now())]);
        RealtimeRollup::run();

        $this->assertCount(1, $this->hourly());
        $this->assertSame(2, $this->hourly()[0]['page_views']);
        $this->assertSame(now()->subMinutes(35)->format('Y-m-d H:00:00'), $this->hourly()[0]['hour']);
    }

    /** Mutation: drop the RealtimeRollup::run() call from RealtimeTracker::prune(). */
    public function test_nothing_is_pruned_before_it_is_counted(): void
    {
        $this->hit(['surface' => 'wp', 'started_at' => now()->subMinutes(50), 'last_seen_at' => now()->subMinutes(50)]);

        $this->travel(30)->minutes();
        RealtimeTracker::prune();

        $this->assertSame(0, DB::table('realtime_hits')->count(), 'the hour is kept');
        $this->assertSame(1, $this->hourly()[0]['page_views'], 'and the view was counted on the way out');
    }

    // ---------------------------------------------------------------- the Trends card

    /**
     * Mutation: drop a WHEN from RealtimeTrends::kind(), the Symfony or the 'paid' test, or the
     * user exclusions.
     */
    public function test_the_activity_tiles_count_what_the_activity_rail_counts(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role);
        $sale = $this->createSale($event, $role, ['payment_amount' => 10]);
        $admin = $this->createOwner(true);

        AuditService::log(AuditService::AUTH_REGISTER, $owner->id, 'User', $owner->id);
        AuditService::log(AuditService::AUTH_REGISTER, $owner->id); // a follower account: no
        AuditService::log(AuditService::AUTH_REGISTER, $admin->id, 'User', $admin->id); // an admin: no
        AuditService::log(AuditService::AUTH_GOOGLE_LOGIN, $owner->id, null, null, null, null, 'new_account');
        AuditService::log(AuditService::AUTH_GOOGLE_LOGIN, $owner->id); // a login: no
        AuditService::log(AuditService::SCHEDULE_CREATE, $owner->id, 'Role', $role->id);
        $this->audit(['user_id' => $owner->id, 'action' => AuditService::EVENT_CREATE, 'model_type' => 'Event', 'model_id' => $event->id, 'user_agent' => 'Mozilla/5.0']);
        $this->audit(['user_id' => $owner->id, 'action' => AuditService::EVENT_CREATE, 'model_type' => 'Event', 'model_id' => $event->id, 'user_agent' => 'Symfony']); // an import: no
        AuditService::log(AuditService::SALE_PAID, null, 'Sale', $sale->id, ['status' => 'unpaid'], ['status' => 'paid']);
        AuditService::log(AuditService::SALE_PAID, null, 'Sale', $sale->id, ['status' => 'unpaid'], ['status' => 'amount_mismatch']); // no

        $tiles = (new RealtimeTrends('24h'))->build()['totals'];
        $rail = collect((new RealtimeDashboard)->payload()['activity']['stats'])->pluck('count', 'type')->all();

        $this->assertSame(['signup' => 2, 'schedule' => 1, 'events' => 1, 'order' => 1], array_intersect_key($tiles, array_flip(['signup', 'schedule', 'events', 'order'])));
        $this->assertSame([$rail['signup'], $rail['schedule'], $rail['events'], $rail['order']], [$tiles['signup'], $tiles['schedule'], $tiles['events'], $tiles['order']]);
    }

    /**
     * The 24 hours of the four activity tiles are the rail's rolling 24 hours, not this hour and the
     * 23 before it: by the clock a sign-up from 23 hours 50 minutes ago was in the rail and not in
     * the tile beside it.
     * Mutation: count the 24-hour activity from the first bar, or the span before from 48 hours by
     * the clock.
     */
    public function test_the_24_hour_activity_figures_are_the_rails_rolling_day(): void
    {
        $owner = $this->createOwner();
        foreach ([10, 23 * 60 + 50, 24 * 60 + 10, 47 * 60 + 50, 48 * 60 + 10] as $minutesAgo) {
            $this->audit(['user_id' => $owner->id, 'action' => AuditService::AUTH_REGISTER, 'model_type' => 'User', 'model_id' => $owner->id, 'created_at' => now()->subMinutes($minutesAgo)]);
        }

        $day = (new RealtimeTrends('24h'))->build();
        $rail = collect((new RealtimeDashboard)->payload()['activity']['stats'])->firstWhere('type', 'signup')['count'];

        $this->assertSame(2, $day['totals']['signup']);
        $this->assertSame($rail, $day['totals']['signup'], 'the tile and the rail give one number');
        $this->assertSame(2, $day['previous']['signup'], 'the 24 hours before those, to the minute');
        $this->assertSame(1, array_sum(array_column($day['buckets'], 'signup')), 'the bars stay clock hours: 23h50m ago is before the first');
    }

    /**
     * Mutation: bucket or label in the app's timezone, or key the hours by the viewer's clock.
     */
    public function test_the_card_is_told_in_the_viewers_own_timezone(): void
    {
        // 12:30 UTC: 15:30 in Jerusalem, 18:00 in Kolkata.
        $owner = $this->createOwner();
        $this->audit(['user_id' => $owner->id, 'action' => AuditService::AUTH_REGISTER, 'model_type' => 'User', 'model_id' => $owner->id,
            'created_at' => now()->subDay()->setTime(22, 30)->setTimezone(config('app.timezone'))]);

        $utc = (new RealtimeTrends('7d', null, 'UTC'))->build();
        $jerusalem = (new RealtimeTrends('7d', null, 'Asia/Jerusalem'))->build();

        // 22:30 UTC yesterday is 01:30 today in Jerusalem.
        $this->assertSame([1, 0], array_slice(array_column($utc['buckets'], 'signup'), -2));
        $this->assertSame([0, 1], array_slice(array_column($jerusalem['buckets'], 'signup'), -2));

        $hours = (new RealtimeTrends('24h', null, 'Asia/Jerusalem'))->build()['buckets'];
        $this->assertStringContainsString('3 PM', end($hours)['label'], 'the hour under way, on the viewer\'s clock');
        $this->assertStringStartsWith("\u{2066}", end($hours)['label'], 'isolated, so a right-to-left page keeps its order');

        // A half-hour zone: each bar is the UTC hour it counts, and says so.
        $kolkata = (new RealtimeTrends('24h', null, 'Asia/Kolkata'))->build()['buckets'];
        $this->assertStringContainsString('5:30 PM', end($kolkata)['label']);

        $this->assertSame('Asia/Jerusalem', RealtimeTrends::viewerZone('Asia/Jerusalem'));
        $this->assertSame(config('app.timezone'), RealtimeTrends::viewerZone(null));
    }

    /** Mutation: compare with the span before cut at another time, or drop a bucket's day. */
    public function test_a_range_is_compared_with_the_same_span_before_it(): void
    {
        $owner = $this->createOwner();
        // Today, earlier this week, and in the seven days before those.
        foreach ([0, 3, 8, 9, 30] as $daysAgo) {
            $this->audit(['user_id' => $owner->id, 'action' => AuditService::AUTH_REGISTER, 'model_type' => 'User', 'model_id' => $owner->id, 'created_at' => now()->subDays($daysAgo)->subHour()]);
        }

        $week = (new RealtimeTrends('7d'))->build();

        $this->assertCount(7, $week['buckets']);
        $this->assertSame(2, $week['totals']['signup']);
        $this->assertSame(2, $week['previous']['signup']);
        $this->assertSame(0.0, (float) $week['change']['signup']);
        $this->assertSame([1, 0, 0, 1, 0, 0, 0], array_reverse(array_column($week['buckets'], 'signup')));
        $this->assertTrue(end($week['buckets'])['current']);

        $this->assertCount(24, (new RealtimeTrends('24h'))->build()['buckets']);
        $this->assertCount(30, (new RealtimeTrends('30d'))->build()['buckets']);
    }

    /** Mutation: drop the since tests from RealtimeTrends::build(). */
    public function test_traffic_says_since_when_it_counts_and_compares_only_what_it_has(): void
    {
        $nothing = (new RealtimeTrends('7d'))->build();
        $this->assertTrue($nothing['collecting']);
        $this->assertNull($nothing['totals']['views'], 'not a zero: nothing was counted');
        $this->assertNull($nothing['buckets'][0]['views']);

        DB::table('realtime_hourly')->insert([
            ['hour' => RealtimeTracker::ts(now()->subDays(2)->startOfHour()), 'surface' => 'gp', 'page_views' => 30, 'visits' => 10],
            ['hour' => RealtimeTracker::ts(now()->startOfHour()), 'surface' => 'wp', 'page_views' => 5, 'visits' => 4],
        ]);

        $week = (new RealtimeTrends('7d'))->build();

        $this->assertFalse($week['collecting']);
        $this->assertSame(35, $week['totals']['views']);
        $this->assertSame(14, $week['totals']['visits']);
        $this->assertNull($week['previous']['views'], 'the week before was not counted');
        $this->assertNull($week['change']['views']);
        $this->assertNotNull($week['since']);
        $this->assertNull($week['buckets'][0]['views'], 'a day before counting began is unknown');
        $this->assertSame(['wp' => 5, 'gp' => 0, 'ap' => 0, 'auth' => 0], end($week['buckets'])['views']);
    }

    public function test_the_trends_address_answers_admins_only(): void
    {
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($this->createOwner(true))
            ->getJson('/admin/realtime/trends?range=30d')
            ->assertOk()
            ->assertJsonPath('range', '30d')
            ->assertJsonCount(30, 'buckets');

        $this->actingAs($this->createOwner())->getJson('/admin/realtime/trends')->assertStatus(403);
    }

    // ---------------------------------------------------------------- the Right now board

    /** Mutation: group the board by anything but the current page, or let it follow the surface filter. */
    public function test_the_board_puts_each_person_on_the_page_they_are_on_now(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Jazz Hall']);
        $event = $this->createEvent($role, ['name' => 'Jazz Night', 'creator_role_id' => $role->id]);
        $signedIn = $this->createOwner();

        $this->hit(['visitor_key' => '1111111111111111', 'surface' => 'gp', 'role_id' => $role->id, 'event_id' => $event->id, 'country' => 'AT']);
        $this->hit(['visitor_key' => '2222222222222222', 'surface' => 'gp', 'role_id' => $role->id, 'event_id' => $event->id, 'country' => 'DE']);
        $this->hit(['visitor_key' => '3333333333333333', 'surface' => 'ap', 'path' => '/home', 'title' => 'Dashboard', 'user_id' => $signedIn->id]);
        // Earlier on the event, now on the pricing page: listed where they are now.
        $this->hit(['visitor_key' => '4444444444444444', 'surface' => 'gp', 'role_id' => $role->id, 'event_id' => $event->id, 'started_at' => now()->subMinutes(9), 'last_seen_at' => now()->subMinutes(8), 'ended_at' => now()->subMinutes(8)]);
        $this->hit(['visitor_key' => '4444444444444444', 'surface' => 'wp', 'path' => '/pricing', 'title' => 'Pricing']);
        // Left ten minutes ago: not on the board.
        $this->hit(['visitor_key' => '5555555555555555', 'surface' => 'wp', 'path' => '/', 'started_at' => now()->subMinutes(12), 'last_seen_at' => now()->subMinutes(10), 'ended_at' => now()->subMinutes(10)]);

        config(['app.is_nexus' => true]);
        $board = (new RealtimeDashboard)->payload()['overview']['now_board'];

        $this->assertSame(['Jazz Night'], array_column($board['gp']['pages'], 'label'));
        $this->assertSame(2, $board['gp']['pages'][0]['count']);
        $this->assertSame(['AT', 'DE'], collect($board['gp']['pages'][0]['people'])->pluck('country')->sort()->values()->all());
        $this->assertNotNull($board['gp']['pages'][0]['url']);
        $this->assertSame(['Pricing'], array_column($board['wp']['pages'], 'label'));
        $this->assertSame('u:'.UrlUtils::encodeId($signedIn->id), $board['ap']['pages'][0]['people'][0]['id']);
        $this->assertSame('user', $board['ap']['pages'][0]['people'][0]['kind']);

        // Filtered to one part of the site, the others stay on the board beside it.
        $filtered = RealtimeDashboard::fromRequest(Request::create('/', 'GET', ['surface' => 'ap']))->payload()['overview']['now_board'];
        $this->assertSame(array_keys($board), array_keys($filtered));
    }

    public function test_the_page_draws_the_board_and_the_trends(): void
    {
        $html = $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($this->createOwner(true))
            ->get('/admin/realtime?trend=7d')->assertOk()->getContent();

        $this->assertStringContainsString('id="realtime-now"', $html);
        $this->assertStringContainsString('aria-labelledby="realtime-trends-heading"', $html);
        $this->assertStringContainsString('"range":"7d"', $html, 'the range in the address opens the card on it');
    }

    private function hourly(): array
    {
        return DB::table('realtime_hourly')->orderBy('hour')->orderBy('surface')->get(['hour', 'surface', 'page_views', 'visits'])
            ->map(fn ($row) => ['hour' => (string) $row->hour, 'surface' => $row->surface, 'page_views' => (int) $row->page_views, 'visits' => (int) $row->visits])
            ->all();
    }

    private function audit(array $attributes): void
    {
        DB::table('audit_logs')->insert($attributes + ['ip_address' => '127.0.0.1', 'created_at' => now()]);
    }

    private function hit(array $attributes): int
    {
        $started = $attributes['started_at'] ?? now();

        $row = array_merge([
            'hit_key' => str_pad(dechex(++$this->sequence), 32, '0', STR_PAD_LEFT),
            'visitor_key' => str_pad(dechex($this->sequence), 16, 'a', STR_PAD_LEFT),
            'consented' => true,
            'surface' => 'wp',
            'path' => '/',
            'device' => 'desktop',
            'browser' => 'Chrome',
            'os' => 'macOS',
            'country' => 'US',
            'hb' => 60,
            'started_at' => $started,
            'last_seen_at' => now(),
            'engaged_at' => $started,
        ], $attributes);

        return DB::table('realtime_hits')->insertGetId(
            array_map(fn ($value) => $value instanceof \DateTimeInterface ? RealtimeTracker::ts($value) : $value, $row)
        );
    }
}
