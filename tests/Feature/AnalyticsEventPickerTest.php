<?php

namespace Tests\Feature;

use App\Models\AnalyticsEventsDaily;
use App\Models\Event;
use App\Models\Role;
use App\Services\AnalyticsService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The /analytics event picker (AnalyticsService::getEventsForSchedule) was the one event-listing
 * query in the app that filtered purely on the stored starts_at column, and the one that narrowed
 * a curator to events it CREATED. Both dropped events the schedule genuinely shows:
 *
 *  - a curator serving an event on its own domain recorded the page view against that event
 *    (PageView::recordView) but could not then select it, while the Top events table beside the
 *    picker counted it all along;
 *  - a recurring event stores only its FIRST occurrence in starts_at, so a live weekly residency
 *    disappeared the day it turned 30 days old.
 *
 * The fixture trap: createEvent() defaults starts_at to +7 days, inside the 30-day window, so any
 * recurrence case that does not pass an explicit old starts_at passes against the bug.
 */
class AnalyticsEventPickerTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The window is relative to now() and the recurrence bound is an absolute date string;
        // freezing keeps both from drifting into a different answer on some future run.
        Carbon::setTestNow(Carbon::parse('2026-09-01 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** Venue-created event listed by a curator: the shape in the bug report. */
    private function curatedEvent(array $eventAttrs = [], $pivotAccepted = true): array
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'Ba-Be Bar']);
        $curator = $this->createCurator($owner, ['name' => 'Emek Live']);

        $event = $this->createEvent($venue, array_merge(['creator_role_id' => $venue->id], $eventAttrs));
        $event->roles()->attach($curator->id, ['is_accepted' => $pivotAccepted]);

        return [$owner, $venue, $curator, $event];
    }

    private function pickerIds(Role $role, bool $ownedDataOnly = false): array
    {
        return app(AnalyticsService::class)
            ->getEventsForSchedule($role->id, $ownedDataOnly)
            ->pluck('raw_id')
            ->all();
    }

    public function test_the_curator_picker_lists_an_event_it_shows_but_did_not_create(): void
    {
        [$owner, , $curator, $event] = $this->curatedEvent();

        $this->assertContains($event->id, $this->pickerIds($curator));

        $this->actingAs($owner)
            ->get(route('analytics', ['role_id' => UrlUtils::encodeId($curator->id)]))
            ->assertOk()
            ->assertViewHas('events', fn ($events) => $events->contains('raw_id', $event->id));
    }

    public function test_a_pending_or_declined_curated_event_stays_out_of_the_picker(): void
    {
        // syncCuratorSources() attaches auto-sourced events at is_accepted = null; those never
        // render on the curator's page, so they must not fill the dropdown either.
        [, , $pendingCurator, $pendingEvent] = $this->curatedEvent([], null);
        $this->assertNotContains($pendingEvent->id, $this->pickerIds($pendingCurator));

        [, , $declinedCurator, $declinedEvent] = $this->curatedEvent([], false);
        $this->assertNotContains($declinedEvent->id, $this->pickerIds($declinedCurator));
    }

    public function test_the_curator_picker_still_lists_its_own_event_with_an_unaccepted_pivot(): void
    {
        // EventRepo's sync() writes the creator's own pivot row and only autoAcceptsEventFrom()
        // promotes it, so a curator's own event can sit at is_accepted = null. Requiring
        // acceptance without this escape would hide the curator's own events.
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($curator, ['creator_role_id' => $curator->id]);

        // Not createEvent(['is_accepted' => null]): that helper reads the flag with ?? true, so an
        // explicit null lands as true on the pivot and only sets the dead legacy events.is_accepted
        // column, which would make this pass whether or not the escape exists.
        $event->roles()->updateExistingPivot($curator->id, ['is_accepted' => null]);
        $this->assertNull($event->roles()->where('roles.id', $curator->id)->first()->pivot->is_accepted);

        $this->assertContains($event->id, $this->pickerIds($curator));
        $this->assertContains($event->id, $this->pickerIds($curator, true));
    }

    public function test_a_curator_event_whose_pivot_row_is_missing_stays_selectable(): void
    {
        // creator_role_id naming a schedule with no matching event_role row is a real state:
        // CheckData::checkEventCreatorRoles() exists to find it and deliberately never repairs it.
        // It still WORKS, because canViewEventData()/canViewEventTraffic() short-circuit on
        // events.user_id the way Event::scopeManagedThrough()'s first arm does, so the picker must
        // not require a pivot for the schedule's own events.
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($curator, ['creator_role_id' => $curator->id]);

        $event->roles()->detach($curator->id);
        $this->assertSame(0, $event->roles()->count());

        $this->assertContains($event->id, $this->pickerIds($curator));
        $this->assertContains($event->id, $this->pickerIds($curator, true));

        $this->actingAs($owner)
            ->get(route('analytics', [
                'role_id' => UrlUtils::encodeId($curator->id),
                'event_id' => UrlUtils::encodeId($event->id),
            ]))
            ->assertOk()
            ->assertViewHas('selectedEventId', $event->id);
    }

    public function test_the_revenue_and_checkins_pickers_exclude_a_curated_event(): void
    {
        [$owner, , $curator, $event] = $this->curatedEvent();

        $this->assertNotContains($event->id, $this->pickerIds($curator, true));

        foreach (['revenue', 'checkins'] as $tab) {
            $this->actingAs($owner)
                ->get(route('analytics', ['role_id' => UrlUtils::encodeId($curator->id), 'tab' => $tab]))
                ->assertOk()
                ->assertViewHas('events', fn ($events) => ! $events->contains('raw_id', $event->id));
        }
    }

    public function test_a_curated_event_deep_linked_on_the_revenue_tab_drops_the_filter(): void
    {
        // The curator does NOT own the venue here, so canViewEventData() must still refuse. The
        // tab links carry event_id forward, so this has to degrade rather than 403.
        $creator = $this->createOwner();
        $venue = $this->createRole($creator, 'venue');
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);

        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id]);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        $ticket = $this->createTicket($event, ['price' => 40]);
        $this->createSale($event, $venue, [
            'name' => 'Ruth Buyer',
            'email' => 'ruth-buyer@gmail.com',
            'payment_amount' => 40,
            'status' => 'paid',
        ], $ticket);

        $this->assertFalse($curatorOwner->canViewEventData($event));
        $this->assertTrue($curatorOwner->canViewEventTraffic($event));

        $response = $this->actingAs($curatorOwner)->get(route('analytics', [
            'role_id' => UrlUtils::encodeId($curator->id),
            'event_id' => UrlUtils::encodeId($event->id),
            'tab' => 'revenue',
        ]));

        $response->assertOk()
            ->assertViewHas('selectedEventId', null)
            ->assertDontSee('Ruth Buyer')
            ->assertDontSee('ruth-buyer@gmail.com');
    }

    public function test_a_curated_event_is_selectable_on_the_web_tab(): void
    {
        [$owner, , $curator, $event] = $this->curatedEvent();

        $this->actingAs($owner)
            ->get(route('analytics', [
                'role_id' => UrlUtils::encodeId($curator->id),
                'event_id' => UrlUtils::encodeId($event->id),
            ]))
            ->assertOk()
            ->assertViewHas('selectedEventId', $event->id)
            ->assertViewHas('selectedEventName', $event->name);
    }

    public function test_a_curator_cannot_read_traffic_for_an_event_it_never_accepted(): void
    {
        // syncCuratorSources() attaches a source venue's events at is_accepted = null without the
        // venue agreeing to anything. Nothing was ever rendered on the curator's page, so the
        // "the curator served the view" claim that widened this gate does not hold, and the gate
        // must not grant. Before canViewEventTraffic() existed this path was a 403.
        foreach ([null, false] as $pivot) {
            $creator = $this->createOwner();
            $venue = $this->createRole($creator, 'venue');
            $curatorOwner = $this->createOwner();
            $curator = $this->createCurator($curatorOwner);

            $event = $this->createEvent($venue, ['creator_role_id' => $venue->id]);
            $event->roles()->attach($curator->id, ['is_accepted' => $pivot]);

            $this->assertFalse($curatorOwner->canViewEventTraffic($event->fresh()));

            $this->actingAs($curatorOwner)
                ->get(route('analytics', ['event_id' => UrlUtils::encodeId($event->id)]))
                ->assertForbidden();
        }
    }

    public function test_a_venue_event_whose_pivot_row_is_missing_stays_selectable(): void
    {
        // Same orphaned-creator state as the curator case above, and CheckData reports it far more
        // often for venues. The picker must not be stricter for one schedule type than the other.
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id]);

        $event->roles()->detach($venue->id);

        $this->assertContains($event->id, $this->pickerIds($venue));
        $this->assertContains($event->id, $this->pickerIds($venue, true));
    }

    public function test_the_tab_links_keep_an_event_the_private_tabs_cannot_show(): void
    {
        // The tab strip rebuilds every href from the id. Nulling the selection without carrying
        // the requested one forward means Web -> Revenue -> Web loses the filter for good.
        [$owner, , $curator, $event] = $this->curatedEvent();

        $this->actingAs($owner)
            ->get(route('analytics', [
                'role_id' => UrlUtils::encodeId($curator->id),
                'event_id' => UrlUtils::encodeId($event->id),
                'tab' => 'revenue',
            ]))
            ->assertOk()
            ->assertViewHas('selectedEventId', null)
            ->assertViewHas('tabEventId', $event->id);
    }

    public function test_an_unrelated_users_event_is_still_403(): void
    {
        [, , , $event] = $this->curatedEvent();

        $stranger = $this->createOwner();
        $this->createRole($stranger, 'venue');

        $this->actingAs($stranger)
            ->get(route('analytics', ['event_id' => UrlUtils::encodeId($event->id)]))
            ->assertForbidden();
    }

    public function test_a_live_weekly_series_stays_in_the_picker_after_thirty_days(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $ninetyDaysAgo = Carbon::now()->subDays(90)->setTime(20, 0)->format('Y-m-d H:i:s');

        $never = $this->createRecurringEvent($venue, [
            'starts_at' => $ninetyDaysAgo,
            'recurring_end_type' => 'never',
        ]);
        // 'after_events' counts occurrences in PHP, so SQL cannot bound it and it must stay in.
        $afterEvents = $this->createRecurringEvent($venue, [
            'starts_at' => $ninetyDaysAgo,
            'recurring_end_type' => 'after_events',
            'recurring_end_value' => '10',
        ]);

        $ids = $this->pickerIds($venue);

        $this->assertContains($never->id, $ids);
        $this->assertContains($afterEvents->id, $ids);
    }

    public function test_a_series_that_ended_on_a_date_leaves_the_picker(): void
    {
        // Pins the bound, so this is not later "simplified" to a bare orWhereNotNull.
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $ended = $this->createRecurringEvent($venue, [
            'starts_at' => Carbon::now()->subDays(90)->setTime(20, 0)->format('Y-m-d H:i:s'),
            'recurring_end_type' => 'on_date',
            'recurring_end_value' => Carbon::now()->subDays(60)->toDateString(),
        ]);

        $this->assertNotContains($ended->id, $this->pickerIds($venue));
    }

    public function test_a_still_running_multi_day_event_stays_in_the_picker(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $festival = $this->createEvent($venue, [
            'starts_at' => Carbon::now()->subDays(40)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'duration' => 24 * 60,
        ]);

        $this->assertContains($festival->id, $this->pickerIds($venue));
    }

    public function test_a_finished_one_off_is_still_outside_the_window(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $old = $this->createEvent($venue, [
            'starts_at' => Carbon::now()->subDays(60)->setTime(20, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->assertNotContains($old->id, $this->pickerIds($venue));
    }

    public function test_the_picker_label_names_a_selected_event_the_list_omits(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $old = $this->createEvent($venue, [
            'name' => 'Last Autumn Festival',
            'starts_at' => Carbon::now()->subDays(60)->setTime(20, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->actingAs($owner)
            ->get(route('analytics', [
                'role_id' => UrlUtils::encodeId($venue->id),
                'event_id' => UrlUtils::encodeId($old->id),
            ]))
            ->assertOk()
            ->assertViewHas('selectedEventId', $old->id)
            ->assertViewHas('selectedEventName', 'Last Autumn Festival');
    }

    public function test_top_events_is_scoped_to_the_selected_schedule(): void
    {
        $owner = $this->createOwner();
        $selected = $this->createRole($owner, 'venue', ['name' => 'Selected Venue']);
        $other = $this->createRole($owner, 'venue', ['name' => 'Other Venue']);

        $mine = $this->createEvent($selected);
        $theirs = $this->createEvent($other);

        foreach ([$mine, $theirs] as $event) {
            AnalyticsEventsDaily::create([
                'event_id' => $event->id,
                'date' => Carbon::now()->subDay()->toDateString(),
                'desktop_views' => 5,
            ]);
        }

        $this->actingAs($owner)
            ->get(route('analytics', ['role_id' => UrlUtils::encodeId($selected->id)]))
            ->assertOk()
            ->assertViewHas('topEvents', function ($topEvents) use ($mine, $theirs) {
                $ids = collect($topEvents)->map(fn ($row) => $row['event']->id);

                return $ids->contains($mine->id) && ! $ids->contains($theirs->id);
            });
    }
}
