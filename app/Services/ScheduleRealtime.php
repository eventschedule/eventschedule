<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\RealtimeRows;
use App\Utils\RealtimeTracker;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * What a schedule's owner sees on the Realtime tab of /analytics, and in the Realtime tile of their dashboard: the live
 * traffic to their OWN guest pages, and nothing about anyone's identity.
 *
 * It reads the table /admin/realtime reads and shares none of that page's code (RealtimeDashboard),
 * on purpose. Most of that service is platform-wide or carries names, and one of its helpers would
 * name any schedule's draft event from a URL parameter. Here there is one query, and the rules that
 * keep it from leaking are in it and in COLUMNS, where a mistake is a missing row and never an
 * extra one:
 *
 *  - Every limit is in SQL, before the cap: the viewer's schedules, guest pages and embedded
 *    calendars only, never a platform admin's visit, never a member of the schedule looking at
 *    their own page (is_team, which is signed at render and so holds for a team member who
 *    declined cookies too).
 *  - It never selects hit_key, path, title, browser, os, utm_campaign or user_id, and never loads
 *    a user. A column that is not selected cannot be printed by a later change to this file.
 *  - People are told apart by visitor_key and by nothing else, and the key itself never leaves:
 *    a row carries a handle, a keyed hash of the key with the viewer's id and a per-session salt,
 *    so a handle means nothing outside the session that is looking: it cannot follow a visitor
 *    from one day, one device or one owner to another. (Two owners watching the same schedule
 *    at the same moment do see rows with the same country, device and page; the handle is not
 *    what could tell them so.)
 *  - A person is listed only when their row is owner_visible: the cookie notice they accepted
 *    said that the organizer of a schedule page sees visits to it, which the choice records and
 *    the beacon endpoint reads back (RealtimeTracker::consentCoversOrganizers()). Everyone else,
 *    including a visitor who accepted an earlier notice, is a page view and never a row.
 *  - A source is read only from the page view a visit BEGAN on. Every later page view inherits
 *    the source of wherever the visit started, which may be another schedule's page.
 *
 * Two populations from the same rows, as on the admin page: every engaged row is a page view; a
 * person needs consent. A visitor who declined sends one page view and no heartbeat, so "on the
 * page right now" cannot be known for them at all. That is why the page shows two figures and
 * labels both.
 *
 * Nothing here is cached, for the reason RealtimeDashboard gives: an expired key in the file or
 * database cache store is only deleted when it is read again.
 */
class ScheduleRealtime
{
    public const WINDOW_MINUTES = 30;

    /** Rows read per poll. A schedule past this in half an hour is told its numbers are a floor. */
    public const FETCH_CAP = 5000;

    /** People listed. Past these the card says how many it is showing of how many. */
    public const NOW_CAP = 50;

    public const EARLIER_CAP = 10;

    /** A breakdown lists this many rows and folds the rest into one, so it still adds up. */
    public const BREAKDOWN_ROWS = 6;

    /**
     * Everything this service may read. hit_key, path, path_template, title, browser, os,
     * utm_campaign, user_id and is_demo are absent deliberately: see the class docblock.
     */
    private const COLUMNS = [
        'visitor_key', 'consented', 'owner_visible', 'surface', 'role_id', 'event_id',
        'source_channel', 'source_name', 'is_entrance', 'country', 'device', 'hb',
        'started_at', 'last_seen_at', 'ended_at',
    ];

    private const DEVICES = ['mobile', 'desktop', 'tablet'];

    private int $nowTs;

    /** @var list<int> */
    private array $roleIds;

    /** @var array<int, ?object> */
    private array $roles = [];

    /** @var array<int, ?object> */
    private array $events = [];

    /** @var array<int, string> appointment type names, for the page of a booking */
    private array $types = [];

    /**
     * @param  iterable<int>  $roleIds  schedules the viewer may see, already authorized by the caller
     * @param  string  $salt  per session: makes a row's handle useless outside it
     */
    public function __construct(private readonly User $viewer, iterable $roleIds, private readonly string $salt)
    {
        $this->roleIds = array_values(array_unique(array_map('intval', is_array($roleIds) ? $roleIds : iterator_to_array($roleIds, false))));
        $this->nowTs = RealtimeTracker::now()->getTimestamp();
    }

    private const SALT_KEY = 'realtime_owner_salt';

    /**
     * The view of the person asking: every schedule they manage, and their session's salt. What
     * the Realtime tab of /analytics renders and what its poll answers with are both this, so a
     * row keeps its handle from the first render to every refresh after it.
     */
    public static function forRequest(Request $request, User $user): self
    {
        return new self($user, $user->manageableRoles()->pluck('id'), self::salt($request));
    }

    /**
     * A row's handle is a hash salted with this, so it means nothing outside the session that is
     * looking: not to another owner, and not to the same owner tomorrow or on another device.
     */
    public static function salt(Request $request): string
    {
        $session = $request->session();

        if (! is_string($session->get(self::SALT_KEY)) || $session->get(self::SALT_KEY) === '') {
            $session->put(self::SALT_KEY, bin2hex(random_bytes(16)));
        }

        return $session->get(self::SALT_KEY);
    }

    /**
     * Whether this person has a Realtime tab at all: the install offers it, they are not the
     * shared demo account (anyone can sign in as it, and it would be watching real visitors), and
     * they manage at least one schedule.
     *
     * manageableRoles(), not editor(): what Sales uses. On hosted a team member reaches somebody
     * else's schedule only while its plan includes a team, and a live view of the audience is not
     * something a lapsed plan should leave open.
     */
    public static function available(?User $user): bool
    {
        if (! $user || ! RealtimeTracker::ownerViewEnabled() || DemoService::isDemoUser($user)) {
            return false;
        }

        try {
            return $user->manageableRoles()->isNotEmpty();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * The page and the poll.
     *
     * @param  ?int  $onlyRoleId  one of the viewer's schedules, or null for all of them
     */
    public function payload(?int $onlyRoleId = null): array
    {
        $roleIds = $onlyRoleId === null ? $this->roleIds : array_values(array_intersect($this->roleIds, [$onlyRoleId]));
        $rows = $this->fetchRows($roleIds);
        $truncated = $rows->count() >= self::FETCH_CAP;

        $pageRows = $rows->where('surface', 'gp')->values();
        $views = $pageRows->filter(fn ($row) => $row->minute !== null)->values();
        $people = $this->people($pageRows);

        $nowPeople = $people->where('now', true)->values();
        $earlierPeople = $people->where('now', false)->values();
        $views5 = $views->filter(fn ($row) => $row->started >= $this->nowTs - 300)->count();
        $lastStarted = $pageRows->max('started');

        $this->loadNames($nowPeople->take(self::NOW_CAP)->pluck('row')
            ->concat($earlierPeople->take(self::EARLIER_CAP)->pluck('row')));

        return [
            'state' => 'ok',
            't' => $this->nowTs,
            'truncated' => $truncated,
            'overview' => [
                'views_5m' => $views5,
                'visitors_now' => $nowPeople->count(),
                'views_30m' => $views->count(),
                'embed_views_30m' => $rows->where('surface', 'embed')->filter(fn ($row) => $row->minute !== null)->count(),
                // Only while nobody has opened a page in five minutes: then it is the answer to
                // "when was the last one". Null when the half hour is empty too.
                'last_view_ago' => $views5 === 0 && $lastStarted ? max(0, $this->nowTs - $lastStarted) : null,
            ],
            'minutes' => $this->minutes($views),
            'visitors' => [
                'now' => $nowPeople->take(self::NOW_CAP)->map(fn ($person) => $this->visitorRow($person))->values()->all(),
                'now_total' => $nowPeople->count(),
                'earlier' => $earlierPeople->take(self::EARLIER_CAP)->map(fn ($person) => $this->visitorRow($person))->values()->all(),
                'earlier_total' => $earlierPeople->count(),
            ],
            'breakdowns' => [
                'pages' => $this->pagesBreakdown($views, $nowPeople),
                'sources' => $this->sourcesBreakdown($views),
                // A view with no country (a private address, a lookup that found nothing) is in
                // the "other" row: every list on the page adds up to the page views above it.
                'countries' => $this->folded(
                    $views->filter(fn ($row) => $row->country)->countBy('country'),
                    fn ($code) => ['key' => $code, 'label' => $code],
                    $views->reject(fn ($row) => $row->country)->count(),
                ),
                'devices' => $this->folded($views->countBy(fn ($row) => in_array($row->device, self::DEVICES, true) ? $row->device : 'other'), fn ($device) => ['key' => $device, 'label' => $device]),
            ],
        ];
    }

    /**
     * The dashboard's Realtime tile and the "visitors now" column of the schedules list. Null when
     * this person has no live view, which the dashboard reads as "show the other tile".
     *
     * @return array{views_5m: int, visitors_now: int, views_30m: int, minutes: list<int>, by_schedule: array<int, int>}|null
     */
    public static function summary(?User $user, string $salt = ''): ?array
    {
        if (! self::available($user)) {
            return null;
        }

        try {
            $self = new self($user, $user->manageableRoles()->pluck('id'), $salt);
            $rows = $self->fetchRows($self->roleIds)->where('surface', 'gp')->values();
            $views = $rows->filter(fn ($row) => $row->minute !== null)->values();
            $nowPeople = $self->people($rows)->where('now', true);

            return [
                'views_5m' => $views->filter(fn ($row) => $row->started >= $self->nowTs - 300)->count(),
                'visitors_now' => $nowPeople->count(),
                'views_30m' => $views->count(),
                'minutes' => $self->minutes($views),
                'by_schedule' => $nowPeople->countBy(fn ($person) => $person['row']->role_id)->all(),
            ];
        } catch (Throwable $e) {
            // The tile is a courtesy on a page that must open. The page itself does not swallow.
            report($e);

            return null;
        }
    }

    /**
     * The one query. The window is on last_seen_at, which is what the index is on and what keeps a
     * page that has been open for an hour among the people who are here; whether a row is also a
     * page view OF the half hour is decided by when it started (see `minute`).
     *
     * @param  list<int>  $roleIds
     */
    private function fetchRows(array $roleIds): Collection
    {
        if (! $roleIds) {
            return collect();
        }

        return DB::table('realtime_hits')
            ->whereIn('role_id', $roleIds)
            ->where('last_seen_at', '>=', RealtimeTracker::ts(RealtimeTracker::now()->subMinutes(self::WINDOW_MINUTES)))
            ->whereIn('surface', ['gp', 'embed'])
            ->where('is_admin', false)
            ->where('is_team', false)
            ->whereNotNull('engaged_at')
            ->orderByDesc('last_seen_at')
            ->limit(self::FETCH_CAP)
            ->get(self::COLUMNS)
            ->map(function ($row) {
                $row->role_id = (int) $row->role_id;
                $row->event_id = $row->event_id ? (int) $row->event_id : null;
                $row->listable = (bool) $row->consented && (bool) $row->owner_visible && $row->visitor_key !== null;
                $row->is_entrance = (bool) $row->is_entrance;
                $row->started = RealtimeRows::timestamp($row->started_at);
                $row->last_seen = RealtimeRows::timestamp($row->last_seen_at);
                $ended = $row->ended_at ? RealtimeRows::timestamp($row->ended_at) : null;
                $row->is_now = RealtimeRows::isNow($ended, $row->last_seen, (int) $row->hb, $this->nowTs);
                $row->minute = RealtimeRows::minuteIndex($row->started, $this->nowTs);
                $row->page_key = $row->role_id.':'.($row->event_id ?? 0);

                return $row;
            });
    }

    /**
     * One entry per visitor who may be listed, newest first, each with the page view that speaks
     * for them: the one they are on now, or the last one they were on.
     *
     * Their rows here are only the ones on this viewer's schedules. Someone who has gone on to
     * another schedule's page has, as far as this page can tell, simply left.
     */
    private function people(Collection $pageRows): Collection
    {
        return $pageRows->where('listable', true)
            ->groupBy('visitor_key')
            ->map(function (Collection $rows) {
                // Rows arrive newest first, so a live row (if any) is the newest live one.
                $current = $rows->firstWhere('is_now', true) ?? $rows->first();

                return ['row' => $current, 'now' => (bool) $current->is_now];
            })
            ->sortByDesc(fn ($person) => $person['now'] ? $person['row']->started : $person['row']->last_seen)
            ->values();
    }

    /**
     * Country, device, the page and how long. That is the whole row, by design.
     */
    private function visitorRow(array $person): array
    {
        $row = $person['row'];
        $page = $this->pageLabel($row);

        return [
            'id' => $this->handle((string) $row->visitor_key),
            'country' => $row->country ?: null,
            'device' => in_array($row->device, self::DEVICES, true) ? $row->device : 'other',
            'page' => $page['label'],
            'kind' => $page['kind'],
            'schedule' => $page['schedule'],
            // Seconds on the page they are on, or seconds since they left the last one.
            'seconds' => $person['now'] ? max(0, $this->nowTs - $row->started) : null,
            'left_ago' => $person['now'] ? null : max(0, $this->nowTs - $row->last_seen),
        ];
    }

    private function handle(string $visitorKey): string
    {
        return substr(hash_hmac('sha256', 'rt-owner|'.$this->viewer->id.'|'.$this->salt.'|'.$visitorKey, (string) config('app.key')), 0, 12);
    }

    /** @return list<int> */
    private function minutes(Collection $views): array
    {
        $buckets = array_fill(0, RealtimeRows::MINUTES, 0);

        foreach ($views as $row) {
            $buckets[$row->minute]++;
        }

        return $buckets;
    }

    /**
     * Names for the rows about to be shown, and for no others. Narrow selects: a name is all that
     * is printed. An event is named because it was served on one of the viewer's own pages, which
     * is what its row's role_id says; that is the only reason its name may be shown here.
     */
    private function loadNames(iterable $rows): void
    {
        $roleIds = [];
        $eventIds = [];
        foreach ($rows as $row) {
            if (! array_key_exists($row->role_id, $this->roles)) {
                $roleIds[$row->role_id] = true;
            }
            if ($row->event_id && ! array_key_exists($row->event_id, $this->events)) {
                $eventIds[$row->event_id] = true;
            }
        }

        if ($roleIds) {
            $found = Role::whereIn('id', array_keys($roleIds))->get(['id', 'name', 'subdomain'])->keyBy('id')->all();
            foreach (array_keys($roleIds) as $id) {
                $this->roles[$id] = $found[$id] ?? null;
            }
        }

        if ($eventIds) {
            $found = Event::whereIn('id', array_keys($eventIds))->get(['id', 'name', 'appointment_type_id'])->keyBy('id')->all();
            foreach (array_keys($eventIds) as $id) {
                $this->events[$id] = $found[$id] ?? null;
            }

            // An appointment booking is an event named after its guest ("Consultation - Dana
            // Whitlock"). Its page is labelled by what was booked, which needs the type's name.
            $typeIds = collect($found)->pluck('appointment_type_id')->filter()->unique()
                ->reject(fn ($id) => array_key_exists((int) $id, $this->types))->values()->all();
            if ($typeIds) {
                $this->types += DB::table('appointment_types')->whereIn('id', $typeIds)->pluck('name', 'id')->all();
            }
        }
    }

    /**
     * @return array{label: string, kind: string, schedule: ?string}
     */
    private function pageLabel(object $row): array
    {
        $role = $this->roles[$row->role_id] ?? null;
        $event = $row->event_id ? ($this->events[$row->event_id] ?? null) : null;
        $schedule = $role?->name ?: null;

        if ($row->event_id) {
            return [
                // Never the event's name directly: see ScheduleActivity::eventLabel().
                'label' => ScheduleActivity::eventLabel($event, $this->types),
                'kind' => 'event',
                // Whose page it is only matters to someone with more than one schedule.
                'schedule' => count($this->roleIds) > 1 ? $schedule : null,
            ];
        }

        return [
            'label' => $schedule ?: __('messages.realtime_deleted_schedule'),
            'kind' => 'schedule',
            'schedule' => null,
        ];
    }

    /**
     * @param  Collection  $nowPeople  everyone who is here now, before the list is cut to NOW_CAP:
     *                                 a page's "now" is counted from all of them, so the figures
     *                                 beside the pages add up to "visitors now" and not to fifty
     */
    private function pagesBreakdown(Collection $views, Collection $nowPeople): array
    {
        $counts = $views->countBy('page_key')->sortDesc();
        $first = $views->groupBy('page_key')->map(fn ($group) => $group->first());
        $here = $nowPeople->countBy(fn ($person) => $person['row']->page_key);

        // Names for exactly the pages folded() is about to list (it keeps this order: a sort of an
        // already sorted tally moves nothing).
        $this->loadNames($counts->take(self::BREAKDOWN_ROWS)->keys()->map(fn ($key) => $first[$key]));

        return $this->folded($counts, function ($key) use ($first, $here) {
            $page = $this->pageLabel($first[$key]);

            return ['key' => 'p'.md5($key), 'label' => $page['label'], 'sub' => $page['schedule'], 'kind' => $page['kind'], 'now' => (int) ($here[$key] ?? 0)];
        });
    }

    /**
     * Visits, not page views: only the page view a visit began on carries a source that is this
     * schedule's to know (see the class docblock), and only an entrance carries one at all for a
     * visitor who declined cookies.
     */
    private function sourcesBreakdown(Collection $views): array
    {
        $entrances = $views->filter(fn ($row) => $row->is_entrance && $row->source_channel);
        $channels = [];

        $counts = $entrances->countBy(function ($row) use (&$channels) {
            $key = $row->source_name ? 'n:'.$row->source_name : 'c:'.$row->source_channel;
            $channels[$key] = $row->source_channel;

            return $key;
        });

        return $this->folded($counts, fn ($key) => [
            'key' => 's'.md5($key),
            'label' => str_starts_with($key, 'n:') ? substr($key, 2) : RealtimeDashboard::channelLabel($channels[$key]),
            'sub' => str_starts_with($key, 'n:') ? RealtimeDashboard::channelLabel($channels[$key]) : null,
        ]);
    }

    /**
     * The largest BREAKDOWN_ROWS of a tally, and one "other" row for the rest, so that the rows of
     * a card add up to the total above them.
     *
     * @param  callable(string): array  $describe
     * @param  int  $unplaced  views that belong to no row of the tally, counted with the rest
     */
    private function folded(Collection $counts, callable $describe, int $unplaced = 0): array
    {
        $sorted = $counts->sortDesc();
        $out = [];

        foreach ($sorted->take(self::BREAKDOWN_ROWS) as $key => $count) {
            $out[] = $describe((string) $key) + ['views' => $count];
        }

        $rest = $sorted->slice(self::BREAKDOWN_ROWS)->sum() + $unplaced;
        if ($rest > 0) {
            $out[] = ['key' => 'other', 'label' => null, 'other' => true, 'views' => $rest];
        }

        return $out;
    }

    /** The encoded ids of the viewer's schedules, for the picker and for checking a request. */
    public static function scheduleOptions(User $user): array
    {
        return $user->manageableRoles()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->map(fn (Role $role) => ['id' => UrlUtils::encodeId($role->id), 'name' => (string) $role->name])
            ->values()->all();
    }
}
