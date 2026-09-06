<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The guest event page's sidebar agenda is a partial view of the schedule twice over: the Vue app
 * slices it to max_events (20), and its payload is fetched for the VIEWED EVENT's month, so an
 * event a month or more out hides everything upcoming before it.
 *
 * The cap is only knowable in the browser, so these pin the gate Blade emits, not the count:
 * `v-if="true"` means the server already proved the window skipped events, `v-if="hasMoreEventsThanShown"`
 * means it is down to the client to compare its own lists. The third test is the one that matters
 * for privacy - a draft, cancelled or unlisted event must not be what makes the link appear.
 */
class GuestEventSidebarLinkTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mid-month and mid-week, so "three months out" cannot land on a boundary that moves the
        // calendar grid's start week across the earlier event.
        Carbon::setTestNow(Carbon::parse('2026-03-11 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function futureEventDate(): string
    {
        return Carbon::now()->addMonths(3)->startOfMonth()->addDays(15)->setTime(12, 0)->format('Y-m-d H:i:s');
    }

    public function test_an_event_beyond_the_widgets_window_links_out_unconditionally(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $event = $this->createEvent($role, ['name' => 'Midsummer Set', 'starts_at' => $this->futureEventDate()]);
        $this->createEvent($role, ['name' => 'Next Week Set', 'starts_at' => Carbon::now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s')]);

        $response = $this->get($this->guestEventUrl($role, $event))->assertOk();

        $response->assertSee('v-if="true" id="viewFullScheduleFooter"', false);
        $response->assertSee('View Full Schedule');

        // Scoped to the footer on purpose: the breadcrumb at the top of the page renders the same
        // $backUrl, so asserting the href against the whole document would pass even if the footer
        // link's own href were empty.
        $this->assertStringContainsString(
            'href="'.e(route('role.view_guest', ['subdomain' => $role->subdomain])).'"',
            $this->footerMarkup($response->getContent()),
        );
    }

    /**
     * The footer link's own markup, so an assertion cannot be satisfied by the breadcrumb.
     */
    private function footerMarkup(string $html): string
    {
        $start = strpos($html, 'id="viewFullScheduleFooter"');
        $this->assertNotFalse($start, 'the sidebar footer link was not rendered at all');

        return substr($html, $start, 900);
    }

    public function test_a_still_running_multi_day_event_is_not_counted_as_hidden(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $event = $this->createEvent($role, ['name' => 'Midsummer Set', 'starts_at' => $this->futureEventDate()]);

        // Starts inside the gap the window opens, but runs long enough to still be on when the
        // window opens - so Event::scopeInMonth's third clause hands it to calendarEvents() and the
        // agenda already lists it. Counting it would promise events the widget is showing.
        $this->createEvent($role, [
            'name' => 'Spring Exhibition',
            'starts_at' => Carbon::now()->addMonths(1)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'duration' => 2000,
        ]);

        $this->get($this->guestEventUrl($role, $event))
            ->assertOk()
            ->assertSee('v-if="hasMoreEventsThanShown" id="viewFullScheduleFooter"', false);
    }

    public function test_a_multi_day_event_that_ends_before_the_window_is_counted(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $event = $this->createEvent($role, ['name' => 'Midsummer Set', 'starts_at' => $this->futureEventDate()]);

        // Began before today and ends well before the window opens: matched by none of
        // scopeInMonth's clauses, so it is genuinely absent from the widget even though a visitor
        // could still walk in today.
        $this->createEvent($role, [
            'name' => 'Ten Day Festival',
            'starts_at' => Carbon::now()->subDays(3)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'duration' => 240,
        ]);

        $this->get($this->guestEventUrl($role, $event))
            ->assertOk()
            ->assertSee('v-if="true" id="viewFullScheduleFooter"', false);
    }

    public function test_an_earlier_event_with_no_duration_is_still_counted(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $event = $this->createEvent($role, ['name' => 'Midsummer Set', 'starts_at' => $this->futureEventDate()]);

        // events.duration is nullable and real rows have NULL in it. This pins the NULL-safety of
        // the multi-day exclusion rather than the window itself: written as a bare `duration < 24`
        // the complement evaluates to NULL for this row and drops it silently.
        $earlier = $this->createEvent($role, [
            'name' => 'Durationless Set',
            'starts_at' => Carbon::now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);
        DB::table('events')->where('id', $earlier->id)->update(['duration' => null]);

        $this->get($this->guestEventUrl($role, $event))
            ->assertOk()
            ->assertSee('v-if="true" id="viewFullScheduleFooter"', false);
    }

    public function test_an_active_filter_leaves_the_decision_to_the_client(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $event = $this->createEvent($role, ['name' => 'Midsummer Set', 'starts_at' => $this->futureEventDate()]);
        $this->createEvent($role, ['name' => 'Next Week Set', 'starts_at' => Carbon::now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s')]);

        // The server cannot honour ?category=, so it must not claim there is more to see: the
        // client half compares isEventVisible()-filtered counts and decides alone.
        $this->get($this->guestEventUrl($role, $event).'?category=99')
            ->assertOk()
            ->assertSee('v-if="hasMoreEventsThanShown" id="viewFullScheduleFooter"', false);
    }

    public function test_an_event_in_the_current_window_leaves_the_decision_to_the_client(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $event = $this->createEvent($role, ['name' => 'This Month Set']);

        $this->get($this->guestEventUrl($role, $event))
            ->assertOk()
            ->assertSee('v-if="hasMoreEventsThanShown" id="viewFullScheduleFooter"', false);
    }

    public function test_hidden_earlier_events_do_not_make_the_link_appear(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $otherRole = $this->createRole($this->createOwner(), 'venue');

        $event = $this->createEvent($role, ['name' => 'Midsummer Set', 'starts_at' => $this->futureEventDate()]);

        $soon = Carbon::now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s');
        $this->createEvent($role, ['name' => 'Draft Set', 'starts_at' => $soon, 'is_draft' => true]);
        $this->createEvent($role, ['name' => 'Cancelled Set', 'starts_at' => $soon, 'is_cancelled' => true]);
        $this->createEvent($role, ['name' => 'Unlisted Set', 'starts_at' => $soon, 'is_private' => true]);
        $this->createEvent($role, ['name' => 'Pending Set', 'starts_at' => $soon, 'is_accepted' => false]);
        $this->createEvent($otherRole, ['name' => 'Someone Elses Set', 'starts_at' => $soon]);

        $this->get($this->guestEventUrl($role, $event))
            ->assertOk()
            ->assertSee('v-if="hasMoreEventsThanShown" id="viewFullScheduleFooter"', false);
    }

    public function test_the_schedule_page_never_links_to_itself(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->createEvent($role);

        $this->get('/'.$role->subdomain)
            ->assertOk()
            ->assertDontSee('viewFullScheduleFooter', false);
    }

    public function test_the_label_honours_an_owners_override(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'custom_labels' => ['view_full_schedule' => ['value' => 'All our gigs']],
        ]);
        $event = $this->createEvent($role, ['name' => 'This Month Set']);

        $this->get($this->guestEventUrl($role, $event))
            ->assertOk()
            ->assertSee('All our gigs')
            ->assertDontSee('View Full Schedule');
    }
}
