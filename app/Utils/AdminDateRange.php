<?php

namespace App\Utils;

use Carbon\Carbon;

/**
 * The reporting windows behind the admin date-range filter (last 7/30/90 days, all time), with
 * the equal-length window before each for period-over-period changes.
 *
 * One definition shared by every /admin page, the growth endpoint and app:export-growth, so a
 * `range` means the same window wherever it is passed. The CLI used to work out its own previous
 * window (a day short of this one), so the same "last 30 days" produced a different
 * first_event_conv_change depending on where the export was taken.
 */
class AdminDateRange
{
    public const RANGES = ['last_7_days', 'last_30_days', 'last_90_days', 'all_time'];

    /**
     * Anything unrecognised, including a non-string (`?range[]=x` arrives as an array, and used to
     * TypeError into a 500 on every admin page), falls back to all time.
     *
     * @return array{start: Carbon, end: Carbon, previous_start: Carbon, previous_end: Carbon}
     */
    public static function for(mixed $range): array
    {
        $now = now();
        $days = match (is_string($range) ? $range : null) {
            'last_7_days' => 7,
            'last_30_days' => 30,
            'last_90_days' => 90,
            default => null,
        };

        if ($days === null) {
            $start = Carbon::createFromDate(2020, 1, 1)->startOfDay();

            return [
                'start' => $start,
                'end' => $now->copy()->endOfDay(),
                'previous_start' => $start->copy(),
                'previous_end' => $start->copy(),
            ];
        }

        $start = $now->copy()->subDays($days)->startOfDay();

        return [
            'start' => $start,
            'end' => $now->copy()->endOfDay(),
            'previous_start' => $start->copy()->subDays($days),
            'previous_end' => $start->copy()->subSecond(),
        ];
    }
}
