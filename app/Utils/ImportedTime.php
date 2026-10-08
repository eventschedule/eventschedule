<?php

namespace App\Utils;

use Carbon\CarbonImmutable;

/**
 * Where an imported event's start is kept.
 *
 * This app shows every event in its schedule's timezone (see Event::getStartDateTime()), so an
 * imported instant is normally converted into that zone. The exception is an event that says
 * where it is: a touring act's Los Angeles show at 8 PM belongs on a New York schedule as 8 PM,
 * the time on the poster and on the door, and converting it would print 11 PM. That is what
 * typing the event in by hand has always produced, so an import does the same.
 *
 * A venue's events all happen at the venue, so a venue schedule always converts.
 */
class ImportedTime
{
    /** The clock times are being placed on, where that is not the zone the reader was handed. */
    private static ?string $clock = null;

    /**
     * Run a read whose times are placed on $zone, whatever zone the reader is handed for the
     * times that name none.
     *
     * The import page reads one link once, and both are the schedule's zone. A feed has them
     * apart: "Times are read as" is the clock of the SOURCE's unzoned times, and the schedule's
     * zone is the clock everything is then placed on. Without this the choice between keeping an
     * event's own clock and converting it was made against the feed's setting, so an entry that
     * says "19:30, Vienna" moved when somebody changed what unzoned times are read as.
     *
     * @template T
     *
     * @param  callable(): T  $read
     * @return T
     */
    public static function onClock(string $zone, callable $read): mixed
    {
        $before = self::$clock;
        self::$clock = $zone;

        try {
            return $read();
        } finally {
            self::$clock = $before;
        }
    }

    /**
     * @param  \DateTimeInterface  $at  The start as the source gives it. A floating time (no zone
     *                                  at all) must already have been read in the schedule's zone.
     * @param  bool  $statesZone  The source named a zone or an offset for it. UTC does not count:
     *                            it says when, not where.
     * @param  bool  $keepLocalClock  False for a venue schedule.
     * @return array{0: CarbonImmutable, 1: ?string} The start as a wall-clock time, and the zone
     *                                               it was kept in when that differs from the
     *                                               schedule's (for a note on the preview).
     */
    public static function place(\DateTimeInterface $at, bool $statesZone, string $scheduleZone, bool $keepLocalClock): array
    {
        $scheduleZone = self::$clock ?? $scheduleZone;
        $local = CarbonImmutable::instance($at);
        $inSchedule = $local->setTimezone($scheduleZone);

        // Only worth keeping, and worth a note, when the two clocks actually read differently.
        if ($statesZone && $keepLocalClock && $local->format('Y-m-d H:i') !== $inSchedule->format('Y-m-d H:i')) {
            return [$local, $local->getTimezone()->getName()];
        }

        return [$inSchedule, null];
    }

    /**
     * An all-day event has no time, and this app has no all-day flag: it is kept as starting at
     * midnight and running to the last minute of its last day, so one day reads as the whole of
     * that day and three days as a three-day event.
     *
     * @param  int  $days  How many calendar days it covers.
     */
    public static function allDayDuration(int $days): float
    {
        return round(max(1, $days) * 24 - 1 / 60, 3);
    }

    /** Hours between two instants, at the precision events.duration stores. Null when not positive. */
    public static function hoursBetween(\DateTimeInterface $start, \DateTimeInterface $end): ?float
    {
        $seconds = $end->getTimestamp() - $start->getTimestamp();

        return $seconds > 0 ? round($seconds / 3600, 3) : null;
    }
}
