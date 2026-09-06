<?php

namespace Tests\Feature;

use App\Models\AnalyticsDaily;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The dashboard Views panel took its NUMBER from a rolling 7/14/30-day window but its PERCENTAGE
 * from getMonthOverMonthComparison(), which compares calendar-month-to-date against the whole of
 * the previous calendar month and accepts no period argument. The two never described the same
 * span, and because "this month so far" is measured against a full month, the badge read heavily
 * negative early in every month for arithmetic reasons rather than traffic ones - roughly -80% on
 * the 6th, whatever the underlying trend.
 *
 * Two things need pinning, and the second is easy to get wrong: the comparison must follow the
 * panel's own period, AND each period's previous window must be the right length. An earlier
 * version of this test asserted only the label for the 14-day case, which left the arithmetic
 * free - a 7-day previous window passed it.
 *
 * Fixture design, all against the frozen clock:
 *
 *   Sep 1  100  inside BOTH the 7-day and 14-day current windows
 *   Aug 25  40  the 7-day previous window (Aug 23-29); also inside the 14-day CURRENT window
 *   Aug 15  50  the 14-day previous window (Aug 9-22) only
 *   Aug 5 1000  outside every comparison window, inside the previous calendar MONTH
 *
 * So the 7-day answer is +150% and the 14-day answer is +180% - deliberately different from each
 * other, and neither is 100, which getPeriodComparison returns as a sentinel whenever the previous
 * window is empty. Month-over-month on the same data is about -90.8%, the opposite sign.
 */
class DashboardViewsComparisonTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Early in a month on purpose: that is where month-to-date vs a whole previous month is
        // most badly skewed, and it is the date the reported screenshot was taken on.
        Carbon::setTestNow(Carbon::parse('2026-09-06 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function seedViews(User $user, int $period): void
    {
        $role = $this->createRole($user);

        foreach ([['2026-09-01', 100], ['2026-08-25', 40], ['2026-08-15', 50], ['2026-08-05', 1000]] as [$date, $views]) {
            AnalyticsDaily::create([
                'role_id' => $role->id,
                'date' => $date,
                'desktop_views' => $views,
                'mobile_views' => 0,
                'tablet_views' => 0,
                'unknown_views' => 0,
            ]);
        }

        $user->dashboard_config = ['panels' => [
            ['id' => 'views', 'visible' => true, 'size' => 1, 'period' => $period],
        ]];
        $user->save();
    }

    public function test_the_change_compares_the_panels_own_window_not_calendar_months(): void
    {
        $user = $this->createOwner();
        $this->seedViews($user, 7);

        $response = $this->actingAs($user)->get(route('home'));
        $response->assertOk();

        // Aug 30 - Sep 6 holds only Sep 1; Aug 23 - Aug 29 only Aug 25. Month over month would be
        // (100 - 1090) / 1090 = -90.8%.
        $this->assertSame(100, (int) $response->viewData('viewsInPeriod'));
        $this->assertSame(150.0, (float) $response->viewData('viewsChange'));
    }

    public function test_the_label_names_the_window_that_was_compared(): void
    {
        $user = $this->createOwner();
        $this->seedViews($user, 7);

        $response = $this->actingAs($user)->get(route('home'));

        $this->assertSame('vs_previous_7_days', $response->viewData('viewsChangeLabel'));
        $response->assertSee(__('messages.last_7_days'));
    }

    /**
     * 14 was the period with no arm in getPeriodComparison()'s match, so it fell through to the
     * 30-day default. Asserting the NUMBER is what pins the window's length: the label alone
     * passes even when the arm compares the wrong span.
     */
    public function test_the_fourteen_day_period_compares_a_fourteen_day_window(): void
    {
        $user = $this->createOwner();
        $this->seedViews($user, 14);

        $response = $this->actingAs($user)->get(route('home'));

        // Aug 23 - Sep 6 holds Sep 1 and Aug 25; Aug 9 - Aug 22 holds only Aug 15.
        $this->assertSame(140, (int) $response->viewData('viewsInPeriod'));
        $this->assertSame(180.0, (float) $response->viewData('viewsChange'));
        $this->assertSame('vs_previous_14_days', $response->viewData('viewsChangeLabel'));
    }

    /**
     * An out-of-range period would build a range key with no match arm (silently comparing the
     * 30-day window and mislabelling it) and no messages.last_N_days translation (rendering the
     * raw key in the card footer). getDashboardConfig only int-casts what it reads back.
     */
    public function test_an_out_of_range_stored_period_falls_back_to_thirty_days(): void
    {
        $user = $this->createOwner();
        $this->seedViews($user, 7);
        $user->dashboard_config = ['panels' => [
            ['id' => 'views', 'visible' => true, 'size' => 1, 'period' => 60],
        ]];
        $user->save();

        $response = $this->actingAs($user)->get(route('home'));
        $response->assertOk();

        $this->assertSame('vs_previous_30_days', $response->viewData('viewsChangeLabel'));
        $response->assertSee(__('messages.last_30_days'));
        $response->assertDontSee('messages.last_60_days');
    }
}
