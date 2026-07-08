<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\EventComment;
use App\Models\EventPhoto;
use App\Models\EventPoll;
use App\Models\EventVideo;
use App\Models\Newsletter;
use App\Models\Role;
use App\Models\Sale;
use App\Services\AnalyticsService;
use App\Utils\DateUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    use Traits\CalendarDataTrait;

    public function landing($slug = null)
    {
        if ($slug && $role = Role::whereSubdomain($slug)->first()) {
            return redirect()->route('role.view_guest', ['subdomain' => $role->subdomain]);
        }

        return redirect(route('home'));
    }

    /**
     * Focused post-signup onboarding step: pick a schedule type without the
     * dashboard chrome. Users who already belong to a schedule or hold tickets
     * have a real dashboard, so bounce them home (this also guards against
     * redirect loops: home() only forwards users this page will render for).
     */
    public function gettingStarted(Request $request)
    {
        $user = $request->user();

        if (is_demo_mode() || $user->member()->exists() || $user->tickets()->count() > 0) {
            return redirect()->route('home');
        }

        return view('getting-started');
    }

    public function home(Request $request)
    {
        if ($pending = session()->pull('pending_fan_content')) {
            $returnUrl = $this->processPendingFanContent($pending);
            if ($returnUrl) {
                return redirect($returnUrl);
            }
        }

        $subdomain = session('pending_follow');

        if (! $subdomain) {
            $subdomain = session('pending_request');
        }

        if ($subdomain) {
            $role = Role::whereSubdomain($subdomain)->firstOrFail();

            return redirect()->route('role.follow', ['subdomain' => $subdomain]);
        }

        $user = $request->user();

        if ($request->boolean('skip_onboarding')) {
            session(['onboarding_skipped' => true]);
        }

        // Pull (and thereby clean up) any marketing-page type choice.
        $signupType = session()->pull('signup_role_type');

        // Focused onboarding: brand-new organizer-intent users go to the type
        // chooser (or straight to the create form when they already picked a
        // type on the marketing site) instead of an empty dashboard. Attendee
        // signups (follow/ticket/etc.) keep their dashboard.
        if (! is_demo_mode()
            && ! session('onboarding_skipped')
            && in_array($user->signup_intent, [null, 'organizer'], true)
            && ! $user->member()->exists()
            && $user->tickets()->count() === 0) {
            if (in_array($signupType, ['talent', 'venue', 'curator'], true)) {
                return redirect()->route('new', ['type' => $signupType]);
            }

            return redirect()->route('getting-started');
        }

        $events = [];
        $month = DateUtils::normalizeMonth($request->month);
        $year = DateUtils::normalizeYear($request->year);
        $startOfMonth = '';

        $timezone = $user->timezone ?? 'UTC';

        // Calculate month boundaries in user's timezone, then convert to UTC for database query
        $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth();

        // Convert to UTC for database query
        $startOfMonthUtc = $startOfMonth->copy()->setTimezone('UTC');

        $roleIds = $user->editor()->pluck('roles.id');

        // Events will be loaded via Ajax in the calendar partial
        if (request()->graphic) {
            $events = Event::with('roles')
                ->where(function ($query) use ($roleIds, $user) {
                    $query->where(function ($query) use ($roleIds) {
                        $query->whereIn('id', function ($query) use ($roleIds) {
                            $query->select('event_id')
                                ->from('event_role')
                                ->whereIn('role_id', $roleIds)
                                ->where('is_accepted', true);
                        });
                    })->orWhere(function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                })
                ->inMonth($startOfMonthUtc)
                ->orderBy('starts_at')
                ->get();
        } else {
            $events = collect();
        }

        // Dashboard config
        $dashboardConfig = $this->getDashboardConfig($user);
        $visiblePanels = collect($dashboardConfig['panels'])->where('visible', true)->pluck('id')->toArray();
        $panelSettings = collect($dashboardConfig['panels'])->keyBy('id')->toArray();

        // Dashboard data - skip queries for hidden panels
        $upcomingCount = 0;
        $views30d = 0;
        $viewsChange = 0;
        $sparklineData = [];
        $followersCount = 0;
        $totalEventsCount = 0;
        $upcomingEvents = collect();
        $recentActivity = collect();
        $revenueStats = null;
        $topEvents = collect();
        $latestNewsletters = collect();
        $boostCampaigns = collect();
        $trafficSources = collect();

        $analyticsService = app(AnalyticsService::class);
        $now = now()->endOfDay();

        if (in_array('upcoming_count', $visiblePanels)) {
            $upcomingCount = Event::whereIn('id', function ($query) use ($roleIds) {
                $query->select('event_id')
                    ->from('event_role')
                    ->whereIn('role_id', $roleIds)
                    ->where('is_accepted', true);
            })->upcomingOrOngoing()->whereNull('days_of_week')->count();
        }
        if (in_array('views', $visiblePanels)) {
            $viewsPeriod = $panelSettings['views']['period'] ?? 30;
            $viewsStart = now()->subDays($viewsPeriod)->startOfDay();
            $periodStats = $analyticsService->getStatsForUser($user, $viewsStart, $now);
            $momComparison = $analyticsService->getMonthOverMonthComparison($user);
            $views30d = $periodStats['period_views'] ?? 0;
            $viewsChange = $momComparison['percentage_change'] ?? 0;
            $sparklineData = $this->getSparklineData($user, $viewsPeriod);
        }
        if (in_array('followers', $visiblePanels)) {
            $followersCount = DB::table('role_user')
                ->whereIn('role_id', $roleIds)
                ->where('level', 'follower')
                ->count();
            $totalEventsCount = Event::whereIn('id', function ($query) use ($roleIds) {
                $query->select('event_id')
                    ->from('event_role')
                    ->whereIn('role_id', $roleIds)
                    ->where('is_accepted', true);
            })->count();
        }
        if (in_array('upcoming_events', $visiblePanels)) {
            $upcomingEventsCount = $panelSettings['upcoming_events']['count'] ?? 3;
            $upcomingEvents = $this->getUpcomingEvents($roleIds, $upcomingEventsCount);
        }
        if (in_array('recent_activity', $visiblePanels)) {
            $recentActivityCount = $panelSettings['recent_activity']['count'] ?? 5;
            $recentActivity = $this->getRecentActivity($roleIds, $recentActivityCount);
        }
        if (in_array('revenue', $visiblePanels)) {
            $revenuePeriod = $panelSettings['revenue']['period'] ?? 30;
            $revenueStart = now()->subDays($revenuePeriod)->startOfDay();
            $revenueStats = $analyticsService->getConversionStats($user, $revenueStart, $now);
        }
        if (in_array('top_events', $visiblePanels)) {
            $topEventsCount = $panelSettings['top_events']['count'] ?? 3;
            $topEventsPeriod = $panelSettings['top_events']['period'] ?? 30;
            $topEventsStart = now()->subDays($topEventsPeriod)->startOfDay();
            $topEvents = $analyticsService->getTopEvents($user, $topEventsCount, $topEventsStart, $now);
        }
        if (in_array('newsletters', $visiblePanels)) {
            $newslettersCount = $panelSettings['newsletters']['count'] ?? 3;
            $latestNewsletters = Newsletter::whereIn('role_id', $roleIds)
                ->where('status', 'sent')
                ->orderByDesc('sent_at')
                ->limit($newslettersCount)
                ->get();
        }
        if (in_array('boosts', $visiblePanels)) {
            $boostsCount = $panelSettings['boosts']['count'] ?? 3;
            $boostCampaigns = BoostCampaign::whereIn('role_id', $roleIds)
                ->whereIn('status', ['active', 'paused'])
                ->latest()
                ->limit($boostsCount)
                ->get();
        }
        if (in_array('traffic_sources', $visiblePanels)) {
            $trafficCount = $panelSettings['traffic_sources']['count'] ?? 5;
            $trafficPeriod = $panelSettings['traffic_sources']['period'] ?? 30;
            $trafficStart = now()->subDays($trafficPeriod)->startOfDay();
            $trafficSources = $analyticsService->getTopReferrerDomains($user, $trafficCount, $trafficStart, $now);
        }

        $canCreateSchedule = ! config('app.hosted') || $user->owner()->count() < 50;

        $allRoles = app('userRoles');
        $schedules = $allRoles->where('type', 'talent')->whereIn('pivot.level', ['owner', 'admin', 'viewer']);
        $venues = $allRoles->where('type', 'venue')->whereIn('pivot.level', ['owner', 'admin', 'viewer']);
        $curators = $allRoles->where('type', 'curator')->whereIn('pivot.level', ['owner', 'admin', 'viewer']);

        // Default currency for empty-state revenue display (no sales yet): use the first
        // role's country to guess. Falls back to USD if no roles have a country set.
        $defaultCurrency = \App\Utils\MoneyUtils::getCurrencyForCountry(
            $allRoles->firstWhere(fn ($r) => ! empty($r->country_code))->country_code ?? null
        );

        // Pending items the user needs to act on, aggregated across every schedule
        // they can edit. Rendered as a "Needs attention" to-do list at the top of the
        // dashboard, and only shown when something is pending.
        $pendingActionItems = $this->getPendingActionItems($roleIds);

        return view('home', compact(
            'events',
            'month',
            'year',
            'startOfMonth',
            'roleIds',
            'upcomingCount',
            'views30d',
            'viewsChange',
            'sparklineData',
            'followersCount',
            'totalEventsCount',
            'upcomingEvents',
            'recentActivity',
            'dashboardConfig',
            'panelSettings',
            'revenueStats',
            'topEvents',
            'latestNewsletters',
            'boostCampaigns',
            'trafficSources',
            'canCreateSchedule',
            'schedules',
            'venues',
            'curators',
            'defaultCurrency',
            'pendingActionItems',
        ));
    }

    /**
     * Aggregate the pending items a user needs to act on across every schedule they
     * can edit (owner/admin), as a flat, sorted collection of to-do rows. Each row is
     * an array: type, count, title, subtitle, url, color. Mirrors the count logic used
     * by the NotifyRequestChanges / NotifyFanContentChanges / NotifyPollOptionChanges
     * commands. Returns an empty collection when there is nothing to handle.
     */
    private function getPendingActionItems($roleIds)
    {
        $items = collect();

        if ($roleIds->isEmpty()) {
            return $items;
        }

        $rolesById = app('userRoles')->keyBy('id');

        // 1) Pending event requests (per schedule) - event_role.is_accepted IS NULL.
        // count(distinct event_id) mirrors the Requests tab's whereHas (distinct-event)
        // semantics exactly, regardless of how many pivot rows an event has per role.
        $requestCounts = DB::table('event_role')
            ->whereIn('role_id', $roleIds)
            ->whereNull('is_accepted')
            ->select('role_id', DB::raw('count(distinct event_id) as cnt'))
            ->groupBy('role_id')
            ->pluck('cnt', 'role_id');

        foreach ($requestCounts as $roleId => $cnt) {
            $role = $rolesById->get($roleId);
            if (! $role) {
                continue;
            }
            $items->push([
                'type' => 'requests',
                'count' => (int) $cnt,
                'title' => trans_choice('messages.pending_action_requests', $cnt, ['count' => $cnt]),
                'subtitle' => $role->name,
                'url' => route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'requests']),
                'color' => 'blue',
            ]);
        }

        // Per-event queries below are scoped to events on the user's editable schedules
        // via a subquery (avoids pulling every event id into PHP on each dashboard load).
        $eventScope = fn ($query) => $query->select('event_id')
            ->from('event_role')
            ->whereIn('role_id', $roleIds);

        // 2) Pending fan submissions (videos + comments + photos) combined per event.
        $fanContentByEvent = collect();
        foreach ([
            EventVideo::class,
            EventComment::class,
            EventPhoto::class,
        ] as $model) {
            $counts = $model::whereIn('event_id', $eventScope)
                ->where('is_approved', false)
                ->select('event_id', DB::raw('count(*) as cnt'))
                ->groupBy('event_id')
                ->pluck('cnt', 'event_id');
            foreach ($counts as $eventId => $cnt) {
                $fanContentByEvent[$eventId] = ($fanContentByEvent[$eventId] ?? 0) + (int) $cnt;
            }
        }

        // 3) Pending poll suggestions per event - sum of pending_options across polls.
        $pollByEvent = EventPoll::whereIn('event_id', $eventScope)
            ->whereNotNull('pending_options')
            ->get()
            ->groupBy('event_id')
            ->map(fn ($polls) => $polls->sum(fn ($poll) => count($poll->pending_options ?? [])))
            ->filter(fn ($cnt) => $cnt > 0);

        // 4) Carpool reports the admin must review per event - all reports on the event's
        // active offers, scoped to editable events (matches the edit page's report list
        // and the event-based dismiss authorization, EventPolicy::update). Carpool
        // *requests* are intentionally excluded: they are approved by the ride's driver
        // (CarpoolController::approveRequest aborts unless the offer creator), not the
        // schedule admin. Dismissing a report deletes it, so every row here is pending.
        $carpoolByEvent = DB::table('carpool_reports')
            ->join('carpool_offers', 'carpool_reports.carpool_offer_id', '=', 'carpool_offers.id')
            ->whereIn('carpool_offers.event_id', $eventScope)
            ->where('carpool_offers.status', 'active')
            ->select('carpool_offers.event_id as event_id', DB::raw('count(*) as cnt'))
            ->groupBy('carpool_offers.event_id')
            ->pluck('cnt', 'event_id');

        // Resolve names + a deep-link subdomain once for every event referenced above.
        $referencedEventIds = collect()
            ->merge($fanContentByEvent->keys())
            ->merge($pollByEvent->keys())
            ->merge($carpoolByEvent->keys())
            ->unique()
            ->values();

        if ($referencedEventIds->isEmpty()) {
            return $this->sortPendingActionItems($items);
        }

        $events = Event::whereIn('id', $referencedEventIds)
            ->with(['roles' => fn ($query) => $query->whereIn('roles.id', $roleIds)])
            ->get()
            ->keyBy('id');

        $eventRow = function ($eventId, $cnt, $type, $transKey, $color, $engagement) use ($events) {
            $event = $events->get($eventId);
            $role = $event ? $event->roles->first() : null;
            if (! $event || ! $role) {
                return null;
            }

            return [
                'type' => $type,
                'count' => (int) $cnt,
                'title' => trans_choice("messages.{$transKey}", $cnt, ['count' => $cnt]),
                'subtitle' => $event->translatedName().' · '.$role->name,
                'url' => route('event.edit', [
                    'subdomain' => $role->subdomain,
                    'hash' => UrlUtils::encodeId($event->id),
                ]).'?engagement='.$engagement.'#section-engagement',
                'color' => $color,
            ];
        };

        foreach ($fanContentByEvent as $eventId => $cnt) {
            if ($row = $eventRow($eventId, $cnt, 'fan_content', 'pending_action_fan_content', 'purple', 'fan_content')) {
                $items->push($row);
            }
        }
        foreach ($pollByEvent as $eventId => $cnt) {
            if ($row = $eventRow($eventId, $cnt, 'polls', 'pending_action_poll_options', 'green', 'polls')) {
                $items->push($row);
            }
        }
        // Carpool rows link to a carpool-enabled editable role (the Carpool tab is gated
        // by $role->carpool_enabled); skip events where the user has no such role.
        foreach ($carpoolByEvent as $eventId => $cnt) {
            $event = $events->get($eventId);
            $carpoolRole = $event ? $event->roles->firstWhere('carpool_enabled', true) : null;
            if (! $carpoolRole) {
                continue;
            }
            $items->push([
                'type' => 'carpool',
                'count' => (int) $cnt,
                'title' => trans_choice('messages.pending_action_carpool_reports', $cnt, ['count' => $cnt]),
                'subtitle' => $event->translatedName().' · '.$carpoolRole->name,
                'url' => route('event.edit', [
                    'subdomain' => $carpoolRole->subdomain,
                    'hash' => UrlUtils::encodeId($event->id),
                ]).'?engagement=carpool#section-engagement',
                'color' => 'amber',
            ]);
        }

        return $this->sortPendingActionItems($items);
    }

    /**
     * Sort pending action items by type priority (requests, fan content, polls,
     * carpool), then by count descending within each type.
     */
    private function sortPendingActionItems($items)
    {
        $priority = ['requests' => 0, 'fan_content' => 1, 'polls' => 2, 'carpool' => 3];

        return $items
            ->sortBy(fn ($item) => sprintf('%d-%010d', $priority[$item['type']] ?? 9, 1_000_000_000 - $item['count']))
            ->values();
    }

    public function calendarEvents(Request $request): JsonResponse
    {
        $month = DateUtils::normalizeMonth($request->month);
        $year = DateUtils::normalizeYear($request->year);

        $user = $request->user();
        $timezone = $user->timezone ?? 'UTC';

        $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth();

        $startOfMonthUtc = $startOfMonth->copy()->setTimezone('UTC');

        $roleIds = $user->editor()->pluck('roles.id');

        $events = Event::with('roles', 'parts', 'tickets')
            ->where(function ($query) use ($roleIds, $user) {
                $query->where(function ($query) use ($roleIds) {
                    $query->whereIn('id', function ($query) use ($roleIds) {
                        $query->select('event_id')
                            ->from('event_role')
                            ->whereIn('role_id', $roleIds)
                            ->where('is_accepted', true);
                    });
                })->orWhere(function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            })
            ->inMonth($startOfMonthUtc)
            ->orderBy('starts_at')
            ->get();

        return $this->buildCalendarResponse($events, collect(), false, null, null, (int) $month, (int) $year, $timezone, 0);
    }

    public function sitemap()
    {
        try {
            $cacheKey = 'sitemap:'.md5(request()->fullUrl());

            $content = Cache::remember($cacheKey, 3600, function () {
                $roles = Role::select([
                    'id', 'subdomain', 'email', 'email_verified_at', 'phone', 'phone_verified_at',
                    'user_id', 'custom_domain', 'custom_domain_mode', 'custom_domain_status',
                    'is_deleted', 'updated_at',
                ])
                    ->with(['groups:id,role_id,slug,updated_at'])
                    ->where(function ($query) {
                        $query->where(function ($q) {
                            $q->whereNotNull('email')
                                ->whereNotNull('email_verified_at');
                        })->orWhere(function ($q) {
                            $q->whereNotNull('phone')
                                ->whereNotNull('phone_verified_at');
                        });
                    })
                    ->where('is_deleted', false)
                    ->orderBy(request()->has('roles') ? 'id' : 'subdomain', request()->has('roles') ? 'desc' : 'asc')
                    ->get();

                $events = Event::select([
                    'id', 'slug', 'starts_at', 'days_of_week', 'creator_role_id',
                    'is_private', 'is_draft', 'is_cancelled', 'event_password', 'updated_at',
                ])
                    ->with([
                        'roles:id,subdomain,type,email_verified_at,phone_verified_at,user_id,custom_domain,custom_domain_mode,custom_domain_status',
                        'creatorRole:id,subdomain,type,email_verified_at,phone_verified_at,user_id',
                    ])
                    ->whereNotNull('starts_at')
                    ->where('is_private', false)
                    ->where('is_draft', false)
                    ->where('is_cancelled', false)
                    ->whereNull('event_password')
                    ->whereHas('roles', fn ($q) => $q->where('is_accepted', true))
                    ->orderBy(request()->has('events') ? 'id' : 'starts_at', 'desc')
                    ->get();

                $blogPosts = BlogPost::select(['id', 'slug', 'published_at', 'updated_at', 'is_published'])
                    ->published()
                    ->orderBy('published_at', 'desc')
                    ->get();

                $hasQueryFilter = request()->has('events') || request()->has('roles');

                return view('sitemap', [
                    'roles' => ! request()->has('events') ? $roles : [],
                    'events' => ! request()->has('roles') ? $events : [],
                    'blogPosts' => $hasQueryFilter ? [] : $blogPosts,
                    'showMarketingLinks' => ! $hasQueryFilter,
                    'lastmod' => now()->toIso8601String(),
                ])->render();
            });

            $isGzipped = str_ends_with(request()->path(), '.gz');

            if ($isGzipped) {
                $gzipped = gzencode($content, 1);

                return response($gzipped)
                    ->header('Content-Type', 'application/xml')
                    ->header('Content-Encoding', 'gzip');
            }

            return response($content)
                ->header('Content-Type', 'application/xml');
        } catch (\Throwable $e) {
            report($e);

            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            $xml .= '<url><loc>'.url('/').'</loc></url>';
            $xml .= '</urlset>';

            return response($xml)
                ->header('Content-Type', 'application/xml');
        }
    }

    public function saveDashboardConfig(Request $request): JsonResponse
    {
        $request->validate([
            'panels' => 'required|array|max:10',
            'panels.*.id' => 'required|string|in:upcoming_count,views,followers,upcoming_events,recent_activity,revenue,top_events,newsletters,boosts,traffic_sources',
            'panels.*.visible' => 'required|boolean',
            'panels.*.size' => 'sometimes|integer|in:1,2',
            'panels.*.period' => 'sometimes|integer|in:7,14,30',
            'panels.*.count' => 'sometimes|integer|in:3,5,10',
        ]);

        $panels = collect($request->input('panels'))->map(function ($panel) {
            $item = [
                'id' => $panel['id'],
                'visible' => (bool) $panel['visible'],
            ];
            if (isset($panel['size'])) {
                $item['size'] = (int) $panel['size'];
            }
            if (isset($panel['period'])) {
                $item['period'] = (int) $panel['period'];
            }
            if (isset($panel['count'])) {
                $item['count'] = (int) $panel['count'];
            }

            return $item;
        })->values()->toArray();

        $user = $request->user();
        $user->dashboard_config = ['panels' => $panels];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => __('messages.dashboard_config_saved'),
        ]);
    }

    private function getDashboardConfig($user): array
    {
        $defaults = [
            ['id' => 'upcoming_count', 'visible' => true, 'size' => 1],
            ['id' => 'views', 'visible' => true, 'size' => 1, 'period' => 30],
            ['id' => 'followers', 'visible' => true, 'size' => 1],
            ['id' => 'revenue', 'visible' => true, 'size' => 1, 'period' => 30],
            ['id' => 'upcoming_events', 'visible' => true, 'size' => 2, 'count' => 3],
            ['id' => 'recent_activity', 'visible' => true, 'size' => 2, 'count' => 5],
            ['id' => 'top_events', 'visible' => false, 'size' => 2, 'count' => 3, 'period' => 30],
            ['id' => 'newsletters', 'visible' => false, 'size' => 2, 'count' => 3],
            ['id' => 'boosts', 'visible' => false, 'size' => 2, 'count' => 3],
            ['id' => 'traffic_sources', 'visible' => false, 'size' => 2, 'count' => 5, 'period' => 30],
        ];

        $defaultsMap = collect($defaults)->keyBy('id')->toArray();

        $config = $user->dashboard_config;

        if (! $config || ! isset($config['panels'])) {
            return ['panels' => $defaults, 'defaultPanels' => $defaults];
        }

        $validIds = array_keys($defaultsMap);
        $configuredIds = [];

        // Keep only valid panels from config, merging missing keys from defaults
        $panels = [];
        foreach ($config['panels'] as $panel) {
            if (! isset($panel['id']) || ! in_array($panel['id'], $validIds)) {
                continue;
            }
            if (in_array($panel['id'], $configuredIds)) {
                continue;
            }
            $configuredIds[] = $panel['id'];
            $merged = array_merge($defaultsMap[$panel['id']], [
                'id' => $panel['id'],
                'visible' => (bool) ($panel['visible'] ?? true),
            ]);
            if (isset($panel['size'])) {
                $merged['size'] = (int) $panel['size'];
            }
            if (isset($panel['period']) && isset($defaultsMap[$panel['id']]['period'])) {
                $merged['period'] = (int) $panel['period'];
            }
            if (isset($panel['count']) && isset($defaultsMap[$panel['id']]['count'])) {
                $merged['count'] = (int) $panel['count'];
            }
            $panels[] = $merged;
        }

        // Add any missing panels at the end (future-proofing)
        foreach ($defaults as $default) {
            if (! in_array($default['id'], $configuredIds)) {
                $panels[] = $default;
            }
        }

        return ['panels' => $panels, 'defaultPanels' => $defaults];
    }

    private function getUpcomingEvents($roleIds, int $count = 3)
    {
        return Event::whereIn('id', function ($query) use ($roleIds) {
            $query->select('event_id')
                ->from('event_role')
                ->whereIn('role_id', $roleIds)
                ->where('is_accepted', true);
        })
            ->upcomingOrOngoing()
            ->whereNull('days_of_week')
            ->orderBy('starts_at')
            ->limit($count)
            ->with(['roles', 'tickets'])
            ->get();
    }

    private function getRecentActivity($roleIds, int $count = 5)
    {
        $eventIds = DB::table('event_role')
            ->whereIn('role_id', $roleIds)
            ->where('is_accepted', true)
            ->pluck('event_id')
            ->unique();

        $activities = collect();

        // Recent sales
        if ($eventIds->isNotEmpty()) {
            $sales = Sale::whereIn('event_id', $eventIds)
                ->where('status', 'paid')
                ->latest()
                ->limit(10)
                ->with('event')
                ->get()
                ->map(function ($sale) {
                    return [
                        'type' => 'sale',
                        'description' => $sale->event ? $sale->event->name : '',
                        'date' => $sale->created_at,
                        'amount' => $sale->payment_amount,
                    ];
                });
            $activities = $activities->merge($sales);
        }

        // Recent followers
        $followers = DB::table('role_user')
            ->whereIn('role_id', $roleIds)
            ->where('level', 'follower')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $followerUserIds = $followers->pluck('user_id')->unique();
        $followerUsers = DB::table('users')->whereIn('id', $followerUserIds)->get()->keyBy('id');

        $followers = $followers->map(function ($follow) use ($followerUsers) {
            $user = $followerUsers[$follow->user_id] ?? null;

            return [
                'type' => 'follower',
                'description' => $user ? trim(($user->first_name ?? '').' '.($user->last_name ?? '')) : '',
                'email' => $user->email ?? '',
                'date' => Carbon::parse($follow->created_at),
            ];
        });
        $activities = $activities->merge($followers);

        // Recent newsletters sent
        $newsletters = Newsletter::whereIn('role_id', $roleIds)
            ->where('status', 'sent')
            ->whereNotNull('sent_at')
            ->orderByDesc('sent_at')
            ->limit(5)
            ->get()
            ->map(function ($newsletter) {
                return [
                    'type' => 'newsletter',
                    'description' => $newsletter->subject,
                    'date' => $newsletter->sent_at,
                    'sent_count' => $newsletter->sent_count,
                ];
            });
        $activities = $activities->merge($newsletters);

        // Sort by date descending
        return $activities->filter(fn ($a) => $a['date'] !== null)->sortByDesc('date')->take($count)->values();
    }

    private function getSparklineData($user, int $days = 30): array
    {
        $analyticsService = app(AnalyticsService::class);
        $start = now()->subDays($days)->startOfDay();
        $now = now()->endOfDay();

        $viewsByPeriod = $analyticsService->getViewsByPeriod($user, 'daily', $start, $now);

        // Fill in missing days with 0
        $data = [];
        $current = $start->copy();
        $viewsMap = $viewsByPeriod->pluck('view_count', 'period')->toArray();

        while ($current->lte($now)) {
            $key = $current->format('Y-m-d');
            $data[] = (int) ($viewsMap[$key] ?? 0);
            $current->addDay();
        }

        return $data;
    }

    private function processPendingFanContent(array $pending): ?string
    {
        $eventId = UrlUtils::decodeId($pending['event_hash'] ?? '');
        if (! $eventId) {
            return null;
        }

        $event = Event::with(['parts', 'roles'])->find($eventId);
        if (! $event) {
            return null;
        }

        $role = Role::where('subdomain', $pending['subdomain'] ?? '')->first();

        $eventPartId = $pending['event_part_id'] ?? null;
        if ($eventPartId) {
            $eventPartId = UrlUtils::decodeId($eventPartId);
            $part = $event->parts->firstWhere('id', $eventPartId);
            if (! $part) {
                $eventPartId = null;
            }
        }

        $eventDate = $event->days_of_week ? ($pending['event_date'] ?? null) : null;
        $returnUrl = $pending['return_url'] ?? null;
        if ($returnUrl) {
            $parsedUrl = parse_url($returnUrl);
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);
            if (isset($parsedUrl['host']) && $parsedUrl['host'] !== $appHost && ! str_ends_with($parsedUrl['host'], '.'.$appHost)) {
                // Allow return URLs on valid custom domains
                $isCustomDomain = Role::where('custom_domain_host', $parsedUrl['host'])
                    ->where('custom_domain_mode', 'direct')
                    ->where('custom_domain_status', 'active')
                    ->exists();
                if (! $isCustomDomain) {
                    $returnUrl = null;
                }
            }
            $lowerUrl = strtolower(trim($returnUrl ?? ''));
            if (str_starts_with($lowerUrl, 'javascript:') || str_starts_with($lowerUrl, 'data:')) {
                $returnUrl = null;
            }
        }

        if ($pending['type'] === 'video') {
            $youtubeUrl = $pending['youtube_url'] ?? '';
            $embedUrl = UrlUtils::getYouTubeEmbed($youtubeUrl);
            if (! $embedUrl) {
                return $returnUrl;
            }

            // Store only the canonical watch URL so no guest-controlled query string is persisted
            $youtubeUrl = UrlUtils::getCanonicalYouTubeUrl($youtubeUrl);

            // Check for duplicate
            $submittedVideoId = basename(parse_url($embedUrl, PHP_URL_PATH));
            $query = EventVideo::where('event_id', $event->id);
            if ($eventPartId) {
                $query->where('event_part_id', $eventPartId);
            } else {
                $query->whereNull('event_part_id');
            }
            if ($eventDate) {
                $query->where('event_date', $eventDate);
            }
            $exists = $query->get()->contains(function ($video) use ($submittedVideoId) {
                $existingEmbed = UrlUtils::getYouTubeEmbed($video->youtube_url);

                return $existingEmbed && basename(parse_url($existingEmbed, PHP_URL_PATH)) === $submittedVideoId;
            });

            if (! $exists) {
                $video = EventVideo::create([
                    'event_id' => $event->id,
                    'event_part_id' => $eventPartId ?: null,
                    'event_date' => $eventDate,
                    'user_id' => auth()->id(),
                    'youtube_url' => $youtubeUrl,
                    'is_approved' => false,
                ]);
                $returnUrl = $event->getGuestUrl($pending['subdomain']);
                session()->flash('scroll_to', 'pending-video-'.$video->id);

                if ($role && ! auth()->user()->isConnected($role->subdomain)) {
                    auth()->user()->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
                }
            }

            session()->flash('message', __('messages.video_submitted'));
        } elseif ($pending['type'] === 'comment') {
            $commentText = $pending['comment'] ?? '';
            if (! $commentText) {
                return $returnUrl;
            }

            $comment = EventComment::create([
                'event_id' => $event->id,
                'event_part_id' => $eventPartId ?: null,
                'event_date' => $eventDate,
                'user_id' => auth()->id(),
                'comment' => $commentText,
                'is_approved' => false,
            ]);
            $returnUrl = $event->getGuestUrl($pending['subdomain']);
            session()->flash('scroll_to', 'pending-comment-'.$comment->id);

            if ($role && ! auth()->user()->isConnected($role->subdomain)) {
                auth()->user()->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
            }

            session()->flash('message', __('messages.comment_submitted'));
        } elseif ($pending['type'] === 'photo') {
            if ($role && ! $role->canUploadPhoto()) {
                $tempFilename = $pending['temp_filename'] ?? '';
                if ($tempFilename) {
                    \Illuminate\Support\Facades\Storage::delete('temp/'.$tempFilename);
                }
                session()->flash('error', __('messages.photo_limit_reached'));

                return $returnUrl;
            }

            $tempFilename = $pending['temp_filename'] ?? '';
            $extension = $pending['extension'] ?? '';
            if (! $tempFilename || ! $extension) {
                if ($tempFilename) {
                    \Illuminate\Support\Facades\Storage::delete('temp/'.$tempFilename);
                }

                return $returnUrl;
            }

            if (! \Illuminate\Support\Facades\Storage::exists('temp/'.$tempFilename)) {
                return $returnUrl;
            }

            $filename = 'photo_'.\Illuminate\Support\Str::random(32).'.'.$extension;
            if (config('filesystems.default') == 'local') {
                \Illuminate\Support\Facades\Storage::move('temp/'.$tempFilename, 'public/'.$filename);
            } else {
                \Illuminate\Support\Facades\Storage::move('temp/'.$tempFilename, $filename);
            }

            $photo = EventPhoto::create([
                'event_id' => $event->id,
                'event_part_id' => $eventPartId ?: null,
                'event_date' => $eventDate,
                'user_id' => auth()->id(),
                'photo_url' => $filename,
                'is_approved' => false,
            ]);

            if ($role && ! auth()->user()->isConnected($role->subdomain)) {
                auth()->user()->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
            }

            if (($pending['return_to'] ?? null) === 'gallery') {
                $returnUrl = $event->getPhotoGalleryUrl($pending['subdomain']);
            } else {
                $returnUrl = $event->getGuestUrl($pending['subdomain']);
                session()->flash('scroll_to', 'pending-photo-'.$photo->id);
            }

            session()->flash('message', __('messages.photo_submitted'));
        }

        return $returnUrl;
    }
}
