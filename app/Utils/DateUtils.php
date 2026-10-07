<?php

namespace App\Utils;

class DateUtils
{
    /**
     * Returns a valid month (1-12), defaulting to the current month when the
     * input is missing, non-numeric, or out of range.
     */
    public static function normalizeMonth($value): int
    {
        $month = is_numeric($value) ? (int) $value : 0;

        return ($month >= 1 && $month <= 12) ? $month : now()->month;
    }

    /**
     * Returns a sane year (1970-9999), defaulting to the current year when the
     * input is missing, non-numeric, or out of range.
     */
    public static function normalizeYear($value): int
    {
        $year = is_numeric($value) ? (int) $value : 0;

        return ($year >= 1970 && $year <= 9999) ? $year : now()->year;
    }

    /**
     * A day in words, in the order the language itself puts them: "Friday, October 9" in
     * English, "vendredi 9 octobre" in French, "Freitag, 9. Oktober" in German. The year is
     * added when it is not this one.
     *
     * Carbon's translatedFormat('l, F j') translates the names and keeps ENGLISH order, which
     * reads wrong beside a list whose headings the browser writes (toLocaleDateString with
     * the same three parts). ICU knows the order; where the intl extension is missing, a
     * selfhost server that never needed it, this falls back to the translated English order.
     */
    public static function dayLabel(\Carbon\CarbonInterface $day, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $withYear = ! $day->isSameYear(\Carbon\Carbon::now($day->getTimezone()));

        if (class_exists(\IntlDatePatternGenerator::class)) {
            try {
                $pattern = (new \IntlDatePatternGenerator($locale))->getBestPattern($withYear ? 'yMMMMEEEEd' : 'MMMMEEEEd');
                $text = $pattern ? (new \IntlDateFormatter($locale, \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, $day->getTimezone()->getName(), null, $pattern))->format($day) : false;
                if (is_string($text) && $text !== '') {
                    return $text;
                }
            } catch (\Throwable $e) {
                // An unknown locale or zone name: the plain form below.
            }
        }

        return $day->translatedFormat($withYear ? 'l, F j, Y' : 'l, F j');
    }
}
