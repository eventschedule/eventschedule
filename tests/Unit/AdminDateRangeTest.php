<?php

namespace Tests\Unit;

use App\Utils\AdminDateRange;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * The window behind every admin `range`, the growth endpoint and app:export-growth. One definition,
 * so the same range is the same window wherever the payload is taken.
 */
class AdminDateRangeTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_each_range_has_an_equal_window_before_it(): void
    {
        Carbon::setTestNow('2026-10-01 15:30:00');

        foreach (['last_7_days' => 7, 'last_30_days' => 30, 'last_90_days' => 90] as $range => $days) {
            $w = AdminDateRange::for($range);

            $this->assertSame(now()->subDays($days)->startOfDay()->toDateTimeString(), $w['start']->toDateTimeString(), $range);
            $this->assertSame(now()->endOfDay()->toDateTimeString(), $w['end']->toDateTimeString(), $range);
            $this->assertSame($w['start']->copy()->subDays($days)->toDateTimeString(), $w['previous_start']->toDateTimeString(), $range);
            // Ends the second before the window opens, so the two never share a moment.
            $this->assertSame($w['start']->copy()->subSecond()->toDateTimeString(), $w['previous_end']->toDateTimeString(), $range);
        }
    }

    /** `?range[]=x` arrives as an array; it used to TypeError into a 500 on every admin page. */
    public function test_anything_unrecognised_is_all_time(): void
    {
        foreach (['all_time', 'yesterday', '', null, ['x'], 30] as $range) {
            $w = AdminDateRange::for($range);

            $this->assertSame('2020-01-01', $w['start']->toDateString(), var_export($range, true));
            $this->assertTrue($w['previous_start']->equalTo($w['previous_end']), 'all time has no earlier period');
        }
    }
}
