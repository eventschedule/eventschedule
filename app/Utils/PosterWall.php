<?php

namespace App\Utils;

/**
 * Rows for a wall of posters that are each shown whole.
 *
 * A flyer is a 9:16 story, a 3:4 poster, a square or a 2:1 banner, and a grid of equal boxes has
 * to crop every one of them to fit. This lays them out the way a picture editor sets a spread
 * instead: in each row a poster's width is its own shape's share of the row, so every poster in
 * the row comes out the same height with nothing cut off and the row is flush at both ends.
 *
 * It only DECIDES the rows. The page draws them with flexbox: a poster is `flex: <ratio> 1 0%`
 * over a box of `aspect-ratio: <ratio>`, which is exactly that arithmetic, and a row ends where
 * this says it does (a zero-height, full-width element the stylesheet shows at one breakpoint
 * only). No script, no measuring, and nothing moves when a picture arrives, because the shapes
 * are known before the first byte of any image.
 *
 * How full a row is. Two honest measures pull opposite ways. Count the ratios (a row is full when
 * they sum to the target) and every row is the same height, but a 2:1 banner is twice the size of
 * the poster beside it and a row of banners owns the wall. Count area (sqrt(n * sum), so a row of
 * banners holds fewer and is shorter) and every poster gets its share, but the rows run from
 * squat to towering. This takes a point between, n^(1 - LEAN) * sum^LEAN, which keeps rows
 * within about a sixth of one height while a banner is a third larger than a square, not double.
 *
 * Nothing here knows a pixel: the same rows hold at any window width inside a breakpoint.
 *
 * Three kinds of tile. A poster has a ratio. A poster with a `stretch` (the blank one the wall
 * ends on, which has no picture to be true to) takes whichever ratio in its range fills its row
 * best. A label (the word a stretch of time opens with) is as wide as its own lettering, which
 * this only needs to know roughly: it is never the last thing in a row unless it is the last
 * tile of all, and on a phone it is a row of its own.
 *
 * Every row is flush. Only a last row too thin to be stretched is left short, at the height a
 * full row of its shapes would have.
 */
class PosterWall
{
    /** Narrower or wider than this and the picture is fitted inside the nearest shape allowed. */
    public const MIN_RATIO = 0.5;

    public const MAX_RATIO = 2.4;

    /** What a picture of unknown shape is given: the box it is then fitted inside. */
    public const UNKNOWN_RATIO = 1.0;

    /** A label's width, in squares. Only an estimate for the sums; the page sizes it by its text. */
    public const LABEL = 0.2;

    /** Where between equal area (0.5) and equal height (1.0) a row's fullness is measured. */
    public const LEAN = 0.7;

    /** target: squares to a row. most: posters to a row. solo: a label is a row of its own. */
    public const PROFILES = [
        'm' => ['target' => 1.65, 'most' => 2, 'solo' => true],
        't' => ['target' => 2.6, 'most' => 5, 'solo' => false],
        'l' => ['target' => 3.8, 'most' => 7, 'solo' => false],
        'd' => ['target' => 4.8, 'most' => 8, 'solo' => false],
        'x' => ['target' => 6.3, 'most' => 10, 'solo' => false],
    ];

    /** A row may be this much fuller than its target before it is refused. */
    private const OVER = 1.25;

    /** A last row thinner than this share of its target is left short instead of stretched. */
    private const SHORT = 0.8;

    public static function clampRatio(?float $ratio): float
    {
        if (! $ratio || $ratio <= 0) {
            return self::UNKNOWN_RATIO;
        }

        return round(max(self::MIN_RATIO, min(self::MAX_RATIO, $ratio)), 3);
    }

    /**
     * The rows for every profile.
     *
     * @param  array<int, array{ratio?: float, stretch?: array{0: float, 1: float}, label?: bool}>  $tiles
     * @return array<string, array{ends: int[], pad: float, share: array<int, float>, ratio: array<int, float>}>
     */
    public static function layout(array $tiles): array
    {
        $out = [];

        foreach (array_keys(self::PROFILES) as $profile) {
            $out[$profile] = self::rows($tiles, $profile);
        }

        return $out;
    }

    /**
     * ends: the index of the last tile of each row. pad: the empty width, in squares, a short
     * last row is left with (0 otherwise). share: each poster's part of its row's width, 0 to 1,
     * for the `sizes` of its picture. ratio: the shape each stretching tile was given.
     *
     * @param  array<int, array{ratio?: float, stretch?: array{0: float, 1: float}, label?: bool}>  $tiles
     * @return array{ends: int[], pad: float, share: array<int, float>, ratio: array<int, float>}
     */
    public static function rows(array $tiles, string $profile): array
    {
        $tiles = array_values($tiles);
        $count = count($tiles);
        $rules = self::PROFILES[$profile];

        if ($count === 0) {
            return ['ends' => [], 'pad' => 0.0, 'share' => [], 'ratio' => []];
        }

        // best[i]: the least cost of laying out tiles i to the end, and where its first row ends.
        $best = array_fill(0, $count + 1, INF);
        $next = array_fill(0, $count + 1, $count);
        $best[$count] = 0.0;

        for ($i = $count - 1; $i >= 0; $i--) {
            for ($j = $i + 1; $j <= $count; $j++) {
                $row = self::row($tiles, $i, $j, $rules);

                if ($row === null) {
                    continue;
                }

                if ($best[$i] > $row['cost'] + $best[$j]) {
                    $best[$i] = $row['cost'] + $best[$j];
                    $next[$i] = $j;
                }

                if ($row['full']) {
                    // Anything longer is fuller still.
                    break;
                }
            }
        }

        $ends = [];
        $share = [];
        $ratio = [];
        $pad = 0.0;
        $from = 0;

        while ($from < $count) {
            // Never an empty row, whatever the search above left behind.
            $to = max($from + 1, min($next[$from], $count));
            $row = self::row($tiles, $from, $to, $rules);

            if ($row === null || is_infinite($row['cost'])) {
                // Nothing the rules allow starts here: one tile to the row.
                $to = $from + 1;
                $row = self::row($tiles, $from, $to, $rules, true);
            }

            $ends[] = $to - 1;
            $pad = $to === $count ? $row['pad'] : 0.0;

            foreach ($row['widths'] as $index => $width) {
                $share[$index] = round($width / ($row['sum'] + $row['pad']), 4);

                if (isset($tiles[$index]['stretch'])) {
                    $ratio[$index] = round($width, 3);
                }
            }

            $from = $to;
        }

        return ['ends' => $ends, 'pad' => round($pad, 3), 'share' => $share, 'ratio' => $ratio];
    }

    /**
     * One candidate row, tiles $from up to but not including $to. Null when the rules refuse it
     * outright, and a cost of INF when it is merely too full.
     *
     * @return array{cost: float, widths: array<int, float>, sum: float, pad: float, full: bool}|null
     */
    private static function row(array $tiles, int $from, int $to, array $rules, bool $force = false): ?array
    {
        $target = $rules['target'];
        $sum = 0.0;
        $labels = 0.0;
        $posters = 0;
        $cost = 0.0;
        $widths = [];
        $stretch = null;
        $last = $to === count($tiles);

        for ($k = $from; $k < $to; $k++) {
            if (! empty($tiles[$k]['label'])) {
                if ($rules['solo']) {
                    if ($to - $from > 1) {
                        // Its neighbours are someone else's row. Longer rows from here are no
                        // better, which `full` says.
                        return $k === $from ? ['cost' => INF, 'widths' => [], 'sum' => 1.0, 'pad' => 0.0, 'full' => true] : null;
                    }

                    // On its own row and as tall as its words: the stylesheet gives it the width.
                    return ['cost' => 0.0, 'widths' => [], 'sum' => 1.0, 'pad' => 0.0, 'full' => true];
                }

                // A label is never the last thing in a row: that would put it at the far end of
                // the row above the posters it names. Mid-row is allowed, and costs a little.
                if ($k === $to - 1 && ! $force && ! $last) {
                    return null;
                }

                $labels += self::LABEL;
                $cost += $k > $from ? 0.03 : 0.0;
            } else {
                if (isset($tiles[$k]['stretch'])) {
                    $stretch = $k;
                } else {
                    $sum += $tiles[$k]['ratio'];
                }

                $widths[$k] = $tiles[$k]['ratio'];
                $posters++;
            }
        }

        if ($posters === 0) {
            return ($force || $last) ? ['cost' => $last ? 0.5 : 0.0, 'widths' => [], 'sum' => 1.0, 'pad' => 0.0, 'full' => true] : null;
        }

        // The width at which this many posters fill a row exactly.
        $fill = ($target / ($posters ** (1 - self::LEAN))) ** (1 / self::LEAN);

        if ($stretch !== null) {
            // The one tile with no shape of its own takes up the slack, within its range.
            [$low, $high] = $tiles[$stretch]['stretch'];

            if ($posters === 1 && $rules['solo']) {
                // Alone on a phone's row it has the whole width to itself, as a strip.
                $high = max($high, $fill);
            }

            $widths[$stretch] = max($low, min($high, $fill - $sum - $labels));
            $sum += $widths[$stretch];
        }

        $measure = ($posters ** (1 - self::LEAN)) * (($sum + $labels) ** self::LEAN);
        $over = $posters > 1 && ($measure > $target * self::OVER || $posters > $rules['most']);

        if ($over && ! $force) {
            return ['cost' => INF, 'widths' => $widths, 'sum' => $sum + $labels, 'pad' => 0.0, 'full' => true];
        }

        $pad = 0.0;
        $off = (($measure - $target) / $target) ** 2;

        if ($last && $measure < $target * self::SHORT) {
            // Too thin to stretch: its posters at the height a full row of them would have, and
            // the rest of the row left empty. Cheaper than the same few posters stretched across
            // a row higher up, so what is left over ends the wall instead of opening it; still
            // costed, so the rows above give the last one something where they can.
            $pad = max(0.0, $fill - $sum - $labels);
            $cost += 0.02 + $off / 2;
        } else {
            $cost += $off;
        }

        return [
            'cost' => $cost,
            'widths' => $widths,
            'sum' => $sum + $labels,
            'pad' => $pad,
            'full' => $posters >= $rules['most'] || $measure > $target * self::OVER,
        ];
    }
}
