<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\FederatedEvent;
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
 *
 * Events other Event Schedule sites share here (FederatedEvent, "from other sites") are posters
 * on the same wall: a listing is next on at the first of its own dates that has not finished
 * (nextListing()), counts as one schedule of its site (listingKey()), and is drawn in the same
 * shape as a local poster, with the site it comes from (remotePoster()). At most NETWORK_CAP of
 * them stand on the mixed wall, so one busy site cannot fill it; the page's "From other sites"
 * view lists every one (networkPick()).
 */
class BrowseWall
{
    /** Posters on the wall. */
    public const LIMIT = 24;

    /** The most posters from other sites on the mixed wall. Their own view has every one. */
    public const NETWORK_CAP = 4;

    /** Listings looked at for the wall and the count, soonest by their stored date first. */
    public const NETWORK_POOL = 2000;

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
     * When a listing from another site is next on, as next() is for an event, or null when it is
     * over. From its own dates (the occurrences its site resolved, or its start), each lasting
     * as long as its first did, or NO_LENGTH_HOURS. Never from next_occurrence_at, which is
     * stamped when the listing arrives and kept for a day after it has passed.
     *
     * @return array{at: Carbon, ends: Carbon, running: bool, date: ?string}|null
     */
    public static function nextListing(FederatedEvent $listing, ?Carbon $now = null): ?array
    {
        $now ??= Carbon::now('UTC');
        $zone = $listing->safeTimezone();

        $length = $listing->starts_at && $listing->ends_at && $listing->ends_at->gt($listing->starts_at)
            ? max(1, (int) round($listing->starts_at->diffInMinutes($listing->ends_at)))
            : self::NO_LENGTH_HOURS * 60;

        $starts = [];

        foreach ((array) ($listing->occurrences ?: []) as $occurrence) {
            try {
                $starts[] = Carbon::parse((string) $occurrence)->setTimezone($zone);
            } catch (\Throwable) {
                // One unreadable date costs that date, not the listing.
            }
        }

        // No dates of its own (intake refuses that, but a row is only a row): the one it was
        // stored with, as its single date.
        $only = $listing->starts_at ?? $listing->next_occurrence_at;

        if (! $starts && $only) {
            $starts[] = $only->copy()->setTimezone($zone);
        }

        usort($starts, fn (Carbon $a, Carbon $b) => $a->getTimestamp() <=> $b->getTimestamp());

        foreach ($starts as $at) {
            $ends = $at->copy()->addMinutes($length);

            if ($ends->gte($now)) {
                return ['at' => $at, 'ends' => $ends, 'running' => $at->lte($now), 'date' => null];
            }
        }

        return null;
    }

    /**
     * The schedule a listing counts against: its site's, by the schedule's own address, or its
     * name when the site sent none, or the listing alone. Never equal to a local schedule's key.
     */
    public static function listingKey(FederatedEvent $listing): string
    {
        $schedule = $listing->schedule_url ?: mb_strtolower(trim((string) $listing->schedule_name));

        return 'f'.$listing->federated_instance_id.':'.($schedule !== '' ? $schedule : 'e'.$listing->id);
    }

    /**
     * The mixed wall: local events and listings from other sites together, one for each schedule,
     * soonest first, and at most $networkCap listings. A tie on the start is settled by kind and
     * then id, never by comparing ids from two tables.
     *
     * @return Collection<int, Event|FederatedEvent>
     */
    public static function pickMixed(Collection $events, Collection $listings, int $limit = self::LIMIT, int $networkCap = self::NETWORK_CAP, ?Carbon $now = null): Collection
    {
        $now ??= Carbon::now('UTC');
        $on = [];

        foreach ($events as $event) {
            if ($next = self::next($event, $now)) {
                $on[] = [[$next['at']->getTimestamp(), 0, $event->id], $event];
            }
        }

        foreach ($listings as $listing) {
            if ($next = self::nextListing($listing, $now)) {
                $on[] = [[$next['at']->getTimestamp(), 1, $listing->id], $listing];
            }
        }

        usort($on, fn ($a, $b) => $a[0] <=> $b[0]);

        $taken = [];
        $picked = [];
        $network = 0;

        foreach ($on as [, $item]) {
            if (count($picked) >= $limit) {
                break;
            }

            $remote = $item instanceof FederatedEvent;

            if ($remote && $network >= $networkCap) {
                continue;
            }

            $keys = $remote ? [self::listingKey($item)] : DiscoveryUtils::scheduleKeys($item);

            if (array_intersect_key($taken, array_flip($keys))) {
                continue;
            }

            $taken += array_fill_keys($keys, true);
            $picked[] = $item;
            $network += $remote ? 1 : 0;
        }

        return collect($picked);
    }

    /**
     * Every listing from other sites that is still to come or on now, soonest first: the "From
     * other sites" view, and the count beside its switch. Not one per schedule there, or a site
     * with one busy schedule would show one poster.
     *
     * @return Collection<int, FederatedEvent>
     */
    public static function networkPick(Collection $listings, ?Carbon $now = null): Collection
    {
        $now ??= Carbon::now('UTC');
        $on = [];

        foreach ($listings as $listing) {
            if ($next = self::nextListing($listing, $now)) {
                $on[] = [[$next['at']->getTimestamp(), $listing->id], $listing];
            }
        }

        usort($on, fn ($a, $b) => $a[0] <=> $b[0]);

        return collect(array_column($on, 1));
    }

    /**
     * One event for each schedule: the one that is on next, soonest first.
     *
     * An event counts against every schedule it shows up under (DiscoveryUtils::scheduleKeys():
     * the one the poster credits, the one that made it, its talent and venues), so two events
     * that share a schedule are never both on the wall. The order of $pool does not matter.
     */
    public static function pick(Collection $pool, int $limit = self::LIMIT, ?Carbon $now = null): Collection
    {
        $now ??= Carbon::now('UTC');
        $on = [];

        foreach ($pool as $event) {
            if ($next = self::next($event, $now)) {
                // By start, so whatever is on now (it started before anything still to come) is
                // first. The id settles a tie.
                $on[] = [[$next['at']->getTimestamp(), $event->id], $event];
            }
        }

        usort($on, fn ($a, $b) => $a[0] <=> $b[0]);

        $taken = [];
        $picked = [];

        foreach ($on as [, $event]) {
            if (count($picked) >= $limit) {
                break;
            }

            $keys = DiscoveryUtils::scheduleKeys($event);

            if (array_intersect_key($taken, array_flip($keys))) {
                continue;
            }

            $taken += array_fill_keys($keys, true);
            $picked[] = $event;
        }

        return $pool->take(0)->concat($picked)->values();
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
     * @return array{tiles: array<int, array<string, mixed>>, bands: array<int, array<string, mixed>>, rows: array<string, array<string, mixed>>, count: int}
     */
    public static function build(Collection $events, array $more = [], ?Carbon $now = null, bool $plain = false): array
    {
        $now ??= Carbon::now('UTC');
        $posters = [];

        foreach ($events as $event) {
            $poster = $event instanceof FederatedEvent
                ? self::remotePoster($event, $now)
                : self::poster($event, $now, $more, $plain);

            if ($poster) {
                $posters[$poster['band']][] = $poster;
            }
        }

        $tiles = [];
        $bands = [];
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
        ];
    }

    /**
     * Listings from other sites as plain tiles, for a page that is not a wall (/search): every
     * listing it is given, each with its blurb. A listing whose dates have all passed (listable()
     * keeps one for a day) is shown at the date it was stored with.
     *
     * @return array{tiles: array<int, array<string, mixed>>}
     */
    public static function remote(Collection $listings, ?Carbon $now = null): array
    {
        $now ??= Carbon::now('UTC');
        $tiles = [];

        foreach ($listings as $listing) {
            if ($tile = self::remotePoster($listing, $now, true)) {
                $tiles[] = $tile;
            }
        }

        return ['tiles' => $tiles];
    }

    /**
     * A listing from another site, drawn as a local poster is: the same bands and words for when
     * it is on, its schedule's name linked to the schedule's own page (held to the site's own host
     * when it arrived), the place, and the site it comes from. Its picture keeps its recorded
     * shape and is served from the light copies once they are made (HasImageVariants).
     *
     * @return array<string, mixed>|null
     */
    public static function remotePoster(FederatedEvent $listing, Carbon $now, bool $stored = false): ?array
    {
        $next = self::nextListing($listing, $now);

        if (! $next && $stored && $listing->next_occurrence_at) {
            $at = $listing->next_occurrence_at->copy()->setTimezone($listing->safeTimezone());
            $next = ['at' => $at, 'ends' => $at, 'running' => false, 'date' => null];
        }

        if (! $next) {
            return null;
        }

        $moment = self::moment($next, $now, false);
        $size = $listing->imageSourceDimensions();
        $ratio = PosterWall::clampRatio($size ? $size[0] / $size[1] : null);

        return [
            'kind' => 'flyer',
            'remote' => true,
            'band' => $moment['band'],
            'ratio' => $ratio,
            'exact' => $size && abs($ratio - $size[0] / $size[1]) < 0.02,
            'name' => $listing->name,
            'url' => $listing->url,
            'image' => $listing->imageVariantUrl(ImageUtils::VARIANT_WIDTH) ?: $listing->imageUrl(),
            'srcset' => $listing->imageVariantSrcset(),
            'when' => $moment['when'],
            'time' => $moment['time'],
            'rhythm' => null,
            'stamp' => $moment['stamp'],
            'live' => $next['running'],
            'schedule' => $listing->schedule_name ?: null,
            'scheduleUrl' => $listing->schedule_name ? ($listing->schedule_url ?: null) : null,
            'more' => false,
            // "Online" comes from the sender's own flag, never from a missing venue.
            'place' => $listing->locationLabel() ?: ($listing->isOnline() ? __('messages.online') : ''),
            'country' => $listing->country_code ? CountryUtils::getName($listing->country_code) : '',
            'ink' => crc32(self::listingKey($listing)) % self::INKS,
            'long' => mb_strlen((string) $listing->name) > 44 ? 2 : (mb_strlen((string) $listing->name) > 22 ? 1 : 0),
            'source' => $listing->sourceHost(),
            'blurb' => $listing->short_description,
            'hash' => UrlUtils::encodeId($listing->id),
            'hidden' => false,
            'scheduleHash' => null,
        ];
    }

    /**
     * When something is on, in the wall's words: its band, the day ("Today", "Tomorrow", a
     * weekday, or a date), its time, and what a poster set in type prints large. Counted in whole
     * days between today WHERE IT HAPPENS and its start, on the calendar: a difference of
     * midnights is a fraction of a day across a clock change.
     *
     * @param  array{at: Carbon, ends: Carbon, running: bool, date: ?string}  $next
     * @return array{band: string, days: int, day: string, when: string, time: ?string, stamp: array<string, string>, today: Carbon}
     */
    private static function moment(array $next, Carbon $now, bool $several, ?string $range = null): array
    {
        $at = $next['at'];
        $today = $now->copy()->setTimezone($at->getTimezone());
        $days = (int) Carbon::parse($today->toDateString(), 'UTC')->diffInDays(Carbon::parse($at->toDateString(), 'UTC'), false);
        $band = match (true) {
            $next['running'] || $days <= 0 => 'today',
            $days <= 6 => 'week',
            default => 'later',
        };

        $year = $at->year !== $today->year ? ', '.$at->year : '';
        $day = match (true) {
            $days === 0 => 'Today',
            $days === 1 => 'Tomorrow',
            default => $at->format('D'),
        };

        return [
            'band' => $band,
            'days' => $days,
            'day' => $day,
            'when' => $range ?? ($days > 1 || $days < 0 ? $at->format('D, M j').$year : $day),
            'time' => $several ? null : $at->format('g:ia'),
            'stamp' => ['top' => $day, 'num' => $at->format('j'), 'mon' => $range ?? $at->format('M')],
            'today' => $today,
        ];
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

        $several = $event->is_multi_day && ! $next['date'];
        $today = $now->copy()->setTimezone($next['at']->getTimezone());
        $range = $several ? self::range($next['at'], $next['ends'], $today) : null;
        $moment = self::moment($next, $now, $several, $range);

        $role = $event->getViewableRole();
        $flyer = (bool) $event->flyer_image_url;
        $size = $flyer ? $event->imageSourceDimensions() : null;
        $ratio = $flyer ? PosterWall::clampRatio($size ? $size[0] / $size[1] : null) : 0.75;
        $key = self::scheduleKey($event);

        return [
            'kind' => $flyer ? 'flyer' : 'type',
            'band' => $moment['band'],
            'ratio' => $ratio,
            // A picture whose shape was never recorded, or is narrower or wider than any tile
            // may be, is fitted inside its box, and the box is filled with its own colours.
            'exact' => $flyer && $size && abs($ratio - $size[0] / $size[1]) < 0.02,
            'name' => $event->name,
            'url' => $url,
            'image' => $event->getImageUrl(480),
            'srcset' => $event->imageSrcset(),
            'when' => $moment['when'],
            'time' => $moment['time'],
            'rhythm' => self::rhythm($event),
            // What a poster set in type prints large: the day it is on.
            'stamp' => $moment['stamp'],
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
            // The admin's "Hide schedule", for the schedule the tile credits, where hiding it takes
            // this tile away (Event::isFromSchedule()). Not on the Hidden events list ($plain).
            'scheduleHash' => ! $plain && $role && $event->isFromSchedule($role) ? UrlUtils::encodeId($role->id) : null,
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
