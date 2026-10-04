<?php

namespace App\Utils;

use Sabre\VObject\DateTimeParser;
use Sabre\VObject\Recur\RRuleIterator;

/**
 * Turns a calendar's repeat rule (an iCalendar RRULE) into this app's own repeating event.
 *
 * A feed's "every Monday at 6" should arrive as ONE repeating event, not as a dozen dated copies
 * that quietly run out. But only when the two mean the same thing: fromRule() answers with the
 * fields EventRepo::saveEvent() reads, or with null when this app cannot express the rule
 * EXACTLY, and the caller then falls back to importing the next few dates one by one.
 *
 * What the app can express (Event::matchesFrequency()): daily; weekly on chosen days; every N
 * weeks; monthly on the start's day of the month; monthly on the start's nth weekday; yearly. Each
 * with an end date, and with dates left out or added. So "the last Friday", "every three days",
 * "every other month" and anything keyed on an hour, a week number or a day of the year are null.
 *
 * Pure: no database, no I/O. Every date is a wall-clock date in whatever zone $start is in, which
 * must be the zone the event will be stored and shown in.
 */
class RecurrenceMapper
{
    /** RRULE day codes in the order of PHP's date('w'): 0 is Sunday. */
    private const DAYS = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];

    /** A COUNT this long is walked to find its last date; anything longer falls back. */
    private const MAX_COUNT = 1000;

    /**
     * @param  array|string  $rule  The RRULE, as "FREQ=WEEKLY;BYDAY=MO" or as its parts.
     * @param  \DateTimeInterface  $start  The first occurrence (DTSTART).
     * @param  string[]  $excluded  Y-m-d dates of occurrences that were removed (EXDATE).
     * @param  string[]  $included  Y-m-d dates of extra occurrences at the same time (RDATE).
     * @return ?array{frequency: string, interval: ?int, days: int[], until: ?string, fields: array}
     *                                                                                               'fields' is what to send saveEvent(); the rest describes the rule for a label.
     */
    public static function fromRule(array|string $rule, \DateTimeInterface $start, array $excluded = [], array $included = []): ?array
    {
        $parts = self::parts($rule);
        $frequency = strtoupper((string) ($parts['FREQ'] ?? ''));
        $interval = max(1, (int) ($parts['INTERVAL'] ?? 1));

        // Parts this app has no notion of at all.
        foreach (['BYSECOND', 'BYMINUTE', 'BYHOUR', 'BYYEARDAY', 'BYWEEKNO'] as $unsupported) {
            if (self::items($parts[$unsupported] ?? null)) {
                return null;
            }
        }

        $byDay = array_map('strtoupper', self::items($parts['BYDAY'] ?? null));
        $byMonthDay = array_map('intval', self::items($parts['BYMONTHDAY'] ?? null));
        $byMonth = array_map('intval', self::items($parts['BYMONTH'] ?? null));
        $bySetPos = array_map('intval', self::items($parts['BYSETPOS'] ?? null));

        $startWeekday = (int) $start->format('w');
        $startDay = (int) $start->format('j');
        $startMonth = (int) $start->format('n');

        $days = [];
        $mapped = null;
        $mappedInterval = null;

        switch ($frequency) {
            case 'DAILY':
                if ($interval !== 1 || $byMonthDay || $byMonth || $bySetPos) {
                    return null;
                }
                $days = self::plainDays($byDay);
                if ($days === null) {
                    return null;
                }
                if ($days === [] || count($days) === 7) {
                    $mapped = 'daily';
                    $days = [];
                } else {
                    // "Daily, on weekdays" is a weekly rule by another name.
                    $mapped = 'weekly';
                }
                break;

            case 'WEEKLY':
                if ($byMonthDay || $byMonth || $bySetPos) {
                    return null;
                }
                $days = self::plainDays($byDay);
                if ($days === null) {
                    return null;
                }
                if ($days === []) {
                    $days = [$startWeekday];
                }
                if ($interval === 1) {
                    $mapped = 'weekly';
                } else {
                    // The app counts weeks from Sunday; a rule counts them from WKST, Monday
                    // unless it says otherwise. With one day a week that makes no difference.
                    // With several it only agrees when the rule's weeks start on Sunday too, or
                    // start on Monday and Sunday is not one of the days.
                    $weekStart = strtoupper((string) ($parts['WKST'] ?? 'MO'));
                    $sameWeeks = count($days) === 1
                        || $weekStart === 'SU'
                        || ($weekStart === 'MO' && ! in_array(0, $days, true));
                    if (! $sameWeeks) {
                        return null;
                    }
                    $mapped = 'every_n_weeks';
                    $mappedInterval = $interval;
                }
                break;

            case 'MONTHLY':
                if ($interval !== 1 || $byMonth) {
                    return null;
                }
                if ($byDay) {
                    // "2TU", or "TU" with BYSETPOS=2: the second Tuesday.
                    if (count($byDay) !== 1 || $byMonthDay || ! preg_match('/^([+-]?\d)?(SU|MO|TU|WE|TH|FR|SA)$/', $byDay[0], $match)) {
                        return null;
                    }
                    $ordinal = $match[1] !== '' ? (int) $match[1] : ($bySetPos[0] ?? null);
                    if (($match[1] !== '' && $bySetPos) || count($bySetPos) > 1 || $ordinal === null) {
                        return null;
                    }
                    // The app derives "the nth" from the start date, so the start has to be one.
                    // That also rules out "the last" (-1): the start's own position is 1 to 5.
                    if (array_search($match[2], self::DAYS, true) !== $startWeekday
                        || (int) ceil($startDay / 7) !== $ordinal) {
                        return null;
                    }
                    $mapped = 'monthly_weekday';
                } else {
                    if ($bySetPos || ($byMonthDay && $byMonthDay !== [$startDay])) {
                        return null;
                    }
                    $mapped = 'monthly_date';
                }
                break;

            case 'YEARLY':
                if ($interval !== 1 || $byDay || $bySetPos
                    || ($byMonth && $byMonth !== [$startMonth])
                    || ($byMonthDay && $byMonthDay !== [$startDay])) {
                    return null;
                }
                $mapped = 'yearly';
                break;

            default:
                return null;
        }

        // A weekly rule whose first occurrence is not on one of its days is undefined in the
        // standard, and here the start date would simply never show.
        if (in_array($mapped, ['weekly', 'every_n_weeks'], true) && ! in_array($startWeekday, $days, true)) {
            return null;
        }

        $until = self::until($parts, $start);
        if ($until === false) {
            return null;
        }

        sort($days);
        $excluded = self::dates($excluded);
        $included = self::dates($included);

        $fields = [
            'schedule_type' => 'recurring',
            'recurring_frequency' => $mapped,
            'recurring_interval' => $mappedInterval,
            'recurring_end_type' => $until ? 'on_date' : 'never',
            'recurring_end_value' => $until,
            'recurring_exclude_dates' => $excluded,
            'recurring_include_dates' => $included,
        ];
        // saveEvent() reads the weekdays as one checkbox each.
        foreach ($days as $day) {
            $fields['days_of_week_'.$day] = 1;
        }

        return [
            'frequency' => $mapped,
            'interval' => $mappedInterval,
            'days' => $days,
            'until' => $until,
            'fields' => $fields,
        ];
    }

    /**
     * The last date of the rule as Y-m-d, null when it never ends, false when it cannot be told.
     *
     * COUNT is turned into a date by walking the rule: the app's own "after N events" counts
     * differently from the standard once dates are left out, and an end date has no such doubt.
     */
    private static function until(array $parts, \DateTimeInterface $start): string|false|null
    {
        if (! empty($parts['COUNT'])) {
            $count = (int) $parts['COUNT'];
            if ($count < 1 || $count > self::MAX_COUNT) {
                return false;
            }

            try {
                $iterator = new RRuleIterator($parts, $start);
                $last = null;
                $seen = 0;
                while ($iterator->valid() && $seen <= self::MAX_COUNT) {
                    $last = $iterator->current();
                    $iterator->next();
                    $seen++;
                }
            } catch (\Throwable $e) {
                return false;
            }

            return $last ? $last->format('Y-m-d') : false;
        }

        if (! empty($parts['UNTIL'])) {
            $value = (string) $parts['UNTIL'];

            try {
                $until = DateTimeParser::parse($value, $start->getTimezone());
            } catch (\Throwable $e) {
                return false;
            }

            $until = $until->setTimezone($start->getTimezone());

            // UNTIL bounds the occurrence's START. On the last day, an occurrence later in the
            // day than UNTIL is already outside it. A bare date is midnight, which is how the
            // calendar library reads it too: it takes in an all-day event on that day (stored
            // here as starting at 00:00) and leaves out a timed one.
            return $until->format('H:i:s') < $start->format('H:i:s')
                ? $until->modify('-1 day')->format('Y-m-d')
                : $until->format('Y-m-d');
        }

        return null;
    }

    private static function parts(array|string $rule): array
    {
        if (is_string($rule)) {
            $rule = \Sabre\VObject\Property\ICalendar\Recur::stringToArray(
                preg_replace('/^RRULE:/i', '', trim($rule))
            );
        }

        return array_change_key_case($rule, CASE_UPPER);
    }

    /** A rule part as a list: sabre gives a string for one value and an array for several. */
    private static function items(mixed $value): array
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }

        return array_values(array_filter(
            is_array($value) ? $value : explode(',', (string) $value),
            fn ($item) => $item !== '' && $item !== null
        ));
    }

    /**
     * BYDAY as weekday indexes (0 is Sunday). Null when any entry carries an ordinal ("2TU"),
     * which only a monthly rule can use.
     *
     * @return ?int[]
     */
    private static function plainDays(array $byDay): ?array
    {
        $days = [];
        foreach ($byDay as $code) {
            $index = array_search($code, self::DAYS, true);
            if ($index === false) {
                return null;
            }
            $days[] = $index;
        }

        return array_values(array_unique($days));
    }

    /** @return string[] */
    private static function dates(array $dates): array
    {
        $dates = array_values(array_unique(array_filter(
            $dates,
            fn ($date) => is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
        )));
        sort($dates);

        return $dates;
    }
}
