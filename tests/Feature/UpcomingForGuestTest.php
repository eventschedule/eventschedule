<?php

namespace Tests\Feature;

use App\Repos\EventRepo;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
}
