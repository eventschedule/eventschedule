<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * What /browse draws: one poster for each schedule, its next event, in the order they happen.
 *
 * The page is a wall. Every tile is an array of plain values, worked out here once, so the view
 * holds no rule about dates, pictures or who is credited, and a test can read the wall without
 * rendering it.
 *
 * "Next" is worked out per event, not read off the query's order: a one-off is next at its start,
 * a festival that is already running is next NOW, and a series is next at its next occurrence
 * (Event::nextOccurrenceFrom(), which knows the rhythm, the skipped dates and the end of the
 * run). So a weekly night is on the wall under the day it is next on, not last under "Recurring"
 * with the date the series began, and a series that has run out is not on it at all.
 */
class BrowseWall
{
    /** Posters on the wall. */
    public const LIMIT = 24;

    /** Stretches of time, in the order the wall runs. */
    public const BANDS = ['today', 'week', 'later'];

    /** Papers for a poster set in type, for an event with no flyer of its own. Never purple. */
    public const INKS = 6;

    /** The most series looked at for the wall. Each is asked when it is next on, which SQL cannot be. */
    public const SERIES = 300;

    /**
     * How long an event with no recorded length is taken to last. The same six hours the door
     * card gives one (ScheduleActivity), and the same in MarketingController::browse()'s SQL.
     */
    public const NO_LENGTH_HOURS = 6;

    /**
     * When this event is next on, or null when it is over.
     *
     * at: the start, on the clock of the place it happens. ends: when that occurrence finishes.
     * running: it has started and not finished. date: for a series, the day of the occurrence.
     *
     * @return array{at: Carbon, ends: Carbon, running: bool, date: ?string}|null
     */
    public static function next(Event $event, ?Carbon $now = null): ?array
    {
        if (! $event->starts_at) {
            return null;
        }

        $now ??= Carbon::now('UTC');
        $length = $event->durationInMinutes() > 0 ? $event->durationInMinutes() : self::NO_LENGTH_HOURS * 60;

        if (! $event->days_of_week) {
            $at = $event->getStartDateTime(null, true);
            $ends = $at->copy()->addMinutes($length);

            return $ends->lt($now) ? null : ['at' => $at, 'ends' => $ends, 'running' => $at->lte($now), 'date' => null];
        }

        // A series: today's occurrence if it has not finished, else the one after.
        $day = $now->copy()->setTimezone($event->scheduleTimezone())->startOfDay();

        foreach ([$day, $day->copy()->addDay()] as $from) {
            $date = $event->nextOccurrenceFrom($from->format('Y-m-d'));

            if (! $date) {
                return null;
            }

            $at = $event->getStartDateTime($date, true);
            $ends = $at->copy()->addMinutes($length);

            if ($ends->gte($now)) {
                return ['at' => $at, 'ends' => $ends, 'running' => $at->lte($now), 'date' => $date];
            }
        }

        return null;
    }

    /**
     * One event for each schedule: the one that is on next, soonest first.
     *
     * The schedule is the one the poster credits (Event::getViewableRole()), because a visitor
     * counts repeats by the name they read. The order of $pool does not matter.
     */
    public static function pick(Collection $pool, int $limit = self::LIMIT, ?Carbon $now = null): Collection
    {
        $now ??= Carbon::now('UTC');
        $best = [];

        foreach ($pool as $event) {
            $next = self::next($event, $now);

            if (! $next) {
                continue;
            }

            // By start, so whatever is on now (it started before anything still to come) is
            // first. The id settles a tie.
            $order = [$next['at']->getTimestamp(), $event->id];
            $key = self::scheduleKey($event);

            if (! isset($best[$key]) || $order < $best[$key][0]) {
                $best[$key] = [$order, $event];
            }
        }

        usort($best, fn ($a, $b) => $a[0] <=> $b[0]);

        return $pool->take(0)->concat(array_column(array_slice($best, 0, $limit), 1))->values();
    }

    /** The schedules on the wall that have something else coming up as well. */
    public static function schedulesWithMore(Collection $pool, Collection $picked, ?Carbon $now = null): array
    {
        $now ??= Carbon::now('UTC');
        $shown = $picked->mapWithKeys(fn (Event $event) => [$event->id => true])->all();
        $more = [];

        foreach ($pool as $event) {
            if (! isset($shown[$event->id]) && self::next($event, $now)) {
                $more[self::scheduleKey($event)] = true;
            }
        }

        return $more;
    }

    /** The same schedule the homepage counts an event against, so the two pages cannot disagree. */
    public static function scheduleKey(Event $event): string
    {
        return DiscoveryUtils::scheduleKey($event);
    }

    /**
     * @param  array<string, bool>  $more  schedulesWithMore()
     * @return array{tiles: array<int, array<string, mixed>>, bands: array<int, array<string, mixed>>, rows: array<string, array<string, mixed>>, count: int, countries: int}
     */
    public static function build(Collection $events, array $more = [], ?Carbon $now = null, bool $plain = false): array
    {
        $now ??= Carbon::now('UTC');
        $posters = [];

        foreach ($events as $event) {
            if ($poster = self::poster($event, $now, $more, $plain)) {
                $posters[$poster['band']][] = $poster;
            }
        }

        $tiles = [];
        $bands = [];
        $countries = [];
        $count = 0;

        foreach (self::BANDS as $band) {
            if (empty($posters[$band])) {
                continue;
            }

            $label = [
                'band' => $band,
                'title' => ['today' => 'Today', 'week' => 'This week', 'later' => 'Later'][$band],
                'count' => count($posters[$band]),
            ];
            $bands[] = $label;

            if (! $plain) {
                $tiles[] = ['kind' => 'label', 'label' => true] + $label;
            }

            foreach ($posters[$band] as $poster) {
                $tiles[] = $poster;
                $count++;

                if ($poster['country']) {
                    $countries[$poster['country']] = true;
                }
            }
        }

        if ($tiles && ! $plain) {
            // The wall ends on one poster that is not printed yet: the next organizer's. It has
            // no picture to be true to, so it is whatever shape, portrait to nearly square,
            // brings the last row flush.
            $tiles[] = ['kind' => 'blank', 'ratio' => 0.75, 'stretch' => [0.62, 1.1]];
        }

        return [
            'tiles' => $tiles,
            'bands' => $bands,
            // An even grid (the admin's hidden list) has no rows to work out.
            'rows' => $plain ? [] : PosterWall::layout($tiles),
            'count' => $count,
            'countries' => count($countries),
        ];
    }

    /**
     * Listings other Event Schedule sites share here. Their pictures are copies kept on this
     * install with no recorded shape, so each is fitted inside one box.
     *
     * @return array{tiles: array<int, array<string, mixed>>}
     */
    public static function remote(Collection $listings, ?Carbon $now = null): array
    {
        $now ??= Carbon::now('UTC');
        $tiles = [];

        foreach ($listings as $listing) {
            $zone = $listing->safeTimezone();
            $at = $listing->next_occurrence_at?->copy()->setTimezone($zone);

            $tiles[] = [
                'kind' => 'flyer',
                'remote' => true,
                'ratio' => 1.0,
                'exact' => false,
                'name' => $listing->name,
                'url' => $listing->url,
                'image' => $listing->imageUrl(),
                'srcset' => null,
                'when' => $at ? $at->format('D, M j').($at->year !== $now->year ? ', '.$at->year : '') : '',
                'time' => $at ? $at->format('g:ia').' '.$at->format('T') : null,
                'rhythm' => null,
                'live' => false,
                'schedule' => null,
                'scheduleUrl' => null,
                'more' => false,
                // "Online" comes from the sender's own flag, never from a missing venue.
                'place' => $listing->locationLabel() ?: ($listing->isOnline() ? __('messages.online') : ''),
                'country' => '',
                'source' => $listing->sourceHost(),
                'hash' => UrlUtils::encodeId($listing->id),
                'hidden' => false,
            ];
        }

        return ['tiles' => $tiles];
    }

    /** @return array<string, mixed>|null */
    private static function poster(Event $event, Carbon $now, array $more, bool $plain): ?array
    {
        $next = self::next($event, $now);

        if (! $next && $plain && $event->starts_at) {
            // The admin's hidden list shows what is hidden, over or not.
            $at = $event->getStartDateTime(null, true);
            $next = ['at' => $at, 'ends' => $at, 'running' => false, 'date' => null];
        }

        $url = $next ? $event->getGuestUrl(false, $next['date']) : null;

        if (! $next || ! $url) {
            return null;
        }

        $at = $next['at'];
        // Whole days between today WHERE IT HAPPENS and its start, counted on the calendar: a
        // difference of midnights is a fraction of a day across a clock change.
        $today = $now->copy()->setTimezone($at->getTimezone());
        $days = (int) Carbon::parse($today->toDateString(), 'UTC')->diffInDays(Carbon::parse($at->toDateString(), 'UTC'), false);
        $band = match (true) {
            $next['running'] || $days <= 0 => 'today',
            $days <= 6 => 'week',
            default => 'later',
        };

        $several = $event->is_multi_day && ! $next['date'];
        $year = $at->year !== $today->year ? ', '.$at->year : '';
        $day = match (true) {
            $days === 0 => 'Today',
            $days === 1 => 'Tomorrow',
            default => $at->format('D'),
        };
        $range = $several ? self::range($at, $next['ends'], $today) : null;

        $role = $event->getViewableRole();
        $flyer = (bool) $event->flyer_image_url;
        $size = $flyer ? $event->imageSourceDimensions() : null;
        $ratio = $flyer ? PosterWall::clampRatio($size ? $size[0] / $size[1] : null) : 0.75;
        $key = self::scheduleKey($event);

        return [
            'kind' => $flyer ? 'flyer' : 'type',
            'band' => $band,
            'ratio' => $ratio,
            // A picture whose shape was never recorded, or is narrower or wider than any tile
            // may be, is fitted inside its box, and the box is filled with its own colours.
            'exact' => $flyer && $size && abs($ratio - $size[0] / $size[1]) < 0.02,
            'name' => $event->name,
            'url' => $url,
            'image' => $event->getImageUrl(480),
            'srcset' => $event->imageSrcset(),
            'when' => $range ?? ($days > 1 || $days < 0 ? $at->format('D, M j').$year : $day),
            'time' => $several ? null : $at->format('g:ia'),
            'rhythm' => self::rhythm($event),
            // What a poster set in type prints large: the day it is on.
            'stamp' => ['top' => $day, 'num' => $at->format('j'), 'mon' => $range ?? $at->format('M')],
            'live' => $next['running'],
            'schedule' => $role?->name,
            'scheduleUrl' => $role && self::linkable($role, $event) ? ($role->getGuestUrl() ?: null) : null,
            'more' => isset($more[$key]),
            'place' => trim((string) ($role?->city ?? '')),
            'country' => $role?->country_code ? CountryUtils::getName($role->country_code) : '',
            'ink' => $role ? ($role->id % self::INKS) : 0,
            'long' => mb_strlen($event->name) > 44 ? 2 : (mb_strlen($event->name) > 22 ? 1 : 0),
            'hash' => $event->hashedId(),
            'hidden' => (bool) $event->is_hidden_from_discovery,
        ];
    }

    /**
     * Whether the credited schedule's own page may be linked. getViewableRole() reads every
     * schedule on the event, and the old card only ever printed the name: a link is more, so it
     * is offered only for a schedule that is listed, not deleted, and has accepted the event.
     */
    private static function linkable(Role $role, Event $event): bool
    {
        if ($role->is_deleted || $role->is_unlisted) {
            return false;
        }

        return $role->pivot ? (bool) $role->pivot->is_accepted : $role->id === $event->creator_role_id;
    }

    /** How a series repeats, in a word. English, like everything else on the marketing site. */
    private static function rhythm(Event $event): ?string
    {
        if (! $event->days_of_week) {
            return null;
        }

        return match ($event->recurring_frequency ?? 'weekly') {
            'daily' => 'Daily',
            'every_n_weeks' => 'Every '.max(2, (int) ($event->recurring_interval ?? 2)).' weeks',
            'monthly_date', 'monthly_weekday' => 'Monthly',
            'yearly' => 'Yearly',
            default => $event->days_of_week === '1111111' ? 'Daily' : 'Weekly',
        };
    }

    /** "Oct 14 - 15", with the year only when it is not this one. */
    private static function range(Carbon $from, Carbon $to, Carbon $today): string
    {
        if ($from->year !== $to->year) {
            return $from->format('M j, Y').' - '.$to->format('M j, Y');
        }

        return $from->format('M j').' - '.$to->format($from->month === $to->month ? 'j' : 'M j').($to->year !== $today->year ? ', '.$to->year : '');
    }
}
