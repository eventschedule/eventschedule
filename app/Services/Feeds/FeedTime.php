<?php

namespace App\Services\Feeds;

use Carbon\Carbon;
use Carbon\CarbonImmutable;

/**
 * A time as a provider writes it, where the provider does not say how it writes it.
 *
 * A calendar and a page's event data have a standard to be read by. A provider's own format has
 * a field called "start" and an example that happens not to be an event, so this takes the
 * shapes such a field is written in and refuses the rest, rather than guessing at a day-first
 * or month-first date.
 *
 * @phpstan-type Moment array{at: CarbonImmutable, all_day: bool, states_zone: bool}
 */
final class FeedTime
{
    /**
     * The clock a reader's row is on.
     *
     * A row's time is a wall-clock time, and whose depends on what the source said. A time with
     * no zone is on the feed's clock, which is what "Times are read as" sets, and so is a time
     * that named a zone and was turned to the feed's. But an event that named ANOTHER zone, on a
     * schedule that keeps such an event's own clock (ImportedTime::place()), is carried as that
     * clock and is meant to read as it on the schedule: it is placed on the schedule's clock, as
     * the import page places it, and the feed's setting does not reach it. A whole-day event is
     * a date, and a date is the schedule's as well.
     *
     * While the two clocks are the same one, which is how a feed is added, none of this shows.
     * It is what keeps "Times are read as" from moving a calendar whose every entry says its zone.
     */
    public static function zoneOf(array $row, string $feedZone, string $scheduleZone): string
    {
        return ! empty($row['local_time_zone']) || ! empty($row['is_all_day']) ? $scheduleZone : $feedZone;
    }

    /** The instant a reader's row starts at. */
    public static function startOf(array $row, string $feedZone, string $scheduleZone): Carbon
    {
        return Carbon::parse($row['event_date_time'], self::zoneOf($row, $feedZone, $scheduleZone))->utc();
    }

    /**
     * @param  string  $timezone  The zone a time with no zone of its own is read in.
     * @return ?array{at: CarbonImmutable, all_day: bool, states_zone: bool} Null when it is not
     *                                                                       a time this reads.
     */
    public static function parse(mixed $value, string $timezone): ?array
    {
        if (is_int($value) || is_float($value)) {
            $value = (string) (int) $value;
        }

        if (! is_string($value) || ($value = trim($value)) === '') {
            return null;
        }

        try {
            // Seconds or milliseconds since 1970: an instant, which says when and not where.
            if (preg_match('/^\d{10}(\d{3})?$/', $value)) {
                $seconds = (int) substr($value, 0, 10);

                return ['at' => CarbonImmutable::createFromTimestampUTC($seconds), 'all_day' => false, 'states_zone' => false];
            }

            // 2026-05-09, 09.05.2026: a day and no time.
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
                return self::day((int) $m[1], (int) $m[2], (int) $m[3], $timezone);
            }
            if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $value, $m)) {
                return self::day((int) $m[3], (int) $m[2], (int) $m[1], $timezone);
            }

            // 09.05.2026 17:30 (and with seconds): day first, as it is written where it is used.
            if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4}),?\s+(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $value, $m)) {
                return self::clock((int) $m[3], (int) $m[2], (int) $m[1], (int) $m[4], (int) $m[5], $timezone);
            }

            // 2026-05-09 17:30[:00], 2026-05-09T17:30:00[.000][Z|+02:00]
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::\d{2}(?:\.\d+)?)?\s*(Z|[+-]\d{2}:?\d{2})?$/i', $value, $m)) {
                if (! checkdate((int) $m[2], (int) $m[3], (int) $m[1]) || (int) $m[4] > 23 || (int) $m[5] > 59) {
                    return null;
                }

                $zone = $m[6] ?? '';

                return [
                    'at' => CarbonImmutable::parse(str_replace(' ', 'T', $value), $timezone),
                    'all_day' => false,
                    // An offset says where the clock is. "Z" only says when.
                    'states_zone' => $zone !== '' && strtoupper($zone) !== 'Z',
                ];
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private static function day(int $year, int $month, int $day, string $timezone): ?array
    {
        if (! checkdate($month, $day, $year)) {
            return null;
        }

        return ['at' => CarbonImmutable::create($year, $month, $day, 0, 0, 0, $timezone), 'all_day' => true, 'states_zone' => false];
    }

    private static function clock(int $year, int $month, int $day, int $hour, int $minute, string $timezone): ?array
    {
        if (! checkdate($month, $day, $year) || $hour > 23 || $minute > 59) {
            return null;
        }

        return ['at' => CarbonImmutable::create($year, $month, $day, $hour, $minute, 0, $timezone), 'all_day' => false, 'states_zone' => false];
    }
}
