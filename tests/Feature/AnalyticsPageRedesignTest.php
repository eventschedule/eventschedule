<?php

namespace Tests\Feature;

use App\Models\AnalyticsDaily;
use App\Models\AnalyticsEventsDaily;
use App\Models\AnalyticsReferrersDaily;
use App\Services\AnalyticsService;
use App\Utils\AnalyticsGrid;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The /analytics page as redesigned in 2026-10: one card of figures and chart per tab, ranked
 * lists under it. What is held here is what the page says is TRUE: a chart with a point for every
 * day, a comparison against the same days before, a revenue chart that adds up to the revenue
 * beside it, numbers that are the schedule's never laid over one event's, and a grid with no hole.
 */
class AnalyticsPageRedesignTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Carbon::setTestNow(Carbon::parse('2026-10-15 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function views($role, string $date, int $count): void
    {
        AnalyticsDaily::create(['role_id' => $role->id, 'date' => $date, 'desktop_views' => $count, 'mobile_views' => 0, 'tablet_views' => 0, 'unknown_views' => 0]);
    }

    public function test_every_row_of_the_card_grid_is_full(): void
    {
        // Columns of the six a card takes: two to a row (from a tablet), three to a row (1440px).
        $widths = [
            2 => fn (string $classes) => str_contains($classes, 'an-w2-full') ? 6 : 3,
            3 => fn (string $classes) => str_contains($classes, 'an-w3-full') ? 6 : (str_contains($classes, 'an-w3-half') ? 3 : 2),
        ];
        for ($count = 1; $count <= 9; $count++) {
            foreach ($widths as $perRow => $width) {
                $row = 0;
                foreach (AnalyticsGrid::spans($count) as $i => $classes) {
                    $row += $width($classes);
                    $this->assertLessThanOrEqual(6, $row, "$count cards, $perRow to a row: card $i overflows its row");
                    if ($row === 6) {
                        $row = 0;
                    }
                }
                $this->assertSame(0, $row, "$count cards, $perRow to a row: the last row is short");
            }
        }
    }

    public function test_a_series_has_every_period_and_its_weeks_are_mysqls(): void
    {
        [$keys, $values] = AnalyticsService::fillPeriods(['2026-10-02' => 5], 'daily', Carbon::parse('2026-10-01'), Carbon::parse('2026-10-04')->endOfDay());
        $this->assertSame(['2026-10-01', '2026-10-02', '2026-10-03', '2026-10-04'], $keys);
        $this->assertSame([0.0, 5.0, 0.0, 0.0], $values, 'A day with no row is a zero, not a gap the line jumps.');

        // Across a year boundary: 2025-12-29 is in week 1 of 2026.
        [$weeks] = AnalyticsService::fillPeriods([], 'weekly', Carbon::parse('2025-12-24'), Carbon::parse('2026-01-12'));
        $this->assertSame(['2025-52', '2026-01', '2026-02', '2026-03'], $weeks);
        foreach (['2025-12-24' => '2025-52', '2025-12-29' => '2026-01', '2026-01-12' => '2026-03'] as $date => $week) {
            $this->assertSame($week, DB::selectOne("SELECT DATE_FORMAT(?, '%x-%v') AS k", [$date])->k, 'The key MySQL groups a day under must be the one the chart lays out.');
        }

        [$months] = AnalyticsService::fillPeriods([], 'monthly', Carbon::parse('2026-09-16'), Carbon::parse('2026-10-15'));
        $this->assertSame(['2026-09', '2026-10'], $months);
    }

    public function test_this_month_and_this_year_are_compared_with_the_same_days_before(): void
    {
        $service = app(AnalyticsService::class);

        Carbon::setTestNow(Carbon::parse('2026-10-03 12:00:00', 'UTC'));
        [$from, $to, $label] = $service->previousWindow('this_month');
        $this->assertSame('2026-09-01 2026-09-03', $from->toDateString().' '.$to->toDateString(), 'Three days of October against all of September read about -90%.');
        $this->assertSame('an_vs_month_to_date', $label);

        Carbon::setTestNow(Carbon::parse('2026-10-31 12:00:00', 'UTC'));
        [, $to] = $service->previousWindow('this_month');
        $this->assertSame('2026-09-30', $to->toDateString());

        Carbon::setTestNow(Carbon::parse('2028-02-29 12:00:00', 'UTC'));
        [$from, $to, $label] = $service->previousWindow('this_year');
        $this->assertSame('2027-01-01 2027-02-28', $from->toDateString().' '.$to->toDateString());
        $this->assertSame('an_vs_year_to_date', $label);

        // The rolling ranges are unchanged: N days against the N before.
        Carbon::setTestNow(Carbon::parse('2026-10-15 12:00:00', 'UTC'));
        [$from, $to] = $service->previousWindow('last_7_days');
        $this->assertSame('2026-10-02 2026-10-08', $from->toDateString().' '.$to->toDateString());
    }

    public function test_the_chart_has_every_day_with_the_window_before_under_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->views($role, '2026-10-01', 40);
        $this->views($role, '2026-10-14', 60);
        $this->views($role, '2026-09-01', 25);

        $chart = $this->actingAs($owner)->get(route('analytics', ['role_id' => UrlUtils::encodeId($role->id)]))
            ->assertOk()
            ->assertSee('id="analytics-tabs"', false)
            ->viewData('viewsChart');

        $this->assertCount(30, $chart['keys']);
        $this->assertSame('2026-09-16', $chart['keys'][0]);
        $this->assertSame('2026-10-15', $chart['keys'][29]);
        $this->assertEquals(100, array_sum($chart['views']));
        $this->assertCount(30, $chart['previous'], 'The grey line is laid day for day under the current one.');
        $this->assertEquals(25, array_sum($chart['previous']));
    }

    public function test_schedule_numbers_are_not_laid_over_one_event(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'starts_at' => '2026-11-01 20:00:00']);
        $this->views($role, '2026-10-10', 50);
        AnalyticsEventsDaily::create(['event_id' => $event->id, 'date' => '2026-10-10', 'desktop_views' => 10, 'mobile_views' => 0, 'tablet_views' => 0, 'unknown_views' => 0]);
        AnalyticsReferrersDaily::create(['role_id' => $role->id, 'date' => '2026-10-10', 'source' => 'boost', 'domain' => null, 'views' => 30]);
        // The boost line is drawn while a campaign ran in the window.
        (new \App\Models\BoostCampaign)->forceFill(['event_id' => $event->id, 'role_id' => $role->id, 'user_id' => $owner->id, 'channel' => 'meta', 'name' => 'Launch', 'status' => 'active', 'currency_code' => 'USD', 'user_budget' => 50, 'markup_rate' => 0, 'scheduled_start' => now()->subDays(8)])->save();
        $rid = UrlUtils::encodeId($role->id);

        $this->actingAs($owner)->get(route('analytics', ['role_id' => $rid]))
            ->assertOk()
            ->assertSee('"id":"boost"', false)
            ->assertDontSee(__('messages.an_whole_schedule_cards'));

        // The boost line counts the schedule's views: above one event's own line it read as
        // more boost visits than the event had visits.
        $this->actingAs($owner)->get(route('analytics', ['role_id' => $rid, 'event_id' => UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->assertDontSee('"id":"boost"', false)
            ->assertSee(__('messages.an_whole_schedule_cards'))
            ->assertSee(__('messages.an_whole_schedule'));
    }

    public function test_a_top_event_opens_with_its_schedule_selected(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'starts_at' => '2026-11-01 20:00:00']);
        $this->views($role, '2026-10-10', 50);
        AnalyticsEventsDaily::create(['event_id' => $event->id, 'date' => '2026-10-10', 'desktop_views' => 10, 'mobile_views' => 0, 'tablet_views' => 0, 'unknown_views' => 0]);

        // All schedules: an event filter alone would have no picker on screen to say it is there.
        $html = $this->actingAs($owner)->get(route('analytics'))->assertOk()->getContent();
        $this->assertStringContainsString(e(route('analytics', ['role_id' => UrlUtils::encodeId($role->id), 'event_id' => UrlUtils::encodeId($event->id), 'range' => 'last_30_days'])), $html);
    }

    public function test_a_revenue_row_opens_with_its_schedule_selected(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
        $this->createSale($event, $role, ['payment_amount' => 40], $this->createTicket($event, ['price' => 40]));

        $html = $this->actingAs($owner)->get(route('analytics', ['tab' => 'revenue']))->assertOk()->getContent();

        $this->assertStringContainsString(e(route('analytics', ['role_id' => UrlUtils::encodeId($role->id), 'event_id' => UrlUtils::encodeId($event->id), 'range' => 'last_30_days', 'tab' => 'revenue'])), $html);
    }

    public function test_chart_marks_leave_out_series_drafts_bookings_and_requests(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['timezone' => 'America/New_York']);
        $at = ['creator_role_id' => $role->id, 'starts_at' => '2026-10-10 23:00:00'];
        $this->createEvent($role, $at + ['name' => 'One Night']);
        // 9pm in New York on the 8th, 01:00 UTC on the 9th.
        $this->createEvent($role, ['creator_role_id' => $role->id, 'starts_at' => '2026-10-09 01:00:00', 'name' => 'Late Show']);
        $this->createEvent($role, $at + ['name' => 'Every Week', 'days_of_week' => '0000010']);
        $this->createEvent($role, $at + ['name' => 'Not Yet', 'is_draft' => true]);
        $type = $this->createAppointmentType($role);
        $this->createEvent($role, $at + ['name' => 'Consultation - Dana Whitlock', 'appointment_type_id' => $type->id]);
        // Somebody else's event, asked of this venue and not answered.
        $promoter = $this->createRole($this->createOwner(), 'talent');
        $request = $this->createEvent($promoter, ['creator_role_id' => $promoter->id, 'starts_at' => '2026-10-12 23:00:00', 'name' => 'Asked, Not Answered']);
        $request->roles()->attach($role->id, ['is_accepted' => null]);

        $events = app(AnalyticsService::class)->getChartEvents($owner, now()->subDays(29)->startOfDay(), now()->endOfDay(), $role->id);

        // A booking is an event named after its guest; a series would mark every week; a
        // request was never on the venue's page.
        $this->assertSame(['Late Show', 'One Night'], array_column($events, 'name'));
        $this->assertSame(['2026-10-08', '2026-10-10'], array_column($events, 'date'), 'A date is the event\'s own, in its schedule\'s time.');
    }

    public function test_last_month_is_last_month_on_the_thirtieth_and_thirty_first(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->views($role, '2026-09-10', 5);
        $url = route('analytics', ['role_id' => UrlUtils::encodeId($role->id), 'range' => 'last_month']);

        // subMonth() on Oct 31 is Oct 1: "last month" was October, compared with August.
        Carbon::setTestNow(Carbon::parse('2026-10-31 12:00:00', 'UTC'));
        $keys = $this->actingAs($owner)->get($url)->assertOk()->viewData('viewsChart')['keys'];
        $this->assertSame(['2026-09-01', '2026-09-30'], [$keys[0], end($keys)]);
        [$from, $to] = app(AnalyticsService::class)->previousWindow('last_month');
        $this->assertSame('2026-08-01 2026-08-31', $from->toDateString().' '.$to->toDateString());

        // Feb 30 is Mar 2: on Mar 30 "last month" was March.
        Carbon::setTestNow(Carbon::parse('2026-03-30 12:00:00', 'UTC'));
        $keys = $this->actingAs($owner)->get($url)->assertOk()->viewData('viewsChart')['keys'];
        $this->assertSame(['2026-02-01', '2026-02-28'], [$keys[0], end($keys)]);

        // And the month before last: on Apr 30, two months back is "Feb 30", which was March.
        Carbon::setTestNow(Carbon::parse('2026-04-30 12:00:00', 'UTC'));
        [$from, $to] = app(AnalyticsService::class)->previousWindow('last_month');
        $this->assertSame('2026-02-01 2026-02-28', $from->toDateString().' '.$to->toDateString());
    }

    public function test_the_revenue_chart_adds_up_to_the_figure_and_needs_one_currency(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
        $ticket = $this->createTicket($event, ['price' => 25]);
        foreach (['2026-10-02 10:00:00' => 50, '2026-10-12 18:00:00' => 25] as $when => $amount) {
            $sale = $this->createSale($event, $role, ['payment_amount' => $amount], $ticket, $amount / 25);
            DB::table('sales')->where('id', $sale->id)->update(['created_at' => $when]);
        }
        $url = route('analytics', ['role_id' => UrlUtils::encodeId($role->id), 'tab' => 'revenue']);

        $response = $this->actingAs($owner)->get($url)->assertOk();
        $chart = $response->viewData('revenueChart');
        $this->assertSame('USD', $chart['currency']);
        $this->assertCount(30, $chart['keys']);
        $this->assertEquals($response->viewData('conversionStats')['total_revenue'], array_sum($chart['values']));

        // A second currency: a bar cannot add euros to dollars.
        $euros = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true, 'ticket_currency_code' => 'EUR']);
        $this->createSale($euros, $role, ['payment_amount' => 30], $this->createTicket($euros, ['price' => 30]));
        $this->assertNull($this->actingAs($owner)->get($url)->assertOk()->viewData('revenueChart'));
    }

    public function test_each_tab_says_what_will_be_there_when_there_is_nothing(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue');

        foreach ([null => 'no_analytics_data', 'revenue' => 'no_revenue_data', 'checkins' => 'no_checkin_data'] as $tab => $key) {
            $this->actingAs($owner)->get(route('analytics', array_filter(['tab' => $tab])))
                ->assertOk()
                ->assertSee('an-ghost', false)
                ->assertSee(__('messages.'.$key));
        }
    }
}
