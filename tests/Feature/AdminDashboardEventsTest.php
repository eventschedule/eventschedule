<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminDashboard;
use App\Services\DemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The upcoming-events card of /admin/dashboard.
 *
 * The tile it replaces counted `starts_at >= now()` with an event_url and no venue. A series'
 * starts_at is its anchor, in the past by design, so every weekly show was missing from it; and
 * the same number included drafts, cancelled events and appointment bookings. Each is a row of the
 * fixture below.
 *
 * The split is counted in SQL and has to agree with Event::getSchemaAttendanceMode(), the rule
 * every event page and the recent-events list read. The first test holds the two together.
 */
class AdminDashboardEventsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $venue;

    private Role $talent;

    protected function setUp(): void
    {
        parent::setUp();

        // "Today" runs to the end of the app's day. At noon, two hours from now is still today.
        $this->travelTo(now()->setTime(12, 0));

        $owner = $this->createOwner();
        $this->venue = $this->createRole($owner, 'venue', ['name' => 'The Room', 'country_code' => 'us']);
        $this->talent = $this->createRole($owner, 'talent', ['name' => 'The Band']);
    }

    /** A UTC start time this many hours from now, as events.starts_at stores it. */
    private function hoursFromNow(int $hours): string
    {
        return now()->utc()->addHours($hours)->format('Y-m-d H:i:s');
    }

    /**
     * Five events with something still to come, one of each kind.
     *
     * @return array<int, Event>
     */
    private function seedUpcoming(): array
    {
        return [
            $this->createEvent($this->venue, ['name' => 'In person', 'starts_at' => $this->hoursFromNow(2)]),
            $this->createEvent($this->talent, ['name' => 'Online', 'starts_at' => $this->hoursFromNow(72), 'event_url' => 'https://meet.example.org/a']),
            $this->createEvent($this->venue, ['name' => 'Hybrid', 'starts_at' => $this->hoursFromNow(24 * 20), 'event_url' => 'https://meet.example.org/b']),
            $this->createEvent($this->talent, ['name' => 'Nowhere yet', 'starts_at' => $this->hoursFromNow(24 * 45)]),
            // A weekly series anchored a year ago: still running, and counted once.
            $this->createRecurringEvent($this->venue, ['name' => 'Weekly', 'starts_at' => $this->hoursFromNow(-24 * 365)]),
        ];
    }

    /** Everything the old tile counted and should not have, plus what is simply over. */
    private function seedNotUpcoming(): void
    {
        $this->createEvent($this->venue, ['name' => 'Draft', 'starts_at' => $this->hoursFromNow(48), 'is_draft' => true]);
        $this->createEvent($this->venue, ['name' => 'Cancelled', 'starts_at' => $this->hoursFromNow(48), 'is_cancelled' => true]);
        $this->createEvent($this->venue, ['name' => 'Past', 'starts_at' => $this->hoursFromNow(-72)]);
        $this->createRecurringEvent($this->venue, [
            'name' => 'Ended series',
            'starts_at' => $this->hoursFromNow(-24 * 365),
            'recurring_end_type' => 'on_date',
            'recurring_end_value' => now()->subMonth()->format('Y-m-d'),
        ]);
    }

    /**
     * Mutation: read `starts_at >= now()` instead of hasUpcomingOccurrence(), drop any one of the
     * draft, cancelled or appointment lines, or test the venue some other way than the model does.
     */
    public function test_upcoming_events_are_counted_once_each_by_how_people_attend(): void
    {
        $upcoming = $this->seedUpcoming();
        $this->seedNotUpcoming();

        // A booking is named after a guest and belongs to no list of events.
        $this->createEvent($this->venue, [
            'name' => 'Booking',
            'starts_at' => $this->hoursFromNow(48),
            'appointment_type_id' => $this->createAppointmentType($this->venue)->id,
        ]);
        // Demo content is keyed on the demo's contact address.
        $demo = $this->createRole($this->createOwner(), 'venue', ['email' => DemoService::DEMO_EMAIL]);
        $this->createEvent($demo, ['name' => 'Demo', 'starts_at' => $this->hoursFromNow(48)]);

        $events = (new AdminDashboard)->events();

        $this->assertSame(5, $events['total']);
        $this->assertSame(1, $events['recurring']);
        $this->assertSame(4, $events['one_off']);

        // The same split, worked out one event at a time by the model.
        $byModel = ['in_person' => 0, 'online' => 0, 'hybrid' => 0, 'no_location' => 0];
        foreach ($upcoming as $event) {
            $byModel[match ($event->fresh()->getSchemaAttendanceMode()) {
                'https://schema.org/MixedEventAttendanceMode' => 'hybrid',
                'https://schema.org/OnlineEventAttendanceMode' => 'online',
                'https://schema.org/OfflineEventAttendanceMode' => 'in_person',
                default => 'no_location',
            }]++;
        }

        $this->assertSame(['in_person' => 2, 'online' => 1, 'hybrid' => 1, 'no_location' => 1], $byModel);
        foreach ($byModel as $mode => $count) {
            $this->assertSame($count, $events[$mode], $mode);
        }

        // In person and hybrid, and the series: three events at the venue's address.
        $this->assertCount(1, $events['countries']);
        $this->assertSame('us', $events['countries'][0]['code']);
        $this->assertSame(3, $events['countries'][0]['count']);

        // Every event that is not a booking or demo content, upcoming or not.
        $this->assertSame(9, $events['all_time']);
    }

    /**
     * "Next 24 hours", "next 7 days" and "next 30 days" are running totals of dated one-off
     * events, all three counted from now. SQL can tell that a series is still running, not when
     * it next happens, so a series is in none of them and the page says so. Mutation: drop
     * `is_series = 0` from the three sums, or end the first at the app's midnight.
     */
    public function test_the_horizons_count_dated_one_off_events_only(): void
    {
        $this->seedUpcoming();
        // Tomorrow morning on the app's clock, twenty hours from this noon: not "today" by any
        // one calendar, and inside the next 24 hours by every one.
        $this->createEvent($this->talent, ['name' => 'Tomorrow morning', 'starts_at' => $this->hoursFromNow(20)]);

        $events = (new AdminDashboard)->events();

        $this->assertSame(2, $events['next_24h']);  // in two hours, and in twenty
        $this->assertSame(3, $events['next_7']);    // and in three days
        $this->assertSame(4, $events['next_30']);   // and in twenty; the one in 45 days is beyond
    }

    /**
     * Deleting a schedule keeps its events (ScheduleDeletionService), and they are on no page
     * anyone can open. An event that is also on a schedule still standing is still an event.
     * Mutation: drop onLiveSchedule() from upcoming() or from the all-time count.
     */
    public function test_events_left_on_a_deleted_schedule_are_not_counted(): void
    {
        $gone = $this->createRole($this->createOwner(), 'venue', ['name' => 'Gone', 'country_code' => 'de', 'is_deleted' => true]);

        $this->createEvent($gone, ['name' => 'Left behind', 'starts_at' => $this->hoursFromNow(48)]);
        $shared = $this->createEvent($gone, ['name' => 'Also at The Band', 'starts_at' => $this->hoursFromNow(48)]);
        $shared->roles()->attach($this->talent->id, ['is_accepted' => true]);

        $events = (new AdminDashboard)->events();

        $this->assertSame(1, $events['total']);
        $this->assertSame(1, $events['all_time']);
        $this->assertSame(1, $events['new_30d']);
    }

    /** Mutation: compare with a whole day thirty days ago, or report a change with no base. */
    public function test_events_added_are_compared_with_the_thirty_days_before(): void
    {
        $this->assertNull((new AdminDashboard)->events()['new_change']);

        foreach ([1, 2, 3] as $days) {
            $this->createEvent($this->venue, ['created_at' => now()->subDays($days)]);
        }
        foreach ([35, 40] as $days) {
            $this->createEvent($this->venue, ['created_at' => now()->subDays($days)]);
        }
        // An hour after noon, thirty days ago: today has not reached that hour, so it is in
        // neither window. A whole-day comparison would count it and report 0% for +50%.
        $this->createEvent($this->venue, ['created_at' => now()->subDays(30)->addHour()]);

        $events = (new AdminDashboard)->events();

        $this->assertSame(6, $events['all_time']);
        $this->assertSame(3, $events['new_30d']);
        $this->assertSame(50.0, $events['new_change']);
    }

    /**
     * Demo content is Role::constrainDemoContent(), which has three arms: the demo's contact
     * address on the schedule, the demo schedule's own subdomain, and anything the demo account
     * owns. The page reads which schedules those are once and leaves their events out of every
     * count and both lists. Mutation: build the list of demo schedules from one arm alone.
     */
    public function test_demo_content_is_left_out_whichever_way_it_is_demo(): void
    {
        $demoAccount = User::factory()->create(['email' => DemoService::DEMO_EMAIL, 'email_verified_at' => now()]);
        $demo = [
            $this->createRole($this->createOwner(), 'venue', ['name' => 'By address', 'email' => DemoService::DEMO_EMAIL]),
            $this->createRole($this->createOwner(), 'venue', ['name' => 'By subdomain', 'subdomain' => DemoService::DEMO_ROLE_SUBDOMAIN]),
            $this->createRole($demoAccount, 'venue', ['name' => 'By owner']),
        ];
        foreach ($demo as $role) {
            $this->createEvent($role, ['name' => 'At '.$role->name, 'starts_at' => $this->hoursFromNow(48)]);
        }
        $this->createEvent($this->venue, ['name' => 'Real', 'starts_at' => $this->hoursFromNow(48)]);

        $dashboard = new AdminDashboard;
        $events = $dashboard->events();

        $this->assertSame(1, $events['total']);
        $this->assertSame(1, $events['all_time']);
        $this->assertSame(1, $events['new_30d']);
        $this->assertSame(['Real'], array_column($dashboard->recentEvents()['rows'], 'name'));

        $schedules = array_column($dashboard->recentSchedules()['rows'], 'name');
        sort($schedules);
        $this->assertSame(['The Band', 'The Room'], $schedules);
        $this->assertSame(2, $dashboard->recentSchedules()['total']);
    }
}
