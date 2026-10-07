<?php

namespace Tests\Feature;

use App\Models\AnalyticsDaily;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\HomeDashboard;
use App\Utils\RealtimeTracker;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * /dashboard, above the calendar: what each kind of person is shown, and the numbers behind it
 * (App\Services\HomeDashboard).
 *
 * Several of these pin a defect the page had before it was rebuilt: a venue that ran only weekly
 * nights was told it had nothing coming up, a curator saw the sales of events it merely lists, a
 * deleted sale still counted, new followers had no names, and someone who only held a ticket was
 * shown an organizer's zeros. Each test names the one-line change that turns it red.
 */
class HomeDashboardTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Noon UTC, a Tuesday: 08:00 in New York, the timezone the fixtures' schedules are in.
        Carbon::setTestNow(Carbon::parse('2026-10-06 12:00:00', 'UTC'));
        Cache::flush();
        Setting::set('realtime_enabled', '1');
        Setting::set('realtime_owner_view', '1');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function build(User $user, int $period = 30, ?array $live = null): array
    {
        $this->actingAs($user);
        app()->forgetInstance('userRoles');

        return (new HomeDashboard)->build($user, $period, $live);
    }

    private function views(Role $role, string $date, int $count): void
    {
        AnalyticsDaily::create(['role_id' => $role->id, 'date' => $date, 'desktop_views' => $count, 'mobile_views' => 0, 'tablet_views' => 0, 'unknown_views' => 0]);
    }

    /**
     * A venue that runs only weekly nights has events coming up. The list this replaces filtered
     * on `days_of_week IS NULL`. Mutation: put that filter back, or sort a series by its first
     * date instead of its next one.
     */
    public function test_a_weekly_series_is_coming_up_at_its_next_date(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        // Began in August, on Thursdays. Its next one is Thursday the 8th.
        $this->createEvent($role, ['name' => 'Thursday Jazz Jam', 'starts_at' => '2026-08-06 23:00:00', 'days_of_week' => '0000100', 'recurring_frequency' => 'weekly']);
        $this->createEvent($role, ['name' => 'One Night Only', 'starts_at' => '2026-10-20 23:00:00']);
        $this->createEvent($role, ['name' => 'Long Gone', 'starts_at' => '2026-09-01 23:00:00']);

        $dashboard = $this->build($owner);

        $this->assertSame(['Thursday Jazz Jam', 'One Night Only'], array_column($dashboard['coming']['rows'], 'name'));
        $this->assertSame(2, $dashboard['coming']['total']);
        $this->assertSame(2, $dashboard['upcoming']['count']);

        $jam = $dashboard['coming']['rows'][0];
        $this->assertTrue($jam['series']);
        $this->assertSame('weekly', $jam['frequency']);
        $this->assertSame(Carbon::parse('2026-10-08')->translatedFormat('D, M j'), $jam['date']);
    }

    /**
     * Sold against the limit, views for the period, and the one thing done at the door on the day.
     * "Today" is today where the event is, not where the server is.
     * Mutation: compare the event's date with now() in UTC, or offer Check in on any day.
     */
    public function test_an_event_row_says_what_sold_what_was_seen_and_when_it_is_today(): void
    {
        // 22:00 on the 6th in New York, and already the 7th in UTC: the hours in which a page
        // that asked the server what day it is would call tonight's event "tomorrow".
        Carbon::setTestNow(Carbon::parse('2026-10-07 02:00:00', 'UTC'));

        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        // 23:30 in New York on the 6th is 03:30 UTC on the 7th: today there, tomorrow in UTC.
        // creator_role_id is what carries the schedule's timezone to its event.
        $tonight = $this->createEvent($role, ['name' => 'Tonight', 'starts_at' => '2026-10-07 03:30:00', 'tickets_enabled' => true, 'creator_role_id' => $role->id]);
        $ticket = $this->createTicket($tonight, ['quantity' => 150, 'price' => 15, 'sold' => json_encode(['2026-10-06' => 86])]);
        $later = $this->createEvent($role, ['name' => 'Later', 'starts_at' => '2026-10-07 23:00:00', 'tickets_enabled' => true, 'creator_role_id' => $role->id]);
        $this->createTicket($later, ['quantity' => 0, 'price' => 0, 'sold' => json_encode(['2026-10-07' => 12])]);
        $this->createEvent($role, ['name' => 'No Tickets', 'starts_at' => '2026-10-20 23:00:00', 'is_draft' => true, 'creator_role_id' => $role->id]);

        DB::table('analytics_events_daily')->insert([
            ['event_id' => $tonight->id, 'date' => '2026-10-01', 'desktop_views' => 200, 'mobile_views' => 14, 'tablet_views' => 0, 'unknown_views' => 0],
            ['event_id' => $tonight->id, 'date' => '2026-08-01', 'desktop_views' => 999, 'mobile_views' => 0, 'tablet_views' => 0, 'unknown_views' => 0],
        ]);

        [$first, $second, $third] = $this->build($owner)['coming']['rows'];

        $this->assertSame('today', $first['when']);
        $this->assertSame(['sold' => 86, 'capacity' => 150, 'paid' => true], $first['tickets']);
        $this->assertSame(214, $first['views'], 'the period only');
        $this->assertSame(route('checkin.index'), $first['check_in']);

        $this->assertSame('tomorrow', $second['when']);
        $this->assertSame(['sold' => 12, 'capacity' => null, 'paid' => false], $second['tickets'], 'no limit, and free');
        $this->assertNull($second['check_in']);

        $this->assertNull($third['when']);
        $this->assertNull($third['tickets']);
        $this->assertSame('draft', $third['visibility']);

        $html = $this->actingAs($owner)->get(route('home'))->assertOk();
        $html->assertSee(__('messages.dash_count_of_total', ['count' => '86', 'total' => '150']));
        $html->assertSee(__('messages.checkin_dashboard'));
    }

    /**
     * Money follows who owns the event. A curator that lists a venue's event sees the event, and
     * neither its revenue nor its sales. Mutation: take sales from every accepted event_role.
     */
    public function test_a_curator_does_not_see_the_sales_of_an_event_it_only_lists(): void
    {
        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue', ['name' => 'The Hall']);
        $curatorOwner = $this->createOwner();
        $curator = $this->createRole($curatorOwner, 'curator', ['name' => 'City Guide']);

        $event = $this->createEvent($venue, ['name' => 'Listed Show', 'ticket_currency_code' => 'USD']);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);
        $this->createSale($event, $venue, ['payment_amount' => 40, 'name' => 'Paying Guest']);

        $theirs = $this->build($curatorOwner);
        $this->assertSame(['Listed Show'], array_column($theirs['coming']['rows'], 'name'), 'it is on their schedule');
        $this->assertSame([], $theirs['revenue']['currencies']);
        $this->assertSame(0, $theirs['revenue']['sales']);
        $this->assertSame([], $theirs['activity']['rows']);

        $mine = $this->build($venueOwner);
        $this->assertSame([['currency_code' => 'USD', 'amount' => 40.0]], $mine['revenue']['currencies']);
        $this->assertSame('Paying Guest', $mine['activity']['rows'][0]['who']);
    }

    /**
     * A deleted sale is not a sale: not in the amount, the count, the bars or the feed. Purchases
     * are counted once however many rows a checkout wrote. Two currencies stay two figures.
     * Mutation: drop either `is_deleted` test, or count rows.
     */
    public function test_revenue_counts_purchases_by_currency_and_never_a_deleted_sale(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $pounds = $this->createEvent($role, ['name' => 'In Pounds', 'ticket_currency_code' => 'GBP']);
        $euros = $this->createEvent($role, ['name' => 'In Euros', 'ticket_currency_code' => 'EUR']);

        $this->createSale($pounds, $role, ['payment_amount' => 30]);
        // One checkout, two named guests: two rows, one purchase.
        $this->createSale($pounds, $role, ['payment_amount' => 12, 'group_id' => 77]);
        $this->createSale($pounds, $role, ['payment_amount' => 12, 'group_id' => 77]);
        $this->createSale($euros, $role, ['payment_amount' => 20]);
        $this->createSale($pounds, $role, ['payment_amount' => 500, 'is_deleted' => true, 'name' => 'Deleted Buyer']);
        $this->createSale($pounds, $role, ['payment_amount' => 500, 'status' => 'unpaid']);
        // Paid before the period.
        $old = $this->createSale($pounds, $role, ['payment_amount' => 900]);
        DB::table('sales')->where('id', $old->id)->update(['created_at' => now()->subDays(45)]);

        $dashboard = $this->build($owner);

        $this->assertSame(
            [['currency_code' => 'GBP', 'amount' => 54.0], ['currency_code' => 'EUR', 'amount' => 20.0]],
            $dashboard['revenue']['currencies']
        );
        $this->assertSame(3, $dashboard['revenue']['sales']);
        $this->assertSame(3, array_sum($dashboard['revenue']['days']), 'the bars are the same purchases');
        $this->assertCount(30, $dashboard['revenue']['days']);
        $this->assertNotContains('Deleted Buyer', array_column($dashboard['activity']['rows'], 'who'));
    }

    /**
     * A new follower is a person with a name; the feed used to build it from first_name and
     * last_name, which are not columns. Mutation: read those again.
     */
    public function test_the_feed_names_followers_and_buyers(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['name' => 'The Show', 'ticket_currency_code' => 'USD']);
        $ticket = $this->createTicket($event, ['price' => 10]);

        $named = User::factory()->create(['name' => 'Dana Whitlock', 'email' => 'dana@gmail.com']);
        $nameless = User::factory()->create(['name' => '', 'email' => 'tom@gmail.com']);
        $role->users()->attach($named->id, ['level' => 'follower', 'created_at' => now()->subHour(), 'updated_at' => now()->subHour()]);
        $role->users()->attach($nameless->id, ['level' => 'follower', 'created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2)]);
        $this->createSale($event, $role, ['payment_amount' => 20, 'name' => 'Maya Okafor'], $ticket, 2);
        // A deleted sale is not something that happened. Mutation: drop is_deleted from the feed.
        $this->createSale($event, $role, ['payment_amount' => 99, 'name' => 'Deleted Buyer', 'is_deleted' => true], $ticket, 1);

        $rows = collect($this->build($owner)['activity']['rows']);
        $this->assertNotContains('Deleted Buyer', $rows->pluck('who')->all());

        $sale = $rows->firstWhere('type', 'sale');
        $this->assertSame(['The Show', 'Maya Okafor', 2, 20.0], [$sale['title'], $sale['who'], $sale['quantity'], $sale['amount']]);

        $followers = $rows->where('type', 'follower')->values();
        $this->assertSame(['Dana Whitlock', 'dana@gmail.com'], [$followers[0]['title'], $followers[0]['who']]);
        $this->assertSame(['tom@gmail.com', null], [$followers[1]['title'], $followers[1]['who']], 'no name: the address stands in');

        $this->actingAs($owner)->get(route('home'))->assertOk()->assertSee('Dana Whitlock')->assertSee('Maya Okafor');
    }

    /**
     * "30 days" is today and the 29 before it, against the 30 before those: the same number of
     * dates on both sides. Mutation: start the current window at subDays($period).
     */
    public function test_a_period_is_the_same_span_on_both_sides(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->views($role, '2026-10-06', 5);   // today
        $this->views($role, '2026-09-07', 7);   // the first day of the 30
        $this->views($role, '2026-09-06', 11);  // the last day of the 30 before
        $this->views($role, '2026-08-08', 13);  // its first
        $this->views($role, '2026-08-07', 100); // before both

        $views = $this->build($owner)['views'];

        $this->assertSame(12, $views['total']);
        $this->assertSame(24, $views['previous']);
        $this->assertSame(-50.0, $views['change']);
        $this->assertCount(30, $views['days']);
        $this->assertSame([7, 5], [$views['days'][0], $views['days'][29]]);
    }

    /** Mutation: report +100% when there is nothing to compare with. */
    public function test_with_no_earlier_views_there_is_no_change_figure(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->views($role, '2026-10-05', 9);

        $this->assertNull($this->build($owner)['views']['change']);
    }

    /**
     * Someone who made their first event a minute ago has nothing to count, and is not shown four
     * zeros: their event, and one card waiting for the first visitor. The tiles arrive with the
     * first thing to count. Mutation: always render the tiles.
     */
    public function test_a_new_organizer_sees_their_event_and_a_card_waiting_not_four_zeros(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['name' => 'League Night']);

        $page = $this->actingAs($owner)->get(route('home'))->assertOk();

        $this->assertTrue($page->viewData('dashboard')['fresh']);
        $page->assertSee('League Night')
            ->assertSee(__('messages.dash_waiting_title'))
            ->assertSee('data-first-visitor-waiting', false)
            ->assertDontSee(__('messages.dash_page_views_30m'))
            ->assertDontSee(__('messages.dash_in_total'));

        // One view, and they are an organizer with numbers.
        $this->views($role, '2026-10-06', 1);
        $page = $this->actingAs($owner)->get(route('home'))->assertOk();

        $this->assertFalse($page->viewData('dashboard')['fresh']);
        $page->assertSee(__('messages.dash_page_views_30m'))->assertDontSee(__('messages.dash_waiting_title'));
    }

    /**
     * The card carries the link to share only when the setup guide is not on the page saying the
     * same thing a card above it. Mutation: always print the link.
     */
    public function test_the_first_visitor_card_shares_the_link_only_when_the_guide_does_not(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role);

        $page = $this->actingAs($owner)->get(route('home'))->assertOk();
        $guide = str_contains($page->getContent(), 'data-setup-guide="section"');

        $this->assertSame(! $guide, str_contains($page->getContent(), 'data-copy-link='));
    }

    /**
     * Someone who runs nothing and holds a ticket came for the ticket: no organizer numbers, no
     * "create your first schedule" as the opening line. Mutation: send every schedule-less
     * account to the empty state.
     */
    public function test_an_attendee_gets_their_tickets_and_what_they_follow(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'The Vinyl Room']);
        $event = $this->createEvent($role, ['name' => 'Halloween Warehouse Party', 'starts_at' => '2026-10-31 23:00:00']);
        $ticket = $this->createTicket($event);
        $past = $this->createEvent($role, ['name' => 'Already Happened', 'starts_at' => '2026-09-01 23:00:00']);

        $attendee = User::factory()->create(['email_verified_at' => now(), 'signup_intent' => 'ticket']);
        $this->createSale($event, $role, ['user_id' => $attendee->id, 'event_date' => '2026-10-31'], $ticket, 2);
        $this->createSale($past, $role, ['user_id' => $attendee->id, 'event_date' => '2026-09-01'], null);
        $this->createSale($event, $role, ['user_id' => $attendee->id, 'event_date' => '2026-10-31', 'is_deleted' => true, 'name' => 'Deleted']);
        $role->users()->attach($attendee->id, ['level' => 'follower']);

        $page = $this->actingAs($attendee)->get(route('home'))->assertOk();
        $dashboard = $page->viewData('dashboard');

        $this->assertSame('attendee', $dashboard['state']);
        $this->assertSame(['Halloween Warehouse Party'], array_column($dashboard['tickets'], 'name'));
        $this->assertSame(2, $dashboard['tickets'][0]['quantity']);
        $this->assertSame(['Halloween Warehouse Party'], array_column($dashboard['follows'], 'name'));
        $this->assertSame('The Vinyl Room', $dashboard['follows'][0]['host']);

        $page->assertSee(__('messages.your_tickets'))
            ->assertSee(__('messages.dash_from_following'))
            ->assertDontSee(__('messages.create_your_first_schedule'))
            ->assertDontSee(__('messages.dash_in_total'))
            ->assertDontSee('data-dashboard-customize', false);
    }

    /**
     * A team member who was only given a look sees those schedules and what is coming up on them,
     * and no number they were not given. Mutation: treat a viewer as an editor.
     */
    public function test_someone_who_may_only_view_sees_the_schedules_and_no_numbers(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'The Hall']);
        $event = $this->createEvent($role, ['name' => 'Members Night', 'ticket_currency_code' => 'USD']);
        $this->createSale($event, $role, ['payment_amount' => 75]);
        $this->views($role, '2026-10-05', 40);

        $viewer = $this->createOwner();
        $role->users()->attach($viewer->id, ['level' => 'viewer']);

        $page = $this->actingAs($viewer)->get(route('home'))->assertOk();
        $dashboard = $page->viewData('dashboard');

        $this->assertSame('viewer', $dashboard['state']);
        $this->assertSame(['The Hall'], array_column($dashboard['schedules'], 'name'));
        $this->assertArrayNotHasKey('revenue', $dashboard);
        $this->assertArrayNotHasKey('views', $dashboard);
        $this->assertNull($dashboard['coming']['rows'][0]['views']);
        $this->assertNull($dashboard['coming']['rows'][0]['tickets']);

        $page->assertSee('Members Night')
            ->assertDontSee(__('messages.dash_page_views_30m'))
            ->assertDontSee(__('messages.dash_in_total'))
            ->assertDontSee('data-dashboard-customize', false)
            ->assertDontSee('dashboard-add-event-menu', false);
        $this->assertArrayNotHasKey('addTargets', $dashboard, 'and nothing to add an event to');
    }

    /** Nothing of theirs to show: the invitation, and no grid of zeros or empty calendar. */
    public function test_someone_with_nothing_is_invited_to_start(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'signup_intent' => 'follow']);

        $page = $this->actingAs($user)->get(route('home'))->assertOk();

        $this->assertSame('empty', $page->viewData('dashboard')['state']);
        $this->assertFalse($page->viewData('showCalendar'));
        $page->assertSee(__('messages.create_your_first_schedule'))->assertDontSee(__('messages.dash_in_total'));
    }

    /**
     * What someone saved in Customize before the page was rebuilt still applies: a card they hid
     * stays hidden, the period is kept, and the four tiles show whatever the old list says of
     * them. Mutation: hide a tile its saved entry marks hidden.
     */
    public function test_a_config_saved_in_the_old_shape_still_applies(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['name' => 'Hidden With Its Card']);
        $this->views($role, '2026-10-05', 3);

        $owner->dashboard_config = ['panels' => [
            ['id' => 'views', 'visible' => false, 'size' => 2, 'period' => 7],
            ['id' => 'upcoming_events', 'visible' => false, 'size' => 2, 'count' => 5],
            ['id' => 'recent_activity', 'visible' => true, 'size' => 1, 'count' => 10],
            // Its own period, from when each card had one. The page has a single period now.
            ['id' => 'top_events', 'visible' => true, 'size' => 1, 'count' => 3, 'period' => 30],
        ]];
        $owner->save();

        $page = $this->actingAs($owner)->get(route('home'))->assertOk();

        // The card's heading names the window its rows cover, not the one saved with it long ago.
        $page->assertSee(__('messages.panel_top_events').' (7d)')->assertDontSee('(30d)');

        $this->assertSame(7, $page->viewData('dashboard')['period']);
        // The Customize dialog prints all three period labels and every card's name on every
        // organizer page, so "the page says 7 days" proves nothing. Once is the dialog; more
        // than once is the tiles and cards saying it too.
        $said = fn ($response, int $days) => substr_count($response->getContent(), e(__('messages.last_'.$days.'_days')));
        $this->assertGreaterThan(1, $said($page, 7), 'the tiles cover the saved period');
        $this->assertSame(1, $said($page, 14));
        $this->assertSame(1, $said($page, 30));
        $page->assertSee('id="dashboard-activity"', false)
            ->assertDontSee('id="dashboard-coming-up"', false)
            ->assertDontSee('Hidden With Its Card')
            ->assertSee(__('messages.dash_in_total'))
            ->assertSee('id="dashboard-calendar"', false);

        // And the calendar, which can be switched off now, is saved through the same endpoint.
        $this->actingAs($owner)->postJson(route('home.save_config'), ['panels' => [
            ['id' => 'views', 'visible' => true, 'period' => 14],
            ['id' => 'calendar', 'visible' => false],
        ]])->assertOk();

        $after = $this->actingAs($owner->fresh())->get(route('home'))->assertOk()
            ->assertDontSee('id="dashboard-calendar"', false);
        $this->assertSame(14, $after->viewData('dashboard')['period']);
        $this->assertGreaterThan(1, $said($after, 14));
        $this->assertSame(1, $said($after, 7));
    }

    /**
     * The live tile, where this person has a live view, and Upcoming events in its place where
     * they do not. With nobody about it says so in words, not zeros.
     * Mutation: render the Realtime tile whenever Realtime is on for admins.
     */
    public function test_the_fourth_tile_is_realtime_only_where_the_owner_has_a_live_view(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role);
        $this->views($role, '2026-10-05', 3);

        $page = $this->actingAs($owner)->get(route('home'))->assertOk();
        $page->assertSee(__('messages.dash_page_views_30m'))
            ->assertSee(__('messages.dash_quiet_now'))
            // The tile opens the Realtime tab of Analytics, and refreshes from the poll beside it.
            ->assertSee('href="'.e(route('analytics', ['tab' => 'realtime'])).'"', false)
            ->assertSee('analytics\/realtime\/summary', false);

        DB::table('realtime_hits')->insert([
            'hit_key' => str_repeat('a', 32), 'visitor_key' => str_repeat('b', 16), 'consented' => true, 'owner_visible' => true,
            'surface' => 'gp', 'path' => '/', 'role_id' => $role->id, 'device' => 'mobile', 'hb' => 60,
            'started_at' => RealtimeTracker::ts(now()), 'last_seen_at' => RealtimeTracker::ts(now()), 'engaged_at' => RealtimeTracker::ts(now()),
        ]);
        $live = $this->actingAs($owner)->get(route('home'))->assertOk()->viewData('dashboard')['live'];
        $this->assertSame([1, 1, 1], [$live['views_5m'], $live['visitors_now'], $live['views_30m']]);

        Setting::set('realtime_owner_view', '0');
        $page = $this->actingAs($owner)->get(route('home'))->assertOk();
        $page->assertDontSee(__('messages.dash_page_views_30m'))
            ->assertDontSee('realtime\/summary', false)
            ->assertSee(__('messages.dash_from_today'))
            // "Next event", not the pager's "Next" (which is "Weiter" and "Avanti" in translation).
            ->assertSee(__('messages.dash_next_event'));
        $this->assertNull($page->viewData('dashboard')['live']);
    }

    /**
     * With several schedules: one row each under the tiles, adding up to them, and a chip of the
     * to-do queue says whose it is. Mutation: sum the wrong schedule's rows, or drop the name.
     */
    public function test_several_schedules_are_listed_and_add_up_to_the_tiles(): void
    {
        $owner = $this->createOwner();
        $first = $this->createRole($owner, 'venue', ['name' => 'Alpha Room']);
        $second = $this->createRole($owner, 'talent', ['name' => 'Beta Band']);
        $this->createEvent($first);
        $this->createEvent($first);
        $this->createEvent($second, ['starts_at' => '2026-08-06 23:00:00', 'days_of_week' => '0000100', 'recurring_frequency' => 'weekly']);
        $this->views($first, '2026-10-05', 30);
        $this->views($second, '2026-10-05', 12);
        $fan = User::factory()->create();
        $second->users()->attach($fan->id, ['level' => 'follower']);
        // Somebody asks to be listed on the first schedule.
        $requested = $this->createEvent($this->createRole($this->createOwner()), ['name' => 'Asking']);
        $requested->roles()->attach($first->id, ['is_accepted' => null]);

        $page = $this->actingAs($owner)->get(route('home'))->assertOk();
        $dashboard = $page->viewData('dashboard');
        $rows = collect($dashboard['schedules'])->keyBy('name');

        $this->assertSame([2, 30, 0], [$rows['Alpha Room']['upcoming'], $rows['Alpha Room']['views'], $rows['Alpha Room']['followers']]);
        $this->assertSame([1, 12, 1], [$rows['Beta Band']['upcoming'], $rows['Beta Band']['views'], $rows['Beta Band']['followers']]);
        $this->assertSame($dashboard['views']['total'], $rows->sum('views'));
        $this->assertSame($dashboard['followers']['total'], $rows->sum('followers'));

        $page->assertSee('id="dashboard-schedules"', false)
            ->assertSee('dashboard-add-event-menu', false)
            ->assertSeeInOrder([__('messages.needs_attention'), 'Alpha Room']);
        $this->assertStringContainsString('&middot; Alpha Room', $page->getContent(), 'the chip names the schedule');
    }

    /**
     * The page costs the same whether someone has six schedules or fifteen. Six, not two: both
     * then fill the five rows of Coming up, so anything a ROW costs is the same on both sides and
     * what is left is what a SCHEDULE costs, which must be nothing.
     * Mutation: ask for a schedule's numbers inside the loop that lists it.
     */
    public function test_the_page_does_not_query_per_schedule(): void
    {
        $count = function (int $schedules): int {
            $owner = $this->createOwner();
            foreach (range(1, $schedules) as $n) {
                $role = $this->createRole($owner, 'venue', ['name' => 'Room '.$n]);
                $this->createEvent($role);
                $this->views($role, '2026-10-05', $n);
            }
            $this->actingAs($owner);
            app()->forgetInstance('userRoles');
            app('userRoles');

            DB::flushQueryLog();
            DB::enableQueryLog();
            (new HomeDashboard)->build($owner, 30, null);

            return count(DB::getQueryLog());
        };

        $six = $count(6);
        $fifteen = $count(15);

        $this->assertSame($six, $fifteen, "6 schedules: {$six} queries, 15 schedules: {$fifteen}");
    }

    /**
     * Realtime is a tab of Analytics, so the sidebar has no entry of its own for it, with the
     * view on or off: one entry answers "how are my pages doing". Every link to the view goes to
     * that tab, the new organizer's card included.
     * Mutation: put the entry back in layouts/navigation, or link the card to /realtime.
     */
    public function test_realtime_has_no_sidebar_entry_and_every_link_goes_to_the_analytics_tab(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role);
        $tab = e(route('analytics', ['tab' => 'realtime']));

        // A page that is not the dashboard: whatever links to Realtime there is the sidebar's.
        $sales = $this->actingAs($owner)->get(route('sales'))->assertOk()->getContent();
        $this->assertStringNotContainsString('tab=realtime', $sales);
        $this->assertStringNotContainsString('href="'.url('/realtime').'"', $sales);
        $this->assertStringContainsString('href="'.route('analytics').'"', $sales, 'Analytics is the way in');

        // Day one: the card that waits for the first visitor opens the tab.
        $home = $this->actingAs($owner)->get(route('home'))->assertOk();
        $this->assertTrue($home->viewData('dashboard')['fresh']);
        $home->assertSee('href="'.$tab.'"', false);
        $this->assertStringNotContainsString('href="'.url('/realtime').'"', $home->getContent());
    }

    // ---- What the review of 2026-10-06 found -----------------------------------------------------

    /**
     * Following a schedule gives no part in running it. The list of what the schedules you follow
     * have on used the organizer's own query, so any stranger who pressed Follow was shown the
     * schedule's drafts, its unlisted and password-protected events, a cancelled one, and
     * appointment bookings, which are named after the guest who made them.
     * Mutation: call coming() without publicOnly in HomeDashboard::attendee().
     */
    public function test_someone_who_follows_a_schedule_sees_only_what_it_shows_a_stranger(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'The Vinyl Room']);
        $soon = fn (int $days) => now()->addDays($days)->format('Y-m-d H:i:s');

        $this->createEvent($role, ['name' => 'Open To All', 'starts_at' => $soon(3)]);
        $this->createEvent($role, ['name' => 'Still A Draft', 'starts_at' => $soon(4), 'is_draft' => true]);
        $this->createEvent($role, ['name' => 'Staff Only', 'starts_at' => $soon(5), 'is_draft' => true, 'is_internal' => true]);
        $this->createEvent($role, ['name' => 'By Link Only', 'starts_at' => $soon(6), 'is_private' => true]);
        $this->createEvent($role, ['name' => 'Behind A Password', 'starts_at' => $soon(7), 'event_password' => 'secret']);
        $this->createEvent($role, ['name' => 'Called Off', 'starts_at' => $soon(8), 'is_cancelled' => true]);
        $type = $this->createAppointmentType($role);
        $this->createEvent($role, ['name' => 'Consultation - Dana Whitlock', 'starts_at' => $soon(9), 'is_private' => true, 'appointment_type_id' => $type->id]);
        $this->createEvent($role, ['name' => 'Weekly And Hidden', 'starts_at' => '2026-08-06 23:00:00', 'days_of_week' => '0000100', 'recurring_frequency' => 'weekly', 'is_draft' => true]);

        $fan = User::factory()->create(['email_verified_at' => now()]);
        $role->users()->attach($fan->id, ['level' => 'follower']);
        // Something the follower made themselves, on that schedule: theirs to see, not to edit there.
        $this->createEvent($role, ['name' => 'Sent In By A Fan', 'starts_at' => $soon(10), 'user_id' => $fan->id]);

        $page = $this->actingAs($fan)->get(route('home'))->assertOk();
        $follows = $page->viewData('dashboard')['follows'];

        $this->assertSame(['Open To All', 'Sent In By A Fan'], array_column($follows, 'name'));
        foreach (['Still A Draft', 'Staff Only', 'By Link Only', 'Behind A Password', 'Called Off', 'Dana Whitlock', 'Weekly And Hidden'] as $hidden) {
            $page->assertDontSee($hidden);
        }
        foreach ($follows as $row) {
            $this->assertStringNotContainsString('/edit', (string) $row['url'], 'a follower is not sent to the edit form');
        }

        // The schedule's own people still see all of it.
        $mine = array_column($this->build($owner)['coming']['rows'], 'name');
        $this->assertContains('Still A Draft', $mine);
        $this->assertContains('By Link Only', $mine);
    }

    /**
     * Exactly one strip of bars is the live one. An include is handed every variable of the view
     * that includes it, so while the bars partial read `$live` the tiles' own `$live` marked all
     * four strips, and the first refresh redrew Views, Followers and Revenue with the minutes of
     * the last half hour. The same leak made every Coming up thumbnail the small one.
     * Mutation: test `$live` in home/_bars, or `$small` in home/_thumb, again.
     */
    public function test_only_the_realtime_tile_is_redrawn_and_an_event_keeps_its_own_thumbnail(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role);
        $this->views($role, '2026-10-05', 3);

        $html = $this->actingAs($owner)->get(route('home'))->assertOk()->getContent();

        $this->assertNotNull($this->actingAs($owner)->get(route('home'))->viewData('dashboard')['live'], 'this page has the live tile');
        $this->assertSame(1, substr_count($html, 'data-live-bars data-tone='), 'one live strip, the Realtime tile\'s');
        $this->assertSame(4, substr_count($html, 'flex items-end justify-center gap-0.5 h-7 w-full'), 'and four strips in all');

        $card = substr($html, (int) strpos($html, 'aria-labelledby="dashboard-coming-up"'));
        $card = substr($card, 0, (int) strpos($card, '</section>'));
        $this->assertStringContainsString('w-12 h-12 rounded-xl', $card, 'the row thumbnail');
        $this->assertStringNotContainsString('w-10 h-10 rounded-lg', $card);
    }

    /**
     * What was sold is the owner's to read, and Check in is offered where /checkin would list the
     * event. A curator that lists a venue's show sees the show, not "Sold 86 of 150"; and a
     * schedule on the free plan is not sent to a paid plan's page that would show it nothing.
     * Mutation: drop canViewEventData() or isPro() from HomeDashboard::coming().
     */
    public function test_sold_counts_follow_who_owns_the_event_and_check_in_follows_the_plan(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-06 16:00:00', 'UTC'));

        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue', ['name' => 'The Hall']);
        $curatorOwner = $this->createOwner();
        $curator = $this->createRole($curatorOwner, 'curator', ['name' => 'City Guide']);

        $event = $this->createEvent($venue, ['name' => 'Listed Show', 'starts_at' => '2026-10-06 23:00:00', 'tickets_enabled' => true, 'creator_role_id' => $venue->id]);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);
        $this->createTicket($event, ['quantity' => 150, 'price' => 15, 'sold' => json_encode(['2026-10-06' => 86])]);

        $theirs = $this->build($curatorOwner)['coming']['rows'][0];
        $this->assertSame('Listed Show', $theirs['name']);
        $this->assertSame('today', $theirs['when']);
        $this->assertNull($theirs['tickets'], 'a curator that only lists it does not read its sales');
        $this->assertNull($theirs['check_in']);

        $mine = $this->build($venueOwner)['coming']['rows'][0];
        $this->assertSame(['sold' => 86, 'capacity' => 150, 'paid' => true], $mine['tickets']);
        $this->assertSame(route('checkin.index'), $mine['check_in']);

        // The same show on a schedule whose plan has no check-in.
        $freeOwner = $this->createOwner();
        $free = $this->createFreeRole($freeOwner, 'venue', ['name' => 'Free Room']);
        $this->assertFalse($free->fresh()->isPro());
        $show = $this->createEvent($free, ['name' => 'Free Plan Show', 'starts_at' => '2026-10-06 23:00:00', 'tickets_enabled' => true, 'creator_role_id' => $free->id]);
        $this->createTicket($show, ['quantity' => 20, 'price' => 0, 'sold' => json_encode(['2026-10-06' => 4])]);

        $row = $this->build($freeOwner)['coming']['rows'][0];
        $this->assertSame(['sold' => 4, 'capacity' => 20, 'paid' => false], $row['tickets'], 'its own numbers are still its own');
        $this->assertNull($row['check_in'], '/checkin would list nothing for it');
    }

    /**
     * "Upcoming" is a count of what is really to come, and the tile, the card and the rows of
     * "Your schedules" share it. The query alone keeps a series that ended and has an extra date
     * on file, which is how the page said "Upcoming 1" beside "No upcoming events"; and a yearly
     * event more than four months out was counted and never listed.
     * Mutation: count with upcomingQuery()->count(), or look 120 days ahead.
     */
    public function test_upcoming_counts_what_is_really_to_come_and_agrees_with_the_list(): void
    {
        $owner = $this->createOwner();
        $first = $this->createRole($owner, 'venue', ['name' => 'Alpha Room']);
        $second = $this->createRole($owner, 'talent', ['name' => 'Beta Band']);

        // Ran on Thursdays until August, with one extra date in September. Nothing to come.
        $this->createEvent($first, [
            'name' => 'Ended With An Extra Date', 'starts_at' => '2026-06-04 23:00:00', 'days_of_week' => '0000100',
            'recurring_frequency' => 'weekly', 'recurring_end_type' => 'on_date', 'recurring_end_value' => '2026-08-27',
            'recurring_include_dates' => ['2026-09-15'],
        ]);
        // Once a year, next on 24 April: 200 days from the 6th of October.
        $this->createEvent($second, ['name' => 'Anniversary', 'starts_at' => '2026-04-24 23:00:00', 'days_of_week' => '1111111', 'recurring_frequency' => 'yearly']);
        $this->createEvent($second, ['name' => 'One Night', 'starts_at' => '2026-10-20 23:00:00']);
        $this->views($first, '2026-10-05', 1);

        $dashboard = $this->build($owner);

        $this->assertSame(['One Night', 'Anniversary'], array_column($dashboard['coming']['rows'], 'name'));
        $this->assertSame(2, $dashboard['coming']['total']);
        $this->assertSame(2, $dashboard['upcoming']['count'], 'the tile says what the list says');

        $rows = collect($dashboard['schedules'])->keyBy('name');
        $this->assertSame(0, $rows['Alpha Room']['upcoming'], 'a series with nothing left is not upcoming');
        $this->assertSame(2, $rows['Beta Band']['upcoming']);
    }

    /**
     * Seats are the event's own arithmetic. Three ticket types that share one house of 100 are
     * 100 seats, not 300; a pass is sold once for a whole series, so its pool is no date's; and an
     * event that takes sign-ups with no ticket types still has a count, which is the one /checkin
     * works from. Mutation: sum quantity over $event->tickets, or return null without tickets.
     */
    public function test_a_shared_house_a_pass_and_sign_ups_are_counted_as_the_event_counts_them(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $at = '2026-10-20 23:00:00';
        $day = '2026-10-20';

        $house = $this->createEvent($role, ['name' => 'A Shared House', 'starts_at' => $at, 'tickets_enabled' => true, 'total_tickets_mode' => 'combined', 'creator_role_id' => $role->id]);
        foreach (['Stalls' => 30, 'Circle' => 20, 'Balcony' => 10] as $name => $sold) {
            $this->createTicket($house, ['type' => $name, 'quantity' => 100, 'price' => 20, 'sold' => json_encode([$day => $sold])]);
        }

        $series = $this->createEvent($role, ['name' => 'B With A Pass', 'starts_at' => '2026-10-21 23:00:00', 'tickets_enabled' => true, 'creator_role_id' => $role->id]);
        $this->createTicket($series, ['type' => 'Door', 'quantity' => 50, 'price' => 10, 'sold' => json_encode(['2026-10-21' => 5])]);
        $this->createTicket($series, ['type' => 'Season Pass', 'quantity' => 30, 'price' => 90, 'is_pass' => true, 'sold' => json_encode(['pass' => 20])]);

        $signUps = $this->createEvent($role, ['name' => 'C Sign Ups', 'starts_at' => '2026-10-22 23:00:00', 'rsvp_enabled' => true, 'rsvp_limit' => 40, 'rsvp_sold' => json_encode(['2026-10-22' => 7]), 'creator_role_id' => $role->id]);

        $lines = collect($this->build($owner)['coming']['rows'])->pluck('tickets', 'name');

        $this->assertSame(['sold' => 60, 'capacity' => 100, 'paid' => true], $lines['A Shared House']);
        $this->assertSame(['sold' => 5, 'capacity' => 50, 'paid' => true], $lines['B With A Pass'], 'the pass pool belongs to no one date');
        $this->assertSame(['sold' => 7, 'capacity' => 40, 'paid' => false], $lines['C Sign Ups']);
    }

    /**
     * A ticket stays on its holder's page until the event is over: day two of a festival is not
     * "past", and any event keeps six hours from its start, since a length is often a guess.
     * And someone whose tickets are all behind them is still an attendee, with the way to them,
     * not a stranger invited to create a schedule.
     * Mutation: filter on the start alone, or answer null when the list is empty.
     */
    public function test_a_ticket_stays_until_its_event_is_over_and_a_past_holder_is_still_an_attendee(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'The Field']);

        // Began two days ago and runs three: day three today.
        $festival = $this->createEvent($role, ['name' => 'Three Day Festival', 'starts_at' => '2026-10-04 13:00:00', 'duration' => 72, 'creator_role_id' => $role->id]);
        // Doors four hours ago, said to last one: over by the clock, and people are still inside.
        // Only the six hours keep it (an event with no length at all counts as two hours long).
        $tonight = $this->createEvent($role, ['name' => 'Doors Four Hours Ago', 'starts_at' => '2026-10-06 08:00:00', 'duration' => 1, 'creator_role_id' => $role->id]);
        // Over since early this morning.
        $done = $this->createEvent($role, ['name' => 'Over This Morning', 'starts_at' => '2026-10-06 02:00:00', 'duration' => 2, 'creator_role_id' => $role->id]);

        $holder = User::factory()->create(['email_verified_at' => now(), 'signup_intent' => 'ticket']);
        $this->createSale($festival, $role, ['user_id' => $holder->id, 'event_date' => '2026-10-04'], $this->createTicket($festival));
        $this->createSale($tonight, $role, ['user_id' => $holder->id, 'event_date' => '2026-10-06'], $this->createTicket($tonight));
        $this->createSale($done, $role, ['user_id' => $holder->id, 'event_date' => '2026-10-05'], $this->createTicket($done));

        $names = array_column($this->build($holder)['tickets'], 'name');
        sort($names);
        $this->assertSame(['Doors Four Hours Ago', 'Three Day Festival'], $names);

        // Only a ticket from last month, and nothing followed.
        $past = $this->createEvent($role, ['name' => 'Last Month', 'starts_at' => '2026-09-01 23:00:00', 'creator_role_id' => $role->id]);
        $former = User::factory()->create(['email_verified_at' => now(), 'signup_intent' => 'ticket']);
        $this->createSale($past, $role, ['user_id' => $former->id, 'event_date' => '2026-09-01'], $this->createTicket($past));

        $page = $this->actingAs($former)->get(route('home'))->assertOk();
        $this->assertSame('attendee', $page->viewData('dashboard')['state']);
        $this->assertSame([], $page->viewData('dashboard')['tickets']);
        $page->assertSee(__('messages.your_tickets'))
            ->assertSee('href="'.route('tickets', ['past' => 1]).'"', false)
            ->assertDontSee(__('messages.create_your_first_schedule'));
    }

    /**
     * One checkout is one row. A purchase for a group writes a row per named guest, and listing
     * each of them filled the whole card with one family; and an add-on (parking) is not a
     * ticket. The feed and the attendee's own card count the same way.
     * Mutation: drop the first-row filter from activity(), or sum sale_tickets.quantity.
     */
    public function test_one_checkout_is_one_row_and_an_add_on_is_not_a_ticket(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['name' => 'Family Night', 'starts_at' => '2026-10-20 23:00:00', 'ticket_currency_code' => 'USD', 'creator_role_id' => $role->id]);
        $ticket = $this->createTicket($event, ['price' => 15]);
        $parking = $this->createTicket($event, ['type' => 'Parking', 'price' => 5, 'is_addon' => true]);

        $buyer = User::factory()->create(['email_verified_at' => now(), 'signup_intent' => 'ticket']);
        $first = $this->createSale($event, $role, ['user_id' => $buyer->id, 'name' => 'Sam Parent', 'payment_amount' => 35, 'event_date' => '2026-10-20'], $ticket, 2);
        $first->forceFill(['group_id' => $first->id])->save();
        // Two parking spaces: with one, "three tickets" and "three lines on the first row" were
        // the same number, and the assertion below could not tell an add-on from a ticket.
        \App\Models\SaleTicket::create(['sale_id' => $first->id, 'ticket_id' => $parking->id, 'quantity' => 2, 'seats' => json_encode([1 => null, 2 => null])]);
        $guest = $this->createSale($event, $role, ['name' => 'Kim Guest', 'payment_amount' => 15, 'event_date' => '2026-10-20'], $ticket, 1);
        $guest->forceFill(['group_id' => $first->id])->save();

        $sales = array_values(array_filter($this->build($owner)['activity']['rows'], fn ($row) => $row['type'] === 'sale'));

        $this->assertCount(1, $sales, 'one purchase, however many guests it named');
        $this->assertSame('Sam Parent', $sales[0]['who']);
        $this->assertSame(3, $sales[0]['quantity'], 'three tickets across the group; the two parking spaces are not tickets');
        $this->assertSame(50.0, $sales[0]['amount'], 'the whole purchase');

        $mine = $this->build($buyer)['tickets'];
        $this->assertCount(1, $mine);
        $this->assertSame(3, $mine[0]['quantity']);
    }

    /**
     * A schedule whose plan no longer includes a team is named with the reason and not linked,
     * for an admin as for a viewer: its own pages send either of them straight back here.
     * Mutation: read `blocked` only for a viewer in home/_schedules.
     */
    public function test_a_schedule_the_plan_closes_to_an_admin_is_named_and_not_linked(): void
    {
        $member = $this->createOwner();
        $own = $this->createRole($member, 'venue', ['name' => 'My Own']);
        $closed = $this->createFreeRole(null, 'venue', ['name' => 'Lapsed Room']);
        $closed->users()->attach($member->id, ['level' => 'admin']);
        $this->views($own, '2026-10-05', 2);

        $page = $this->actingAs($member)->get(route('home'))->assertOk();
        $rows = collect($page->viewData('dashboard')['schedules'])->keyBy('name');

        $this->assertTrue($rows['Lapsed Room']['blocked']);
        $this->assertFalse($rows['My Own']['blocked']);
        // Within the card: the sidebar lists every schedule and is not this page's to change.
        $html = $page->getContent();
        $card = substr($html, (int) strpos($html, 'id="dashboard-schedules"'));
        $card = substr($card, 0, (int) strpos($card, '</section>'));

        $this->assertStringContainsString(e(__('messages.dash_plan_closed')), $card);
        $this->assertStringContainsString('href="'.$rows['My Own']['url'].'"', $card);
        $this->assertStringNotContainsString('href="'.$rows['Lapsed Room']['url'].'"', $card);
        $this->assertStringContainsString('Lapsed Room', $card, 'named, with the reason, and not a link');

        // With that schedule alone, the Followers tile has no page of its own to open.
        $only = $this->createOwner();
        $closed->users()->attach($only->id, ['level' => 'admin']);
        $this->assertNull($this->build($only)['followersUrl']);
    }

    /**
     * A card that could not be built is left out and the page still opens. Under test a card
     * rethrows, so nothing else renders this: a dashboard with every card missing.
     * Mutation: read a card's rows in home.blade.php or home/_tiles without asking if it is there.
     */
    public function test_a_page_whose_cards_all_failed_still_opens(): void
    {
        $owner = $this->createOwner();
        $first = $this->createRole($owner, 'venue', ['name' => 'Alpha Room']);
        $this->createRole($owner, 'talent', ['name' => 'Beta Band']);
        $this->createEvent($first);
        $this->views($first, '2026-10-05', 3);

        $this->app->bind(HomeDashboard::class, fn () => new class extends HomeDashboard
        {
            public function build(User $user, int $period, ?array $live = null): array
            {
                $dashboard = parent::build($user, $period, $live);
                foreach (['views', 'followers', 'revenue', 'upcoming', 'schedules', 'coming', 'activity'] as $card) {
                    $dashboard[$card] = null;
                }

                return ['fresh' => false, 'live' => null] + $dashboard;
            }
        });

        $this->actingAs($owner)->get(route('home'))->assertOk()
            ->assertSee(__('messages.dashboard'))
            ->assertDontSee('id="dashboard-coming-up"', false)
            ->assertDontSee('id="dashboard-schedules"', false);
    }

    /**
     * "Needs attention" is on the page whoever is looking: a schedule offered to someone who runs
     * none arrives there, and that someone may be an attendee, may only view a schedule, or may
     * have nothing at all. Mutation: render the chips inside the organizer branch only.
     */
    public function test_needs_attention_shows_in_every_state(): void
    {
        $giver = $this->createOwner();
        $offered = $this->createRole($giver, 'venue', ['name' => 'Handed Over Hall']);
        $offer = function (User $to) use ($giver, $offered) {
            $transfer = new \App\Models\RoleTransfer;
            $transfer->role_id = $offered->id;
            $transfer->from_user_id = $giver->id;
            $transfer->to_email = strtolower($to->email);
            $transfer->save();
        };
        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Somebody Else']);

        $nobody = User::factory()->create(['email_verified_at' => now(), 'signup_intent' => 'ticket']);
        $attendee = User::factory()->create(['email_verified_at' => now(), 'signup_intent' => 'ticket']);
        $venue->users()->attach($attendee->id, ['level' => 'follower']);
        $viewer = User::factory()->create(['email_verified_at' => now()]);
        $venue->users()->attach($viewer->id, ['level' => 'viewer']);

        foreach (['empty' => $nobody, 'attendee' => $attendee, 'viewer' => $viewer] as $state => $user) {
            $offer($user);
            app()->forgetInstance('userRoles');

            $page = $this->actingAs($user)->get(route('home'))->assertOk();
            $this->assertSame($state, $page->viewData('dashboard')['state']);
            $page->assertSee(__('messages.needs_attention'))
                ->assertSee(__('messages.pending_action_schedule_transfer'));
        }
    }

    /**
     * An organizer with nothing coming up is offered the way to add something, in the card that
     * says so, where there is one schedule it could go to. A brand-new organizer is not: the
     * setup guide directly above is already asking for exactly that. And on a phone "View page"
     * lives in the menu with the two rare actions, since a third button did not fit beside
     * "Add event" in a longer language.
     * Mutation: drop the action from the empty card, or print it for a new organizer too.
     */
    public function test_an_empty_coming_up_card_offers_add_event_except_to_a_brand_new_organizer(): void
    {
        $add = fn (Role $role) => 'href="'.route('event.create', ['subdomain' => $role->subdomain]).'"';
        $card = function ($response): string {
            $html = $response->getContent();
            $at = strpos($html, 'aria-labelledby="dashboard-coming-up"');
            $this->assertNotFalse($at, 'the page has the card');

            return substr($html, $at, (int) strpos(substr($html, $at), '</section>'));
        };

        // Has been seen, and its only event is behind it.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['starts_at' => '2026-09-01 23:00:00']);
        $this->views($role, '2026-10-05', 4);

        $page = $this->actingAs($owner)->get(route('home'))->assertOk();
        $this->assertFalse($page->viewData('dashboard')['fresh']);
        $this->assertStringContainsString($add($role), $card($page));

        // The phone menu holds the way to the public page.
        $html = $page->getContent();
        $menu = substr($html, (int) strpos($html, 'id="dashboard-more-menu"'));
        $menu = substr($menu, 0, (int) strpos($menu, 'dashboard-add-event-menu') ?: 4000);
        $this->assertStringContainsString(e(__('messages.dash_view_page')), substr($menu, 0, 2500));

        // Day one: no views, no followers, no sales, nothing coming up.
        $newcomer = $this->createOwner();
        $first = $this->createRole($newcomer);
        // The container outlives a request inside one test, and holds the last person's schedules.
        app()->forgetInstance('userRoles');

        $page = $this->actingAs($newcomer)->get(route('home'))->assertOk();
        $this->assertTrue($page->viewData('dashboard')['fresh']);
        $this->assertStringNotContainsString($add($first), $card($page));
    }
}
