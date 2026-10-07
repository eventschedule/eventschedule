<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * "More events" at the foot of the guest event page.
 *
 * It used to be a second copy of the calendar app in the page's side column: a Vue mount that
 * fetched the schedule's events for the viewed event's month, sliced them to twenty cards, and had
 * a footer link whose appearance was decided half by the server and half in the browser. On a
 * phone it was more than half of the page. It is three rows now, drawn by the server from the
 * schedule's next public events, and the link to the whole schedule is simply there.
 *
 * The test that matters for privacy is still here: a draft, cancelled, unlisted or
 * password-protected event must never be one of the rows.
 */
class GuestEventSidebarLinkTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-03-11 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function at(int $days): string
    {
        return Carbon::now()->addDays($days)->setTime(12, 0)->format('Y-m-d H:i:s');
    }

    /** The section's own markup, so an assertion cannot be satisfied by the rest of the page. */
    private function more(string $html): string
    {
        $start = strpos($html, 'id="gp-upcoming-events"');
        $this->assertNotFalse($start, 'the page has its "more events" section');

        return substr($html, $start, strpos($html, '</section>', $start) - $start);
    }

    public function test_the_next_three_other_events_are_rows_that_link_to_them(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['name' => 'Tonight', 'starts_at' => $this->at(1)]);
        $names = ['Second', 'Third', 'Fourth', 'Fifth'];
        $others = [];
        foreach ($names as $i => $name) {
            $others[$name] = $this->createEvent($role, ['name' => $name.' Night', 'starts_at' => $this->at(2 + $i)]);
        }

        $more = $this->more($this->get($this->guestEventUrl($role, $event))->assertOk()->getContent());

        $this->assertSame(3, substr_count($more, '<a class="gk-row '), 'three, not twenty');
        foreach (['Second', 'Third', 'Fourth'] as $name) {
            $this->assertStringContainsString($name.' Night', $more);
            $this->assertStringContainsString('href="'.e($others[$name]->fresh()->getGuestUrl($role->subdomain)).'"', $more, 'a real link, not a click handler');
        }
        $this->assertStringNotContainsString('Fifth Night', $more);
        $this->assertStringNotContainsString('Tonight', $more, 'the event the visitor is already on');
    }

    public function test_the_way_to_the_whole_schedule_is_always_there_in_the_owners_words(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'custom_labels' => ['view_full_schedule' => ['value' => 'See the whole season'], 'events' => ['value' => 'Coming up']],
        ]);
        $event = $this->createEvent($role, ['name' => 'Tonight', 'starts_at' => $this->at(1)]);
        $this->createEvent($role, ['name' => 'Next Week', 'starts_at' => $this->at(8)]);

        $more = $this->more($this->get($this->guestEventUrl($role, $event))->assertOk()->getContent());

        $this->assertStringContainsString('See the whole season', $more);
        $this->assertStringContainsString('Coming up', $more);
        $this->assertStringContainsString('href="'.e(route('role.view_guest', ['subdomain' => $role->subdomain])).'"', $more);
    }

    public function test_events_a_visitor_may_not_see_are_never_rows(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['name' => 'Tonight', 'starts_at' => $this->at(1)]);
        $this->createEvent($role, ['name' => 'Draft Night', 'starts_at' => $this->at(2), 'is_draft' => true]);
        $this->createEvent($role, ['name' => 'Cancelled Night', 'starts_at' => $this->at(3), 'is_cancelled' => true]);
        $this->createEvent($role, ['name' => 'Unlisted Night', 'starts_at' => $this->at(4), 'is_private' => true]);
        $this->createEvent($role, ['name' => 'Locked Night', 'starts_at' => $this->at(5), 'event_password' => 'hunter2']);
        $this->createEvent($role, ['name' => 'Yesterday Night', 'starts_at' => $this->at(-2)]);
        $this->createEvent($role, ['name' => 'Open Night', 'starts_at' => $this->at(6)]);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();
        $more = $this->more($html);

        $this->assertSame(1, substr_count($more, '<a class="gk-row '));
        $this->assertStringContainsString('Open Night', $more);
        foreach (['Draft', 'Cancelled', 'Unlisted', 'Locked', 'Yesterday'] as $hidden) {
            $this->assertStringNotContainsString($hidden.' Night', $html, $hidden.' is nowhere on the page');
        }
    }

    public function test_an_event_with_nothing_after_it_has_no_empty_section(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['name' => 'The Only One', 'starts_at' => $this->at(1)]);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertStringNotContainsString('id="gp-upcoming-events"', $html);
    }

    public function test_the_event_page_no_longer_carries_a_second_calendar_app(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['name' => 'Tonight', 'starts_at' => $this->at(1)]);
        $this->createEvent($role, ['name' => 'Next Week', 'starts_at' => $this->at(8)]);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        // The app, its footer link, and the request it made for up to 400 events.
        $this->assertStringNotContainsString('id="calendar-app"', $html);
        $this->assertStringNotContainsString('viewFullScheduleFooter', $html);
        $this->assertStringNotContainsString('api/calendar-events', $html);
    }

    public function test_a_weekly_event_does_not_list_itself_as_more(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $weekly = $this->createEvent($role, ['name' => 'Every Thursday', 'starts_at' => $this->at(1), 'days_of_week' => '1111111']);
        $this->createEvent($role, ['name' => 'One Off', 'starts_at' => $this->at(9)]);

        $more = $this->more($this->get($this->guestEventUrl($role, $weekly))->assertOk()->getContent());

        $this->assertStringNotContainsString('Every Thursday', $more);
        $this->assertStringContainsString('One Off', $more);
    }
}
