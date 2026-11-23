<?php

namespace App\Support;

/**
 * Laravel's plural selector, with one addition: a string may give exact forms ({0}, [2,19]) AND
 * the language's own forms without a condition, and the plain ones are then counted by the
 * language's rule. Laravel strips the conditions and counts every segment, so in
 * "{0} None|:count item|:count items" a 1 picked "None".
 *
 * That is what Russian and Romanian need: their forms follow the last digits (21 is like 1, 22
 * like 2, 101 in Romanian like 1), which no list of ranges can say. A string with only exact
 * forms, or only plain ones, reads exactly as before. resources/js/plural.js is the same rule
 * for the strings the vendor map counts in the browser.
 */
class PluralSelector extends RegionalMessageSelector
{
    private const EXACT = '/^\s*[\{\[]([-?\d|*,\.*]*)[\}\]](.*)/s';

    public function choose($line, $number, $locale)
    {
        $segments = explode('|', $line);
        $plain = array_values(array_filter($segments, fn ($s) => preg_match(self::EXACT, $s) !== 1));

        if ($plain === [] || count($plain) === count($segments)) {
            return parent::choose($line, $number, $locale);
        }

        foreach ($segments as $segment) {
            if (($value = $this->exact($segment, $number)) !== null) {
                return trim($value);
            }
        }

        $index = $this->getPluralIndex($locale, $number);

        return trim($plain[$index] ?? $plain[count($plain) - 1]);
    }

    private function exact(string $segment, $number): ?string
    {
        if (preg_match(self::EXACT, $segment, $m) !== 1) {
            return null;
        }
        [$condition, $value] = [$m[1], $m[2]];

        if (str_contains($condition, ',')) {
            [$from, $to] = explode(',', $condition, 2);

            if (($to === '*' && $number >= $from) || ($from === '*' && $number <= $to) || ($number >= $from && $number <= $to)) {
                return $value;
            }

            return null;
        }

        return $condition == $number ? $value : null;
    }
}
