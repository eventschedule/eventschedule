<?php

namespace Tests\Feature;

use App\Http\Controllers\MarketingController;
use App\Models\Event;
use App\Models\Role;
use App\Utils\BrowseWall;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * /browse is a wall of posters: one for each schedule, the event it has on next.
 *
 * Until 2026-10 the page showed the 24 soonest events, two per schedule and then whoever had
 * more, so six of the 24 on the live page were one schedule's synced work meetings. The owner's
 * instruction was one event per schedule. That rule is only worth having if "its event" is the
 * right one, and three things made the obvious version (the first row per schedule, in the
 * query's order) wrong:
 *
 *  - the query kept everything from midnight UTC, so a show that finished last night was a
 *    schedule's one poster;
 *  - a series sorted after every dated event, under the date it BEGAN, so a weekly night was
 *    last on the page for ever, and a series that had run out never left;
 *  - the pool holds three rows per schedule, so a studio with three classes already over today
 *    and one tonight was not on the wall at all.
 *
 * So "next" is worked out for each event (BrowseWall::next()) and "not ended" is in the SQL
 * before the pool is cut. The clock is set in every test: these are all about what time it is.
 */
class BrowseWallTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** A Thursday. 15:45 UTC is 11:45 in New York, where every fixture schedule is unless it says. */
    private const NOW = '2026-10-08 15:45:00';

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse(self::NOW, 'UTC'));
    }

    // ------------------------------------------------------- one per schedule

    public function test_each_schedule_has_one_poster_and_it_is_its_soonest(): void
    {
        $busy = $this->schedule('Busy Calendar');
        $this->event($busy, 'Call on Monday', '2026-10-12 14:00:00');
        $this->event($busy, 'Call tomorrow', '2026-10-09 14:00:00');
        $this->event($busy, 'Call on Sunday', '2026-10-11 14:00:00');

        $quiet = $this->schedule('Quiet Room');
        $this->event($quiet, 'Quiet Night', '2026-10-10 23:00:00');

        $response = $this->get('/browse')->assertOk();

        $this->assertSame(['Call tomorrow', 'Quiet Night'], $this->names($response));

        $tiles = $this->posters($response);
        $this->assertTrue($tiles[0]['more'], 'A schedule with more coming up says so');
        $this->assertFalse($tiles[1]['more']);
    }

    public function test_the_schedule_is_the_one_the_poster_credits_not_the_one_that_owns_the_event(): void
    {
        // A visitor counts repeats by the name they read. Two events credited to one venue are
        // that venue twice, whoever created them: keyed on creator_role_id these would both show.
        $venue = $this->schedule('The Venue');
        $decoy = $this->schedule('A Curator');
        $other = $this->schedule('Another Curator');

        $this->event($venue, 'First Night', '2026-10-09 23:00:00', ['creator_role_id' => $decoy->id]);
        $this->event($venue, 'Second Night', '2026-10-10 23:00:00', ['creator_role_id' => $other->id]);

        $this->assertSame(['First Night'], $this->names($this->get('/browse')->assertOk()));
    }

    // ------------------------------------------------------------ what is over

    public function test_an_event_that_has_ended_gives_way_to_the_schedules_next(): void
    {
        $studio = $this->schedule('The Studio');
        // 08:00 to 09:00 this morning in New York: over. It is still "today" by any date test.
        $this->event($studio, 'Morning Class', '2026-10-08 12:00:00', ['duration' => 1]);
        $this->event($studio, 'Evening Class', '2026-10-08 23:00:00', ['duration' => 1]);

        $response = $this->get('/browse')->assertOk();

        $this->assertSame(['Evening Class'], $this->names($response));
        $this->assertSame('today', $this->posters($response)[0]['band']);
        $this->assertFalse($this->posters($response)[0]['more'], 'What is over is not "more"');
    }

    public function test_a_schedule_with_nothing_left_is_not_on_the_wall(): void
    {
        $this->event($this->schedule('Last Night Only'), 'Last Night', '2026-10-08 01:00:00', ['duration' => 2]);

        $response = $this->get('/browse')->assertOk();

        $this->assertSame([], $this->names($response));
        $response->assertSee('No upcoming events yet');
    }

    public function test_an_event_with_no_length_is_on_for_six_hours(): void
    {
        $fresh = $this->event($this->schedule('Five Hours In'), 'Still On', '2026-10-08 10:45:00');
        $stale = $this->event($this->schedule('Seven Hours In'), 'Long Gone', '2026-10-08 08:45:00');
        Event::whereKey([$fresh->id, $stale->id])->update(['duration' => null]);

        $response = $this->get('/browse')->assertOk();

        $this->assertSame(['Still On'], $this->names($response));
        $this->assertTrue($this->posters($response)[0]['live']);

        // The SQL and the pick must agree, or the pool holds rows the pick throws away.
        $this->assertNull(BrowseWall::next($stale->fresh(), now('UTC')));
        $this->assertNotNull(BrowseWall::next($fresh->fresh(), now('UTC')));
    }

    public function test_a_busy_schedule_whose_day_is_mostly_over_still_has_its_poster(): void
    {
        // The pool takes three rows per owning schedule and is full at a hundred. Chosen AFTER
        // the pool was cut, "not ended" left this studio's three places to classes already over.
        foreach (range(1, 34) as $i) {
            $other = $this->schedule('Other '.$i);

            foreach ([1, 2, 3] as $day) {
                $this->event($other, "Other $i day $day", Carbon::parse(self::NOW, 'UTC')->addDays($day)->format('Y-m-d H:i:s'));
            }
        }

        $studio = $this->schedule('The Studio');
        foreach (['12:00:00', '13:00:00', '14:00:00'] as $over) {
            $this->event($studio, 'Class at '.$over, '2026-10-08 '.$over, ['duration' => 1]);
        }
        $this->event($studio, 'Class tonight', '2026-10-08 23:00:00', ['duration' => 1]);

        $names = $this->names($this->get('/browse')->assertOk());

        $this->assertContains('Class tonight', $names);
        $this->assertCount(BrowseWall::LIMIT, $names, 'One each, and the wall is full');
    }

    // ---------------------------------------------------------------- running

    public function test_a_festival_that_is_running_comes_before_the_same_schedules_later_one_off(): void
    {
        // The query's old order put anything that had started after every future event.
        $fest = $this->schedule('The Festival');
        $this->event($fest, 'One Night Next Week', '2026-10-13 23:00:00');
        $this->event($fest, 'Three Day Festival', '2026-10-07 14:00:00', ['duration' => 72]);

        $other = $this->schedule('Tomorrow Somewhere');
        $this->event($other, 'Tomorrow', '2026-10-09 23:00:00');

        $response = $this->get('/browse')->assertOk();

        $this->assertSame(['Three Day Festival', 'Tomorrow'], $this->names($response), 'What is on now is first on the wall');

        $poster = $this->posters($response)[0];
        $this->assertTrue($poster['live']);
        $this->assertSame('today', $poster['band']);
        $this->assertSame('Oct 7 - 10', $poster['when']);
    }

    // ----------------------------------------------------------------- series

    public function test_a_series_is_listed_by_the_day_it_is_next_on(): void
    {
        // A weekly Wednesday night that began in June. It used to sit last under "Recurring",
        // carrying the June date. Next Wednesday is the 14th.
        $club = $this->schedule('The Club');
        $this->event($club, 'Quiz Night', '2026-06-03 23:30:00', [
            'days_of_week' => '0001000',
            'recurring_frequency' => 'weekly',
        ]);
        $this->event($this->schedule('Next Thursday'), 'A Week Away', '2026-10-15 23:00:00');
        $this->event($this->schedule('Tomorrow'), 'Tomorrow Night', '2026-10-09 23:00:00');

        $response = $this->get('/browse')->assertOk();

        $this->assertSame(['Tomorrow Night', 'Quiz Night', 'A Week Away'], $this->names($response));

        $poster = $this->posters($response)[1];
        $this->assertSame('Wed, Oct 14', $poster['when']);
        $this->assertSame('7:30pm', $poster['time']);
        $this->assertSame('Weekly', $poster['rhythm']);
        $this->assertSame('week', $poster['band']);
        $this->assertStringContainsString('2026-10-14', $poster['url'], 'The poster opens the night it names, not the first of the series');
    }

    public function test_a_series_that_has_run_out_is_not_listed_and_a_monthly_one_is_not_called_weekly(): void
    {
        $this->event($this->schedule('Ended In September'), 'Summer Sessions', '2026-06-03 23:30:00', [
            'days_of_week' => '0001000',
            'recurring_frequency' => 'weekly',
            'recurring_end_type' => 'on_date',
            'recurring_end_value' => '2026-09-30',
        ]);
        // The 20th of each month. saveEvent() writes every weekday for a monthly rhythm.
        $this->event($this->schedule('Once A Month'), 'Members Evening', '2026-06-20 23:00:00', [
            'days_of_week' => '1111111',
            'recurring_frequency' => 'monthly_date',
        ]);

        $response = $this->get('/browse')->assertOk();

        $this->assertSame(['Members Evening'], $this->names($response));
        $this->assertSame('Monthly', $this->posters($response)[0]['rhythm']);
        $this->assertSame('Tue, Oct 20', $this->posters($response)[0]['when']);
    }

    // ------------------------------------------------------------------- days

    public function test_tomorrow_is_tomorrow_on_the_day_the_clocks_go_forward(): void
    {
        // Chile, 6 September 2026: midnight does not exist, the day starts at 01:00. A count of
        // whole days between two midnights came to 23 hours, was cut to zero, and tomorrow's
        // show read "Today".
        $this->travelTo(Carbon::parse('2026-09-06 15:00:00', 'UTC'));

        $venue = $this->schedule('Sala Santiago', ['timezone' => 'America/Santiago']);
        // 20:00 on the 7th in Santiago, which is then three hours behind UTC.
        $this->event($venue, 'Concierto', '2026-09-07 23:00:00');

        $poster = $this->posters($this->get('/browse')->assertOk())[0];

        $this->assertSame('Tomorrow', $poster['when']);
        $this->assertSame('8:00pm', $poster['time']);
        $this->assertSame('week', $poster['band']);
    }

    // ------------------------------------------------------------------ links

    public function test_a_schedule_that_is_unlisted_is_named_and_not_linked(): void
    {
        // The poster credits the first claimed schedule on the event, which need not be the one
        // that got it onto the page. The old card only printed a name; a link is more than that.
        $act = $this->schedule('The Unlisted Act', ['is_unlisted' => true], 'talent');
        $venue = $this->schedule('The Venue');

        $event = $this->event($act, 'Billed Here', '2026-10-09 23:00:00', ['creator_role_id' => $venue->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $response = $this->get('/browse')->assertOk();
        $poster = $this->posters($response)[0];

        $this->assertSame('The Unlisted Act', $poster['schedule']);
        $this->assertNull($poster['scheduleUrl']);
        $response->assertDontSee('/'.$act->subdomain.'"', false);

        // And one that is listed is linked.
        $this->event($venue, 'Open Night', '2026-10-10 23:00:00');
        $linked = collect($this->posters($this->get('/browse')->assertOk()))->firstWhere('name', 'Open Night');
        $this->assertStringContainsString($venue->subdomain, (string) $linked['scheduleUrl']);
    }

    // ------------------------------------------------------------------- page

    public function test_the_wall_is_there_without_any_script_and_its_first_row_is_not_left_to_load_late(): void
    {
        foreach (range(1, 6) as $i) {
            $this->event($this->schedule('Schedule '.$i), 'Night '.$i, Carbon::parse(self::NOW, 'UTC')->addDays($i)->format('Y-m-d H:i:s'));
        }

        $html = $this->get('/browse')->assertOk()->getContent();
        $wall = $this->between($html, 'class="bw-wall"', 'class="bw-after"');

        // marketing.css hides anything carrying data-reveal until marketing-home.js runs.
        $this->assertStringNotContainsString('data-reveal', $wall);
        $this->assertSame(1, substr_count($wall, 'fetchpriority="high"'), 'One picture is asked for ahead of the rest');
        $this->assertGreaterThanOrEqual(2, substr_count($wall, 'loading="eager"'));
        $this->assertSame(1, preg_match_all('/<h1\b/', $html));
    }

    public function test_the_structured_list_is_exactly_what_is_on_the_wall(): void
    {
        $busy = $this->schedule('Busy');
        $shown = $this->event($busy, 'Shown', '2026-10-09 23:00:00');
        $this->event($busy, 'Not Shown', '2026-10-10 23:00:00');

        $response = $this->get('/browse')->assertOk();

        preg_match('/<script type="application\/ld\+json"[^>]*>\s*(\{[^<]*"ItemList"[^<]*\})\s*<\/script>/', $response->getContent(), $block);
        $list = json_decode($block[1], true);

        $this->assertSame(
            array_column($this->posters($response), 'url'),
            array_column($list['itemListElement'], 'url')
        );
        $this->assertSame(['Shown'], array_column($list['itemListElement'], 'name'));
        $this->assertStringContainsString($shown->slug, $list['itemListElement'][0]['url']);
    }

    public function test_an_admin_is_told_when_a_hide_was_refused(): void
    {
        // toggleEventDiscovery() answers a lapsed re-auth with back()->with('error', ...), and
        // the old page printed only session('message'): the press did nothing, and said nothing.
        $this->event($this->schedule('Somewhere'), 'Something', '2026-10-09 23:00:00');

        $this->withSession(['error' => 'Please confirm your password first.'])
            ->get('/browse')
            ->assertOk()
            ->assertSee('Please confirm your password first.');
    }

    public function test_only_an_admin_gets_the_hide_buttons_and_the_hidden_list(): void
    {
        $shown = $this->event($this->schedule('Shown'), 'On The Wall', '2026-10-09 23:00:00');
        $hidden = $this->event($this->schedule('Hidden'), 'Off The Wall', '2026-10-09 23:00:00');
        Event::whereKey($hidden->id)->update(['is_hidden_from_discovery' => true]);

        $this->get('/browse')->assertOk()
            ->assertDontSee('Off The Wall')
            ->assertDontSee(route('marketing.discovery.toggle', $shown->hashedId()), false);

        $this->actingAs($this->createOwner(admin: true))->get('/browse')->assertOk()
            ->assertSee('Off The Wall')
            ->assertSee('Hidden events')
            ->assertSee(route('marketing.discovery.toggle', $shown->hashedId()), false)
            ->assertSee(route('marketing.discovery.toggle', $hidden->hashedId()), false);
    }

    // ------------------------------------------------------------------ cache

    public function test_what_the_wall_is_chosen_from_is_cached_but_an_event_that_ends_is_gone_at_once(): void
    {
        config(['marketing.wall_cache_seconds' => 600]);
        Cache::forget(MarketingController::browseCacheKey());

        $venue = $this->schedule('The Venue');
        // On now, for another three minutes: well inside the ten the cache lasts.
        $this->event($venue, 'Matinee', '2026-10-08 15:00:00', ['duration' => 0.8]);

        $this->assertSame(['Matinee'], $this->names($this->get('/browse')->assertOk()));
        $this->assertTrue(Cache::has(MarketingController::browseCacheKey()));

        // A new event waits out the cache...
        $late = $this->schedule('Late Arrival');
        Event::withoutEvents(fn () => $this->event($late, 'Added Since', '2026-10-09 23:00:00'));
        $this->assertSame(['Matinee'], $this->names($this->get('/browse')->assertOk()));

        // ...but one that has ended does not stay up for it.
        $this->travelTo(Carbon::parse(self::NOW, 'UTC')->addMinutes(5));
        $this->assertTrue(Cache::has(MarketingController::browseCacheKey()), 'The cache has not run out: this is the pick, run again');
        $this->assertSame([], $this->names($this->get('/browse')->assertOk()));

        // And the homepage wall's own reset clears this one with it.
        MarketingController::forgetWallCache();
        $this->assertSame(['Added Since'], $this->names($this->get('/browse')->assertOk()));
    }

    // ---------------------------------------------------------------- helpers

    private function schedule(string $name, array $attrs = [], string $type = 'venue'): Role
    {
        return $this->createRole($this->createOwner(), $type, ['name' => $name] + $attrs);
    }

    /** An event with a flyer of its own, owned by $role unless told otherwise. Times are UTC. */
    private function event(Role $role, string $name, string $startsAt, array $attrs = []): Event
    {
        return $this->createEvent($role, $attrs + [
            'name' => $name,
            'starts_at' => $startsAt,
            'creator_role_id' => $role->id,
            'flyer_image_url' => strtolower(preg_replace('/\W+/', '-', $name)).'.png',
        ]);
    }

    private function names($response): array
    {
        return $response->viewData('events')->map(fn (Event $event) => $event->name)->all();
    }

    /** The wall's tiles that are posters, in order. */
    private function posters($response): array
    {
        return array_values(array_filter(
            $response->viewData('wall')['tiles'],
            fn (array $tile) => in_array($tile['kind'], ['flyer', 'type'], true)
        ));
    }

    private function between(string $html, string $from, string $to): string
    {
        $start = strpos($html, $from);
        $this->assertNotFalse($start, "$from is not on the page");

        return substr($html, $start, strpos($html, $to, $start) - $start);
    }
}
