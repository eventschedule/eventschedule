<?php

namespace App\Utils;

/**
 * The maths behind the "So far today, against before" card of /admin/dashboard (RealtimeSoFar):
 * how much of a usual day has come in by a given time, and what today's total looks like from what
 * is in so far. No database, so the numbers can be checked by hand.
 *
 * Every comparison on that card is like with like: today until now against yesterday until the
 * same clock time, and against the same weekday of the four weeks before, each until that time.
 * Whether a gap is bigger than chance is said in the page (fairVerdict in admin/realtime/_script),
 * from the numbers this and RealtimeSoFar give it.
 */
class DayPace
{
    /** A shape counts a day's weight for this much: shrinks a small sample toward a flat day. */
    private const SHRINK = 100;

    /** A projection widens its range by this, for the day's shape being a guess. */
    private const PROJECTION_WIDEN = 1.25;

    /** Under this so far, today's total is not projected. */
    public const PROJECT_FROM = 15;

    /** Before a usual day has this share in, today's total is not projected. */
    public const PROJECT_SHARE = 0.25;

    /**
     * The share of a usual day's count that is in by `minutes` since midnight, from each local
     * hour's total over the reference days: smoothed around the clock, an empty hour lifted a
     * little for each week of data, and a small sample drawn toward a flat day.
     *
     * @param  list<int|float>  $weights  24 local hours' totals
     */
    public static function shape(array $weights, int $weeks, int $minutes): float
    {
        $minutes = max(0, min(1440, $minutes));
        $w = array_values(array_pad(array_slice($weights, 0, 24), 24, 0));
        $n = array_sum($w);
        $flat = $minutes / 1440;
        $hour = intdiv($minutes, 60);

        if ($hour >= 24) {
            return 1.0;
        }

        $smooth = [];
        for ($h = 0; $h < 24; $h++) {
            $smooth[$h] = ($w[($h + 23) % 24] + 2 * $w[$h] + $w[($h + 1) % 24]) / 4 + 0.5 * max(0, $weeks);
        }

        $sum = array_sum($smooth);
        if ($sum <= 0) {
            return $flat;
        }

        $by = array_sum(array_slice($smooth, 0, $hour)) + $smooth[$hour] * ($minutes % 60) / 60;
        $lambda = $n / ($n + self::SHRINK);

        return $lambda * ($by / $sum) + (1 - $lambda) * $flat;
    }

    /**
     * Today's total from `n` so far, when a usual day has `share` of its count in by now, with a
     * range. Hidden without a week of history, before a quarter of a usual day is in, or under
     * PROJECT_FROM so far.
     *
     * @return array{shown: bool, hidden: string|null, value: int|null, low: int|null, high: int|null}
     */
    public static function project(int $n, float $share, bool $history): array
    {
        $hidden = match (true) {
            ! $history => 'noHistory',
            $share < self::PROJECT_SHARE => 'early',
            $n < self::PROJECT_FROM => 'tooFew',
            default => null,
        };

        if ($hidden !== null || $share <= 0) {
            return ['shown' => false, 'hidden' => $hidden ?? 'early', 'value' => null, 'low' => null, 'high' => null];
        }

        $value = $n / $share;
        $range = 2 * sqrt($n * self::PROJECTION_WIDEN) / $share;

        return [
            'shown' => true,
            'hidden' => null,
            'value' => (int) round($value),
            // A running total cannot end below what is already in.
            'low' => (int) max($n, round($value - $range)),
            'high' => (int) round($value + $range),
        ];
    }

    /**
     * The month's total from `n` so far, `elapsed` days into a month of `length` (the days before
     * today and today's usual share). Hidden before three days are in or under 30 so far: a
     * month read from its first two days is mostly the weekday it began on.
     *
     * @return array{shown: bool, hidden: string|null, value: int|null, low: int|null, high: int|null}
     */
    public static function projectMonth(int $n, float $elapsed, int $length, bool $history): array
    {
        $hidden = match (true) {
            ! $history => 'noHistory',
            $elapsed < 3 => 'early',
            $n < 30 => 'tooFew',
            default => null,
        };

        if ($hidden !== null || $length <= 0) {
            return ['shown' => false, 'hidden' => $hidden ?? 'early', 'value' => null, 'low' => null, 'high' => null];
        }

        $share = $elapsed / $length;
        $value = $n / $share;
        $range = 2 * sqrt($n * self::PROJECTION_WIDEN) / $share;

        return ['shown' => true, 'hidden' => null, 'value' => (int) round($value), 'low' => (int) max($n, round($value - $range)), 'high' => (int) round($value + $range)];
    }

    /**
     * The first whole hour by which a usual day has `share` of its count in: when a projection
     * that is hidden for being early will show. Null if it never does.
     *
     * @param  list<int|float>  $weights
     */
    public static function reaches(array $weights, int $weeks, float $share): ?int
    {
        // Midnight is not a time it can show from today: shape() is 1 there for any day.
        for ($hour = 1; $hour < 24; $hour++) {
            if (self::shape($weights, $weeks, $hour * 60) >= $share) {
                return $hour;
            }
        }

        return null;
    }

    /**
     * A day's count up to `hour`:`minute`, from its 24 hourly counts: the hours before, and the
     * hour under way by its minutes.
     *
     * @param  list<int|float>  $hours
     */
    public static function byTime(array $hours, int $hour, int $minute): float
    {
        return (float) array_sum(array_slice($hours, 0, $hour)) + ($hours[$hour] ?? 0) * $minute / 60;
    }

    /**
     * @param  list<int|float|null>  $counts
     * @return list<float|null>
     */
    public static function running(array $counts): array
    {
        $total = 0.0;

        return array_map(function ($n) use (&$total) {
            if ($n === null) {
                return null;
            }
            $total += $n;

            return round($total, 1);
        }, $counts);
    }
}
