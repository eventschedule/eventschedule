<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Group;
use App\Models\Role;
use App\Repos\EventRepo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The venue map on a schedule's guest page: which venues are on it, where each one is, and what
 * the band, the open map and the schedule's owner are told. One rule for all three, so their
 * numbers cannot disagree.
 *
 * WHICH VENUES. The query is Role::logoWallRoles()'s, without its logo requirement and its cap:
 * a venue of an event that is accepted on this schedule and is not draft, cancelled, unlisted or
 * password-protected, where the venue's own answer is yes (or unanswered, for a placeholder nobody
 * owns). Bounded by time: an event from GRACE_DAYS ago onwards, so a venue stays through a quiet
 * few weeks instead of the map shrinking the day after its last show. Read fresh on every request:
 * an event that is unpublished takes its venue off the map at once.
 *
 * WHERE. From PlaceLookupService's answer for the venue's own address, never from roles.geo_lat /
 * geo_lon: those are Google's, and Google's terms allow them on a Google map only.
 *
 * A venue is one of: placed (a pin), approximate (a pin at a small place's centre), waiting (its
 * address has not been asked yet), no_address, not_found. The public is never shown a waiting
 * venue: "not on the map" would be said of a venue that is only in the queue. A map's FIRST pass
 * is published whole (ready_at); a venue added afterwards joins the map when it has been placed.
 */
class VenueMap
{
    /** How long a venue stays on the map after its last event. */
    public const GRACE_DAYS = 60;

    public const CAP = 200;

    private const EVENTS_PER_VENUE = 3;

    public const PLACED = 'placed';

    public const APPROXIMATE = 'approximate';

    public const WAITING = 'waiting';

    public const NO_ADDRESS = 'no_address';

    public const NOT_FOUND = 'not_found';

    /** How long the closed band is served from the cache. */
    private const BAND_SECONDS = 300;

    private const VERSION_KEY = 'venue_map.version.';

    /** Whether this install has a venue map at all: it needs an address search to place pins. */
    public static function available(): bool
    {
        return PlaceLookupService::enabled();
    }

    /**
     * Whether the schedule form offers the map. A venue schedule's venue is itself. And never a
     * demo schedule: anybody can sign in to the demo, and whatever they typed as a venue's
     * address would be sent to the address search under this install's name.
     */
    public static function offeredTo(Role $role): bool
    {
        return self::available() && ! $role->isVenue() && ! is_demo_role($role);
    }

    public static function enabledFor(Role $role): bool
    {
        return self::offeredTo($role) && (bool) $role->venueMapSetting->enabled;
    }

    /** Whether the map has had its first pass: every venue's address has been asked once. */
    public static function ready(Role $role): bool
    {
        return (bool) $role->venueMapSetting->ready_at;
    }

    public static function startsOpen(Role $role): bool
    {
        return (bool) $role->venueMapSetting->starts_open;
    }

    /**
     * Store the owner's two switches. The settings live in a table of their own (`roles` is at
     * MySQL's row-size limit), so the schedule form's save hands them here instead of filling them.
     * ready_at is never touched: a map switched off and on again keeps its first pass.
     */
    public static function saveSettings(Role $role, bool $enabled, bool $startsOpen): void
    {
        \App\Models\VenueMapSetting::updateOrCreate(['role_id' => $role->id], ['enabled' => $enabled, 'starts_open' => $enabled && $startsOpen]);
        $role->unsetRelation('venueMapSetting');
        self::changed($role);
    }

    /**
     * Something the band is drawn from was changed by its owner (a switch, a pin, a venue taken
     * off) or by the first pass finishing: the cached band is not to be served again. A counter
     * in the cache, read into the band's key, because the keys cannot be listed to forget them.
     */
    public static function changed(Role $role): void
    {
        Cache::forever(self::VERSION_KEY.$role->id, ((int) Cache::get(self::VERSION_KEY.$role->id, 0)) + 1);
    }

    /**
     * Every venue on $role's map, with where it is and why not when it is nowhere.
     *
     * With $register, an address nobody has asked about yet is noted for the runner, a miss old
     * enough to be asked again goes back in the queue, and a row read for the first time today is
     * dated as still needed. Those are the writes a page view can make here, each of them once:
     * an INSERT IGNORE for a new address, an UPDATE for a stale miss, an UPDATE a day for the date.
     *
     * The owner's own decisions come first (venue_map_marks): a venue taken off this map is still
     * returned, flagged `hidden`, for the owner's list to put back, and every public reader leaves
     * it out; a pin placed by hand is where the venue is, whatever the search said or did not say
     * (`by_hand`).
     *
     * @return Collection<int, array{venue: Role, upcoming: bool, state: string, why: ?string, lat: ?float, lon: ?float, hash: ?string, street: bool, hidden: bool, by_hand: bool}>
     */
    public static function venues(Role $role, ?Group $group = null, bool $register = true): Collection
    {
        // Venue id => whether something is still to come there.
        $upcoming = self::venueIds($role, $group, Carbon::now('UTC'));

        if ($upcoming->isEmpty()) {
            return collect();
        }

        $venues = Role::query()->whereIn('id', $upcoming->keys())->orderBy('name')->orderBy('id')->get();
        $addresses = $venues->mapWithKeys(fn (Role $venue) => [$venue->id => PlaceLookupService::addressFor($venue, $role)]);

        // An online event's "venue" (a meeting link a calendar import stored as an address) is not
        // a place: it is on nobody's map and in nobody's list, the owner's included.
        $venues = $venues->reject(fn (Role $venue) => $addresses[$venue->id]['why'] === 'online')->values();
        $rows = PlaceLookupService::rows($addresses->pluck('hash')->all());
        $marks = \App\Models\VenueMapMark::where('role_id', $role->id)->whereIn('venue_id', $venues->pluck('id'))->get()->keyBy('venue_id');

        if ($register) {
            // A venue its owner took off the map is not asked about: nobody will see the answer.
            $wanted = $addresses->filter(fn (array $a, int $venueId) => $a['hash'] !== null && ! $marks->get($venueId)?->hidden);

            PlaceLookupService::register($wanted->filter(fn (array $a) => ! $rows->has($a['hash']))->unique('hash')->values()->all());
            // Not ->only(): on an Eloquent collection that reads its argument as PRIMARY keys,
            // whatever the collection itself is keyed by, and no row here has a hash for an id.
            $wantedHashes = $wanted->pluck('hash')->flip();
            PlaceLookupService::stillNeeded($rows->filter(fn (\App\Models\PlaceLookup $row) => $wantedHashes->has($row->address_hash)));
        }

        return $venues->map(function (Role $venue) use ($addresses, $rows, $upcoming, $register, $marks) {
            $address = $addresses[$venue->id];
            $row = $address['hash'] !== null ? $rows->get($address['hash']) : null;

            if ($row && $register && PlaceLookupService::requeueIfStale($row)) {
                $row = null;
            }

            [$state, $lat, $lon] = match (true) {
                $address['hash'] === null => [self::NO_ADDRESS, null, null],
                ! $row || $row->status === PlaceLookupService::PENDING => [self::WAITING, null, null],
                $row->status === PlaceLookupService::FOUND => [self::PLACED, $row->lat, $row->lon],
                $row->status === PlaceLookupService::APPROXIMATE => [self::APPROXIMATE, $row->lat, $row->lon],
                default => [self::NOT_FOUND, null, null],
            };

            $mark = $marks->get($venue->id);
            $byHand = $mark && $mark->lat !== null && $mark->lon !== null;

            return [
                'venue' => $venue,
                'upcoming' => (bool) $upcoming->get($venue->id),
                'state' => $byHand ? self::PLACED : $state,
                'why' => $byHand ? null : $address['why'],
                'lat' => $byHand ? $mark->lat : $lat,
                'lon' => $byHand ? $mark->lon : $lon,
                'hash' => $address['hash'],
                'street' => $address['street'],
                'hidden' => (bool) $mark?->hidden,
                'by_hand' => $byHand,
                // Where the search put it, kept beside a pin moved by hand so it can be gone back to.
                'found' => $lat !== null ? [$lat, $lon] : null,
            ];
        })->values();
    }

    /**
     * The venues of $role's public events of the last GRACE_DAYS and to come, each with whether
     * something is still to come there.
     *
     * er1 is this schedule's side of an event, er2 the venue's. The venue is the event's FIRST
     * venue (the lowest pivot id), which is the one Event::getVenueAttribute() and the list's own
     * venue filter mean: a second venue on an event would be a pin whose "see all events here"
     * finds none.
     *
     * Two things here are for a schedule that lists tens of thousands of events, measured on one
     * that lists 30,000 beside as many of other schedules:
     *   - the read starts from this schedule's own rows and the order is HELD (straight_join).
     *     Left to choose, MySQL started from every venue on the install and walked all of their
     *     rows: 470 ms against 145, and a cost that grew with the install, not the schedule;
     *   - it is ONE walk. "Still to come" was a second run of the same query with a later date.
     *
     * @return Collection<int, bool>
     */
    private static function venueIds(Role $role, ?Group $group, Carbon $now): Collection
    {
        // "Still to come", as an expression: the rule the window below is, asked of now.
        $coming = DB::query();
        Event::constrainToOccurrencesSince($coming, $now);
        $comingSql = preg_replace('/^\s*where\s+/i', '', $coming->getGrammar()->compileWheres($coming));

        return DB::table('event_role as er1')
            ->join('events', 'events.id', '=', 'er1.event_id')
            ->join('event_role as er2', 'er2.event_id', '=', 'er1.event_id')
            ->join('roles', 'roles.id', '=', 'er2.role_id')
            ->where('er1.role_id', $role->id)
            ->where('er1.is_accepted', true)
            ->when($group, fn ($q) => $q->where('er1.group_id', $group->id))
            // The venue must not be advertised without its consent: an explicit yes for a claimed
            // one, and for a placeholder nobody owns, anything but a no. Role::logoWallRoles()
            // explains why a placeholder's unanswered row counts.
            ->where(fn ($q) => $q->where('er2.is_accepted', true)
                ->orWhere(fn ($q2) => $q2->whereNull('er2.is_accepted')->whereNull('roles.user_id')))
            ->whereRaw('er2.id = (select min(v.id) from event_role v join roles vr on vr.id = v.role_id where v.event_id = er2.event_id and vr.type = ?)', ['venue'])
            ->where('events.is_draft', false)
            ->where('events.is_private', false)
            ->where('events.is_cancelled', false)
            ->where(fn ($q) => Event::constrainNotPasswordProtected($q))
            ->where(fn ($q) => Event::constrainToOccurrencesSince($q, $now->copy()->subDays(self::GRACE_DAYS)))
            ->where('roles.id', '!=', $role->id)
            ->where('roles.type', 'venue')
            ->where('roles.is_deleted', false)
            ->groupBy('roles.id')
            ->orderBy('roles.id')
            ->limit(self::CAP)
            ->selectRaw('straight_join roles.id, max(case when '.$comingSql.' then 1 else 0 end) as upcoming', $coming->getBindings())
            ->pluck('upcoming', 'id')
            ->map(fn ($flag) => (bool) $flag);
    }

    /**
     * What the closed band shows, or null when there is no band: the map is off, not ready yet,
     * or fewer than two venues have a pin (one pin is what an event page already shows).
     *
     * $upcoming is the page's own list of coming events (EventRepo::upcomingForGuest()), used only
     * to put the venues with the soonest events first among the logos.
     *
     * @return array{logos: array<int, string>, total: int, towns: array<int, string>, more_towns: bool}|null
     */
    public static function band(Role $role, ?Group $group = null, ?Collection $upcoming = null, ?string $lang = null): ?array
    {
        if (! self::enabledFor($role) || ! self::ready($role)) {
            return null;
        }

        $lang ??= $role->displayLanguageCode();

        // On every view of the schedule's page, so not worked out on every view: the venue set is
        // two joins over everything the schedule ever listed, and up to 200 whole schedule rows,
        // for five logos and three town names. Served for five minutes, and not at all once its
        // owner has changed something (changed()). Whether there is a map is decided above, fresh.
        $key = 'venue_map.band.'.$role->id.'.'.($group?->id ?? 0).'.'.$lang.'.'.((int) Cache::get(self::VERSION_KEY.$role->id, 0));

        // false, not null, for "no band": a null is never stored, and would be worked out each time.
        return Cache::remember($key, self::BAND_SECONDS, fn () => self::bandNow($role, $group, $upcoming, $lang) ?? false) ?: null;
    }

    private static function bandNow(Role $role, ?Group $group, ?Collection $upcoming, string $lang): ?array
    {
        $venues = self::venues($role, $group)->reject(fn (array $v) => $v['hidden'] || $v['state'] === self::WAITING)->values();
        $placed = $venues->filter(fn (array $v) => $v['lat'] !== null)->values();

        if ($placed->count() < 2) {
            return null;
        }

        $soonest = collect($upcoming ?? [])->map(fn (array $row) => $row['event']->venue?->id)->filter()->unique()->values()->flip();
        $ordered = $placed->sortBy(fn (array $v) => [$soonest->get($v['venue']->id, PHP_INT_MAX), $v['upcoming'] ? 0 : 1, $v['venue']->name])->values();

        $towns = $placed->map(fn (array $v) => trim((string) $v['venue']->textInLanguage('city', $lang)))->filter()
            ->countBy()->sortDesc()->keys();

        return [
            'logos' => $ordered->map(fn (array $v) => $v['venue']->profile_image_url ? $v['venue']->getProfileImageUrl(480) : null)->filter()->take(5)->values()->all(),
            'total' => $venues->count(),
            'towns' => $towns->take(3)->values()->all(),
            'more_towns' => $towns->count() > 3,
        ];
    }

    /**
     * What the open map is sent. A waiting venue is left out. Dates are said by the server, in the
     * schedule's own timezone and the visitor's language, so the browser does no date arithmetic.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function payload(Role $role, ?Group $group, string $lang): array
    {
        $venues = self::venues($role, $group)->reject(fn (array $v) => $v['hidden'] || $v['state'] === self::WAITING)->values();

        if ($venues->isEmpty()) {
            return [];
        }

        $use24 = get_use_24_hour_time($role);

        // What the list itself can show, soonest first, grouped by the venue the list files each
        // event under.
        $upcoming = app(EventRepo::class)->upcomingForGuest($role, $group, self::CAP)->values();
        $coming = $upcoming->groupBy(fn (array $row) => $row['event']->venue?->id ?? 0);
        $soonest = $upcoming->map(fn (array $row) => $row['event']->venue?->id ?? 0)->unique()->values()->flip();

        // The venue with the soonest event first, as a visitor reads the list beside the map; the
        // ones with nothing listed after them, by name.
        $venues = $venues->sortBy(fn (array $v) => [$soonest->get($v['venue']->id, PHP_INT_MAX), $v['venue']->name])->values();

        return $venues->map(function (array $v) use ($role, $lang, $coming, $use24) {
            $venue = $v['venue'];
            $rows = $coming->get($venue->id, collect())->values();
            $first = $rows->first();
            $name = $venue->nameInLanguage($lang);
            $street = $v['street'] ? trim((string) $venue->textInLanguage('address1', $lang)) : '';
            $town = trim((string) $venue->textInLanguage('city', $lang));
            $where = implode(', ', array_filter([$street, $town]));

            $event = function (array $row) use ($role, $lang, $use24) {
                $title = $row['event']->nameInLanguage($lang, $role);

                // Short, for a row a third of the page wide: "Today 21:00", "Wed, Oct 14 20:30".
                // On the clock of the schedule the event belongs to, as every date of it is
                // (Event::scheduleTimezone()), and today is today THERE.
                $zone = $row['event']->scheduleTimezone();
                $start = $row['event']->getStartDateTime($row['date'], true, $zone);
                $day = $start->format('Y-m-d');
                $time = $start->format($use24 ? 'H:i' : 'g:i A');

                return [
                    'name' => $title,
                    'dir' => content_dir_for_language($title, $row['event']->creatorRole?->language_code ?: $lang),
                    'when' => match (true) {
                        $day === Carbon::now($zone)->format('Y-m-d') => __('messages.today').' '.$time,
                        $day === Carbon::now($zone)->addDay()->format('Y-m-d') => __('messages.tomorrow').' '.$time,
                        default => self::shortDay($start, $lang).' '.$time,
                    },
                    'url' => $row['event']->getGuestUrl($role->subdomain, $row['event']->days_of_week ? $row['date'] : null),
                ];
            };

            // Whether its next event is today or within a week, on that EVENT's own clock (the same
            // zone its row is dated in, or the pin says "today" beside a row that says Tomorrow),
            // and a show that has begun and is still on is on today.
            $soon = function (array $row) {
                $zone = $row['event']->scheduleTimezone();
                $start = $row['event']->getStartDateTime($row['date'], true, $zone);
                $now = Carbon::now($zone);

                return match (true) {
                    $start->lte($now) || $start->format('Y-m-d') === $now->format('Y-m-d') => 'today',
                    $start->format('Y-m-d') <= $now->copy()->addDays(6)->format('Y-m-d') => 'week',
                    default => null,
                };
            };

            return [
                'key' => $venue->subdomain,
                'name' => $name,
                'dir' => content_dir_for_language($name, $venue->language_code ?: $lang),
                'address' => $where,
                'town' => $town,
                'lat' => $v['lat'],
                'lon' => $v['lon'],
                'approx' => $v['state'] === self::APPROXIMATE,
                // "No street address" only when that IS the reason: a venue with a full address
                // and no country was told to visitors as having none.
                'why' => $v['lat'] !== null ? null : ($v['why'] === 'no_street' ? 'no_address' : 'not_found'),
                'logo' => $venue->profile_image_url ? $venue->getProfileImageUrl(480) : null,
                'url' => $venue->isClaimed() ? ($venue->getGuestUrl() ?: null) : null,
                // By the address, never by coordinates: Google gets what it would be given on any
                // other page, and the two sets of coordinates stay apart.
                'directions' => $v['street'] && $where !== '' ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($where) : null,
                // Something still to come here, even where the list above could not hold it.
                'upcoming' => $v['upcoming'],
                'soon' => $first ? $soon($first) : null,
                'next' => $first ? $event($first) : null,
                'events' => $rows->take(self::EVENTS_PER_VENUE)->map($event)->values()->all(),
                // Whether the list holds more than the panel shows: more rows, a series (one row
                // here, many dates there), or events the cap above did not reach.
                'more' => $rows->count() > self::EVENTS_PER_VENUE
                    || $rows->contains(fn (array $row) => (bool) $row['event']->days_of_week)
                    || ($rows->isEmpty() && $v['upcoming']),
            ];
        })->all();
    }

    /**
     * A day in a few characters, in the order the language puts them: "Wed, Oct 14", "mer. 14
     * oct.". The short sibling of DateUtils::dayLabel(), with the same fallback where the intl
     * extension is missing.
     */
    private static function shortDay(\Carbon\CarbonInterface $day, string $locale): string
    {
        $withYear = ! $day->isSameYear(Carbon::now($day->getTimezone()));

        if (class_exists(\IntlDatePatternGenerator::class)) {
            try {
                $pattern = (new \IntlDatePatternGenerator($locale))->getBestPattern($withYear ? 'yMMMEEEd' : 'MMMEEEd');
                $text = $pattern ? (new \IntlDateFormatter($locale, \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, $day->getTimezone()->getName(), null, $pattern))->format($day) : false;

                if (is_string($text) && $text !== '') {
                    return $text;
                }
            } catch (\Throwable $e) {
                // An unknown locale or zone name: the plain form below.
            }
        }

        return $day->translatedFormat($withYear ? 'D, M j, Y' : 'D, M j');
    }

    /**
     * What the schedule's owner is told: every venue, its state, and how far the first pass is.
     *
     * @return array{ready: bool, total: int, asked: int, placed: int, venues: array<int, array<string, mixed>>}
     */
    public static function status(Role $role, ?\App\Models\User $viewer = null): array
    {
        $venues = self::venues($role);
        $onMap = $venues->reject(fn (array $v) => $v['hidden'])->values();
        $lang = $role->displayLanguageCode();

        // The schedules this person may edit, read once: asking per venue is a query per row.
        $editable = $viewer ? $viewer->editor()->pluck('subdomain')->flip() : collect();

        // Problems first, as the owner's list shows them; what was taken off the map last.
        $order = [self::NOT_FOUND => 0, self::NO_ADDRESS => 1, self::WAITING => 2, self::APPROXIMATE => 3, self::PLACED => 4];

        return [
            'ready' => self::ready($role),
            // The counts are about the map: a venue taken off it is not waited for or counted.
            'total' => $onMap->count(),
            'asked' => $onMap->reject(fn (array $v) => $v['state'] === self::WAITING)->count(),
            'placed' => $onMap->filter(fn (array $v) => $v['lat'] !== null)->count(),
            'venues' => $venues->sortBy(fn (array $v) => [$v['hidden'] ? 1 : 0, $order[$v['state']], $v['venue']->name])->map(function (array $v) use ($lang, $editable) {
                $venue = $v['venue'];
                $raw = $venue->getAttributes();

                return [
                    // The handle the mark endpoints take: an id is never shown raw.
                    'id' => \App\Utils\UrlUtils::encodeId($venue->id),
                    'key' => $venue->subdomain,
                    'name' => $venue->nameInLanguage($lang),
                    'address' => implode(', ', array_filter(array_map(fn ($part) => trim((string) ($raw[$part] ?? '')), ['address1', 'city']))),
                    'state' => $v['state'],
                    'why' => $v['why'],
                    'hidden' => $v['hidden'],
                    'by_hand' => $v['by_hand'],
                    'lat' => $v['lat'],
                    'lon' => $v['lon'],
                    // Whether the address search has a position of its own to go back to.
                    'found' => $v['found'] !== null,
                    'claimed' => $venue->isClaimed(),
                    // Where the address is changed, for someone who may change it. A venue nobody
                    // has claimed is edited inside its events; one that is another person's, only
                    // by them.
                    'edit_url' => $venue->isClaimed() && $editable->has($venue->subdomain)
                        ? route('role.edit', ['subdomain' => $venue->subdomain]).'#section-address'
                        : null,
                ];
            })->values()->all(),
        ];
    }

    /**
     * Mark the map ready once every venue's address has been asked: from then on the band may
     * show. A row in the map's own table, so the schedule's saving hook and updated_at never move.
     */
    public static function refreshReady(Role $role): bool
    {
        if (! self::enabledFor($role) || self::ready($role)) {
            return self::ready($role);
        }

        // A venue the owner took off the map, or placed by hand, is not waited for.
        if (self::venues($role)->contains(fn (array $v) => $v['state'] === self::WAITING && ! $v['hidden'])) {
            return false;
        }

        \App\Models\VenueMapSetting::where('role_id', $role->id)->update(['ready_at' => now()]);
        $role->unsetRelation('venueMapSetting');
        self::changed($role);

        return true;
    }

    /** The addresses of $role's venues that are still waiting, for a run limited to them. */
    public static function waitingHashes(Role $role): array
    {
        return self::venues($role)->filter(fn (array $v) => $v['state'] === self::WAITING)->pluck('hash')->filter()->unique()->values()->all();
    }
}
