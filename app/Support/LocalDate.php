<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;

/**
 * A date written the way the reader's language writes it: "terça-feira, 17 de novembro de 2026",
 * "Dienstag, 17. November 2026", "Tuesday, November 17, 2026".
 *
 * translatedFormat('l, F j, Y') translates the words but keeps English's order, so every other
 * language got "terça-feira, novembro 17, 2026". The order comes from ICU (ext-intl, which the
 * app requires) for the locale in force, in the date's own timezone. English is still written
 * by translatedFormat(), so its output is exactly what it was.
 *
 * Reached as a Carbon macro: $date->localDate() (AppServiceProvider::boot()).
 */
class LocalDate
{
    public static function format(DateTimeInterface $date, bool $withYear = true, bool $withWeekday = true, bool $shortMonth = false, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        $english = ($withWeekday ? 'l, ' : '').($shortMonth ? 'M' : 'F').' j'.($withYear ? ', Y' : '');

        if ($locale === 'en' || ! class_exists(\IntlDatePatternGenerator::class)) {
            return Carbon::instance($date)->locale($locale)->translatedFormat($english);
        }

        $icuLocale = str_replace('-', '_', $locale);
        $skeleton = ($withYear ? 'y' : '').($shortMonth ? 'MMM' : 'MMMM').($withWeekday ? 'EEEE' : '').'d';

        try {
            $pattern = (new \IntlDatePatternGenerator($icuLocale))->getBestPattern($skeleton);
            $formatter = new \IntlDateFormatter($icuLocale, \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, $date->getTimezone(), null, $pattern ?: null);
            $text = $pattern ? $formatter->format($date) : false;
        } catch (\Throwable $e) {
            $text = false;
        }

        return is_string($text) && $text !== ''
            ? $text
            : Carbon::instance($date)->locale($locale)->translatedFormat($english);
    }
}
