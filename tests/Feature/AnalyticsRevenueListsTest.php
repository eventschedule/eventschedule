<?php

namespace Tests\Feature;

use App\Models\AnalyticsDaily;
use App\Models\PromoCode;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Two things /analytics shares with the dashboard, which the dashboard's own tests cannot see.
 *
 * A deleted sale is not revenue. The totals at the top of the Revenue tab stopped counting one
 * when the dashboard's Revenue tile did (AnalyticsService::salesByCurrency()); the two lists under
 * those totals went on counting it, so the tab disagreed with itself.
 *
 * And "last N days" is N dates on both sides of a comparison. It used to be N + 1 dates against
 * N, a head start of a day. The dashboard's own fixtures gave the same answer under either
 * window, so nothing pinned the page that most people read the comparison on.
 */
class AnalyticsRevenueListsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-10-06 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * Mutation: drop `is_deleted` from getTopEventsByRevenue() or getPromoCodeStats().
     */
    public function test_a_deleted_sale_is_in_neither_the_top_events_nor_a_promo_codes_count(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['name' => 'Paid Show', 'ticket_currency_code' => 'USD', 'creator_role_id' => $role->id]);
        $ticket = $this->createTicket($event, ['price' => 25]);
        $code = PromoCode::create(['event_id' => $event->id, 'code' => 'EARLY', 'type' => 'fixed', 'value' => 5]);

        $this->createSale($event, $role, ['payment_amount' => 20, 'promo_code_id' => $code->id, 'discount_amount' => 5], $ticket);
        $this->createSale($event, $role, ['payment_amount' => 500, 'promo_code_id' => $code->id, 'discount_amount' => 50, 'is_deleted' => true], $ticket);

        $analytics = app(AnalyticsService::class);
        [$start, $end] = [now()->subDays(29)->startOfDay(), now()->endOfDay()];

        $top = $analytics->getTopEventsByRevenue($owner, 10, $start, $end);
        $this->assertCount(1, $top);
        $this->assertSame(20.0, $top[0]['revenue']);
        $this->assertSame(1, $top[0]['sales_count']);

        $promo = $analytics->getPromoCodeStats($owner, $start, $end);
        $this->assertCount(1, $promo);
        $this->assertSame(1, $promo[0]['sales_count']);
        $this->assertSame(5.0, $promo[0]['total_discount']);
    }

    /**
     * Seven days is today and the six before it, against the seven before those. A view on the
     * day that is exactly a week ago belongs to the EARLIER seven; it used to be counted in the
     * current ones, and the day two weeks ago in neither.
     * Mutation: start the range at subDays(7) in AnalyticsController, or the previous window at
     * subDays(14) in AnalyticsService::getPeriodComparison().
     */
    public function test_last_seven_days_is_seven_dates_on_both_sides(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        foreach ([0 => 1, 6 => 10, 7 => 100, 13 => 1000, 14 => 10000] as $daysAgo => $views) {
            AnalyticsDaily::create([
                'role_id' => $role->id, 'date' => now()->subDays($daysAgo)->toDateString(),
                'desktop_views' => $views, 'mobile_views' => 0, 'tablet_views' => 0, 'unknown_views' => 0,
            ]);
        }

        $comparison = $this->actingAs($owner)->get(route('analytics', ['range' => 'last_7_days']))
            ->assertOk()->viewData('periodComparison');

        $this->assertSame(11, (int) $comparison['current_period'], 'today and six days ago');
        $this->assertSame(1100, (int) $comparison['previous_period'], 'seven to thirteen days ago');
        $this->assertSame('vs_previous_7_days', $comparison['comparison_label']);
    }
}
