<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Newsletter;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The pages people open most, and the ones a browser polls, ask the database the same number of
 * times whatever the size of what they list: the N+1 check, page by page.
 *
 * One schedule is grown from a small size to a larger one (events with tickets and sales, a
 * series, followers, a team, sub-schedules, requests waiting, newsletters, visits, other
 * schedules on a curator's list) and each page is measured cold at both sizes
 * (TestCase::assertQueriesDoNotGrow). A page whose count grows asks once per row somewhere; the
 * failure names the queries that grew. Each page also has a budget, the most queries it may run
 * at the larger size, so a page that gets heavier in a flat way is seen too: when one is raised
 * on purpose, say why in the commit.
 *
 * Every request here also passes through QueryGuard, which fails on an Eloquent relation lazy
 * loaded per row even where the count does not grow (a list capped below the sizes used here).
 *
 * A page added to the app that lists anything belongs here. A case whose count may grow on
 * purpose goes in GROWS with the reason.
 */
class PageQueryScalingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * The most queries each page may run at the larger size. Measured 2026-10-22 and given a
     * little room; lower one when a page gets lighter.
     */
    private const BUDGETS = [
        // page => budget, // measured
        'guest schedule page' => 26, // 23
        'guest schedule list layout' => 26, // 23
        'guest embed' => 21, // 18
        'guest event page' => 44, // 40
        'guest calendar events api' => 20, // 17
        'guest past events api' => 7, // 4
        'guest curator page' => 24, // 21
        'guest rss feed' => 10, // 7
        'guest ical feed' => 10, // 7
        'guest schedule sitemap' => 11, // 8
        'guest request form' => 14, // 11
        'guest booking page' => 14, // 11
        'dashboard' => 88, // 80
        'following' => 20, // 17
        'schedule tab' => 47, // 42
        'requests tab' => 29, // 26
        'followers tab' => 38, // 34
        'team tab' => 30, // 27
        'plan tab' => 31, // 28
        'availability tab' => 31, // 28
        'appointments tab' => 36, // 32
        'schedule settings' => 38, // 34
        'event form' => 72, // 65
        'sales' => 35, // 31
        'analytics' => 54, // 49
        'realtime data' => 12, // 9
        'realtime summary' => 9, // 6
        'realtime activity' => 21, // 18
        'newsletters' => 18, // 15
        'boost' => 23, // 20
        'account settings' => 20, // 17
        'check-in stats' => 14, // 11
        'admin dashboard' => 9, // 6
        'admin dashboard recent' => 18, // 15
        'admin dashboard growth' => 15, // 12
        'admin schedules' => 20, // 17
        'admin revenue' => 37, // 33
        'admin queue' => 39, // 35
        'admin realtime data' => 23, // 20
        'admin realtime trends' => 12, // 9
        'admin blocked' => 18, // 15
        'api events' => 16, // 13
        'api schedules' => 12, // 9
        'api categories' => 6, // 3
    ];

    /** Pages whose count may grow with the data, and by how much, with the reason. */
    private const GROWS = [
        // The card of coming events asks each event it shows for the seats passes hold
        // (Event::passReservedSeats()), so it grows until it shows HomeDashboard::COMING events
        // and no further. Measured: one query between 2 and 5 of everything.
        'dashboard' => ['slack' => 1],
    ];

    private User $owner;

    private User $admin;

    private Role $venue;

    private Role $talent;

    private Role $curator;

    private string $apiKey;

    private int $size = 0;

    /** @var list<Event> */
    private array $events = [];

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        config(['app.hosted' => true, 'app.is_nexus' => true]);
        $this->travelTo('2026-06-15 12:00:00');

        $this->owner = $this->createOwner();
        $this->admin = $this->createOwner(true);
        $this->venue = $this->createRole($this->owner, 'venue', ['name' => 'Harbor Hall', 'accept_requests' => true]);
        // Served on its own domain with the subdomain as the main address, so every list that
        // names one of its events asks which address is main (CustomDomainSetting): once a page.
        $this->talent = $this->createRole($this->owner, 'talent', [
            'name' => 'The Harbor Band',
            'custom_domain' => 'https://harbor-band.test',
            'custom_domain_host' => 'harbor-band.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);
        \App\Models\CustomDomainSetting::store($this->talent, \App\Models\CustomDomainSetting::SUBDOMAIN);
        $this->curator = $this->createCurator($this->owner, ['name' => 'Harbor Guide']);

        $raw = 'testapikey_'.Str::random(24);
        $this->owner->api_key = substr(hash('sha256', $raw), 0, 8);
        $this->owner->api_key_hash = Hash::make($raw);
        $this->owner->save();
        $this->apiKey = $raw;
    }

    /** Bring every list the pages read up to $n rows. */
    private function grow(int $n): void
    {
        for ($i = $this->size + 1; $i <= $n; $i++) {
            $when = now()->addDays(3 + $i)->setTime(19, 0);

            $event = $this->createEvent($this->venue, [
                'name' => 'Night '.$i,
                'starts_at' => $when->format('Y-m-d H:i:s'),
                'creator_role_id' => $this->venue->id,
                'tickets_enabled' => true,
                'ticket_currency_code' => 'USD',
            ]);
            $event->roles()->attach($this->talent->id, ['is_accepted' => true]);
            $event->roles()->attach($this->curator->id, ['is_accepted' => true]);
            $ticket = $this->createTicket($event, ['price' => 10 + $i, 'quantity' => 100]);
            $this->createSale($event, $this->venue, ['name' => 'Buyer '.$i, 'email' => "buyer{$i}@gmail.com", 'payment_amount' => 10 + $i], $ticket);
            $this->events[] = $event;

            $this->createRecurringEvent($this->venue, ['name' => 'Weekly '.$i, 'creator_role_id' => $this->venue->id, 'days_of_week' => '0000100']);

            // A schedule of the owner's own: the sidebar, the dashboard and every page that lists
            // "your schedules" read each one's plan.
            $this->createRole($this->owner, 'talent', ['name' => 'Side project '.$i]);

            $this->followRole($this->createOwner(), $this->venue);
            $this->followRole($this->createOwner(), $this->venue, 'admin');
            $this->createGroup($this->venue, ['name' => 'Room '.$i]);

            // Another organizer's schedule: one event on the curator's list, one asking the venue.
            $other = $this->createRole($this->createOwner(), 'talent', ['name' => 'Visiting Act '.$i]);
            $listed = $this->createEvent($other, ['name' => 'Guest set '.$i, 'creator_role_id' => $other->id, 'starts_at' => $when->copy()->addHour()->format('Y-m-d H:i:s')]);
            $listed->roles()->attach($this->curator->id, ['is_accepted' => true]);
            $request = $this->createEvent($other, ['name' => 'Asks to play '.$i, 'creator_role_id' => $other->id, 'starts_at' => $when->copy()->addDay()->format('Y-m-d H:i:s')]);
            $request->roles()->attach($this->venue->id, ['is_accepted' => null]);
            $this->followRole($this->owner, $other);

            Newsletter::create([
                'role_id' => $this->venue->id,
                'user_id' => $this->owner->id,
                'type' => 'schedule',
                'subject' => 'News '.$i,
                'status' => 'draft',
                'template' => 'modern',
            ]);

            $this->createAppointmentType($this->venue, ['name' => 'Studio session '.$i]);

            DB::table('realtime_hits')->insert([
                'hit_key' => str_pad(dechex($i), 32, '0', STR_PAD_LEFT),
                'visitor_key' => str_pad(dechex($i), 16, 'a', STR_PAD_LEFT),
                'consented' => true,
                'owner_visible' => true,
                'surface' => 'gp',
                'path' => '/'.$this->venue->subdomain,
                'role_id' => $this->venue->id,
                'device' => 'desktop',
                'browser' => 'Chrome',
                'os' => 'macOS',
                'country' => 'US',
                'hb' => 60,
                'started_at' => \App\Utils\RealtimeTracker::ts(now()),
                'last_seen_at' => \App\Utils\RealtimeTracker::ts(now()),
                'engaged_at' => \App\Utils\RealtimeTracker::ts(now()),
            ]);
        }

        $this->size = max($this->size, $n);
    }

    public static function pages(): array
    {
        $pages = [
            // The guest portal
            'guest schedule page', 'guest schedule list layout', 'guest embed', 'guest event page',
            'guest calendar events api', 'guest past events api', 'guest curator page',
            'guest rss feed', 'guest ical feed', 'guest schedule sitemap', 'guest request form', 'guest booking page',
            // The admin portal
            'dashboard', 'following', 'schedule tab', 'requests tab', 'followers tab', 'team tab', 'plan tab',
            'availability tab', 'appointments tab', 'schedule settings', 'event form', 'sales', 'analytics',
            'realtime data', 'realtime summary', 'realtime activity', 'newsletters', 'boost', 'account settings',
            'check-in stats',
            // The platform admin
            'admin dashboard', 'admin dashboard recent', 'admin dashboard growth', 'admin schedules', 'admin revenue', 'admin queue', 'admin realtime data',
            'admin realtime trends', 'admin blocked',
            // The API
            'api events', 'api schedules', 'api categories',
        ];

        return array_combine($pages, array_map(fn ($page) => [$page], $pages));
    }

    /**
     * Mutation: drop 'creatorRole' from the eager loads of the guest list, or from the dashboard's
     * `userRoles` with(['subscriptions', 'user']) in AppServiceProvider.
     */
    #[DataProvider('pages')]
    public function test_a_page_asks_no_more_as_its_lists_grow(string $page): void
    {
        $grows = self::GROWS[$page] ?? null;

        [$small, $large] = $this->assertQueriesDoNotGrow(
            fn (int $n) => $this->grow($n),
            fn () => $this->visit($page),
            [2, 5],
            $grows['slack'] ?? 0,
            self::BUDGETS[$page] ?? null,
            $page,
        );

        // QUERY_BUDGET_PRINT=1 prints what each page ran, for setting or lowering a budget.
        if (getenv('QUERY_BUDGET_PRINT')) {
            fwrite(STDERR, sprintf("QUERY_BUDGET %s %d %d\n", $page, $small, $large));
        }

        $this->assertArrayHasKey($page, self::BUDGETS, "{$page} has no budget: it ran {$large} queries at the larger size. Add it to BUDGETS.");
    }

    private function visit(string $page): TestResponse
    {
        $owner = $this->owner->fresh();
        $admin = $this->admin->fresh();
        $sub = $this->venue->subdomain;
        $event = $this->events[0];
        $year = (int) now()->addDays(4)->format('Y');
        $month = (int) now()->addDays(4)->format('n');

        $asAdmin = fn () => $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
        $api = fn () => $this->withHeaders(['X-API-Key' => $this->apiKey]);

        $response = match ($page) {
            'guest schedule page' => $this->get('/'.$sub),
            'guest schedule list layout' => $this->get('/'.$sub.'?layout=list'),
            'guest embed' => $this->get('/'.$sub.'?embed=true'),
            'guest event page' => $this->get($this->guestEventUrl($this->venue, $event)),
            'guest calendar events api' => $this->getJson('/'.$sub.'/api/calendar-events?year='.$year.'&month='.$month),
            'guest past events api' => $this->getJson('/'.$sub.'/api/past-events'),
            'guest curator page' => $this->get('/'.$this->curator->subdomain),
            'guest rss feed' => $this->get('/'.$sub.'/feed/rss'),
            'guest ical feed' => $this->get('/'.$sub.'/feed/ical'),
            'guest schedule sitemap' => $this->get('/'.$sub.'/sitemap.xml'),
            'guest request form' => $this->followingRedirects()->get('/'.$sub.'/request'),
            'guest booking page' => $this->get('/'.$sub.'/book'),

            'dashboard' => $this->actingAs($owner)->get(route('home')),
            'following' => $this->actingAs($owner)->get(route('following')),
            'schedule tab', 'requests tab', 'followers tab', 'team tab', 'plan tab', 'availability tab', 'appointments tab' => $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $sub, 'tab' => explode(' ', $page)[0]])),
            'schedule settings' => $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $sub])),
            'event form' => $this->actingAs($owner)->get(route('event.edit', ['subdomain' => $sub, 'hash' => UrlUtils::encodeId($event->id)])),
            'sales' => $this->actingAs($owner)->get(route('sales')),
            'analytics' => $this->actingAs($owner)->get(route('analytics')),
            'realtime data' => $this->actingAs($owner)->getJson(route('analytics.realtime.data')),
            'realtime summary' => $this->actingAs($owner)->getJson(route('analytics.realtime.summary')),
            'realtime activity' => $this->actingAs($owner)->getJson(route('analytics.realtime.activity')),
            'newsletters' => $this->actingAs($owner)->get(route('newsletter.index')),
            'boost' => $this->actingAs($owner)->get(route('boost.index')),
            'account settings' => $this->actingAs($owner)->get(route('profile.edit')),
            'check-in stats' => $this->actingAs($owner)->getJson(route('checkin.stats', ['event_id' => UrlUtils::encodeId($event->id)])),

            'admin dashboard' => $asAdmin()->get(route('admin.dashboard')),
            // The cards the dashboard fetches as they near the screen (newest schedules and events;
            // sign-ups, sources and upcoming events), which are the parts of it that list things.
            'admin dashboard recent' => $asAdmin()->get(route('admin.dashboard.panel', ['panel' => 'recent'])),
            'admin dashboard growth' => $asAdmin()->get(route('admin.dashboard.panel', ['panel' => 'growth'])),
            'admin schedules' => $asAdmin()->get(route('admin.schedules')),
            'admin revenue' => $asAdmin()->get(route('admin.revenue')),
            'admin queue' => $asAdmin()->get(route('admin.queue')),
            'admin realtime data' => $asAdmin()->getJson(route('admin.realtime.data')),
            'admin realtime trends' => $asAdmin()->getJson(route('admin.realtime.trends')),
            'admin blocked' => $asAdmin()->get(route('admin.blocked')),

            'api events' => $api()->getJson('/api/events'),
            'api schedules' => $api()->getJson('/api/schedules'),
            'api categories' => $api()->getJson('/api/categories'),
        };

        return $response->assertOk();
    }
}
