<?php

namespace App\Utils;

use App\Models\Event;
use Carbon\Carbon;

/**
 * Date/time rendering for appointment bookings.
 *
 * A booking is a ONE-OFF event: events.starts_at holds the exact UTC instant of the slot. That makes
 * sales.event_date (a schedule-local calendar date, only meaningful for recurring events) redundant
 * here - and actively dangerous to reuse, which is why this class exists instead of a wider
 * Event::getStartEndTime() signature.
 *
 * Event::getStartDateTime($date, true, $tz) converts to $tz and THEN overwrites the calendar date
 * from $date. That is correct while $date and $tz are both schedule-local (today's behaviour), but
 * pass a guest timezone alongside a schedule-local date and the result slides a whole day whenever
 * the two disagree - e.g. a 22:00 New York appointment is already "tomorrow" in Paris.
 */
class AppointmentTimeUtils
{
    /**
     * Render a booking's start for display in $timezone, falling back to the schedule's own zone
     * when the timezone is missing or not a recognised identifier.
     *
     * @return array{date: string, time: string, tz: string}
     */
    public static function render(Event $event, ?string $timezone = null, bool $use24 = false): array
    {
        $tz = self::resolveTimezone($timezone) ?? self::scheduleTimezone($event);
        $start = self::startAt($event)->setTimezone($tz);
        $minutes = $event->durationInMinutes();
        $format = $use24 ? 'H:i' : 'g:i A';

        return [
            // translatedFormat, not format: these strings go into localized pages and mails.
            'date' => $start->translatedFormat('l, F j, Y'),
            'time' => $minutes > 0
                ? $start->format($format).' - '.$start->copy()->addMinutes($minutes)->format($format)
                : $start->format($format),
            'tz' => $tz,
        ];
    }

    /**
     * The zone the schedule itself keeps: the value recorded on the event at booking time, falling
     * back to the creator schedule's current zone for rows written before that column was set.
     */
    public static function scheduleTimezone(Event $event): string
    {
        return $event->timezone ?: $event->scheduleTimezone();
    }

    /**
     * A timezone setTimezone() will accept, or null. Guards every caller that would otherwise hand an
     * arbitrary stored string to setTimezone() - which throws, and would take down both the manage
     * page and the confirmation-mail render.
     *
     * Tests constructibility rather than membership of DateTimeZone::listIdentifiers(): that list omits
     * backward-compatibility aliases, so Asia/Calcutta, Europe/Kiev, Asia/Saigon and US/Eastern were all
     * rejected despite being perfectly usable. Browsers really do report them - Intl.DateTimeFormat()
     * follows the host's tz database, not PHP's canonical list - and rejecting them silently rendered
     * every time for those guests in the schedule's zone instead of their own.
     *
     * Constructibility is the exact property callers need, so this is both safer and more permissive.
     */
    public static function resolveTimezone(?string $timezone): ?string
    {
        if (! $timezone) {
            return null;
        }

        // Guest-supplied and hit once per rendered time, so memoise both outcomes.
        static $resolved = [];

        if (! array_key_exists($timezone, $resolved)) {
            try {
                new \DateTimeZone($timezone);
                $resolved[$timezone] = $timezone;
            } catch (\Throwable $e) {
                $resolved[$timezone] = null;
            }
        }

        return $resolved[$timezone];
    }

    /**
     * Parse a stored UTC instant string, tolerating a legacy date-only value.
     *
     * Public because the reschedule mail and picker work from a SCALAR old-start (they cannot read the
     * event, which already holds the new time) and were doing a bare createFromFormat('Y-m-d H:i:s'),
     * which throws on a 10-char value. Every other consumer in this file already branches on the length;
     * those three did not, so a restored or legacy row killed the guest's mail job and 500'd the
     * reschedule page while the manage page rendered fine.
     */
    public static function parseUtcInstant(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return strlen($value) === 10
                ? Carbon::createFromFormat('Y-m-d', $value, 'UTC')->startOfDay()
                : Carbon::createFromFormat('Y-m-d H:i:s', $value, 'UTC');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** The exact UTC instant of the booking. Tolerates a legacy date-only starts_at. */
    protected static function startAt(Event $event): Carbon
    {
        return strlen((string) $event->starts_at) === 10
            ? Carbon::createFromFormat('Y-m-d', $event->starts_at, 'UTC')->startOfDay()
            : Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC');
    }
}
