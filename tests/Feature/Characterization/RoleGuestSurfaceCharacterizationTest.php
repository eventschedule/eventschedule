<?php

namespace Tests\Feature\Characterization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Route-level characterization of RoleController's guest/public surface ahead
 * of the P7 split (REFACTOR_PLAN.md): the viewGuest minimum set plus
 * follow/unfollow, calendarEvents, past events, unsubscribe, and the
 * followers QR code. Pins status codes, redirect targets, and one content
 * assertion per endpoint - the split must keep every one identical.
 */
class RoleGuestSurfaceCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_view_guest_minimum_set(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Guest Surface Venue']);
        $event = $this->createEvent($role, ['name' => 'Guest Surface Event']);

        // Schedule home.
        $this->get('/'.$role->subdomain)
            ->assertOk()
            ->assertSee('Guest Surface Venue');

        // Event by slug.
        $this->get('/'.$role->subdomain.'/'.$event->slug)
            ->assertOk()
            ->assertSee('Guest Surface Event');

        // Event by slug + encoded id.
        $this->get($this->guestEventUrl($role, $event))
            ->assertOk()
            ->assertSee('Guest Surface Event');

        // Embed variant renders the embed view (no full chrome).
        $this->get('/'.$role->subdomain.'?embed=1')->assertOk();
    }

    public function test_view_guest_recurring_event_with_date_instance(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createRecurringEvent($role, ['name' => 'Weekly Session']);

        $date = now()->addDays(14)->format('Y-m-d');
        $this->get($this->guestEventUrl($role, $event, $date))
            ->assertOk()
            ->assertSee('Weekly Session');
    }

    public function test_view_guest_unknown_slug_redirects_to_schedule_home(): void
    {
        // An unmatched slug (no event, no sub-schedule) redirects to the
        // schedule's guest URL - NOT a 404. The P7 split must keep this.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Fallback Venue']);
        $this->createEvent($role);

        $this->get('/'.$role->subdomain.'/no-such-event-slug')
            ->assertRedirect($role->getGuestUrl());
    }

    public function test_unknown_subdomain_redirects_home_not_404(): void
    {
        // viewGuest bails with redirect(app_url()) for unknown or unclaimed
        // schedules - there is no 404 on this path.
        $this->get('/nosuchschedule12345')->assertRedirect(app_url());
    }

    public function test_calendar_events_json_shape(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'name' => 'Calendar Event',
            'starts_at' => now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);

        $target = now()->addDays(7);
        $response = $this->get('/'.$role->subdomain.'/api/calendar-events?month='.$target->month.'&year='.$target->year);

        $response->assertOk();
        $response->assertJsonStructure(['events', 'eventsMap', 'pastEvents', 'hasMorePastEvents', 'filterMeta']);
        $this->assertStringContainsString('Calendar Event', $response->getContent());
    }

    public function test_calendar_events_include_future_month_events_but_map_stays_in_month(): void
    {
        // Neither layout is bounded to the viewed month: the list layout loads every upcoming event
        // in one flat fetch, and the calendar layout's mobile view is a flat four-month agenda with
        // no month navigation, so a month-bounded fetch left later events unreachable there. What
        // still holds the desktop grid to one month is eventsMap, which it renders its cells from.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['event_layout' => 'list']);

        $thisMonth = now()->setTime(12, 0);
        $future = now()->addMonthsNoOverflow(2)->setTime(12, 0);

        $this->createEvent($role, [
            'name' => 'This Month Event',
            'starts_at' => $thisMonth->format('Y-m-d H:i:s'),
        ]);
        $this->createEvent($role, [
            'name' => 'Next Month Event',
            'starts_at' => $future->format('Y-m-d H:i:s'),
        ]);

        // Both the plain calendar fetch and the list fetch carry the future-month event.
        foreach (['', '?list=1'] as $query) {
            $response = $this->get('/'.$role->subdomain.'/api/calendar-events'.$query);
            $response->assertOk();
            $this->assertStringContainsString('Next Month Event', $response->getContent());

            // The grid map still covers only the month being viewed, so the extra rows are loaded
            // but never placed in a desktop calendar cell.
            $eventsMap = (array) $response->json('eventsMap');
            $this->assertArrayHasKey($thisMonth->format('Y-m-d'), $eventsMap);
            $this->assertArrayNotHasKey($future->format('Y-m-d'), $eventsMap);
        }
    }

    public function test_calendar_events_for_unclaimed_schedule_returns_empty_shell(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['email_verified_at' => null]);
        $role->users()->detach();
        $role->forceFill(['user_id' => null])->save();

        $this->get('/'.$role->subdomain.'/api/calendar-events')
            ->assertOk()
            ->assertExactJson([
                'events' => [],
                'eventsMap' => [],
                'pastEvents' => [],
                'hasMorePastEvents' => false,
                'filterMeta' => ['uniqueCategoryIds' => [], 'hasOnlineEvents' => false],
            ]);
    }

    public function test_follow_and_unfollow_round_trip(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $fan = $this->createOwner();

        $this->actingAs($fan)->get('/'.$role->subdomain.'/follow')->assertRedirect();

        $this->assertDatabaseHas('role_user', [
            'role_id' => $role->id,
            'user_id' => $fan->id,
            'level' => 'follower',
        ]);

        $this->actingAs($fan)->get('/'.$role->subdomain.'/unfollow')->assertRedirect();

        $this->assertDatabaseMissing('role_user', [
            'role_id' => $role->id,
            'user_id' => $fan->id,
        ]);
    }

    public function test_guest_follow_redirects_to_sign_up(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $response = $this->get('/'.$role->subdomain.'/follow');

        $response->assertRedirect();
        $this->assertStringContainsString('/sign_up', $response->headers->get('Location'));
    }

    public function test_show_unsubscribe_page_loads(): void
    {
        $this->get(route('role.show_unsubscribe'))->assertOk();
    }

    public function test_followers_qr_code_returns_png_for_member(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $response = $this->actingAs($owner)->get(
            route('role.qr_code', ['subdomain' => $role->subdomain])
        );

        $response->assertOk();
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
    }

    public function test_past_events_endpoint_requires_before_param(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->createEvent($role, [
            'name' => 'Past Event',
            'starts_at' => now()->subDays(14)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);

        // Without ?before= the endpoint short-circuits to an empty shell.
        $this->get('/'.$role->subdomain.'/api/past-events')
            ->assertOk()
            ->assertExactJson(['events' => [], 'has_more' => false]);

        // With a cursor it returns the past event.
        $this->get('/'.$role->subdomain.'/api/past-events?before='.now()->format('Y-m-d'))
            ->assertOk()
            ->assertSee('Past Event');
    }

    /**
     * EVENTSCHEDULE-PHP-41: ?date=6348 on the guest event page was a fatal 500.
     *
     * viewGuest() sanitizes the param, but show-guest.blade.php re-read the raw query string
     * into the same view-scoped $date (Blade @php shares the template scope), so every date
     * consumer below the breadcrumb got the garbage back. It surfaced in the add-to-calendar
     * link, via Event::occurrenceStartUtc()'s Carbon::createFromFormat().
     *
     * The URL shape matters: slug only, no event id, date in the QUERY STRING. The dated route
     * (event.view_guest_full) constrains {date} to \d{4}-\d{2}-\d{2}, so only the unconstrained
     * /{slug} catch-all can carry a malformed date this far.
     */
    public function test_view_guest_survives_a_malformed_date_query_param(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        // Recurring, because that is what carries a ?date= in the first place: a one-off event
        // does not resolve on a date it never occurs on, and viewGuest redirects to the schedule
        // home instead of rendering. No tickets and no RSVP, so the page falls through to the
        // plain add-to-calendar dropdown - the branch holding the getGoogleCalendarUrl($date)
        // in the Sentry frame.
        $event = $this->createRecurringEvent($role, [
            'name' => 'Malformed Date Event',
            'creator_role_id' => $role->id,
        ]);

        // A claimed talent on the bill, so the three talent-card blocks that also rebuild a
        // month+year from the raw ?date= actually render. They sit inside a @foreach, above the
        // breadcrumb, and are the reason the raw param is normalized once at the top of the view.
        $talent = $this->createRole($this->createOwner(), 'talent', ['name' => 'Headline Talent']);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        // '6348' is the reported payload: strtotime() reads a bare 4-digit number as a YEAR, so
        // the controller's guard passes it through as '6348-08-15' and only the view's raw
        // re-read reaches Carbon as '6348'.
        foreach (['6348', 'abc', '2026-13-45', '0', '../../etc/passwd', '<script>x</script>'] as $payload) {
            $response = $this->get('/'.$role->subdomain.'/'.$event->slug.'?date='.urlencode($payload));
            $this->assertSame(200, $response->status(), 'payload: '.$payload);
            $response->assertSee('Malformed Date Event');
            // Proves the talent blocks really rendered, so they are covered above.
            $response->assertSee('Headline Talent');
        }

        // $rawDateParam is defined once at the top of the view and read inside a @foreach further
        // down. Were it out of scope there, PHP would quietly read null and take the else branch,
        // and every assertion above would still pass - so pin that the talent link built its
        // month+year from the date rather than falling through.
        $response = $this->get('/'.$role->subdomain.'/'.$event->slug.'?date=2026-09-04');
        $response->assertOk();
        $this->assertMatchesRegularExpression(
            '/'.preg_quote($talent->subdomain, '/').'\?[^"\']*month=9[^"\']*year=2026/',
            $response->getContent(),
            'The talent link should carry the month and year derived from ?date='
        );
    }

    /**
     * The same clobber reached Event::canAcceptRsvp(), which uses Carbon::parse() rather than
     * createFromFormat() and so fails on a DIFFERENT set of inputs - and sits at a top-level
     *
     * @elseif above the calendar links, so it threw first. Needs a recurring event with RSVP on:
     * canAcceptRsvp() only touches $date under `if ($this->recurring_frequency)`.
     */
    public function test_view_guest_rsvp_gate_survives_a_malformed_date_query_param(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createRecurringEvent($role, [
            'name' => 'Recurring Rsvp Event',
            'creator_role_id' => $role->id,
            'rsvp_enabled' => true,
        ]);

        // 'abc' and '2026-13-45' throw in Carbon::parse() but NOT in createFromFormat()
        // ('2026-13-45' silently rolls over to 2027-02-14 there), so they pin this sink
        // specifically. '2026-13-45' also matches the bare shape regex, which is why the
        // guard pairs it with checkdate().
        foreach (['abc', '2026-13-45', '6348', '2026-02-30'] as $payload) {
            $this->get('/'.$role->subdomain.'/'.$event->slug.'?date='.urlencode($payload))
                ->assertOk()
                ->assertSee('Recurring Rsvp Event');
        }
    }

    /**
     * ?date[]=x hands strtotime() an array. That is a TypeError in the controller itself, so it
     * is upstream of the view fix and hits the schedule page too, not just the event page.
     */
    public function test_view_guest_survives_an_array_date_query_param(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Array Date Venue']);
        $event = $this->createEvent($role, [
            'name' => 'Array Date Event',
            'creator_role_id' => $role->id,
        ]);

        $this->get('/'.$role->subdomain.'?date[]=x')
            ->assertOk()
            ->assertSee('Array Date Venue');

        $this->get('/'.$role->subdomain.'/'.$event->slug.'?date[]=x')
            ->assertOk()
            ->assertSee('Array Date Event');
    }

    /**
     * The other half of the fix: a VALID ?date= must still select that occurrence. Rejecting or
     * dropping the date would make every test above pass while quietly breaking recurring events.
     */
    public function test_view_guest_valid_date_query_param_still_selects_the_occurrence(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['timezone' => 'America/New_York']);
        $event = $this->createRecurringEvent($role, [
            'name' => 'Occurrence Event',
            'creator_role_id' => $role->id,
        ]);

        $date = now()->addDays(21)->format('Y-m-d');
        $response = $this->get('/'.$role->subdomain.'/'.$event->slug.'?date='.$date);
        $response->assertOk();

        // The add-to-calendar link carries the requested occurrence, not the recurrence anchor.
        $occurrenceStamp = $event->occurrenceStartUtc($date)->format('Ymd\THis\Z');
        $anchorStamp = $event->getStartDateTime()->format('Ymd\THis\Z');

        $this->assertNotSame($anchorStamp, $occurrenceStamp, 'Fixture is wrong: pick a date that differs from starts_at.');
        $this->assertStringContainsString($occurrenceStamp, $response->getContent());
    }

    /**
     * Pins the view fix specifically, not just the model guards behind it.
     *
     * The other tests here would still pass if the blade kept clobbering $date, because
     * occurrenceStartUtc() now swallows a malformed date. '12/31' is the case that separates
     * them: viewGuest() normalizes it to a REAL date (strtotime is deliberately loose, for
     * backwards compatibility), so the sanitized value and the raw query param are both usable
     * but different. Clobbering makes the page render the recurrence anchor instead of Dec 31.
     */
    public function test_view_guest_uses_the_sanitized_date_not_the_raw_query_param(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['timezone' => 'America/New_York']);
        $event = $this->createRecurringEvent($role, [
            'name' => 'Loose Date Event',
            'creator_role_id' => $role->id,
        ]);

        $response = $this->get('/'.$role->subdomain.'/'.$event->slug.'?date='.urlencode('12/31'));
        $response->assertOk();

        $normalized = date('Y-m-d', strtotime('12/31'));
        $expectedStamp = $event->occurrenceStartUtc($normalized)->format('Ymd\THis\Z');
        $anchorStamp = $event->occurrenceStartUtc($event->saleEventDateFromStartsAt())->format('Ymd\THis\Z');

        $this->assertNotSame($anchorStamp, $expectedStamp, 'Fixture is wrong: 12/31 must differ from starts_at.');
        $this->assertStringContainsString($expectedStamp, $response->getContent());
        $this->assertStringNotContainsString($anchorStamp, $response->getContent());
    }
}
