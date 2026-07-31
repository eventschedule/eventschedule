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
}
