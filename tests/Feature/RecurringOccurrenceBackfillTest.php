<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The guest page must land on a date the event actually occurs on.
 *
 * RoleController::viewGuest() backfills $date for a recurring event, and that value is what
 * event/tickets.blade.php:1180 and event/rsvp.blade.php:341 post as event_date. Guest checkout does
 * NOT validate it (the only matchesDate() calls in TicketController are a helper closure, a loop
 * and the bulk-import path), so a wrong backfill books a sale against a day the event does not
 * happen - and the buyer's ticket URL, the door scanner's QR and the calendar entry all inherit it.
 *
 * The old backfill scanned days_of_week alone, and EventRepo::saveEvent() writes '1111111' for
 * daily, monthly_date, monthly_weekday and yearly, so it answered TODAY for all of them.
 */
class RecurringOccurrenceBackfillTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        $this->role = $this->createRole($this->createOwner());
    }

    /** The day-of-month an event started on, chosen so "today" is never accidentally right. */
    private function anchorDay(): int
    {
        return now()->day === 15 ? 20 : 15;
    }

    public function test_a_monthly_event_lands_on_a_real_occurrence(): void
    {
        $anchor = now()->subMonths(3)->day($this->anchorDay())->setTime(19, 0);

        $event = $this->createRecurringEvent($this->role, [
            'creator_role_id' => $this->role->id,
            'starts_at' => $anchor->format('Y-m-d H:i:s'),
            'recurring_frequency' => 'monthly_date',
        ]);

        $resolved = $event->nextOccurrenceFrom();

        $this->assertNotNull($resolved, 'a monthly event must have a next occurrence');
        $this->assertTrue(
            $event->matchesDate($resolved, $event->scheduleTimezone()),
            "backfilled {$resolved}, which is not an occurrence of this event"
        );
        // The actual defect: it used to answer today, because days_of_week is '1111111'.
        $this->assertSame($this->anchorDay(), (int) date('j', strtotime($resolved)));
    }

    public function test_a_yearly_event_lands_on_a_real_occurrence(): void
    {
        $anchor = now()->subYears(2)->day($this->anchorDay())->setTime(19, 0);

        $event = $this->createRecurringEvent($this->role, [
            'creator_role_id' => $this->role->id,
            'starts_at' => $anchor->format('Y-m-d H:i:s'),
            'recurring_frequency' => 'yearly',
        ]);

        $resolved = $event->nextOccurrenceFrom();

        $this->assertNotNull($resolved);
        $this->assertTrue($event->matchesDate($resolved, $event->scheduleTimezone()));
        $this->assertSame((int) $anchor->format('n'), (int) date('n', strtotime($resolved)));
        $this->assertSame((int) $anchor->format('j'), (int) date('j', strtotime($resolved)));
    }

    public function test_a_weekly_event_skips_an_excluded_date(): void
    {
        // Only Mondays, and the next Monday is cancelled. The old scan read days_of_week and
        // handed back that very date; matchesDate() honours recurring_exclude_dates.
        $monday = now()->next('Monday');

        $event = $this->createRecurringEvent($this->role, [
            'creator_role_id' => $this->role->id,
            'starts_at' => now()->subMonth()->next('Monday')->setTime(19, 0)->format('Y-m-d H:i:s'),
            'days_of_week' => '0100000',
            'recurring_frequency' => 'weekly',
            'recurring_exclude_dates' => [$monday->format('Y-m-d')],
        ]);

        $resolved = $event->nextOccurrenceFrom();

        $this->assertNotSame($monday->format('Y-m-d'), $resolved, 'an excluded date is not an occurrence');
        $this->assertTrue($event->matchesDate($resolved, $event->scheduleTimezone()));
    }

    public function test_an_ended_recurrence_resolves_to_nothing_rather_than_a_wrong_date(): void
    {
        $event = $this->createRecurringEvent($this->role, [
            'creator_role_id' => $this->role->id,
            'starts_at' => now()->subYear()->format('Y-m-d H:i:s'),
            'recurring_end_type' => 'on_date',
            'recurring_end_value' => now()->subMonth()->format('Y-m-d'),
        ]);

        $this->assertNull($event->nextOccurrenceFrom(), 'a finished recurrence has no next occurrence');
    }

    public function test_no_matching_weekday_terminates_instead_of_hanging(): void
    {
        // The old backfill was an unbounded `while (true)` over days_of_week, so '0000000' - which
        // an owner reaches by unchecking every day - spun forever and hung the request.
        $event = $this->createRecurringEvent($this->role, [
            'creator_role_id' => $this->role->id,
            'days_of_week' => '0000000',
        ]);

        $this->assertNull($event->nextOccurrenceFrom());
    }

    public function test_the_guest_page_serves_a_real_occurrence_for_a_monthly_event(): void
    {
        $anchor = now()->subMonths(3)->day($this->anchorDay())->setTime(19, 0);

        $event = $this->createRecurringEvent($this->role, [
            'creator_role_id' => $this->role->id,
            'starts_at' => $anchor->format('Y-m-d H:i:s'),
            'recurring_frequency' => 'monthly_date',
            'tickets_enabled' => true,
        ]);
        $this->createTicket($event, ['price' => 10]);

        $html = $this->get($this->guestEventUrl($this->role, $event))->assertOk()->getContent();

        // The hidden field the checkout posts as sales.event_date.
        preg_match('/name="event_date" value="([^"]*)"/', $html, $m);
        $this->assertNotEmpty($m[1] ?? '', 'the ticket form must carry an event_date');
        $this->assertTrue(
            $event->matchesDate($m[1], $event->scheduleTimezone()),
            "the ticket form posts {$m[1]}, which is not an occurrence of this event"
        );
    }
}
