<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\Group;
use App\Models\Role;
use Illuminate\Support\Str;

/**
 * What one request waiting on a schedule says, as plain text in the language being read: what is
 * asked for, by whom, when, where, how to reach them, their answers to the schedule's own
 * questions and their message. It is what the email that announces a request prints.
 *
 * The same facts as a card on the Requests tab (role/show-admin-requests): a form request is
 * from the person who filled it in, any other is from the schedule that made the event.
 * RequestMailTest holds the answers of the two to each other.
 *
 * Who filled a form in is told ONLY to the schedule whose form it was (isFormRequestTo()). The
 * name, address, phone number and message ride on the event, and the event goes on: the act
 * that accepted a booking adds a venue to it, and the venue is then asked about an event that
 * still carries the first sender's details. To the venue it is the act's request.
 *
 * Every date is the EVENT's clock (the schedule that made it, Event::scheduleTimezone()) and the
 * receiving schedule's choice of 12 or 24 hours, never Event::localStartsAt(): that reads the
 * signed-in user, and this is built inside a visitor's own request, where the user (if anybody)
 * is the person asking.
 */
class RequestSummary
{
    /** Longest message a mail about ONE request prints. A description accepts 15,000 characters. */
    public const NOTE_LIMIT = 600;

    /** The same, and the longest answer, where a mail spells out several requests. */
    public const BRIEF_LIMIT = 160;

    /**
     * $brief is a mail that spells out several requests: each is cut shorter, so that five of
     * them with ten answers apiece stay under the size a mail client cuts a mail off at.
     *
     * @return array{title: string, from: ?string, day: ?string, time: ?string, when: ?string, where: ?string, sub_schedule: ?string, email: ?string, phone: ?string, answers: list<array<string, mixed>>, note: ?string}
     */
    public static function describe(Event $event, Role $role, bool $brief = false): array
    {
        [$submitter, $asker] = self::who($event, $role);

        // Where: the venue, unless it is the schedule being written to or the one asking (both
        // are named already). An act asking a curator to list its night is asked "where?" first.
        $venue = $event->venue;
        if ($venue && ($venue->id === $role->id || $venue->id === $asker?->id)) {
            $venue = null;
        }
        $where = implode(' · ', array_filter([
            $venue ? implode(', ', array_filter([$venue->name, $venue->city])) : null,
            $submitter && $event->event_url ? __('messages.online') : null,
        ])) ?: null;

        $groupId = $event->getGroupIdForSubdomain($role->subdomain);
        $group = $groupId ? Group::find($groupId) : null;

        [$day, $time] = self::when($event, $role);

        return [
            'title' => (string) $event->name,
            'from' => $submitter ?: ($asker?->name ?: null),
            'day' => $day,
            'time' => $time,
            // One string, for a subject line or a text part. The clock is wrapped in a
            // left-to-right isolate (U+2066, U+2069): in a Hebrew or Arabic line "8:00 PM -
            // 10:00 PM" otherwise reads "PM - 10:00 PM 8:00".
            'when' => $day ? $day.($time ? " · \u{2066}".$time."\u{2069}" : '') : null,
            'where' => $where,
            'sub_schedule' => $group?->name ?: null,
            'email' => $submitter && filled($event->contact_email) ? (string) $event->contact_email : null,
            'phone' => $submitter && filled($event->contact_phone) ? (string) $event->contact_phone : null,
            'answers' => CustomFieldDisplay::forRequest($event, $role, $brief ? self::BRIEF_LIMIT : CustomFieldDisplay::MAIL_ANSWER_LIMIT),
            'note' => $submitter && filled($event->description)
                ? Str::limit(trim((string) $event->description), $brief ? self::BRIEF_LIMIT : self::NOTE_LIMIT)
                : null,
        ];
    }

    /**
     * A request in one line, for a mail that has more new requests than it can spell out: the
     * event, who asked and the day. Who asked follows the same rule as the full form (who()), so
     * a line cannot tell a schedule of a sender who did not write to it.
     *
     * @return array{title: string, from: ?string, day: ?string}
     */
    public static function line(Event $event, Role $role): array
    {
        [$submitter, $asker] = self::who($event, $role);

        return [
            'title' => Str::limit(Str::squish((string) $event->name), 80),
            'from' => Str::limit(Str::squish((string) ($submitter ?: $asker?->name)), 60) ?: null,
            'day' => self::when($event, $role)[0],
        ];
    }

    /** Roughly what a line costs a mail in bytes: its text as it is sent, and a list item's markup. */
    public static function lineWeight(array $line): int
    {
        return 400 + strlen(e($line['title'].$line['from'].$line['day']));
    }

    /**
     * Who is asking, as $role may be told: the person who filled in its own form, or else the
     * schedule that made the event (failing that, the one a card has always named: the act for
     * a venue or a curator, the venue for an act).
     *
     * @return array{0: ?string, 1: ?Role}
     */
    private static function who(Event $event, Role $role): array
    {
        if (self::isFormRequestTo($event, $role)) {
            return [trim((string) $event->contact_name), null];
        }

        $asker = $event->creatorRole && $event->creatorRole->id !== $role->id
            ? $event->creatorRole
            : (($role->isVenue() || $role->isCurator()) ? $event->role() : $event->venue);

        return [null, $asker && $asker->id !== $role->id ? $asker : null];
    }

    /**
     * Whether somebody filled in $role's own request form to make this event. The booking form
     * is the one writer of contact_name, and it records the schedule it belongs to as the
     * event's maker; a row with no recorded maker is from before it did.
     */
    public static function isFormRequestTo(Event $event, Role $role): bool
    {
        return filled($event->contact_name)
            && ($event->creator_role_id === null || (int) $event->creator_role_id === (int) $role->id);
    }

    /**
     * A summary that could not be built, so that one request with something odd about it does
     * not hold back the mail (and with it every request after it, each time one arrives).
     */
    public static function bare(Event $event): array
    {
        return [
            'title' => (string) $event->name, 'from' => null, 'day' => null, 'time' => null, 'when' => null, 'where' => null,
            'sub_schedule' => null, 'email' => null, 'phone' => null, 'answers' => [], 'note' => null,
        ];
    }

    /**
     * Roughly what a summary costs a mail in bytes: its own text as it is sent (escaped: a
     * quotation mark is six bytes there), and the markup around each line of it (a details item
     * is some 900 bytes of table). Close enough to budget with.
     */
    public static function weight(array $summary): int
    {
        $lines = count(array_filter([$summary['when'], $summary['from'], $summary['phone'], $summary['email'], $summary['where'], $summary['sub_schedule']]))
            + count($summary['answers']);
        $text = strlen(e($summary['title'].$summary['when'].$summary['from'].$summary['where'].$summary['email'].$summary['note']));
        foreach ($summary['answers'] as $answer) {
            $text += strlen(e($answer['label'].$answer['value']));
        }

        return 1800 + $lines * 900 + $text;
    }

    /** @return array{0: ?string, 1: ?string} the day, and the hours with their zone when it is not the reader's */
    private static function when(Event $event, Role $role): array
    {
        if (! $event->starts_at) {
            return [null, null];
        }

        $zone = $event->scheduleTimezone();
        // isoFormat: the day, month and year in the reader's own order ("Mi., 14. Okt 2026").
        $start = $event->getStartDateTime(null, true, $zone)->locale(app()->getLocale());
        $day = $event->is_multi_day ? $event->getDateRangeDisplay() : $start->isoFormat('ddd, ll');
        $time = $event->getStartEndTime(null, (bool) $role->use_24_hour_time);

        // The hours are the event's own clock. Where that is not the clock of the schedule
        // being written to (an act in Lisbon asking a curator in Berlin), say whose they are.
        if ($role->timezone && $zone !== $role->timezone) {
            $time .= ' '.$start->format('T');
        }

        return [$day, $time];
    }
}
