<?php

namespace App\Utils;

/**
 * The arithmetic two Realtime pages share: /admin/realtime (App\Services\RealtimeDashboard) and a
 * schedule's own Realtime tab on /analytics (App\Services\ScheduleRealtime).
 *
 * The two services read the same table through different queries on purpose (the owner's must
 * never be able to return a row that is not theirs), which leaves "who is here right now" and
 * "which minute does this page view belong to" as the only things that could quietly come to mean
 * two different numbers on two pages. They are defined once, here.
 * tests/Feature/ScheduleRealtimeTest.php holds the two services to the same answer.
 */
class RealtimeRows
{
    /** The per-minute chart is thirty bars: the current minute and the twenty-nine before it. */
    public const MINUTES = 30;

    /**
     * A stored UTC datetime as a Unix second.
     *
     * strtotime, not Carbon: this runs up to three times per row, 60,000 times a poll at the admin
     * page's cap.
     */
    public static function timestamp(string $value): int
    {
        return (int) strtotime($value.' UTC');
    }

    /**
     * Right now: the page has not said it was hidden or closed, and its last heartbeat is no older
     * than two of its own intervals plus thirty seconds of slack. $hb is the interval the page was
     * rendered with, which slows down on a busy install (RealtimeTracker::heartbeatSeconds()).
     */
    public static function isNow(?int $ended, int $lastSeen, int $hb, int $nowTs): bool
    {
        return $ended === null && $lastSeen >= $nowTs - (2 * $hb + 30);
    }

    /**
     * What a page view is a view OF, for Top pages and its filter: the live view and the hourly
     * totals of the longer ranges (RealtimeRollup) key a page the same way, or a page would be two
     * rows across the switch. Every hosted schedule's home page has the path "/", same as the
     * marketing home, so a schedule page is keyed by its ids, not its path.
     */
    public static function pageKey(string $surface, $roleId, $eventId, ?string $path): string
    {
        if ($surface === 'gp' && $roleId) {
            return 'e:'.UrlUtils::encodeId($roleId).($eventId ? ':'.UrlUtils::encodeId($eventId) : '');
        }

        return 'p:'.$surface.':'.$path;
    }

    /** Where a visit began, as Sources keys it: the named site, else the kind of source. */
    public static function sourceKey(?string $channel, ?string $name): ?string
    {
        if (! $channel) {
            return null;
        }

        return $name ? 'n:'.$name : 'c:'.$channel;
    }

    /** The first second of the oldest bar. */
    public static function firstMinute(int $nowTs): int
    {
        return intdiv($nowTs, 60) * 60 - (self::MINUTES - 1) * 60;
    }

    /**
     * Which bar a page view that started at $started belongs to, or null when it began before the
     * chart does. A clock a few seconds ahead still lands in the last bar.
     */
    public static function minuteIndex(int $started, int $nowTs): ?int
    {
        $first = self::firstMinute($nowTs);

        if ($started < $first) {
            return null;
        }

        return min(self::MINUTES - 1, intdiv($started - $first, 60));
    }
}
