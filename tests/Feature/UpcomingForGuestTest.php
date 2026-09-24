<?php

namespace Tests\Feature;

use App\Repos\EventRepo;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * EventRepo::upcomingForGuest() feeds everything a crawler reads about a schedule's coming events:
 * the noscript list, the "Upcoming: ..." meta description, the "- Upcoming Events" title and the
 * JSON-LD event list. So what it may and may not return is public.
 */
class UpcomingForGuestTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Role's saving hook geocodes a new address whenever a Google key is configured.
        config(['services.google.backend' => null]);
    }

    /**
     * A password gate hides an event's details on the calendar, and the JSON-LD this list feeds
     * prints the venue's address and the flyer. is_private normally travels with a password, but a
     * row saved before that rule has the password alone - so the password itself must exclude it.
     */
    public function test_a_password_protected_event_is_never_listed(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $open = $this->createEvent($role, ['name' => 'Open Night', 'creator_role_id' => $role->id]);
        $gated = $this->createEvent($role, ['name' => 'Members Night', 'creator_role_id' => $role->id]);

        DB::table('events')->where('id', $gated->id)->update(['event_password' => 'secret', 'is_private' => false]);

        $names = app(EventRepo::class)->upcomingForGuest($role)->map(fn ($row) => $row['event']->name)->all();

        $this->assertContains($open->name, $names);
        $this->assertNotContains('Members Night', $names);
    }

    /**
     * Only UPCOMING_SERIES_LIMIT series are asked for their next occurrence. When the budget was
     * filled oldest-first with anything not ended by an on_date, long-finished after_events series
     * used it all up and a newer series that is running never made the list.
     */
    public function test_finished_series_do_not_crowd_out_a_running_one(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $longAgo = Carbon::now('UTC')->subYears(3)->setTime(12, 0);

        for ($i = 0; $i < EventRepo::UPCOMING_SERIES_LIMIT + 5; $i++) {
            $this->createEvent($role, [
                'name' => 'Finished Series '.$i,
                'creator_role_id' => $role->id,
                'starts_at' => $longAgo->copy()->addDays($i)->format('Y-m-d H:i:s'),
                'days_of_week' => '1111111',
                'recurring_frequency' => 'weekly',
                'recurring_end_type' => 'after_events',
                'recurring_end_value' => '3',
            ]);
        }

        $this->createEvent($role, [
            'name' => 'Weekly Jam',
            'creator_role_id' => $role->id,
            'starts_at' => Carbon::now('UTC')->subMonth()->setTime(12, 0)->format('Y-m-d H:i:s'),
            'days_of_week' => '1111111',
            'recurring_frequency' => 'weekly',
        ]);

        $names = app(EventRepo::class)->upcomingForGuest($role)->map(fn ($row) => $row['event']->name)->all();

        $this->assertContains('Weekly Jam', $names);
        $this->assertSame(['Weekly Jam'], array_values(array_unique($names)));
    }

    /**
     * A restored backup can carry recurrence data saveEvent() never writes, and matchesDate()
     * throws on it: an every_n_weeks interval of 0 is a modulo by zero, and an on_date end that is
     * not a date fails to parse. Each such series is reported and left out, and the schedule page
     * still renders everything else.
     */
    public function test_a_series_whose_recurrence_cannot_be_read_is_left_out(): void
    {
        Exceptions::fake();

        $role = $this->createRole($this->createOwner(), 'venue');
        $series = fn (string $name, array $attrs = []) => $this->createEvent($role, $attrs + [
            'name' => $name,
            'creator_role_id' => $role->id,
            'starts_at' => Carbon::now('UTC')->subWeek()->setTime(12, 0)->format('Y-m-d H:i:s'),
            'days_of_week' => '1111111',
            'recurring_frequency' => 'weekly',
        ]);

        $series('Weekly Jam');
        $series('Zero Interval', ['recurring_frequency' => 'every_n_weeks', 'recurring_interval' => 0]);
        $series('Garbled End', ['recurring_end_type' => 'on_date', 'recurring_end_value' => 'soon']);

        $names = app(EventRepo::class)->upcomingForGuest($role)->map(fn ($row) => $row['event']->name)->all();

        $this->assertSame(['Weekly Jam'], array_values(array_unique($names)));
        Exceptions::assertReported(\DivisionByZeroError::class);
        Exceptions::assertReported(InvalidFormatException::class);

        $this->get('/'.$role->subdomain)->assertOk();
    }

    /**
     * The series query runs the sitemap window, whose SQL once overflowed on a huge count and took
     * every visit to the schedule page down with it. A failure there now costs only the series:
     * it is reported, and the one-off events are still listed.
     */
    public function test_a_failing_series_query_costs_only_the_series(): void
    {
        Exceptions::fake();

        $role = $this->createRole($this->createOwner(), 'venue');
        $this->createEvent($role, ['name' => 'One Night Only', 'creator_role_id' => $role->id]);
        $this->createEvent($role, [
            'name' => 'Weekly Jam',
            'creator_role_id' => $role->id,
            'starts_at' => Carbon::now('UTC')->subWeek()->setTime(12, 0)->format('Y-m-d H:i:s'),
            'days_of_week' => '1111111',
            'recurring_frequency' => 'weekly',
        ]);

        // The series query is the only one here that reads recurring_end_type.
        DB::connection()->beforeExecuting(function (string $query, array $bindings) {
            if (str_contains($query, 'recurring_end_type')) {
                throw new QueryException('mysql', $query, $bindings,
                    new \PDOException('SQLSTATE[22003]: Numeric value out of range: 1690 BIGINT UNSIGNED value is out of range'));
            }
        });

        $names = app(EventRepo::class)->upcomingForGuest($role)->map(fn ($row) => $row['event']->name)->all();

        $this->assertSame(['One Night Only'], $names);
        Exceptions::assertReported(QueryException::class);
    }
}
