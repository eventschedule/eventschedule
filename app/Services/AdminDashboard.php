<?php

namespace App\Services;

use App\Models\BoostBillingRecord;
use App\Models\Event;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Utils\CountryUtils;
use App\Utils\ImageUtils;
use App\Utils\SignupSource;
use App\Utils\UrlUtils;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Everything /admin/dashboard shows, one method per card.
 *
 * The windows are fixed, which is why the page has no date-range select: "24 hours" is rolling,
 * "30 days" is thirty calendar days including today in the app timezone, and "previous" is the same
 * span thirty days earlier, cut at the same clock time - so a look at 9am is compared with thirty
 * days ago at 9am, not with a whole day.
 *
 * Definitions worth knowing before changing a number here:
 *
 * - Organizers are GrowthExportService::cohort(): verified, not the demo account, and signed up to
 *   run a schedule (signup_intent null or organizer). Accounts an audience creates - a follower, a
 *   ticket buyer, someone submitting an event to another schedule - are counted beside them.
 * - Demo content is Role::constrainDemoContent(), the predicate RecurringRevenue and the growth
 *   payload use, on every query written here. Not the `demo-%` subdomain shape the page used to
 *   test. The one exception is the "Outside Stripe" line of the revenue card: those three counts
 *   are AdminPlanCounts, which keeps the older test because /admin/schedules, the page they come
 *   from, uses it everywhere. Which schedules are demo is read ONCE (demoRoleIds()) and handed to
 *   each query as ids: asked inside the query, it was answered again for every event.
 * - An event belongs to the page while one of its schedules is not deleted. Deleting a schedule
 *   keeps its events (ScheduleDeletionService), and they are on no page anyone can open.
 * - An upcoming event has an occurrence still to come (Event::scopeHasUpcomingOccurrence(), so a
 *   running series counts, once), and is published, not cancelled, and not an appointment booking.
 * - How people attend is Event::getSchemaAttendanceMode(): a join link is online, a venue is in
 *   person, both is hybrid.
 * - Active users are ActiveDays; recurring revenue is RecurringRevenue. Neither is restated here.
 *
 * build() isolates each card: this is the page an admin lands on, and one failing query should
 * cost one card, not the page. It also times each one (timings()), which the page sends as a
 * Server-Timing header: the page's first run against production's data took longer than a request
 * is allowed, and nothing said which card.
 *
 * What a query here costs is how many events it READS, so three rules hold for all of them. Start
 * from `events` (EVENTS_FIRST). Never read every event to answer for a few: the newest-events list
 * walks the created_at index and stops, the 30-day figures are bounded by created_at. And never
 * load a whole event or schedule that is not going to be shown.
 */
class AdminDashboard
{
    /** Rows a list loads, and how many it shows before "Show more". */
    public const LIST_ROWS = 20;

    public const LIST_VISIBLE = 8;

    /**
     * How many of the newest events are looked at before bursts are collapsed down to LIST_ROWS.
     * Twenty fill the list unless imports crowd it; the rest are for the day one schedule brings
     * in a few hundred at once. Three columns each, so the number costs little.
     */
    private const EVENT_SCAN = 300;

    /**
     * Opens the select list of every query here that filters `events` by a subquery, so that
     * `events` is the table the query starts from.
     *
     * Without it MySQL rewrites `EXISTS (on a live schedule)` as a semijoin, and is then free to
     * start from the schedules instead: every schedule, every pivot row, every event fetched one
     * at a time by its id, duplicates removed in a temporary table. For a count that is slow. For
     * the newest-events list it meant sorting every event there is to keep sixty, with the index
     * on created_at never used. The modifier rules the rewrite out (MySQL documents that it does),
     * so each subquery is answered beside the event it is asked of: a probe, or a set gathered
     * once (see onLiveSchedule() and liveEventIds()). MariaDB reads the modifier the same way.
     */
    private const EVENTS_FIRST = 'STRAIGHT_JOIN';

    /** Events one schedule creates within this many seconds of each other are one row. */
    private const BURST_SECONDS = 600;

    private const LATEST_SIGNUPS = 8;

    private CarbonImmutable $now;

    private CarbonImmutable $start;

    /** @var array<string, float> card => milliseconds, for the last build() */
    private array $timings = [];

    /** @var array<int, int>|null */
    private ?array $demoRoleIds = null;

    public function __construct(?CarbonImmutable $now = null)
    {
        $this->now = $now ?? now()->toImmutable();
        $this->start = $this->now->startOfDay()->subDays(29);
    }

    /**
     * Every card, or null for one that failed (reported, and the page renders without it).
     */
    public function build(): array
    {
        $hosted = (bool) config('app.hosted');

        $data = [
            'signups' => $this->card('signups', fn () => $this->signups()),
            'active' => $this->card('active', fn () => $this->activeUsers()),
            'revenue' => $hosted ? $this->card('revenue', fn () => $this->revenue()) : null,
            'events' => $this->card('events', fn () => $this->events()),
            'federation' => $this->card('federation', fn () => $this->federation()),
            'schedules' => $this->card('schedules', fn () => $this->recentSchedules()),
            'recentEvents' => $this->card('recentEvents', fn () => $this->recentEvents()),
            'system' => $this->card('system', fn () => $this->system()),
        ];

        // A new install: no schedule, no event, and nobody but the person looking. Their own
        // account is a sign-up from this week, so "no sign-ups" would never be true on day one.
        $data['firstRun'] = ($data['schedules']['total'] ?? 1) === 0
            && ($data['events']['all_time'] ?? 1) === 0
            && ($data['system']['accounts'] ?? 2) <= 1;

        return $data;
    }

    /**
     * Invented data in build()'s shape, for the documentation screenshot: the real page lists
     * people, schedules and events by name. See AdminDashboardSample.
     */
    public static function sample(bool $empty = false): array
    {
        return AdminDashboardSample::data($empty);
    }

    private function card(string $name, callable $build): ?array
    {
        $started = hrtime(true);

        try {
            return $build();
        } catch (Throwable $e) {
            // A test must see the failure, not a page with a card missing.
            if (config('app.is_testing')) {
                throw $e;
            }

            report($e);

            return null;
        } finally {
            $this->timings[$name] = (hrtime(true) - $started) / 1e6;
        }
    }

    /**
     * How long each card of the last build() took, in milliseconds.
     *
     * @return array<string, float>
     */
    public function timings(): array
    {
        return $this->timings;
    }

    /**
     * timings() as a Server-Timing header, with whatever else the caller timed. Durations only.
     * Read it in the browser's network panel, on the page's own request.
     *
     * @param  array<string, float>  $also  name => milliseconds
     */
    public function serverTiming(array $also = []): string
    {
        $entries = [];
        foreach ($this->timings + $also as $name => $milliseconds) {
            $entries[] = sprintf('%s;dur=%.1f', $name, $milliseconds);
        }

        return implode(', ', $entries);
    }

    /** The demo's schedules, read once for this page. Empty where there is no demo. */
    private function demoRoleIds(): array
    {
        return $this->demoRoleIds ??= DemoService::demoRoleIds();
    }

    /**
     * For whereNotIn('events.id', ...): the events on a demo schedule. MySQL reads it once, from
     * the pivot's (role_id, event_id) index, and no schedule or owner is looked up per event.
     */
    private function demoEventIds(): \Illuminate\Database\Query\Builder
    {
        return DB::table('event_role')->whereIn('role_id', $this->demoRoleIds())->select('event_id');
    }

    /**
     * New accounts over the last 30 days: the headline counts, one bar per day, how organizers
     * signed up, where they came from, and the newest of them with how far each has got.
     *
     * One read of the window's rows; everything else is derived from it.
     */
    public function signups(): array
    {
        $rows = DB::table('users')
            ->whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->where('created_at', '>=', $this->start)
            ->where('created_at', '<=', $this->now)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get([
                'id', 'name', 'email', 'created_at', 'signup_intent',
                'utm_source', 'utm_medium', 'utm_campaign', 'referrer_url', 'landing_page', 'referred_by_user_id',
                // Whether, never what: no hash is read into memory to count sign-up methods.
                DB::raw('password IS NOT NULL as has_password'),
                DB::raw('google_oauth_id IS NOT NULL as has_google'),
            ]);

        $isOrganizer = fn ($row) => $row->signup_intent === null || $row->signup_intent === 'organizer';
        $organizers = $rows->filter($isOrganizer)->values();
        $others = $rows->reject($isOrganizer)->values();
        // Hours, not subDay(): a day is 23 or 25 of them when the clocks change.
        $dayAgo = $this->now->subHours(24)->toDateTimeString();

        $previous = app(GrowthExportService::class)
            ->cohort(Carbon::instance($this->start->subDays(30)), Carbon::instance($this->now->subDays(30)))
            ->count();

        $days = [];
        for ($day = $this->start; $day->lte($this->now); $day = $day->addDay()) {
            $days[$day->toDateString()] = ['date' => $day->toDateString(), 'label' => $day->translatedFormat('M j'), 'organizers' => 0, 'others' => 0];
        }
        foreach ($rows as $row) {
            $date = substr((string) $row->created_at, 0, 10);
            if (isset($days[$date])) {
                $days[$date][$isOrganizer($row) ? 'organizers' : 'others']++;
            }
        }

        return [
            'organizers' => [
                'last_24h' => $organizers->filter(fn ($row) => (string) $row->created_at > $dayAgo)->count(),
                'last_30d' => $organizers->count(),
                'previous_30d' => $previous,
                'change' => $previous > 0 ? round((($organizers->count() - $previous) / $previous) * 100, 1) : null,
            ],
            'others' => [
                'total' => $others->count(),
                'by_intent' => $others->countBy('signup_intent')->sortDesc()
                    ->map(fn ($count, $intent) => ['intent' => (string) $intent, 'count' => $count])->values()->all(),
            ],
            'days' => array_values($days),
            'methods' => [
                'email' => $organizers->filter(fn ($row) => $row->has_password && ! $row->has_google)->count(),
                'google' => $organizers->filter(fn ($row) => $row->has_google && ! $row->has_password)->count(),
                'both' => $organizers->filter(fn ($row) => $row->has_google && $row->has_password)->count(),
                // Neither: Facebook, or an account made for someone. Counted so the line adds up
                // to the headline.
                'other' => $organizers->filter(fn ($row) => ! $row->has_google && ! $row->has_password)->count(),
            ],
            'sources' => $this->sources($organizers),
            'latest' => $this->latestSignups($organizers->take(self::LATEST_SIGNUPS)),
        ];
    }

    /**
     * Where the window's organizers came from. Every row is a share of all of them, "Not recorded"
     * included, so the list adds up to the headline rather than to a base the reader cannot see.
     *
     * `names` are the two most common sources inside a channel (a host, or a campaign's source).
     * `landing` is the first page they saw, where one was stored and it is not the sign-up page.
     */
    private function sources(Collection $organizers): array
    {
        $classified = $organizers->map(fn ($row) => SignupSource::forUser($row));
        $total = $classified->count();
        $byChannel = $classified->groupBy('channel');

        $channels = [];
        foreach (SignupSource::CHANNELS as $channel) {
            $group = $byChannel->get($channel);
            if (! $group) {
                continue;
            }

            $channels[] = [
                'channel' => $channel,
                'label' => SignupSource::label($channel),
                'count' => $group->count(),
                'share' => (int) round($group->count() / $total * 100),
                'names' => $group->pluck('name')->filter()->countBy()->sortDesc()->take(2)
                    ->map(fn ($count, $name) => ['name' => (string) $name, 'count' => $count])->values()->all(),
            ];
        }

        // Largest first, with "Not recorded" always last: it is the absence of a source, not one.
        usort($channels, fn ($a, $b) => [$a['channel'] === 'unrecorded', -$a['count']] <=> [$b['channel'] === 'unrecorded', -$b['count']]);

        return [
            'total' => $total,
            'unrecorded' => $byChannel->get('unrecorded')?->count() ?? 0,
            'channels' => $channels,
            'landing' => $classified->pluck('landing')->filter()->countBy()->sortDesc()->take(5)
                ->map(fn ($count, $path) => ['path' => (string) $path, 'count' => $count])->values()->all(),
        ];
    }

    /**
     * The newest organizers and the step each has reached, by the rules RealtimeActivity and
     * GrowthExportService::signupRows() use so the pages cannot disagree about one person: a
     * deleted schedule still counts as made, a guest submission is not their own event, and an
     * add-on is not a ticket. Cumulative: 0 account, 1 schedule, 2 event, 3 ticket type.
     */
    private function latestSignups(Collection $rows): array
    {
        if ($rows->isEmpty()) {
            return [];
        }

        $ids = $rows->pluck('id')->all();

        $schedules = DB::table('roles')->whereIn('user_id', $ids)->orderBy('id')
            ->get(['id', 'user_id', 'name', 'is_deleted'])->groupBy('user_id');
        $events = DB::table('events')->whereIn('user_id', $ids)->where('is_guest_submission', false)
            ->distinct()->pluck('user_id')->flip();
        $tickets = DB::table('events')->join('tickets', 'tickets.event_id', '=', 'events.id')
            ->whereIn('events.user_id', $ids)
            ->where('tickets.is_deleted', false)
            ->where('tickets.is_addon', false)
            ->distinct()->pluck('events.user_id')->flip();

        $referrerIds = $rows->pluck('referred_by_user_id')->filter()->unique()->all();
        $referrers = $referrerIds ? DB::table('users')->whereIn('id', $referrerIds)->pluck('name', 'id') : collect();

        return $rows->map(function ($row) use ($schedules, $events, $tickets, $referrers) {
            $stage = match (true) {
                $tickets->has($row->id) => 3,
                $events->has($row->id) => 2,
                $schedules->has($row->id) => 1,
                default => 0,
            };
            $schedule = ($schedules->get($row->id) ?? collect())->firstWhere('is_deleted', 0);
            // A person with no name is known by their address; admins may see it.
            $name = $row->name ?: $row->email;

            return [
                'name' => $name,
                'initials' => RealtimeDashboard::initials((string) $name),
                'source' => SignupSource::display($row, $row->referred_by_user_id ? ($referrers[$row->referred_by_user_id] ?? null) : null),
                'stage' => $stage,
                'schedule' => $schedule ? [
                    'name' => $schedule->name,
                    'url' => route('admin.schedules.edit', ['role' => UrlUtils::encodeId($schedule->id)]),
                ] : null,
                'created_at' => Carbon::parse($row->created_at),
            ];
        })->values()->all();
    }

    /**
     * ActiveDays::stats(), with a label for each point of the chart.
     */
    public function activeUsers(): array
    {
        $stats = ActiveDays::stats($this->now);

        $stats['series'] = array_map(fn ($point) => $point + [
            'label' => CarbonImmutable::parse($point['date'])->translatedFormat('M j'),
        ], $stats['series']);
        $stats['exact_from_label'] = $stats['exact_from'] ? CarbonImmutable::parse($stats['exact_from'])->translatedFormat('M j') : null;

        return $stats;
    }

    /**
     * Hosted only. RecurringRevenue::breakdown() as it is, the plans that are not being billed
     * (AdminPlanCounts, the numbers /admin/schedules shows), and the boost markup of the window.
     */
    public function revenue(): array
    {
        return RecurringRevenue::breakdown() + [
            'outside' => [
                'granted' => AdminPlanCounts::manual(),
                'trial' => AdminPlanCounts::trial(),
                'selling' => AdminPlanCounts::sellingTrials(),
            ],
            'boost' => [
                'markup' => (float) BoostBillingRecord::where('type', 'charge')
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$this->start, $this->now])
                    ->sum('markup_amount'),
                // Off the campaigns, not off the Meta ad account's currency: that config defaults
                // to USD and printed "$0" on every selfhost whatever the operator had picked.
                'currency' => BoostBillingService::markupCurrency($this->start, $this->now),
            ],
        ];
    }

    /**
     * Upcoming events by how people attend, the countries the in-person ones are in, how soon the
     * dated ones start, and the all-time totals.
     *
     * The horizons count one-off events only and the page says so: SQL can tell that a series is
     * still running, not when its next occurrence is. All three are rolling from now. The first
     * is 24 hours and not "today", because a day belongs to a schedule's timezone and this is a
     * count across all of them: an evening show in New York is tomorrow on a UTC clock.
     */
    public function events(): array
    {
        $utc = $this->now->utc();
        $horizons = [
            'next_24h' => $utc->addHours(24)->format('Y-m-d H:i:s'),
            'next_7' => $utc->addDays(7)->format('Y-m-d H:i:s'),
            'next_30' => $utc->addDays(30)->format('Y-m-d H:i:s'),
        ];

        // ONE read of the events, four small columns of each upcoming one, added up here. Telling
        // whether an event is still to come needs its row (Event::constrainToOccurrencesSince()),
        // so this is the page's one pass over the whole table, and the reason there is only one:
        // the split by attendance and the countries were two queries, each reading every event.
        //
        // venue_countries is the countries of the event's venues: null with no venue, '' for a
        // venue with no country on record, otherwise the codes. Nothing newer than MySQL 5.7 or
        // MariaDB 10.3 can run.
        $upcoming = $this->upcoming()->toBase()->selectRaw(
            self::EVENTS_FIRST." (events.event_url IS NOT NULL AND events.event_url <> '') as has_url,
            (events.days_of_week IS NOT NULL) as is_series,
            events.starts_at as starts_at,
            (SELECT GROUP_CONCAT(DISTINCT LOWER(COALESCE(roles.country_code, '')))
                FROM event_role JOIN roles ON roles.id = event_role.role_id
                WHERE event_role.event_id = events.id AND roles.type = 'venue') as venue_countries"
        )->cursor();

        $count = ['total' => 0, 'hybrid' => 0, 'online' => 0, 'in_person' => 0, 'no_location' => 0, 'recurring' => 0]
            + array_fill_keys(array_keys($horizons), 0);
        $byCountry = [];

        foreach ($upcoming as $event) {
            $hasUrl = (bool) $event->has_url;
            $hasVenue = $event->venue_countries !== null;

            $count['total']++;
            $count[match (true) {
                $hasUrl && $hasVenue => 'hybrid',
                $hasUrl => 'online',
                $hasVenue => 'in_person',
                default => 'no_location',
            }]++;

            if ($event->is_series) {
                $count['recurring']++;
            } elseif ($event->starts_at !== null) {
                foreach ($horizons as $horizon => $before) {
                    // Both are UTC datetimes written the same way, so they compare as text.
                    $count[$horizon] += (int) ($event->starts_at < $before);
                }
            }

            // Once per country, however many of the event's venues are in it.
            foreach (array_unique(array_filter(array_map('trim', explode(',', (string) $event->venue_countries)))) as $code) {
                $byCountry[$code] = ($byCountry[$code] ?? 0) + 1;
            }
        }

        // Most events first, and by code among equals so the order does not move between loads.
        ksort($byCountry);
        arsort($byCountry);
        $countries = array_slice($byCountry, 0, 5, true);

        // Two reads where there was one, so that neither opens every event. The total names no
        // column but the id and appointment_type_id, which that column's index holds between
        // them; the two windows are bounded by created_at, so they read sixty days of events.
        // As one query it read every event ever made to tell which were from this month.
        $allTime = (int) DB::table('events')
            ->whereNull('events.appointment_type_id')
            ->whereNotIn('events.id', $this->demoEventIds())
            ->whereIn('events.id', self::liveEventIds())
            ->selectRaw(self::EVENTS_FIRST.' COUNT(*) as total')
            ->first()->total;

        $added = DB::table('events')
            ->whereNull('events.appointment_type_id')
            ->whereNotIn('events.id', $this->demoEventIds())
            ->whereExists(self::onLiveSchedule())
            ->where('events.created_at', '>=', $this->start->subDays(30))
            ->where('events.created_at', '<=', $this->now)
            ->selectRaw(
                self::EVENTS_FIRST.' COALESCE(SUM(events.created_at >= ?), 0) as new_30d,
                COALESCE(SUM(events.created_at <= ?), 0) as previous_30d',
                [$this->start, $this->now->subDays(30)]
            )->first();

        $new = (int) $added->new_30d;
        $previous = (int) $added->previous_30d;

        return [
            'total' => $count['total'],
            'in_person' => $count['in_person'],
            'online' => $count['online'],
            'hybrid' => $count['hybrid'],
            'no_location' => $count['no_location'],
            'one_off' => $count['total'] - $count['recurring'],
            'recurring' => $count['recurring'],
            'next_24h' => $count['next_24h'],
            'next_7' => $count['next_7'],
            'next_30' => $count['next_30'],
            'countries' => array_map(fn ($code, $events) => [
                'code' => (string) $code,
                'name' => CountryUtils::getName((string) $code),
                'count' => $events,
            ], array_keys($countries), $countries),
            'all_time' => $allTime,
            'new_30d' => $new,
            'new_change' => $previous > 0 ? round((($new - $previous) / $previous) * 100, 1) : null,
        ];
    }

    /** The population every upcoming-event figure is drawn from. */
    private function upcoming()
    {
        return Event::query()
            ->hasUpcomingOccurrence(Carbon::instance($this->now->utc()))
            ->where('events.is_cancelled', false)
            ->where('events.is_draft', false)
            ->whereNull('events.appointment_type_id')
            ->whereNotIn('events.id', $this->demoEventIds())
            ->whereIn('events.id', self::liveEventIds());
    }

    /**
     * "The event is on a schedule that is not deleted", in the two forms a query can ask it.
     *
     * For whereExists(): a probe per event, the pivot and then the schedule. Right for a query
     * that looks at few events (the newest-events walk, the thirty-day figures).
     */
    private static function onLiveSchedule(): \Closure
    {
        return fn ($exists) => $exists->select(DB::raw(1))
            ->from('event_role as live_pivot')
            ->join('roles as live_role', 'live_role.id', '=', 'live_pivot.role_id')
            ->whereColumn('live_pivot.event_id', 'events.id')
            ->where('live_role.is_deleted', false);
    }

    /**
     * For whereIn('events.id', ...): every such event, which MySQL gathers once and then looks
     * each event up in. Right for a query that looks at them all (the upcoming pass, the total),
     * where a probe per event was most of what the count cost.
     */
    private static function liveEventIds(): \Illuminate\Database\Query\Builder
    {
        return DB::table('event_role as live_pivot')
            ->join('roles as live_role', 'live_role.id', '=', 'live_pivot.role_id')
            ->where('live_role.is_deleted', false)
            ->select('live_pivot.event_id');
    }

    /**
     * The hub's numbers on the nexus; this install's own sharing state anywhere else; and, where
     * sharing is off, nothing but the fact - no event query runs for a feature that is not in use.
     */
    public function federation(): array
    {
        if (config('app.is_nexus')) {
            return ['mode' => 'hub']
                + ['installs' => FederationStats::instances()]
                + ['listings' => FederationStats::listings()]
                + ['clicks' => FederationStats::clicks()];
        }

        $service = app(FederationService::class);

        if (! $service->isEnabled()) {
            return ['mode' => 'off'];
        }

        $synced = Setting::get('federation_last_synced_at');

        return [
            'mode' => 'sender',
            'status' => $service->status(),
            'last_synced_at' => $synced ? Carbon::parse($synced) : null,
            'has_error' => filled(Setting::get('federation_last_error')),
            'listings_url' => Setting::get('federation_listings_url'),
            // The settings card's own figures, so the two cannot disagree.
            'totals' => $service->previewTotals(),
            'undecided' => $service->undecidedScheduleCount(),
        ];
    }

    /**
     * The newest schedules that are real: claimed, live, and not demo content. No per-row queries:
     * the owner and (on hosted, where a plan means something) the subscriptions are eager-loaded,
     * and the image variant is read off the row.
     */
    public function recentSchedules(): array
    {
        $base = fn () => Role::query()
            ->claimed()
            ->where('is_deleted', false)
            ->whereNotIn('roles.id', $this->demoRoleIds());

        $hosted = (bool) config('app.hosted');

        $roles = $base()
            ->with($hosted ? ['user:id,name,email', 'subscriptions.items'] : ['user:id,name,email'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(self::LIST_ROWS)
            ->get();

        $eventCounts = $roles->isEmpty() ? collect() : DB::table('event_role')
            ->whereIn('role_id', $roles->pluck('id')->all())
            ->groupBy('role_id')
            ->selectRaw('role_id, COUNT(DISTINCT event_id) as events')
            ->pluck('events', 'role_id');

        $totals = $base()->toBase()->selectRaw(
            'COUNT(*) as total, COALESCE(SUM(roles.created_at >= ? AND roles.created_at <= ?), 0) as new_30d',
            [$this->start, $this->now]
        )->first();

        return [
            'total' => (int) $totals->total,
            'new_30d' => (int) $totals->new_30d,
            'rows' => $roles->map(function (Role $role) use ($eventCounts, $hosted) {
                $tier = $hosted ? $role->actualPlanTier() : 'free';

                return [
                    'name' => $role->name,
                    'type' => $role->type,
                    'place' => implode(', ', array_filter([$role->city, $role->country_code ? strtoupper($role->country_code) : null])),
                    'owner' => $role->user?->name ?: $role->user?->email,
                    'events' => (int) ($eventCounts[$role->id] ?? 0),
                    // A plan is only a fact on hosted: a selfhost schedule reads "enterprise".
                    'plan' => $tier === 'free' ? null : ($role->isOnTrial() ? 'trial' : $tier),
                    'url' => $role->getGuestUrl(),
                    'image' => $role->getProfileImageUrl(ImageUtils::VARIANT_WIDTH) ?: null,
                    'created_at' => $role->created_at,
                ];
            })->all(),
        ];
    }

    /**
     * The newest events anyone can be shown: not unlisted, not an appointment booking (which is
     * named after a guest), not demo content, and on a schedule that still exists. A draft, a
     * guest submission and an import are listed and flagged - they are the first sign of a new
     * organizer, the likeliest spam, and the reason twelve events appeared at once.
     *
     * A burst from one schedule is one row: its newest event, and how many came with it. The
     * newest EVENT_SCAN events are looked at, so an import of a few hundred is still one row with
     * the rest of the list under it, and its count is the whole burst (up to EVENT_SCAN events,
     * past which the count is a floor).
     *
     * Two reads, because what decides the list and what the list shows are different sizes. The
     * first takes three columns of each candidate and walks the created_at index from its newest
     * end, stopping at EVENT_SCAN: bursts are collapsed on those. Only the twenty that came
     * through are then loaded whole, with their schedules. Read whole from the start, sixty at a
     * time until the list filled, the day of an import loaded three hundred events and every
     * schedule on them to show one row.
     *
     * The link is where the event is really shown. Event::canonicalTarget() picks a schedule that
     * serves it - accepted, claimed, not deleted - where naming the first claimed schedule sent
     * the admin to a 404 for a submission still waiting to be accepted. With no schedule serving
     * it yet, the link is the admin's own edit page, as it is for a draft.
     */
    public function recentEvents(): array
    {
        $candidates = DB::table('events')
            ->where('events.is_private', false)
            ->whereNull('events.appointment_type_id')
            ->whereNotIn('events.id', $this->demoEventIds())
            ->whereExists(self::onLiveSchedule())
            ->orderByDesc('events.created_at')
            ->orderByDesc('events.id')
            ->limit(self::EVENT_SCAN)
            ->selectRaw(self::EVENTS_FIRST.' events.id, events.creator_role_id, events.created_at')
            ->get();

        $picked = [];
        $last = null;

        foreach ($candidates as $candidate) {
            $createdAt = $candidate->created_at ? Carbon::parse($candidate->created_at)->getTimestamp() : 0;
            $creator = $candidate->creator_role_id === null ? null : (int) $candidate->creator_role_id;

            if ($last !== null && $creator !== null
                && $creator === $last['creator']
                && abs($last['at'] - $createdAt) <= self::BURST_SECONDS) {
                $picked[$last['index']]['more']++;
                $last['at'] = $createdAt;

                continue;
            }

            // Full, and the last row's burst has ended: nothing further can change the list.
            if (count($picked) === self::LIST_ROWS) {
                break;
            }

            $picked[] = ['id' => (int) $candidate->id, 'more' => 0];
            $last = ['creator' => $creator, 'at' => $createdAt, 'index' => count($picked) - 1];
        }

        if ($picked === []) {
            return ['rows' => []];
        }

        $events = Event::query()
            ->with(['roles', 'creatorRole'])
            ->whereIn('events.id', array_column($picked, 'id'))
            ->get()
            ->keyBy('id');

        $use24 = (bool) auth()->user()?->use_24_hour_time;
        $rows = [];

        foreach ($picked as $pick) {
            // Deleted between the two reads.
            if (! $event = $events->get($pick['id'])) {
                continue;
            }

            [$publicUrl, $home] = $event->canonicalTarget();
            $shownOn = $home ?? $event->getViewableRole();
            $series = $event->days_of_week !== null;
            $start = ! $series && $event->starts_at ? $event->getStartDateTime(null, true) : null;

            $rows[] = [
                'name' => $event->name,
                'schedule' => $shownOn?->name,
                'series' => $series,
                'when' => $start ? $start->translatedFormat('D, M j').' · '.$start->format($use24 ? 'H:i' : 'g:i A') : null,
                'mode' => match ($event->getSchemaAttendanceMode()) {
                    'https://schema.org/MixedEventAttendanceMode' => 'hybrid',
                    'https://schema.org/OnlineEventAttendanceMode' => 'online',
                    'https://schema.org/OfflineEventAttendanceMode' => 'in_person',
                    default => 'no_location',
                },
                'flag' => match (true) {
                    (bool) $event->is_draft => 'draft',
                    (bool) $event->is_guest_submission => 'submitted',
                    // Read as an attribute, never in a WHERE: a selfhost can run this code
                    // before the migration that adds the column.
                    filled($event->getAttribute('import_source')) => 'imported',
                    default => null,
                },
                'more' => $pick['more'],
                'url' => $event->is_draft || ! $home || ! $publicUrl
                    ? route('event.edit_admin', ['hash' => UrlUtils::encodeId($event->id)])
                    : $publicUrl,
                'image' => $event->getImageUrl(ImageUtils::VARIANT_WIDTH) ?: null,
                'created_at' => $event->created_at,
            ];
        }

        return ['rows' => $rows];
    }

    /**
     * The one line at the foot of the page: the queue, custom domains (hosted), accounts in total.
     * Each already raises Needs attention when something is wrong; this is the calm reading.
     */
    public function system(): array
    {
        $domains = null;

        if (config('app.hosted')) {
            $row = DB::table('roles')->whereNotNull('custom_domain')->selectRaw(
                "COUNT(*) as total,
                COALESCE(SUM(custom_domain_mode = 'direct' AND custom_domain_status = 'pending'), 0) as pending"
            )->first();

            $domains = ['total' => (int) $row->total, 'pending' => (int) $row->pending];
        }

        return [
            'jobs_waiting' => DB::table('jobs')->count(),
            'jobs_failed' => DB::table('failed_jobs')->count(),
            'domains' => $domains,
            'accounts' => User::whereNotNull('email_verified_at')->where('email', '!=', DemoService::DEMO_EMAIL)->count(),
        ];
    }
}
