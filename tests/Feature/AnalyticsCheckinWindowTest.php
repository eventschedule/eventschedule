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
}
