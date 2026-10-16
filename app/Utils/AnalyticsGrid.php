<?php

namespace App\Utils;

/**
 * The cards under the Web Analytics chart stand in a grid of six columns: a card takes three of
 * them (two to a row) from a tablet, and two (three to a row) from a 1440px window. Which cards
 * are there depends on the data, so the last row would often be short and leave a hole at the
 * bottom right. These are the classes that widen the last cards of such a row so every row is
 * full: in two columns an odd last card takes the row (an-w2-full); in three, a row of two shares
 * it by halves, a lone card takes it whole, and four left over become two rows of two
 * (an-w3-half, an-w3-full). The widths themselves are in analytics/_styles.
 */
class AnalyticsGrid
{
    /**
     * @return string[] the extra classes of each card, by position
     */
    public static function spans(int $count): array
    {
        $spans = [];
        $rest = $count % 3;

        for ($i = 0; $i < $count; $i++) {
            $classes = [];

            if ($count % 2 === 1 && $i === $count - 1) {
                $classes[] = 'an-w2-full';
            }

            if ($count === 1) {
                $classes[] = 'an-w3-full';
            } elseif ($rest === 2 && $i >= $count - 2) {
                $classes[] = 'an-w3-half';
            } elseif ($rest === 1 && $i >= $count - 4) {
                $classes[] = 'an-w3-half';
            }

            $spans[] = implode(' ', $classes);
        }

        return $spans;
    }
}
