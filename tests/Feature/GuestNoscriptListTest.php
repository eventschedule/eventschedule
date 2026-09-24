<?php

namespace Tests\Feature;

use App\Models\EventPoll;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The schedule page's <noscript> list: what a crawler that does not run JavaScript sees of the
 * events, since the calendar itself is a Vue app filled by Ajax.
 *
 * It used to be built from the whole month's grid PLUS every recurring series the schedule had
 * ever created (scopeInMonth() admits any days_of_week row, ended or not), each loaded with its
 * roles, parts, videos, photos, commenters and polls, and then its creating schedule lazily, one
 * query per row. A 3,000-event schedule took 6.65 s to first byte. It is now
 * EventRepo::upcomingForGuest($role, $group, 50) - the next 50 public events, dated by occurrence -
 * while ?graphic=1, which renders the month itself, keeps the full month.
 */
class GuestNoscriptListTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Role's saving hook geocodes a new address whenever a Google key is configured, which a
        // developer's .env may carry.
        config(['services.google.backend' => null]);

        // A Thursday, late in the month, so "earlier this month" exists for the graphic test.
        $this->travelTo(Carbon::parse('2026-09-24 09:00:00', 'UTC'));
    }

    private function noscript(string $html): string
    {
        $this->assertSame(1, preg_match('#<noscript v-pre>(.*?)</noscript>#s', $html, $m),
            'The schedule page renders its noscript list, v-pre and all');

        return $m[1];
    }

    private function schedulePage(Role $role, string $path = ''): string
    {
        return $this->get('/'.$role->subdomain.$path)->assertOk()->getContent();
    }

    public function test_the_list_is_the_next_fifty_events_soonest_first(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Blue Room']);

        foreach (range(1, 55) as $day) {
            $this->createEvent($role, [
                'name' => sprintf('Session %02d', $day),
                'starts_at' => now()->addDays($day)->setTime(18, 0)->format('Y-m-d H:i:s'),
            ]);
        }
        $noscript = $this->noscript($this->schedulePage($role));

        $this->assertSame(50, substr_count($noscript, '<li'));
        $this->assertStringContainsString('Session 01', $noscript);
        $this->assertStringContainsString('Session 50', $noscript);
        $this->assertStringNotContainsString('Session 51', $noscript);
        $this->assertLessThan(strpos($noscript, 'Session 02'), strpos($noscript, 'Session 01'), 'Soonest first');
    }

    /**
     * A series is one page, its undated URL, dated here by the occurrence it next happens on. A
     * series whose end date has passed is not upcoming at all - the old list carried every one.
     */
    public function test_a_series_is_linked_undated_and_an_ended_one_is_left_out(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Blue Room']);

        $weekly = $this->createRecurringEvent($role, [
            'name' => 'Sunday Yin Yoga',
            'days_of_week' => '1000000',
            'starts_at' => '2026-06-07 16:00:00',
        ]);
        $this->createRecurringEvent($role, [
            'name' => 'Old Tuesday Jam',
            'days_of_week' => '0010000',
            'starts_at' => '2026-03-03 19:00:00',
            'recurring_end_type' => 'on_date',
            'recurring_end_value' => '2026-08-25',
        ]);

        $noscript = $this->noscript($this->schedulePage($role));

        $this->assertStringContainsString('href="'.$weekly->getUndatedGuestUrl($role->subdomain).'"', $noscript);
        $this->assertStringNotContainsString('href="'.$weekly->getGuestUrl($role->subdomain).'"', $noscript,
            'Never the series at its first date');
        // The next Sunday, not the first one back in June.
        $this->assertStringContainsString($weekly->localStartsAt(true, '2026-09-27'), $noscript);
        $this->assertStringNotContainsString('Old Tuesday Jam', $noscript);
    }

    public function test_the_list_names_the_venue_in_the_page_language(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['name' => 'Night Guide']);
        $venue = $this->createRole($owner, 'venue', ['name' => 'Blue Room', 'city' => 'Springfield']);
        $event = $this->createEvent($curator, ['name' => 'Autumn Session', 'creator_role_id' => $curator->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $noscript = $this->noscript($this->schedulePage($curator));

        $this->assertStringContainsString('Autumn Session', $noscript);
        $this->assertStringContainsString('Blue Room | Springfield', $noscript);
    }

    public function test_a_sub_schedule_lists_only_its_own_events(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Blue Room']);
        $jazz = $this->createGroup($role, ['name' => 'Jazz', 'slug' => 'jazz']);

        // group_id lives on the event_role pivot, not on events.
        $this->createEvent($role, ['name' => 'Late Jazz Set'])->roles()->updateExistingPivot($role->id, ['group_id' => $jazz->id]);
        $this->createEvent($role, ['name' => 'Comedy Hour']);

        $noscript = $this->noscript($this->schedulePage($role, '/jazz'));

        $this->assertStringContainsString('Late Jazz Set', $noscript);
        $this->assertStringNotContainsString('Comedy Hour', $noscript);

        // The home lists both.
        $home = $this->noscript($this->schedulePage($role));
        $this->assertStringContainsString('Comedy Hour', $home);
    }

    /**
     * The cost of the page must not follow the size of the schedule. Measured as queries: the
     * old list lazy-loaded each row's creating schedule, one query per event in the month and per
     * series, on top of eight eager loads.
     */
    public function test_a_busy_schedule_page_costs_a_bounded_number_of_queries(): void
    {
        $owner = $this->createOwner();
        $quiet = $this->createRole($owner, 'venue', ['name' => 'Quiet Room']);
        $busy = $this->createRole($owner, 'venue', ['name' => 'Busy Room']);

        $this->createEvent($quiet, ['name' => 'Only Session']);

        // A month and a half of nightly events, each created by a schedule of its own the way a
        // curator's feed is, plus the series a busy venue accumulates.
        foreach (range(1, 45) as $day) {
            $creator = $this->createRole($owner, 'talent', ['name' => 'Act '.$day]);
            $event = $this->createEvent($busy, [
                'name' => 'Night '.$day,
                'creator_role_id' => $creator->id,
                'starts_at' => now()->addDays($day - 20)->setTime(20, 0)->format('Y-m-d H:i:s'),
            ]);
            EventPoll::create(['event_id' => $event->id, 'question' => 'Encore?', 'options' => ['Yes', 'No'], 'is_active' => true]);
        }
        foreach (range(1, 12) as $i) {
            $this->createRecurringEvent($busy, ['name' => 'Weekly '.$i, 'days_of_week' => '0000100', 'starts_at' => '2026-01-08 19:00:00']);
        }

        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        $queries = 0;
        $this->get('/'.$quiet->subdomain)->assertOk();
        $quietQueries = $queries;

        $queries = 0;
        $this->get('/'.$busy->subdomain)->assertOk();
        $busyQueries = $queries;

        $this->assertLessThanOrEqual($quietQueries + 5, $busyQueries,
            "A schedule with 57 events ran {$busyQueries} queries against {$quietQueries} for one: something is loading per event");
        $this->assertLessThan(40, $busyQueries);
    }

    /**
     * ?graphic=1 renders the month itself, so it keeps the whole month: more events than the
     * upcoming list's 50, from the query it always used.
     */
    public function test_graphic_mode_still_gets_the_whole_month(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Blue Room']);

        foreach (range(1, 52) as $i) {
            $this->createEvent($role, ['name' => sprintf('Crowded Session %02d', $i), 'starts_at' => '2026-09-28 18:00:00']);
        }
        $this->createEvent($role, ['name' => 'Early September Session', 'starts_at' => '2026-09-10 18:00:00']);

        $graphic = $this->schedulePage($role, '?graphic=1');

        foreach (range(1, 52) as $i) {
            $this->assertStringContainsString(sprintf('"name":"Crowded Session %02d"', $i), $graphic);
        }
        // With the same card image fields the Ajax payload carries.
        $this->assertStringContainsString('"image_thumb_url"', $graphic);

        // The plain page lists only what is still to come, 50 of it.
        $noscript = $this->noscript($this->schedulePage($role));
        $this->assertSame(50, substr_count($noscript, '<li'));
        $this->assertStringNotContainsString('Early September Session', $noscript);
    }

    /**
     * The poll celebration script loads only where the page's own events carry a poll, and that
     * check now reads the upcoming list's withCount('polls').
     */
    public function test_the_confetti_script_still_loads_when_an_event_has_a_poll(): void
    {
        $owner = $this->createOwner();
        $withPoll = $this->createRole($owner, 'venue', ['name' => 'Poll Room']);
        $withoutPoll = $this->createRole($owner, 'venue', ['name' => 'Quiet Room']);

        $event = $this->createEvent($withPoll, ['name' => 'Vote Night']);
        EventPoll::create(['event_id' => $event->id, 'question' => 'Encore?', 'options' => ['Yes', 'No'], 'is_active' => true]);
        $this->createEvent($withoutPoll, ['name' => 'Plain Night']);

        $this->assertTrue($withPoll->fresh()->isPro(), 'fixture: polls are a Pro feature');
        $this->assertStringContainsString('js/poll-confetti.js', $this->schedulePage($withPoll));
        $this->assertStringNotContainsString('js/poll-confetti.js', $this->schedulePage($withoutPoll));
    }
}
