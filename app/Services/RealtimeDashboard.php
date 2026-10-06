<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
use App\Models\SupportConversation;
use App\Models\User;
use App\Utils\RealtimeRows;
use App\Utils\RealtimeTracker;
use App\Utils\UrlUtils;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * Everything /admin/realtime shows, built from ONE bounded query over the last 30 minutes of
 * realtime_hits and aggregated in PHP (portable to MySQL 5.7 / MariaDB 10.3, and testable on rows).
 *
 * Two populations from the same rows:
 *  - every engaged row feeds PAGE-VIEW numbers: the per-minute chart, the "Last 30 minutes" total and
 *    its split, the four breakdown cards, the dashboard teaser. A visitor who has not accepted cookies is counted
 *    here, as an anonymous page view, and nowhere else.
 *  - consented rows only feed everything about PEOPLE: visitors, right now, the Visitors list,
 *    timelines, "possibly stuck".
 *
 * Definitions (the docs #realtime section says the same in words):
 *  - Right now: ended_at IS NULL and last_seen_at within 2 x hb + 30s, hb being the heartbeat the
 *    row's page was rendered with.
 *  - Last 30 minutes: last_seen_at within 30 minutes.
 *  - A person is u:{user} for a signed-in user. An anonymous row joins a user when its visitor key
 *    saw exactly one non-demo user in the window, that user is not an admin, and the row started no
 *    later than that user's latest signed-in page view on the key (so a new user's pre-sign-up
 *    pages land in their timeline, while their browsing after signing out, the next person on a
 *    shared laptop, and an admin's own private window, which shares their IP and browser, do not).
 *    Anything else is v:{visitor key}. A user keeps v:{key} among their aliases, so a v:{key}
 *    person can exist beside them: every lookup tries the exact id before an alias.
 *  - Filters match page views. A person is "right now" when their CURRENT page view matches and in
 *    "last 30 minutes" when any of theirs does. Timelines and Activity ignore filters.
 *
 * Nothing here is cached: the file and database cache stores only delete expired keys when they are
 * read, so per-filter payloads would leave names and emails on disk past the one-hour promise.
 */
class RealtimeDashboard
{
    public const WINDOW_MINUTES = 30;

    public const ROW_CAP = 20;

    public const ROW_CAP_ALL = 500;

    public const FETCH_CAP = 20000;

    private const BREAKDOWN_LIMIT = 50;

    private const STUCK_MINUTES = 10;

    private const COLUMNS = [
        'id', 'hit_key', 'visitor_key', 'consented', 'user_id', 'is_admin', 'is_demo', 'surface', 'path',
        'title', 'role_id', 'event_id', 'source_channel', 'source_name', 'utm_campaign', 'is_entrance',
        'country', 'device', 'browser', 'os', 'hb', 'started_at', 'last_seen_at', 'ended_at',
    ];

    public const CHANNELS = ['direct', 'search', 'social', 'ai', 'email', 'paid', 'campaign', 'other'];

    private CarbonImmutable $now;

    private int $nowTs;

    /** @var array<int, object> */
    private array $users = [];

    /** @var array<int, object> */
    private array $roles = [];

    /** @var array<int, object> */
    private array $events = [];

    public function __construct(
        public readonly array $filters = [],
        public readonly string $who = 'all',
        public readonly bool $all = false,
        public readonly bool $admins = false,
        public readonly ?string $expand = null,
        public readonly ?string $feed = null,
    ) {
        $this->now = RealtimeTracker::now();
        $this->nowTs = $this->now->getTimestamp();
    }

    /**
     * Read and normalize the query string. Invalid values are dropped, never a 422: the URL is
     * bookmarkable, and a stale bookmark should still open the page.
     */
    public static function fromRequest(Request $request): self
    {
        $filters = [];

        $surface = $request->query('surface');
        if (in_array($surface, ['wp', 'gp', 'ap', 'auth'], true)) {
            $filters['surface'] = $surface;
        }

        $page = $request->query('page');
        if (is_string($page) && preg_match('/^(p:(wp|gp|ap|auth):\/.{0,254}|e:[A-Za-z0-9]+(:[A-Za-z0-9]+)?)$/s', $page) === 1) {
            $filters['page'] = $page;
        }

        $source = $request->query('source');
        if (is_string($source) && preg_match('/^(c:('.implode('|', self::CHANNELS).')|n:.{1,100})$/s', $source) === 1) {
            $filters['source'] = $source;
        }

        $country = $request->query('country');
        if (is_string($country) && preg_match('/^[A-Z]{2}$/', $country) === 1) {
            $filters['country'] = $country;
        }

        $who = $request->query('who');
        $expand = $request->query('expand');
        $feed = $request->query('feed');

        return new self(
            filters: $filters,
            who: in_array($who, ['signed_in', 'anonymous'], true) ? $who : 'all',
            all: $request->query('all') === '1',
            admins: $request->query('admins') === '1',
            expand: is_string($expand) && preg_match('/^(u:[A-Za-z0-9]+|v:[0-9a-f]{16})$/', $expand) === 1 ? $expand : null,
            feed: in_array($feed, RealtimeActivity::FEEDS, true) ? $feed : null,
        );
    }

    public function payload(): array
    {
        if (! RealtimeTracker::enabled()) {
            return ['state' => 'off'];
        }

        $rows = $this->fetchRows();
        $truncated = $rows->count() >= self::FETCH_CAP;

        if (! $this->admins) {
            $rows = $rows->reject(fn ($row) => $row->is_admin)->values();
        }

        $pageRows = $rows->where('surface', '!=', 'embed')->values();
        $embedViews = $rows->where('surface', 'embed')->filter(fn ($row) => $this->matches($row, ignoreSurface: true))->count();
        $matchingRows = $pageRows->filter(fn ($row) => $this->matches($row))->values();

        $this->loadUsers($pageRows);
        $people = $this->people($pageRows->where('consented', true)->values());
        $matchingPeople = $people->filter(fn ($person) => $person['matches_window'])->values();

        $counts = [
            'all' => $matchingPeople->count(),
            'signed_in' => $matchingPeople->where('kind', 'user')->count(),
            'anonymous' => $matchingPeople->where('kind', 'anon')->count(),
        ];

        $nowPeople = $matchingPeople->filter(fn ($person) => $person['now'] && $person['matches_now']);
        $consentedViews = $matchingRows->where('consented', true)->count();
        $unidentifiedViews = $matchingRows->where('consented', false)->count();
        $allViews = $consentedViews + $unidentifiedViews;

        $state = $people->isEmpty() && $pageRows->isEmpty() && $this->justEnabled() ? 'waiting' : 'ok';

        // Built first: the per-minute chart marks the sign-ups it reports.
        $activity = (new RealtimeActivity)->build($this->admins, $this->presenceByUser($people), $this->feed);
        $marks = $activity['signups']['marks'];
        unset($activity['signups']['marks']);

        $payload = [
            'state' => $state,
            't' => $this->nowTs,
            'truncated' => $truncated,
            'filters' => $this->filters,
            'filter_labels' => $this->filterLabels($pageRows),
            'filtered' => $this->filters !== [],
            'who' => $this->who,
            'all' => $this->all,
            'admins' => $this->admins,
            'overview' => [
                'now' => $nowPeople->count(),
                'now_total' => $people->filter(fn ($person) => $person['now'])->count(),
                'now_signed_in' => $nowPeople->where('kind', 'user')->count(),
                'now_anon' => $nowPeople->where('kind', 'anon')->count(),
                'now_by_surface' => $this->nowBySurface($people),
                'win_visitors' => $counts['all'],
                'win_views' => $consentedViews,
                'unidentified_views' => $unidentifiedViews,
                'consent_share' => $allViews > 0 ? (int) round(100 * $consentedViews / $allViews) : null,
                'last_seen_ago' => $nowPeople->isEmpty() ? $this->lastSeenAgo() : null,
                'phrases' => $this->overviewPhrases($nowPeople, $counts['all'], $consentedViews, $unidentifiedViews, $allViews),
            ],
            'minutes' => $this->minutes($matchingRows, $marks),
            'counts' => $counts,
            'visitors' => $this->visitorList($matchingPeople),
            'breakdowns' => [
                'pages' => $this->pagesBreakdown($matchingRows, $nowPeople),
                'sources' => $this->sourcesBreakdown($matchingRows),
                'countries' => $this->countriesBreakdown($matchingRows),
                'surfaces' => $this->surfacesBreakdown($pageRows->filter(fn ($row) => $this->matches($row, ignoreSurface: true)), $embedViews),
            ],
            'activity' => $activity,
            'expanded' => $this->expanded($people),
        ];

        return $payload;
    }

    /**
     * The dashboard teaser: page views in the last five minutes, both consent tiers, no admins.
     * Page views rather than visitors so it never overstates who can be seen.
     */
    public static function recentViews(): ?int
    {
        try {
            if (! RealtimeTracker::enabled()) {
                return null;
            }

            $since = RealtimeTracker::ts(RealtimeTracker::now()->subMinutes(5));

            // The last_seen_at bound is what lets the index do the work (started_at has none);
            // it never drops a row, because last_seen_at is never earlier than started_at.
            return DB::table('realtime_hits')
                ->where('last_seen_at', '>=', $since)
                ->where('started_at', '>=', $since)
                ->whereNotNull('engaged_at')
                ->where('surface', '!=', 'embed')
                ->where('is_admin', false)
                ->count();
        } catch (Throwable) {
            return null;
        }
    }

    private function fetchRows(): Collection
    {
        return DB::table('realtime_hits')
            ->where('last_seen_at', '>=', RealtimeTracker::ts($this->now->subMinutes(self::WINDOW_MINUTES)))
            ->whereNotNull('engaged_at')
            ->orderByDesc('last_seen_at')
            ->limit(self::FETCH_CAP)
            ->get(self::COLUMNS)
            ->map(function ($row) {
                $row->consented = (bool) $row->consented;
                $row->is_admin = (bool) $row->is_admin;
                $row->is_demo = (bool) $row->is_demo;
                $row->is_entrance = (bool) $row->is_entrance;
                $row->started = $this->timestamp($row->started_at);
                $row->last_seen = $this->timestamp($row->last_seen_at);
                $row->ended = $row->ended_at ? $this->timestamp($row->ended_at) : null;
                $row->is_now = RealtimeRows::isNow($row->ended, $row->last_seen, (int) $row->hb, $this->nowTs);
                $row->page_key = $this->pageKey($row);
                $row->source_key = $this->sourceKey($row);

                return $row;
            });
    }

    /** One definition for both Realtime pages: see RealtimeRows. */
    private function timestamp(string $value): int
    {
        return RealtimeRows::timestamp($value);
    }

    private function pageKey(object $row): string
    {
        // Every hosted schedule's home page has the path "/", same as the marketing home, so a
        // schedule page is keyed by its ids, not its path.
        if ($row->surface === 'gp' && $row->role_id) {
            return 'e:'.UrlUtils::encodeId($row->role_id).($row->event_id ? ':'.UrlUtils::encodeId($row->event_id) : '');
        }

        return 'p:'.$row->surface.':'.$row->path;
    }

    private function sourceKey(object $row): ?string
    {
        if (! $row->source_channel) {
            return null;
        }

        return $row->source_name ? 'n:'.$row->source_name : 'c:'.$row->source_channel;
    }

    private function matches(object $row, bool $ignoreSurface = false): bool
    {
        foreach ($this->filters as $key => $value) {
            $ok = match ($key) {
                'surface' => $ignoreSurface || $row->surface === $value,
                'page' => ($row->page_key ?? $this->pageKey($row)) === $value,
                'source' => ($row->source_key ?? $this->sourceKey($row)) === $value,
                'country' => $row->country === $value,
                default => true,
            };

            if (! $ok) {
                return false;
            }
        }

        return true;
    }

    /**
     * Human labels for the active filter chips, so a reloaded URL never shows a raw key.
     */
    private function filterLabels(Collection $rows): array
    {
        $labels = [];

        foreach ($this->filters as $key => $value) {
            $labels[$key] = match ($key) {
                'surface' => __('messages.realtime_surface_'.$value),
                'country' => $value,
                'source' => str_starts_with($value, 'c:') ? $this->channelLabel(substr($value, 2)) : substr($value, 2),
                'page' => ($row = $rows->firstWhere('page_key', $value)) ? $this->labelRow($row)['label'] : $this->pageKeyLabel($value),
                default => $value,
            };
        }

        return $labels;
    }

    private function pageKeyLabel(string $key): string
    {
        if (str_starts_with($key, 'p:')) {
            return (string) preg_replace('/^p:[a-z]+:/', '', $key);
        }

        [, $role, $event] = array_pad(explode(':', $key), 3, null);
        $eventName = $event ? Event::whereKey(UrlUtils::decodeId($event))->value('name') : null;

        return $eventName ?: (Role::whereKey(UrlUtils::decodeId($role))->value('name') ?: $key);
    }

    /**
     * Every user in the window: the person merge needs them all.
     */
    private function loadUsers(Collection $rows): void
    {
        $userIds = $rows->pluck('user_id')->filter()->unique()->values();
        $this->users = $userIds->isEmpty() ? [] : User::whereIn('id', $userIds)
            ->get(['id', 'name', 'email', 'is_admin', 'created_at', 'signup_intent'])
            ->keyBy('id')->all();
    }

    /**
     * Schedule and event models only for the rows about to be labeled (the shown people, the top
     * pages, a filter chip), loading just the ids not already held. At 20,000 rows in the window,
     * loading every one up front cost far more than the page shows.
     */
    private function ensureNames(iterable $rows): void
    {
        $roleIds = [];
        $eventIds = [];
        foreach ($rows as $row) {
            if ($row->role_id && ! array_key_exists($row->role_id, $this->roles)) {
                $roleIds[$row->role_id] = true;
            }
            if ($row->event_id && ! array_key_exists($row->event_id, $this->events)) {
                $eventIds[$row->event_id] = true;
            }
        }

        if ($roleIds) {
            $found = Role::whereIn('id', array_keys($roleIds))->get()->keyBy('id')->all();
            foreach (array_keys($roleIds) as $id) {
                $this->roles[$id] = $found[$id] ?? null;
            }
        }

        if ($eventIds) {
            $found = Event::with('roles')->whereIn('id', array_keys($eventIds))->get()->keyBy('id')->all();
            foreach (array_keys($eventIds) as $id) {
                $this->events[$id] = $found[$id] ?? null;
            }
        }
    }

    /**
     * The person merge. See the class docblock for the rule.
     */
    private function people(Collection $rows): Collection
    {
        // A user id with no users row (a deleted account) is anonymous, so later anonymous rows on
        // the same browser can never merge into someone who no longer exists.
        foreach ($rows as $row) {
            if ($row->user_id && ! isset($this->users[$row->user_id])) {
                $row->user_id = null;
            }
        }

        // visitor key => user id => the start of that user's latest signed-in page view on the key.
        $keyUsers = [];
        foreach ($rows as $row) {
            if ($row->user_id && ! $row->is_demo && $row->visitor_key) {
                $keyUsers[$row->visitor_key][$row->user_id] = max($keyUsers[$row->visitor_key][$row->user_id] ?? 0, $row->started);
            }
        }

        $groups = [];
        foreach ($rows as $row) {
            if ($row->user_id && ! $row->is_demo) {
                $id = 'u:'.UrlUtils::encodeId($row->user_id);
            } else {
                $owner = self::keyOwner($keyUsers[$row->visitor_key] ?? [], $row->started, $this->users);
                $id = $owner ? 'u:'.UrlUtils::encodeId($owner) : 'v:'.$row->visitor_key;
            }

            $groups[$id][] = $row;
        }

        return collect($groups)->map(fn ($personRows, $id) => $this->person($id, collect($personRows)))->values();
    }

    /**
     * The user an anonymous page view on a visitor key belongs to, or null: the key's single
     * non-admin user, and only for a page view that started no later than that user's latest
     * signed-in one there. So the pages before signing in join the account, and the browsing after
     * signing out (or the next person on a shared laptop) does not.
     *
     * @param  array<int, int>  $users  user id => start of their latest signed-in page view on the key
     * @param  array<int, object>  $known  user id => users row with is_admin (missing = deleted)
     */
    private static function keyOwner(array $users, int $started, array $known): ?int
    {
        if (count($users) !== 1) {
            return null;
        }

        $userId = array_key_first($users);
        $user = $known[$userId] ?? null;

        return $user && ! $user->is_admin && $started <= $users[$userId] ? $userId : null;
    }

    private function person(string $id, Collection $rows): array
    {
        $rows = $rows->sortBy('started')->values();
        $nowRows = $rows->where('is_now', true);
        $current = $nowRows->isNotEmpty() ? $nowRows->sortByDesc('started')->first() : $rows->sortByDesc('last_seen')->first();
        $isNow = (bool) $current->is_now;
        $userId = str_starts_with($id, 'u:') ? UrlUtils::decodeId(substr($id, 2)) : null;
        $user = $userId ? ($this->users[$userId] ?? null) : null;
        $isNew = $user && $user->created_at && Carbon::parse($user->created_at)->getTimestamp() >= $this->nowTs - 86400;
        $lastSeen = $rows->max('last_seen');

        $badges = [];
        if ($isNew) {
            $badges[] = 'new';
        }
        if ($rows->contains('is_demo', true)) {
            $badges[] = 'demo';
        }

        $stuck = null;
        if ($isNew && $isNow && $current->surface === 'ap' && in_array($user->signup_intent, [null, 'organizer'], true)) {
            $seconds = $rows->where('surface', 'ap')->sum(function ($row) {
                $end = $row->is_now ? $this->nowTs : ($row->ended ?? $row->last_seen);

                return max(0, min($end, $this->nowTs) - max($row->started, $this->nowTs - self::WINDOW_MINUTES * 60));
            });
            $stuck = (int) floor($seconds / 60) >= self::STUCK_MINUTES ? (int) floor($seconds / 60) : null;
        }

        return [
            'id' => $id,
            'aliases' => $rows->pluck('visitor_key')->filter()->unique()->map(fn ($key) => 'v:'.$key)->values()->all(),
            'user_id' => $userId,
            'kind' => $user ? 'user' : 'anon',
            'demo' => in_array('demo', $badges, true),
            'name' => $user?->name,
            'email' => $user?->email,
            'initials' => $user ? self::initials($user->name ?: $user->email) : null,
            'badges' => $badges,
            'stuck_mins' => $stuck,
            'country' => $current->country,
            'device' => $current->device,
            'browser' => $current->browser,
            'os' => $current->os,
            'current' => $current,
            'source' => [
                'channel' => $current->source_channel,
                'channel_label' => $current->source_channel ? $this->channelLabel($current->source_channel) : null,
                'name' => $current->source_name,
                'campaign' => $current->utm_campaign,
            ],
            'views' => $rows->count(),
            'now' => $isNow,
            'sort_at' => $isNow ? $rows->max('started') : $lastSeen,
            'on_page_secs' => $isNow ? max(0, $this->nowTs - $current->started) : null,
            'left_ago' => $isNow ? null : max(0, $this->nowTs - $lastSeen),
            'matches_window' => $rows->contains(fn ($row) => $this->matches($row)),
            'matches_now' => $this->matches($current),
            // For the "where are they" buttons, which keep every area's count while one is the filter.
            'matches_now_any_surface' => $this->matches($current, ignoreSurface: true),
            'rows' => $rows,
        ];
    }

    public static function initials(string $name): string
    {
        $words = preg_split('/\s+/u', trim($name)) ?: [];
        $letters = array_map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)), array_slice($words, 0, 2));

        return implode('', $letters) ?: '?';
    }

    /**
     * pageLabel() for one row, loading its names first.
     */
    private function labelRow(object $row): array
    {
        $this->ensureNames([$row]);

        return $this->pageLabel($row);
    }

    /**
     * @return array{label: string, surface: string, surface_label: string, url: ?string}
     */
    private function pageLabel(object $row): array
    {
        $label = null;
        $url = null;

        if (in_array($row->surface, ['gp', 'embed'], true)) {
            $event = $row->event_id ? ($this->events[$row->event_id] ?? null) : null;
            $role = $row->role_id ? ($this->roles[$row->role_id] ?? null) : null;
            $label = $event?->name ?: $role?->name;
            $url = $this->guestUrl($role, $event);
        } elseif ($row->surface === 'auth') {
            $label = $this->authLabel($row->path);
        } else {
            $label = $row->title;
        }

        if ($row->surface === 'wp') {
            $url = url($row->path);
        }

        return [
            'label' => $label ?: $row->path,
            'surface' => $row->surface,
            'surface_label' => __('messages.realtime_surface_'.$row->surface),
            'url' => $url,
        ];
    }

    private function guestUrl(?object $role, ?object $event): ?string
    {
        try {
            if ($event && $role) {
                return $event->getGuestUrl($role->subdomain) ?: null;
            }

            return $role ? ($role->getGuestUrl() ?: null) : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function authLabel(string $path): ?string
    {
        return match (true) {
            str_contains($path, 'sign_up') || str_contains($path, 'register') => __('messages.sign_up'),
            str_contains($path, 'login') => __('messages.log_in'),
            str_contains($path, 'password') => __('messages.reset_password'),
            str_contains($path, 'verify') || str_contains($path, 'verification') => __('messages.realtime_page_verify_email'),
            str_contains($path, 'two-factor') => __('messages.realtime_page_two_factor'),
            default => null,
        };
    }

    /** Public and static: App\Utils\SignupSource names its channels with the same words. */
    public static function channelLabel(string $channel): string
    {
        return match ($channel) {
            'direct' => __('messages.direct'),
            'search' => __('messages.search'),
            'social' => __('messages.social'),
            'email' => __('messages.email_source'),
            default => __('messages.channel_'.$channel),
        };
    }

    private function overviewPhrases(Collection $nowPeople, int $visitors, int $views, int $unidentified, int $allViews): array
    {
        $share = $allViews > 0 ? (int) round(100 * $views / $allViews) : null;

        return [
            'split' => __('messages.realtime_split', [
                'signed_in' => trans_choice('messages.realtime_signed_in_count', $nowPeople->where('kind', 'user')->count(), ['count' => number_format($nowPeople->where('kind', 'user')->count())]),
                'anonymous' => trans_choice('messages.realtime_anonymous_count', $nowPeople->where('kind', 'anon')->count(), ['count' => number_format($nowPeople->where('kind', 'anon')->count())]),
            ]),
            // The total first, then how it splits: every phrase carries its own unit.
            'views_total' => trans_choice('messages.realtime_views_count', $allViews, ['count' => number_format($allViews)]),
            'accepted' => __('messages.realtime_split', [
                'signed_in' => trans_choice('messages.realtime_views_count', $views, ['count' => number_format($views)]),
                'anonymous' => trans_choice('messages.realtime_visitors_count', $visitors, ['count' => number_format($visitors)]),
            ]),
            'not_accepted' => trans_choice('messages.realtime_views_count', $unidentified, ['count' => number_format($unidentified)]),
            'consent' => $share !== null
                ? __('messages.realtime_consent_share', ['percent' => $share])
                : null,
        ];
    }

    /**
     * 30 clock-aligned page-view buckets, oldest first, the last one being the current minute.
     * Each also carries the sign-ups that happened in it, as the sentences the feed shows, for the
     * chart's markers. Like the rest of Activity, those ignore the page filters.
     *
     * @param  list<array{ago: int, text: string}>  $marks
     */
    private function minutes(Collection $rows, array $marks = []): array
    {
        $first = RealtimeRows::firstMinute($this->nowTs);
        $buckets = array_fill(0, RealtimeRows::MINUTES, ['consented' => 0, 'unidentified' => 0, 'signups' => []]);

        foreach ($rows as $row) {
            $index = RealtimeRows::minuteIndex($row->started, $this->nowTs);

            if ($index !== null) {
                $buckets[$index][$row->consented ? 'consented' : 'unidentified']++;
            }
        }

        foreach ($marks as $mark) {
            $at = $this->nowTs - $mark['ago'];
            if ($at >= $first) {
                $buckets[min(29, intdiv($at - $first, 60))]['signups'][] = $mark['text'];
            }
        }

        return $buckets;
    }

    private function visitorList(Collection $people): array
    {
        $listed = match ($this->who) {
            'signed_in' => $people->where('kind', 'user'),
            'anonymous' => $people->where('kind', 'anon'),
            default => $people,
        };

        $nowGroup = $listed->filter(fn ($person) => $person['now'] && $person['matches_now'])->sortByDesc('sort_at');
        $earlier = $listed->reject(fn ($person) => $person['now'] && $person['matches_now'])->sortByDesc('sort_at');
        $ordered = $nowGroup->concat($earlier)->values();

        $cap = $this->all ? self::ROW_CAP_ALL : self::ROW_CAP;
        $shown = $ordered->take($cap);
        $this->attachPersonDetails($shown);
        $this->ensureNames($shown->pluck('current'));

        $more = $ordered->count() - $shown->count();

        return [
            'now' => $shown->filter(fn ($person) => $person['now'] && $person['matches_now'])->map(fn ($person) => $this->publicPerson($person))->values()->all(),
            'earlier' => $shown->reject(fn ($person) => $person['now'] && $person['matches_now'])->map(fn ($person) => $this->publicPerson($person))->values()->all(),
            'now_total' => $nowGroup->count(),
            'earlier_total' => $earlier->count(),
            'total' => $ordered->count(),
            'shown' => $shown->count(),
            'more_text' => $more > 0 && ! $this->all
                ? trans_choice('messages.realtime_show_more', $more, ['count' => number_format($more)])
                : null,
            'capped_text' => $this->all && $more > 0
                ? __('messages.realtime_showing_newest', ['shown' => number_format($shown->count()), 'total' => number_format($ordered->count())])
                : null,
        ];
    }

    /**
     * Plan badges for the people actually shown. Hosted only: off hosted every schedule reports as
     * Enterprise (Role::actualPlanTier), which would badge everyone.
     */
    private function attachPersonDetails(Collection &$people): void
    {
        $userIds = $people->pluck('user_id')->filter()->values();
        if ($userIds->isEmpty()) {
            return;
        }

        $plans = [];
        if (config('app.hosted')) {
            Role::whereIn('user_id', $userIds)->where('is_deleted', false)->with('subscriptions')->get()
                ->each(function ($role) use (&$plans) {
                    $tier = $role->actualPlanTier();
                    $rank = ['free' => 0, 'pro' => 1, 'enterprise' => 2][$tier] ?? 0;
                    if ($rank > ($plans[$role->user_id]['rank'] ?? -1)) {
                        $plans[$role->user_id] = ['tier' => $tier, 'rank' => $rank];
                    }
                });
        }

        $withEvents = Event::whereIn('user_id', $userIds)->distinct()->pluck('user_id')->flip();

        $people = $people->map(function ($person) use ($plans, $withEvents) {
            if (! $person['user_id']) {
                return $person;
            }

            $tier = $plans[$person['user_id']]['tier'] ?? null;
            if (in_array($tier, ['pro', 'enterprise'], true)) {
                $person['badges'][] = $tier;
            }

            // "No event yet" is about people who have not created one; someone who has is not stuck.
            if ($person['stuck_mins'] !== null && $withEvents->has($person['user_id'])) {
                $person['stuck_mins'] = null;
            }

            return $person;
        });
    }

    private function publicPerson(array $person): array
    {
        $person['page'] = $this->pageLabel($person['current']);
        unset($person['rows'], $person['current'], $person['user_id'], $person['matches_window'], $person['matches_now'], $person['matches_now_any_surface']);

        if ($person['stuck_mins'] !== null) {
            $person['stuck_text'] = trans_choice('messages.realtime_stuck', $person['stuck_mins'], ['count' => $person['stuck_mins']]);
        }

        return $person;
    }

    private function pagesBreakdown(Collection $rows, Collection $nowPeople): array
    {
        $nowByKey = $nowPeople->countBy(fn ($person) => $this->currentRow($person)->page_key)->all();

        // Count, sort and cap first; only the pages that are shown get a label and a URL.
        $top = $rows->groupBy('page_key')
            ->map(fn ($group) => ['first' => $group->first(), 'views' => $group->count()])
            ->sortByDesc('views')
            ->take(self::BREAKDOWN_LIMIT);

        $this->ensureNames($top->pluck('first'));

        return $top->map(function ($entry, $key) use ($nowByKey) {
            $first = $entry['first'];
            $page = $this->pageLabel($first);

            return [
                'key' => $key,
                'label' => $page['label'],
                'sub' => $page['surface_label'],
                'icon' => $first->surface,
                'url' => $page['url'],
                'views' => $entry['views'],
                'now' => $nowByKey[$key] ?? 0,
                'filterable' => true,
            ];
        })->values()->all();
    }

    /**
     * Visits, not page views: only entrances carry a source for visitors who have not accepted
     * cookies, so counting every page would weigh the two tiers differently.
     */
    private function sourcesBreakdown(Collection $rows): array
    {
        return $rows->where('is_entrance', true)->filter(fn ($row) => $row->source_key !== null)
            ->groupBy('source_key')
            ->map(function ($group, $key) {
                $first = $group->first();

                return [
                    'key' => $key,
                    'label' => $first->source_name ?: $this->channelLabel($first->source_channel),
                    'sub' => $first->source_name ? $this->channelLabel($first->source_channel) : null,
                    'icon' => $first->source_channel,
                    'url' => null,
                    'views' => $group->count(),
                    'now' => 0,
                    'filterable' => true,
                ];
            })
            ->sortByDesc('views')->take(self::BREAKDOWN_LIMIT)->values()->all();
    }

    private function countriesBreakdown(Collection $rows): array
    {
        return $rows->filter(fn ($row) => $row->country)
            ->groupBy('country')
            ->map(fn ($group, $country) => [
                'key' => $country,
                'label' => $country,
                'sub' => null,
                'icon' => null,
                'url' => null,
                'views' => $group->count(),
                'now' => 0,
                'filterable' => true,
            ])
            ->sortByDesc('views')->take(self::BREAKDOWN_LIMIT)->values()->all();
    }

    private function surfacesBreakdown(Collection $rows, int $embedViews): array
    {
        $surfaces = config('app.is_nexus') ? ['wp', 'gp', 'ap', 'auth'] : ['gp', 'ap', 'auth'];
        $counts = $rows->countBy('surface')->all();

        $list = collect($surfaces)->map(fn ($surface) => [
            'key' => $surface,
            'label' => __('messages.realtime_surface_'.$surface),
            'sub' => null,
            'icon' => $surface,
            'url' => null,
            'views' => $counts[$surface] ?? 0,
            'now' => 0,
            'filterable' => true,
        ])->sortByDesc('views')->values();

        if ($embedViews > 0) {
            $list->push([
                'key' => 'embed',
                'label' => __('messages.realtime_surface_embed'),
                'sub' => null,
                'icon' => 'embed',
                'url' => null,
                'views' => $embedViews,
                'now' => 0,
                'filterable' => false,
            ]);
        }

        return $list->all();
    }

    /**
     * Where the people on the site right now are: one entry per area, in a fixed order, for the
     * buttons beside "Visitors right now". An area is where a person's CURRENT page is.
     *
     * The other filters apply; the surface filter does not, so pressing one area leaves the counts
     * of the rest in place (surfacesBreakdown() makes the same exception). The marketing site
     * exists on the nexus only, and "Sign up & log in" is a doorway rather than a place to be, so
     * it is listed only while someone is on it or it is the filter.
     *
     * @return list<array{key: string, label: string, count: int}>
     */
    private function nowBySurface(Collection $people): array
    {
        $counts = $people
            ->filter(fn ($person) => $person['now'] && $person['matches_now_any_surface'])
            ->countBy(fn ($person) => $person['current']->surface)
            ->all();

        $surfaces = config('app.is_nexus') ? ['wp', 'gp', 'ap'] : ['gp', 'ap'];
        if (($counts['auth'] ?? 0) > 0 || ($this->filters['surface'] ?? null) === 'auth') {
            $surfaces[] = 'auth';
        }

        return array_map(fn ($surface) => [
            'key' => $surface,
            'label' => __('messages.realtime_surface_'.$surface),
            'count' => $counts[$surface] ?? 0,
        ], $surfaces);
    }

    private function currentRow(array $person): object
    {
        $rows = $person['rows'];
        $nowRows = $rows->where('is_now', true);

        return $nowRows->isNotEmpty() ? $nowRows->sortByDesc('started')->first() : $rows->sortByDesc('last_seen')->first();
    }

    /**
     * @return array<int, array{now: bool, ago: int, stuck: ?int, surface: string}>
     */
    private function presenceByUser(Collection $people): array
    {
        $presence = [];
        foreach ($people as $person) {
            if ($person['user_id']) {
                $presence[$person['user_id']] = [
                    'now' => $person['now'],
                    'ago' => $person['left_ago'] ?? 0,
                    'stuck' => $person['stuck_mins'],
                    'surface' => $person['current']->surface,
                ];
            }
        }

        return $presence;
    }

    private function lastSeenAgo(): ?int
    {
        $query = DB::table('realtime_hits')->whereNotNull('engaged_at')->where('surface', '!=', 'embed');
        if (! $this->admins) {
            $query->where('is_admin', false);
        }

        $last = $query->max('last_seen_at');

        return $last ? max(0, $this->nowTs - $this->timestamp($last)) : null;
    }

    private function justEnabled(): bool
    {
        $enabledAt = (int) \App\Models\Setting::get('realtime_enabled_at', 0);

        return $enabledAt > 0 && $this->nowTs - $enabledAt < self::WINDOW_MINUTES * 60;
    }

    /**
     * The expanded visitor's last hour, with filters ignored.
     */
    private function expanded(Collection $people): ?array
    {
        if ($this->expand === null) {
            return null;
        }

        // The exact id first: a user keeps v:{key} as an alias while their post-sign-out browsing on
        // that key is its own v:{key} person, and the two must never be confused.
        $person = $people->first(fn ($p) => $p['id'] === $this->expand)
            ?? $people->first(fn ($p) => in_array($this->expand, $p['aliases'], true));
        $userId = $person['user_id'] ?? (str_starts_with($this->expand, 'u:') ? UrlUtils::decodeId(substr($this->expand, 2)) : null);
        $keys = $person ? array_map(fn ($alias) => substr($alias, 2), $person['aliases']) : [];
        if (! $person && str_starts_with($this->expand, 'v:')) {
            $keys = [substr($this->expand, 2)];
        }

        if (! $userId && ! $keys) {
            return null;
        }

        $since = RealtimeTracker::ts($this->now->subMinutes(RealtimeTracker::RETENTION_MINUTES));
        $user = $userId ? User::find($userId, ['id', 'name', 'email', 'created_at', 'is_admin']) : null;

        // Anonymous rows on these keys are split by the rule people() merges by (keyOwner()), over the
        // hour rather than the 30-minute window: a user's timeline takes only the ones that belong to
        // them, and an anonymous person's takes only the ones that belong to no one. Otherwise the
        // anonymous timeline would repeat the user's pre-sign-in pages and link the two.
        $keyUsers = [];
        $known = [];
        if ($keys) {
            $owners = DB::table('realtime_hits')
                ->whereIn('visitor_key', $keys)
                ->where('last_seen_at', '>=', $since)
                ->whereNotNull('user_id')
                ->where('is_demo', false)
                ->groupBy('visitor_key', 'user_id')
                ->get(['visitor_key', 'user_id', DB::raw('MAX(started_at) AS latest_start')]);

            $known = $owners->isEmpty() ? [] : User::whereIn('id', $owners->pluck('user_id')->unique())
                ->get(['id', 'is_admin'])->keyBy('id')->all();

            foreach ($owners as $owner) {
                // A deleted account owns nothing, as in people().
                if (isset($known[$owner->user_id])) {
                    $keyUsers[$owner->visitor_key][$owner->user_id] = $this->timestamp($owner->latest_start);
                }
            }
        }

        $rows = DB::table('realtime_hits')
            ->where('consented', true)
            ->where('last_seen_at', '>=', $since)
            ->where(function ($query) use ($userId, $keys) {
                if ($userId) {
                    $query->where('user_id', $userId);
                }
                if ($keys) {
                    $query->orWhere(fn ($q) => $q->whereIn('visitor_key', $keys)->whereNull('user_id'));
                }
            })
            ->orderBy('started_at')
            ->limit(500)
            ->get(self::COLUMNS)
            ->map(function ($row) {
                $row->started = $this->timestamp($row->started_at);
                $row->last_seen = $this->timestamp($row->last_seen_at);
                $row->ended = $row->ended_at ? $this->timestamp($row->ended_at) : null;
                $row->is_now = RealtimeRows::isNow($row->ended, $row->last_seen, (int) $row->hb, $this->nowTs);

                return $row;
            })
            ->filter(function ($row) use ($userId, $keyUsers, $known) {
                if ($row->user_id) {
                    return true;
                }

                $owner = self::keyOwner($keyUsers[$row->visitor_key] ?? [], $row->started, $known);

                return $userId ? $owner === (int) $userId : $owner === null;
            })
            ->values();

        if ($rows->isEmpty()) {
            return null;
        }

        $this->ensureNames($rows);

        $timeline = $rows->map(function ($row) {
            $end = $row->is_now ? $this->nowTs : ($row->ended ?? $row->last_seen);
            $page = $this->pageLabel($row);

            return [
                'key' => $row->hit_key,
                'at_ago' => max(0, $this->nowTs - $row->started),
                'label' => $page['label'],
                'surface' => $row->surface,
                'surface_label' => $page['surface_label'],
                'url' => in_array($row->surface, ['wp', 'gp'], true) ? $page['url'] : null,
                'secs' => max(0, $end - $row->started),
                'current' => (bool) $row->is_now,
                'marker' => null,
            ];
        })->values();

        if ($user && $user->created_at && $user->created_at->getTimestamp() >= $this->nowTs - RealtimeTracker::RETENTION_MINUTES * 60) {
            $timeline->push([
                'key' => 'signed-up',
                'at_ago' => max(0, $this->nowTs - $user->created_at->getTimestamp()),
                'label' => __('messages.realtime_signed_up_marker'),
                'surface' => null,
                'surface_label' => null,
                'url' => null,
                'secs' => null,
                'current' => false,
                'marker' => 'signed_up',
            ]);
            // Oldest first; a sign-up recorded in the same second as the first app page still reads
            // before it, because that is the order it happened in.
            $timeline = $timeline->sort(fn ($a, $b) => ($b['at_ago'] <=> $a['at_ago']) ?: (($a['marker'] ? 0 : 1) <=> ($b['marker'] ? 0 : 1)))->values();
        }

        $schedules = [];
        $chatUrl = null;
        if ($user) {
            $schedules = Role::where('user_id', $user->id)->where('is_deleted', false)->with('subscriptions')->orderBy('id')->limit(10)->get()
                ->map(fn ($role) => [
                    'name' => $role->name,
                    'plan' => config('app.hosted') ? __('messages.'.$role->actualPlanTier()) : null,
                    'admin_url' => route('admin.schedules.edit', ['role' => UrlUtils::encodeId($role->id)]),
                ])->all();

            if (Route::has('admin.support')) {
                $conversation = SupportConversation::where('user_id', $user->id)->orderByDesc('id')->first(['id']);
                $chatUrl = $conversation ? route('admin.support', ['c' => UrlUtils::encodeId($conversation->id)]) : null;
            }
        }

        return [
            'person_id' => $person['id'] ?? $this->expand,
            'timeline' => $timeline->all(),
            'schedules' => $schedules,
            'email' => $user?->email,
            'chat_url' => $chatUrl,
        ];
    }
}
