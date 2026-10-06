<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Services\AdminDashboard;
use App\Services\DemoService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The two lists at the top of /admin/dashboard: the newest schedules and the newest events.
 *
 * They are what the page is opened for, so three things are held here: who is in them (never demo
 * content, a deleted schedule, an unlisted event or a guest's booking), that an import of a dozen
 * events is one row and not the whole list, and that neither list runs a query per row - the page
 * they replace read every schedule's owner twice.
 */
class AdminDashboardRecentTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setTime(12, 0));
        config(['app.hosted' => false]);
    }

    private function queriesRunBy(callable $run): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $run();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    /** Mutation: drop claimed(), the is_deleted line or the demo line from recentSchedules(). */
    public function test_recent_schedules_are_the_real_ones_newest_first(): void
    {
        $owner = $this->createOwner();

        $kept = $this->createRole($owner, 'venue', ['name' => 'Kept', 'city' => 'Linz', 'country_code' => 'at']);
        $this->createEvent($kept);
        $this->createEvent($kept);
        $this->createRole($owner, 'talent', ['name' => 'Older', 'created_at' => now()->subDays(40)]);

        $this->createRole($owner, 'venue', ['name' => 'Deleted', 'is_deleted' => true]);
        $this->createRole($owner, 'venue', ['name' => 'Never confirmed', 'email_verified_at' => null]);
        $this->createRole($owner, 'venue', ['name' => 'Nobody owns it', 'user_id' => null]);
        $this->createRole($owner, 'venue', ['name' => 'Demo', 'email' => DemoService::DEMO_EMAIL]);

        $schedules = (new AdminDashboard)->recentSchedules();

        $this->assertSame(['Kept', 'Older'], array_column($schedules['rows'], 'name'));
        $this->assertSame(2, $schedules['total']);
        $this->assertSame(1, $schedules['new_30d']);

        $row = $schedules['rows'][0];
        $this->assertSame('venue', $row['type']);
        $this->assertSame('Linz, AT', $row['place']);
        $this->assertSame($owner->name, $row['owner']);
        $this->assertSame(2, $row['events']);
        $this->assertSame(0, $schedules['rows'][1]['events']);
    }

    /**
     * A plan is only a fact where there is billing. On a selfhost every schedule reads
     * "enterprise" (Role::actualPlanTier()), and a chip saying so on every row says nothing.
     * Mutation: read the tier whatever the install is.
     */
    public function test_a_plan_is_shown_only_where_there_are_plans(): void
    {
        $this->createRole($this->createOwner(), 'venue', ['name' => 'Paid']);

        $this->assertNull((new AdminDashboard)->recentSchedules()['rows'][0]['plan']);

        // createFreeRole() makes the install hosted, which is the other half of this test.
        $this->createFreeRole(null, 'venue', ['name' => 'Free']);
        $plans = array_column((new AdminDashboard)->recentSchedules()['rows'], 'plan', 'name');

        $this->assertSame('enterprise', $plans['Paid']);
        $this->assertNull($plans['Free']);
    }

    /**
     * Mutation: drop the unlisted line or the appointment line from recentEvents(), or flag
     * nothing.
     */
    public function test_recent_events_flag_what_is_not_yet_an_ordinary_public_event(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'The Room']);
        $at = fn (int $minutes) => now()->subMinutes($minutes);

        $public = $this->createEvent($venue, ['name' => 'Public', 'created_at' => $at(1)]);
        $draft = $this->createEvent($venue, ['name' => 'Draft', 'created_at' => $at(2), 'is_draft' => true]);
        $this->createEvent($venue, ['name' => 'Sent in by a guest', 'created_at' => $at(3), 'is_guest_submission' => true]);
        $this->createEvent($venue, ['name' => 'From a feed', 'created_at' => $at(4), 'import_source' => Event::IMPORT_ICS]);
        $this->createRecurringEvent($venue, ['name' => 'Weekly', 'created_at' => $at(5), 'event_url' => 'https://meet.example.org/a']);

        // Never listed: unlisted, a guest's booking, demo content.
        $this->createEvent($venue, ['name' => 'Unlisted', 'created_at' => $at(1), 'is_private' => true]);
        $booking = $this->createEvent($venue, [
            'name' => 'Booking',
            'created_at' => $at(1),
            'appointment_type_id' => $this->createAppointmentType($venue)->id,
        ]);
        // Saving a booking makes it unlisted, so on its own this row would be removed by the
        // unlisted line and the appointment line would never be asked. Bookings made before that
        // rule are still listed in production; this is one of those.
        DB::table('events')->where('id', $booking->id)->update(['is_private' => false]);
        $demo = $this->createRole($this->createOwner(), 'venue', ['email' => DemoService::DEMO_EMAIL]);
        $this->createEvent($demo, ['name' => 'Demo', 'created_at' => $at(1)]);

        $rows = (new AdminDashboard)->recentEvents()['rows'];

        $this->assertSame(['Public', 'Draft', 'Sent in by a guest', 'From a feed', 'Weekly'], array_column($rows, 'name'));
        $this->assertSame([null, 'draft', 'submitted', 'imported', null], array_column($rows, 'flag'));
        $this->assertSame(['in_person', 'in_person', 'in_person', 'in_person', 'hybrid'], array_column($rows, 'mode'));
        $this->assertSame('The Room', $rows[0]['schedule']);

        // A dated event says when; a series has no one date to give.
        $this->assertNotNull($rows[0]['when']);
        $this->assertFalse($rows[0]['series']);
        $this->assertNull($rows[4]['when']);
        $this->assertTrue($rows[4]['series']);

        // A public event opens its public page. A draft has none, so it opens the admin's own
        // edit page.
        $this->assertStringContainsString($venue->subdomain, $rows[0]['url']);
        $this->assertStringContainsString($public->slug, $rows[0]['url']);
        $this->assertSame(route('event.edit_admin', ['hash' => UrlUtils::encodeId($draft->id)]), $rows[1]['url']);
    }

    /**
     * The link goes where the event is really shown. A submission nobody has accepted yet is on
     * no public page, so its link is the admin's edit page; an event listed on a deleted schedule
     * and a live one opens under the live one. Mutation: build the link from the first claimed
     * schedule (getViewableRole()), which is a 404 for the first and for the second.
     */
    public function test_a_recent_event_links_to_a_page_that_opens(): void
    {
        $live = $this->createRole($this->createOwner(), 'venue', ['name' => 'Still Here']);
        $gone = $this->createRole($this->createOwner(), 'talent', ['name' => 'Gone', 'is_deleted' => true]);

        $waiting = $this->createEvent($live, ['name' => 'Waiting', 'created_at' => now()->subMinutes(1), 'is_guest_submission' => true]);
        DB::table('event_role')->where('event_id', $waiting->id)->update(['is_accepted' => false]);

        // The deleted schedule is attached first and is a performer, which is the one the old
        // pick took.
        $moved = $this->createEvent($gone, ['name' => 'Moved', 'created_at' => now()->subMinutes(2)]);
        $moved->roles()->attach($live->id, ['is_accepted' => true]);

        // On the deleted schedule and nowhere else: not an event anyone can be shown.
        $this->createEvent($gone, ['name' => 'Left behind', 'created_at' => now()->subMinutes(3)]);

        $rows = (new AdminDashboard)->recentEvents()['rows'];

        $this->assertSame(['Waiting', 'Moved'], array_column($rows, 'name'));
        $this->assertSame(route('event.edit_admin', ['hash' => UrlUtils::encodeId($waiting->id)]), $rows[0]['url']);
        // Served under the live schedule. (The other schedule's name is the second segment of an
        // event address by design; the first is the one that has to answer.)
        $this->assertStringStartsWith(url('/'.$live->subdomain).'/', $rows[1]['url']);
        $this->assertSame('Still Here', $rows[1]['schedule']);
    }

    /**
     * An import larger than one read is still one row, counted whole, with the rest of the list
     * under it. Mutation: read one page only (EVENT_PAGES = 1): the list is that single row and
     * its count stops at 59.
     */
    public function test_an_import_larger_than_one_read_is_still_one_row(): void
    {
        $importer = $this->createRole($this->createOwner(), 'venue', ['name' => 'Importer']);
        $other = $this->createRole($this->createOwner(), 'venue', ['name' => 'Other']);

        foreach (range(0, 69) as $index) {
            $this->createEvent($importer, [
                'name' => 'Imported '.$index,
                'creator_role_id' => $importer->id,
                'created_at' => now()->subMinutes(5)->subSeconds($index),
            ]);
        }
        foreach ([1, 2, 3] as $hours) {
            $this->createEvent($other, ['name' => 'Earlier '.$hours, 'created_at' => now()->subHours($hours)]);
        }

        $rows = (new AdminDashboard)->recentEvents()['rows'];

        $this->assertSame(['Imported 0', 'Earlier 1', 'Earlier 2', 'Earlier 3'], array_column($rows, 'name'));
        $this->assertSame([69, 0, 0, 0], array_column($rows, 'more'));
    }

    /**
     * Twelve events from one schedule inside ten minutes are an import, and one row. Mutation: set
     * BURST_SECONDS to 0, or collapse on the schedule alone and lose the event from this morning.
     */
    public function test_a_burst_from_one_schedule_is_one_row(): void
    {
        $importer = $this->createRole($this->createOwner(), 'venue', ['name' => 'Importer']);
        $other = $this->createRole($this->createOwner(), 'venue', ['name' => 'Other']);

        $this->createEvent($other, ['name' => 'Just now', 'creator_role_id' => $other->id, 'created_at' => now()->subMinute()]);
        foreach (range(0, 11) as $index) {
            $this->createEvent($importer, [
                'name' => 'Imported '.$index,
                'creator_role_id' => $importer->id,
                'created_at' => now()->subMinutes(10)->subSeconds($index * 20),
            ]);
        }
        // The same schedule, three hours before: its own row.
        $this->createEvent($importer, ['name' => 'This morning', 'creator_role_id' => $importer->id, 'created_at' => now()->subHours(3)]);

        $rows = (new AdminDashboard)->recentEvents()['rows'];

        $this->assertSame(['Just now', 'Imported 0', 'This morning'], array_column($rows, 'name'));
        $this->assertSame([0, 11, 0], array_column($rows, 'more'));
    }

    /** Events with no owning schedule on record are never taken for one another's burst. */
    public function test_events_without_an_owning_schedule_are_never_collapsed(): void
    {
        $venue = $this->createRole($this->createOwner());

        foreach (range(1, 3) as $index) {
            $this->createEvent($venue, ['created_at' => now()->subSeconds($index)]);
        }

        $this->assertCount(3, (new AdminDashboard)->recentEvents()['rows']);
    }

    /**
     * The same number of queries for three rows as for nine. Mutation: read $role->owner() or the
     * subscription per row, or drop `roles` or `creatorRole` from the eager load.
     */
    public function test_neither_list_runs_a_query_per_row(): void
    {
        config(['app.hosted' => true]);

        $seed = function (int $count): void {
            foreach (range(1, $count) as $index) {
                $role = $this->createRole($this->createOwner(), $index % 2 ? 'venue' : 'talent');
                $this->createEvent($role, ['creator_role_id' => $role->id, 'event_url' => $index % 3 ? null : 'https://meet.example.org/a']);
            }
        };
        $measure = fn () => [
            $this->queriesRunBy(fn () => (new AdminDashboard)->recentSchedules()),
            $this->queriesRunBy(fn () => (new AdminDashboard)->recentEvents()),
        ];

        $seed(3);
        $few = $measure();
        $seed(6);
        $many = $measure();

        $this->assertCount(9, (new AdminDashboard)->recentSchedules()['rows']);
        $this->assertCount(9, (new AdminDashboard)->recentEvents()['rows']);
        $this->assertSame($few, $many);
    }

    /** The lists load twenty and the page shows eight until "Show more". */
    public function test_a_list_is_capped(): void
    {
        foreach (range(1, AdminDashboard::LIST_ROWS + 3) as $index) {
            $role = $this->createRole($this->createOwner());
            $this->createEvent($role);
        }

        $schedules = (new AdminDashboard)->recentSchedules();

        $this->assertCount(AdminDashboard::LIST_ROWS, $schedules['rows']);
        $this->assertSame(AdminDashboard::LIST_ROWS + 3, $schedules['total']);
        $this->assertCount(AdminDashboard::LIST_ROWS, (new AdminDashboard)->recentEvents()['rows']);
    }
}
