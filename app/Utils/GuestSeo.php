<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\Group;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * The guest portal's <title> and meta description, on event pages and schedule pages.
 *
 * A crawl of production found titles of just "{event} | {schedule}" (no date, no place) and
 * schedule titles of just the name; a third of the event descriptions under 50 characters and a
 * quarter with raw newlines, words glued across line breaks and entities escaped twice; and 112 of
 * 188 schedule descriptions reading "View the event schedule for X". These build both from what the
 * owner wrote plus the facts a searcher needs - when, and where - within the lengths a results
 * page shows.
 *
 * Never our brand, anywhere: the schedule is the site (docs/BRANDING_MATRIX.md rule 6, which
 * marketing/white-label.blade.php publishes). Dates are the SCHEDULE's clock and calendar day, not
 * the viewer's, as everywhere else an occurrence is rendered.
 */
class GuestSeo
{
    /** Roughly what a results page shows of a title before cutting it. */
    public const TITLE_MAX = 60;

    /** Roughly what a results page shows of a description before cutting it. */
    public const DESCRIPTION_MAX = 155;

    /** Below this many characters a description has room for the date and place. */
    private const FACTS_ROOM = 110;

    /** A short description under this many characters is too thin to stand on its own. */
    private const THIN_SHORT = 50;

    private const SEPARATOR = ' · ';

    /**
     * An event page's title: "{name} - {date}, {venue} | {schedule}", dropping the venue for the
     * city, then the place, then the date, until it fits in TITLE_MAX. Today's "{name} |
     * {schedule}" is the floor, used when nothing richer fits.
     *
     * A recurring series never carries a date: its page is the series, whose next date changes
     * every week while the title sits in the index. A place the name or the suffix already says is
     * left out - the venue on its own page, where it IS the suffix - and an online-only event says
     * "Online", never its join link, which is private.
     */
    public static function eventTitle(Event $event, Role $role): string
    {
        $lang = $role->displayLanguageCode();
        $name = SeoUtils::cleanText($event->nameInLanguage($lang, $role));
        $schedule = SeoUtils::cleanText($role->translatedName()) ?: config('app.name');

        if ($name === '') {
            return $schedule;
        }

        $suffix = ' | '.$schedule;
        $places = self::titlePlaces($event, $lang, $name.$suffix);
        $date = ($event->days_of_week || ! $event->starts_at) ? null : self::localStart($event, null)->isoFormat('ll');

        $candidates = [];

        if ($date !== null) {
            foreach ($places as $place) {
                $candidates[] = $name.' - '.$date.', '.$place;
            }
            $candidates[] = $name.' - '.$date;
        }

        foreach ($places as $place) {
            $candidates[] = $name.', '.$place;
        }

        $candidates = array_map(fn (string $candidate) => $candidate.$suffix, $candidates);
        $candidates[] = $name.$suffix;

        return SeoUtils::fitTitle($candidates, self::TITLE_MAX);
    }

    /**
     * A schedule page's title: "{name} - Upcoming Events" while it has any, "{name} - Events"
     * otherwise, with the city for a venue whose name does not already carry it, and the bare name
     * when nothing else fits. A sub-schedule keeps "{sub-schedule} | {name}".
     */
    public static function scheduleTitle(Role $role, ?Group $group, bool $hasUpcoming): string
    {
        $name = SeoUtils::cleanText($role->translatedName()) ?: config('app.name');

        if ($group) {
            return $group->translatedName().' | '.($role->translatedName() ?: config('app.name'));
        }

        $label = __($hasUpcoming ? 'messages.upcoming_events' : 'messages.events');
        $candidates = [];

        if ($role->isVenue() && ($city = self::unlessContained(SeoUtils::cleanText($role->translatedCity()), $name)) !== null) {
            $candidates[] = $name.', '.$city.' - '.$label;
        }

        $candidates[] = $name.' - '.$label;
        $candidates[] = $name;

        return SeoUtils::fitTitle($candidates, self::TITLE_MAX);
    }

    /**
     * An event page's meta description, also its og: and twitter: description.
     *
     * The owner's text first: the short description, joined to the long one when it is too thin to
     * stand alone, else the event's name. Then, while there is room, when and where it happens.
     *
     * $date is the occurrence the page is about. A one-off event ignores it and describes its own
     * start. A recurring event has a date only on a dated occurrence page; the undated series page
     * gets none, for the reason its title has none.
     */
    public static function eventDescription(Event $event, ?string $date, string $lang, ?Role $role = null): string
    {
        $text = self::combine(
            SeoUtils::cleanText($event->shortDescriptionInLanguage($lang, $role)),
            SeoUtils::plainText($event->descriptionHtmlInLanguage($lang, $role)),
        );

        if ($text === '') {
            $text = SeoUtils::cleanText($event->nameInLanguage($lang, $role));
        }

        if (mb_strlen($text) < self::FACTS_ROOM) {
            $text = self::append($text, implode(self::SEPARATOR, array_filter([
                self::eventWhen($event, $date, $role),
                self::eventWhere($event, $lang, $text),
            ])));
        }

        return SeoUtils::excerpt($text, self::DESCRIPTION_MAX);
    }

    /**
     * A schedule page's meta description, also its og: and twitter: description.
     *
     * The schedule's own text, a venue's street and city when the text does not already give them,
     * and, while there is room, the next few events by name. The old "View the event schedule for
     * X" is the fallback only for a schedule with nothing at all to say.
     *
     * @param  \Illuminate\Support\Collection<int, array{event: Event, date: string}>|null  $upcoming  EventRepo::upcomingForGuest()
     */
    public static function scheduleDescription(Role $role, ?Collection $upcoming = null): string
    {
        $lang = $role->displayLanguageCode();

        $text = self::combine(
            SeoUtils::cleanText($role->translatedShortDescription()),
            SeoUtils::plainText($role->translatedDescription()),
        );

        if ($role->isVenue()) {
            $text = self::append($text, implode(', ', array_filter([
                self::unlessContained(SeoUtils::cleanText($role->translatedAddress1()), $text),
                self::unlessContained(SeoUtils::cleanText($role->translatedCity()), $text),
            ])));
        }

        if ($upcoming && $upcoming->isNotEmpty() && mb_strlen($text) < self::FACTS_ROOM) {
            $names = $upcoming
                ->map(fn (array $row) => SeoUtils::cleanText($row['event']->nameInLanguage($lang, $role)))
                ->filter()
                ->unique()
                ->take(3)
                ->implode(', ');

            if ($names !== '') {
                $text = self::append($text, __('messages.guest_meta_upcoming_list', ['events' => $names]));
            }
        }

        if ($text === '') {
            $text = __('messages.view_schedule_for', ['name' => SeoUtils::cleanText($role->translatedName())]);
        }

        return SeoUtils::excerpt($text, self::DESCRIPTION_MAX);
    }

    /**
     * The owner's text: the short description, or the short and the long together when the short
     * one is too thin to stand alone - unless the long one already opens with it.
     */
    private static function combine(string $short, string $long): string
    {
        if ($short === '') {
            return $long;
        }

        if ($long === '' || mb_strlen($short) >= self::THIN_SHORT) {
            return $short;
        }

        if (mb_stripos($long, $short) === 0) {
            return $long;
        }

        return $short.(SeoUtils::endsWithPunctuation($short) ? ' ' : '. ').$long;
    }

    /**
     * The places a title may name, richest first: the venue, then its city. Each is left out when
     * $already, the name and the suffix, says it - which is also what drops the venue on the
     * venue's own page, where its name IS the suffix.
     *
     * @return array<int, string>
     */
    private static function titlePlaces(Event $event, string $lang, string $already): array
    {
        $venue = $event->venue;

        if (! $venue) {
            return $event->event_url
                ? array_filter([self::unlessContained(__('messages.online'), $already)])
                : [];
        }

        return array_values(array_filter([
            self::unlessContained(SeoUtils::cleanText($venue->nameInLanguage($lang)), $already),
            self::unlessContained(SeoUtils::cleanText($venue->textInLanguage('city', $lang)), $already),
        ]));
    }

    /**
     * When: "Sat, Oct 24, 2026, 7:30 PM" in the schedule's timezone and 12/24-hour preference, a
     * bare date for an event with no time, "Oct 24, 2026 - Oct 26, 2026" for one of a day or more.
     * Null for a series page, which is about every occurrence at once.
     */
    private static function eventWhen(Event $event, ?string $date, ?Role $role): ?string
    {
        if (! $event->starts_at) {
            return null;
        }

        if ($event->days_of_week) {
            if (! Event::isOccurrenceDate($date)) {
                return null;
            }
        } else {
            $date = null;
        }

        $start = self::localStart($event, $date);

        if ($event->is_multi_day) {
            return $start->isoFormat('ll').' - '.$start->copy()->addMinutes($event->durationInMinutes())->isoFormat('ll');
        }

        $day = $start->isoFormat('ddd, ll');

        if (strlen((string) $event->starts_at) === 10) {
            return $day;
        }

        $use24 = $role ? get_use_24_hour_time($role) : $event->use24HourTime();

        return $day.', '.$start->format($use24 ? 'H:i' : 'g:i A');
    }

    /**
     * Where: "{venue}, {city}", leaving out whatever the text already says, or "Online" for an
     * online-only event. Never the join link: it is private.
     */
    private static function eventWhere(Event $event, string $lang, string $text): ?string
    {
        $venue = $event->venue;

        if (! $venue) {
            return $event->event_url ? self::unlessContained(__('messages.online'), $text) : null;
        }

        // A venue a calendar sync made from an address has no name; its street says where it is.
        $label = SeoUtils::cleanText($venue->nameInLanguage($lang))
            ?: SeoUtils::cleanText($venue->textInLanguage('address1', $lang));
        $label = self::unlessContained($label, $text);
        $city = self::unlessContained(SeoUtils::cleanText($venue->textInLanguage('city', $lang)), $text.' '.$label);

        return implode(', ', array_filter([$label, $city])) ?: null;
    }

    /**
     * The start of the occurrence on $date (else of the event) in the schedule's timezone, in the
     * page's locale. A date-only starts_at is already the schedule's calendar day: reading it as
     * midnight UTC and converting would move it a day for every schedule west of Greenwich.
     */
    private static function localStart(Event $event, ?string $date): Carbon
    {
        $locale = app()->getLocale();

        if (strlen((string) $event->starts_at) === 10) {
            return Carbon::parse(Event::isOccurrenceDate($date) ? $date : $event->starts_at)->locale($locale);
        }

        return $event->getStartDateTime($date, true, $event->scheduleTimezone())->locale($locale);
    }

    /** "$text · $fact", or whichever of the two is not empty. */
    private static function append(string $text, string $fact): string
    {
        if ($fact === '') {
            return $text;
        }

        return $text === '' ? $fact : $text.self::SEPARATOR.$fact;
    }

    /** $part, unless it is empty or $haystack already contains it (ignoring case). */
    private static function unlessContained(?string $part, string $haystack): ?string
    {
        if ($part === null || $part === '' || mb_stripos($haystack, $part) !== false) {
            return null;
        }

        return $part;
    }
}
