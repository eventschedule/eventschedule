<?php

namespace Tests\Feature;

use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The check-ins tab is the one analytics panel that windows the EVENT's own date (sales.event_date)
 * rather than something that happened. Every range preset except last_month ends at now(), so an
 * upper bound there hid every upcoming show: its tickets were sold and its door list existed, but
 * it stayed invisible until the morning of the event and then appeared - which is precisely the
 * "missing yesterday, shown today" report this came from.
 */
class AnalyticsCheckinWindowTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The presets are relative to now() and event_date is an absolute date string; freezing
        // keeps a run in December from landing on a different answer than one in June.
        Carbon::setTestNow(Carbon::parse('2026-09-06 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function checkins(array $overrides = []): array
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue, array_merge([
            'creator_role_id' => $venue->id,
            'starts_at' => '2026-09-09 18:00:00',
        ], $overrides));
        $ticket = $this->createTicket($event);
        $this->createSale($event, $venue, ['event_date' => '2026-09-09'], $ticket, 2);

        // last_30_days: the default preset, and the one the reporter would have been on.
        return app(AnalyticsService::class)->getCheckinStats(
            $owner,
            now()->subDays(30)->startOfDay(),
            now()->endOfDay(),
            $venue->id,
            null,
            null // $eventDateEnd: no upper bound, which is what "…to now" ranges now pass
        );
    }

    public function test_a_future_dated_event_appears_on_the_checkins_tab_today(): void
    {
        $stats = $this->checkins();

        $this->assertTrue($stats['has_data'], 'An event three days out was invisible on the check-ins tab.');
        $this->assertSame(2, $stats['total_sold']);
    }

    public function test_a_closed_historical_range_still_excludes_a_future_event(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'starts_at' => '2026-09-09 18:00:00']);
        $ticket = $this->createTicket($event);
        $this->createSale($event, $venue, ['event_date' => '2026-09-09'], $ticket, 2);

        // last_month is the one preset that names a finished window, so it keeps BOTH bounds.
        $stats = app(AnalyticsService::class)->getCheckinStats(
            $owner,
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
            $venue->id,
            null,
            now()->subMonth()->endOfMonth()
        );

        $this->assertFalse($stats['has_data']);
    }

    public function test_a_deleted_sale_does_not_count_toward_sold(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'starts_at' => '2026-09-09 18:00:00']);
        $ticket = $this->createTicket($event);

        $this->createSale($event, $venue, ['event_date' => '2026-09-09'], $ticket, 2);
        $deleted = $this->createSale($event, $venue, ['event_date' => '2026-09-09'], $ticket, 5);
        $deleted->is_deleted = true;
        $deleted->saveQuietly();

        $stats = app(AnalyticsService::class)->getCheckinStats(
            $owner, now()->subDays(30)->startOfDay(), now()->endOfDay(), $venue->id, null, null
        );

        // Was 7: a deleted sale counted toward sold and dragged the attendance rate down with it,
        // the same way getConversionStats() already guards against.
        $this->assertSame(2, $stats['total_sold']);
    }

    /** Scans the first $count tickets of a sale and clears the rest (0 clears them all). */
    private function scan(\App\Models\Sale $sale, int $count): void
    {
        $saleTicket = $sale->saleTickets()->first();
        $seats = json_decode($saleTicket->seats, true);
        foreach (array_keys($seats) as $n => $seat) {
            $seats[$seat] = $n < $count ? Carbon::parse($sale->event_date.' 20:00:00', 'America/New_York')->timestamp : null;
        }
        $saleTicket->seats = json_encode($seats);
        $saleTicket->save();
    }

    private function stats($owner, $venue): array
    {
        return app(AnalyticsService::class)->getCheckinStats(
            $owner, now()->subDays(30)->startOfDay(), now()->endOfDay(), $venue->id, null, null
        );
    }

    /**
     * A ticket for a show that has not happened yet is sold, and it is not a no-show. Counted
     * together, a schedule with its next shows on sale read 48% attendance and 52% no-shows.
     */
    public function test_tickets_for_a_show_still_to_come_are_not_no_shows(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $past = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'name' => 'Last Week', 'starts_at' => '2026-09-03 23:00:00']);
        $soon = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'name' => 'Next Week', 'starts_at' => '2026-09-09 23:00:00']);
        $this->scan($this->createSale($past, $venue, ['event_date' => '2026-09-03'], $this->createTicket($past), 4), 3);
        $this->createSale($soon, $venue, ['event_date' => '2026-09-09'], $this->createTicket($soon), 2);

        $stats = $this->stats($owner, $venue);

        $this->assertSame(6, $stats['total_sold']);
        $this->assertSame(4, $stats['past_sold']);
        $this->assertSame(2, $stats['upcoming_sold']);
        $this->assertSame(3, $stats['total_checked_in']);
        $this->assertEquals(75.0, $stats['attendance_rate'], 'Was 50%: the two tickets for next week counted as no-shows.');
        $this->assertSame(1, $stats['no_shows']);
        $this->assertEquals(25.0, $stats['no_show_rate']);
        $rows = collect($stats['events_breakdown'])->keyBy('event_name');
        $this->assertTrue($rows['Next Week']['is_upcoming']);
        $this->assertFalse($rows['Last Week']['is_upcoming']);
    }

    /**
     * Tonight's show has not begun at breakfast, in the schedule's own time. A 9pm New York show
     * is stored at 01:00 UTC the NEXT day: set on the UTC time, its start fell on the evening
     * before, and on show day all its tickets read as no-shows from the first hour.
     */
    public function test_tonights_show_begins_at_its_own_local_start(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['timezone' => 'America/New_York']);
        $tonight = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'starts_at' => '2026-09-07 01:00:00']);
        $sale = $this->createSale($tonight, $venue, ['event_date' => '2026-09-06'], $this->createTicket($tonight), 2);

        // 8am in New York.
        $stats = $this->stats($owner, $venue);
        $this->assertSame(0, $stats['past_sold']);
        $this->assertSame(2, $stats['upcoming_sold']);
        $this->assertSame(0, $stats['no_shows']);
        $this->assertEquals(0, $stats['no_show_rate']);

        // 8:30pm: still not begun.
        Carbon::setTestNow(Carbon::parse('2026-09-07 00:30:00', 'UTC'));
        $this->assertSame(0, $this->stats($owner, $venue)['past_sold']);

        // 9:30pm: begun, and the unscanned tickets are no-shows.
        Carbon::setTestNow(Carbon::parse('2026-09-07 01:30:00', 'UTC'));
        $stats = $this->stats($owner, $venue);
        $this->assertSame(2, $stats['past_sold']);
        $this->assertSame(2, $stats['no_shows']);

        // Before the start, one scan opens the doors.
        Carbon::setTestNow(Carbon::parse('2026-09-06 23:00:00', 'UTC'));
        $this->scan($sale, 1);
        $this->assertEquals(50.0, $this->stats($owner, $venue)['attendance_rate']);
    }

    /** One scanned ticket means the doors are open: for the whole show, not only that sale. */
    public function test_a_scan_opens_the_whole_show(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['timezone' => 'America/New_York']);
        $tonight = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'starts_at' => '2026-09-07 01:00:00']);
        $ticket = $this->createTicket($tonight);
        $this->scan($this->createSale($tonight, $venue, ['event_date' => '2026-09-06'], $ticket, 2), 1);
        $this->createSale($tonight, $venue, ['event_date' => '2026-09-06'], $ticket, 3);

        $stats = $this->stats($owner, $venue);

        $this->assertSame(5, $stats['past_sold'], 'The unscanned sale counted as a show still to come.');
        $this->assertSame(0, $stats['upcoming_sold']);
        $this->assertEquals(20.0, $stats['attendance_rate']);
    }

    /** Every event has its own "General": the types are by name, or one name is listed six times. */
    public function test_ticket_types_are_counted_by_name_across_events(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        foreach (['2026-09-01', '2026-09-03'] as $date) {
            $event = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'starts_at' => $date.' 23:00:00']);
            $this->scan($this->createSale($event, $venue, ['event_date' => $date], $this->createTicket($event, ['type' => 'General']), 2), 2);
            $this->createSale($event, $venue, ['event_date' => $date], $this->createTicket($event, ['type' => 'VIP']), 1);
        }

        $types = collect($this->stats($owner, $venue)['ticket_types'])->keyBy('name');

        $this->assertCount(2, $types);
        $this->assertSame(4, $types['General']['sold']);
        $this->assertSame(4, $types['General']['checked_in']);
        $this->assertSame(2, $types['VIP']['sold']);
    }
}
