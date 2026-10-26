<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\AccountBlockService;
use App\Services\RealtimeDashboard;
use App\Services\RealtimeRollup;
use App\Services\RealtimeTrends;
use App\Utils\RealtimeTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The visit totals kept past the live hour (RealtimeRollup): whose pages each one counts, a
 * blocked account's left out of every range afterwards, and the longer ranges of the dashboard's
 * Top pages, Sources and Countries.
 *
 * What is pinned here is what would come back unnoticed:
 *
 * - views of a schedule counted before its owner was blocked, still drawn in Trends (the totals
 *   said nothing about whose pages they were);
 * - the blocked account's own browsing left in, or taken out twice over when it was on its own pages;
 * - a total that says who viewed a page (the privacy policy promises it never does);
 * - the longer ranges disagreeing with the live card about what a page, a source or a country is.
 */
class RealtimeBlockedTotalsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private int $sequence = 0;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->travelTo(now()->utc()->setTime(12, 30));
        Setting::set('realtime_enabled', '1');
        $this->admin = $this->createOwner(true);
    }

    public function test_each_total_names_whose_pages_it_counts_and_never_who_viewed_them(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Spam Hall']);
        $event = $this->createEvent($role, ['name' => 'Spam Night']);
        DB::table('events')->where('id', $event->id)->update(['creator_role_id' => $role->id]);
        $visitor = $this->createOwner();

        $this->hit(['surface' => 'gp', 'role_id' => $role->id, 'event_id' => $event->id, 'user_id' => $visitor->id, 'country' => 'RU', 'is_entrance' => true, 'source_channel' => 'search', 'source_name' => 'Google']);
        $this->hit(['surface' => 'wp', 'path' => '/pricing', 'country' => 'US']);

        RealtimeRollup::run();

        $gp = DB::table('realtime_hourly')->where('surface', 'gp')->first();
        $this->assertSame($owner->id, (int) $gp->role_owner_id);
        $this->assertSame($owner->id, (int) $gp->event_owner_id);
        $this->assertSame(1, (int) $gp->page_views);
        $this->assertSame(0, (int) DB::table('realtime_hourly')->where('surface', 'wp')->value('role_owner_id'));

        foreach (['realtime_hourly', 'realtime_breakdowns'] as $table) {
            $this->assertNotContains('user_id', \Illuminate\Support\Facades\Schema::getColumnListing($table), $table.' must not say who viewed a page');
        }

        $this->assertEqualsCanonicalizing(['page', 'page', 'country', 'country', 'source'], DB::table('realtime_breakdowns')->pluck('dimension')->all());
        $this->assertSame('n:Google', DB::table('realtime_breakdowns')->where('dimension', 'source')->value('item'));
    }

    public function test_trends_leave_out_a_schedule_whose_owner_was_blocked_after_it_was_counted(): void
    {
        $spammer = $this->createOwner();
        $role = $this->createRole($spammer, 'venue');

        $this->hit(['surface' => 'gp', 'role_id' => $role->id, 'started_at' => now()->subMinutes(20)]);
        $this->hit(['surface' => 'gp', 'role_id' => $role->id, 'started_at' => now()->subMinutes(20)]);
        $this->hit(['surface' => 'wp', 'started_at' => now()->subMinutes(20)]);
        RealtimeRollup::run();

        $this->assertSame(3, $this->trendViews());

        $blocks = app(AccountBlockService::class);
        $blocks->block($spammer, $this->admin);
        $this->assertSame(1, $this->trendViews(), 'the block shows at once, whatever the cache holds');

        $blocks->unblock($spammer, $this->admin);
        $this->assertSame(3, $this->trendViews());
    }

    public function test_a_block_takes_back_its_own_browsing_once_and_only_once(): void
    {
        $spammer = $this->createOwner();
        $role = $this->createRole($spammer, 'venue');

        // Signed in, on the app and on its own schedule's page, before it was blocked.
        $this->hit(['surface' => 'ap', 'path' => '/home', 'user_id' => $spammer->id, 'started_at' => now()->subMinutes(20)]);
        $this->hit(['surface' => 'ap', 'path' => '/home', 'user_id' => $spammer->id, 'started_at' => now()->subMinutes(20)]);
        $this->hit(['surface' => 'gp', 'role_id' => $role->id, 'user_id' => $spammer->id, 'started_at' => now()->subMinutes(20)]);
        $this->hit(['surface' => 'ap', 'path' => '/home', 'started_at' => now()->subMinutes(20)]);
        RealtimeRollup::run();

        $blocks = app(AccountBlockService::class);
        $blocks->block($spammer, $this->admin);

        $this->assertSame(1, (int) DB::table('realtime_hourly')->where('surface', 'ap')->sum('page_views'), 'its own app pages are taken back');
        $this->assertSame(1, $this->trendViews());
        $this->assertSame(1, (int) DB::table('realtime_breakdowns')->where('dimension', 'page')->where('item', 'p:ap:/home')->sum('views'));

        // Its own schedule's page was left to the owner columns, so letting it back in brings
        // that view back rather than leaving it missing twice.
        $blocks->unblock($spammer, $this->admin);
        $this->assertSame(2, $this->trendViews());
    }

    public function test_the_longer_ranges_read_the_totals_as_the_live_cards_read_the_rows(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Jazz Hall']);

        $this->hit(['surface' => 'gp', 'role_id' => $role->id, 'country' => 'DE', 'is_entrance' => true, 'source_channel' => 'social', 'started_at' => now()->subDays(3)]);
        $this->hit(['surface' => 'gp', 'role_id' => $role->id, 'country' => 'DE', 'started_at' => now()->subDays(3)]);
        $this->hit(['surface' => 'wp', 'path' => '/pricing', 'title' => 'Pricing', 'country' => 'FR', 'is_entrance' => true, 'source_channel' => 'search', 'source_name' => 'Google', 'started_at' => now()->subMinutes(20)]);
        RealtimeRollup::run();

        $day = (new RealtimeDashboard)->rangeBreakdowns('24h');
        $this->assertSame(['Pricing'], array_column($day['pages'], 'label'));
        $this->assertSame(['FR'], array_column($day['countries'], 'key'));

        $week = (new RealtimeDashboard)->rangeBreakdowns('7d');
        $pages = collect($week['pages'])->keyBy('label');
        $this->assertSame(2, $pages['Jazz Hall']['views']);
        $this->assertFalse($pages['Jazz Hall']['filterable'], 'a total has no rows to filter');
        $this->assertSame(['DE' => 2, 'FR' => 1], collect($week['countries'])->pluck('views', 'key')->all());
        // Visits, not page views: the second view of the visit carries no source.
        $this->assertEqualsCanonicalizing(['Google' => 1, __('messages.social') => 1], collect($week['sources'])->pluck('views', 'label')->all());

        app(AccountBlockService::class)->block($owner, $this->admin);
        $week = (new RealtimeDashboard)->rangeBreakdowns('7d');
        $this->assertSame(['Pricing'], array_column($week['pages'], 'label'), 'a blocked owner\'s pages are left out');
        $this->assertSame(['FR'], array_column($week['countries'], 'key'));
    }

    public function test_the_breakdowns_address_answers_admins_only_and_knows_its_ranges(): void
    {
        $this->actingAs($this->createOwner())->getJson(route('admin.realtime.breakdowns', ['range' => '7d']))->assertStatus(403);

        $admin = $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($this->admin);
        $admin->getJson(route('admin.realtime.breakdowns', ['range' => '7d']))->assertOk()->assertJsonPath('range', '7d');
        $admin->getJson(route('admin.realtime.breakdowns', ['range' => 'forever']))->assertOk()->assertJsonPath('range', '24h');
    }

    public function test_the_dashboard_draws_the_switch_and_opens_on_the_range_in_its_address(): void
    {
        $html = $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($this->admin)
            ->get(route('admin.dashboard', ['cards' => '7d']))
            ->assertOk()
            ->assertSee('setCardsRange(option.value)', false)
            ->getContent();

        $this->assertStringContainsString('"cards":"7d"', $html);
        $this->assertStringContainsString('"breakdownsUrl":', $html);
    }

    private function trendViews(): int
    {
        return (int) (new RealtimeTrends('24h'))->cached()['totals']['views'];
    }

    private function hit(array $attributes): int
    {
        $started = $attributes['started_at'] ?? now()->subMinutes(5);

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
            'last_seen_at' => $started,
            'engaged_at' => $started,
        ], $attributes);

        return DB::table('realtime_hits')->insertGetId(
            array_map(fn ($value) => $value instanceof \DateTimeInterface ? RealtimeTracker::ts($value) : $value, $row)
        );
    }
}
