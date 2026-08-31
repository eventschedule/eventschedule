<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BoostCampaign;
use App\Models\DismissedNextStep;
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
use App\Utils\LegacyRedirects;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HomeController extends Controller
{
    use Traits\CalendarDataTrait;

    /**
     * The catch-all at the bottom of routes/web.php, for single-segment paths nothing else claims.
     *
     * It used to end in redirect(route('home')) for every miss. /dashboard needs auth, so a
     * signed-out visitor - or Googlebot - was forwarded to /login, which robots.txt disallows.
     * Nothing ever returned 404, and errors/404.blade.php was unreachable for one-segment paths.
     * Two whole classes of still-ranking URL were dead-ending there: all 187 posts in
     * sitemap-blog-1.xml (the blog moved to blog.{domain} and the apex URLs were never
     * redirected) and the old WordPress marketing pages in LegacyRedirects.
     *
     * A bare hit with no slug is NOT a miss - app.{domain}/ relies on it to reach the dashboard -
     * so only a slug that matches nothing 404s.
     */
    public function landing($slug = null)
    {
        if ($slug && $role = Role::whereSubdomain($slug)->first()) {
            return redirect()->route('role.view_guest', ['subdomain' => $role->subdomain]);
        }

        if ($slug) {
            // Matched against the same published() scope the sitemap uses, so the set that
            // redirects is exactly the set that is advertised.
            if (BlogPost::published()->where('slug', $slug)->exists()) {
                return redirect(blog_url('/'.$slug), 301);
            }

            if (trim($slug, '/') === 'blog') {
                return redirect(blog_url(), 301);
            }

            if ($target = LegacyRedirects::targetFor($slug)) {
                return redirect(marketing_url($target), 301);
            }

            abort(404);
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

        // A recipient sent to sign in from the public handover page comes back here first.
        // Same shape as pending_follow below, and it has to run BEFORE the new-user bounce
        // to /getting-started further down, or someone whose only tie to the app is the
        // schedule they were offered never reaches the offer.
        if ($pendingTransfer = session()->pull('pending_transfer')) {
            return redirect()->route('role.transfer.show', ['token' => $pendingTransfer]);
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
        //
        // roles() (any pivot level), not member(): a user who only follows a schedule
        // has a real dashboard and must not be bounced to the "create your first
        // schedule" chooser. Matches post_signup_redirect_url(), and stays loop-safe
        // because member() is a subset of roles(), so gettingStarted()'s member() guard
        // never bounces a user this forwards.
        // The incoming-handover check is last because it is the only extra query, and the
        // cheaper conditions in front of it already exclude almost everybody. Someone who
        // has been offered a schedule must not be pushed into "create your first
        // schedule": they are about to receive one, and the dashboard is where the offer
        // is waiting for them.
        if (! is_demo_mode()
            && ! session('onboarding_skipped')
            && in_array($user->signup_intent, [null, 'organizer'], true)
            && ! $user->roles()->exists()
            && $user->tickets()->count() === 0
            && $this->incomingTransfers($user)->isEmpty()) {
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
        // Upper-bound the query to the visible grid window so a user with many future events
        // doesn't hydrate their entire event table (matches buildCalendarResponse's default grid).
        $endOfGridUtc = $startOfMonth->copy()->endOfMonth()->endOfWeek(6)->addDays(2)->setTimezone('UTC');

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
                ->inMonth($startOfMonthUtc, $endOfGridUtc)
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

        // Suggestions, kept OUT of the list above. getPendingActionItems() is deliberately
        // reactive - "a to-do list is for things that need doing" - and mixing growth
        // suggestions into it would make a real queue impossible to trust. Same component,
        // different heading, the way AdminAlertService reuses it.
        $nextStepItems = $this->getNextStepItems($roleIds);

        // Nudge admins to turn federation on once there is something worth sharing.
        $showFederationPrompt = app(\App\Services\FederationService::class)
            ->shouldPromptAdoption($user);

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
            'pendingActionItems', 'nextStepItems', 'showFederationPrompt',
        ));
    }

    /**
     * Ownership handovers waiting on this user, live rows only.
     *
     * Shared by the onboarding bounce in home() and the "Needs attention" row, so the two
     * can never disagree about whether there is an offer to answer.
     */
    private function incomingTransfers(\App\Models\User $user)
    {
        return \App\Models\RoleTransfer::open()
            ->where('to_email', strtolower($user->email))
            ->with('role')
            ->get()
            ->filter(fn ($transfer) => $transfer->role && ! $transfer->role->is_deleted);
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

        // Incoming ownership handovers, ABOVE the early return: a recipient whose only
        // reason to be here is the schedule they were offered has no editable schedules
        // yet. Only the incoming side is listed - a pending OUTGOING offer drains when
        // someone else acts, which AdminAlertService calls a metric, not a queue.
        foreach ($this->incomingTransfers(auth()->user()) as $transfer) {
            $items->push([
                'type' => 'schedule_transfer',
                'count' => 1,
                'title' => __('messages.pending_action_schedule_transfer'),
                'subtitle' => $transfer->role->name,
                'url' => route('role.transfer.show', ['token' => $transfer->token]),
                'color' => 'amber',
            ]);
        }

        if ($roleIds->isEmpty()) {
            return $items;
        }

        $rolesById = app('userRoles')->keyBy('id');

        // Overdue installment payments.
        //
        // Scoped to match the Installments tab it links to, so the badge never opens an empty
        // page. That tab now spans schedules the user administers as well as the ones they own,
        // so this follows it - it used to be events.user_id on both sides.
        //
        // Counts only genuinely overdue plans, never the whole live book: the panel's header
        // badge is a plain sum, so a count that never drains reads as a permanent to-do. That is
        // exactly why schedules_unverified was removed from the admin panel.
        $overduePlans = \App\Models\SaleInstallmentPlan::query()
            ->where('status', 'delinquent')
            ->whereHas('sale', function ($q) {
                $q->where('is_deleted', false)
                    ->whereHas('event', fn ($eq) => $eq->managedBy(auth()->user())->where('is_cancelled', false));
            })
            ->count();

        if ($overduePlans > 0) {
            $items->push([
                'type' => 'installments_overdue',
                'count' => $overduePlans,
                'title' => trans_choice('messages.pending_action_installments_overdue', $overduePlans, ['count' => $overduePlans]),
                'subtitle' => __('messages.installments'),
                'url' => route('sales', ['tab' => 'installments']),
                'color' => 'amber',
            ]);
        }

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

        // 1b) Free-plan ticket allowance running low or spent.
        //
        // The guest side is deliberately silent when the allowance runs out (a missing buy button
        // reads the same as sales not being open yet, which is the least-shaming outcome), so the
        // organizer has to get the loud signal here instead.
        //
        // Guarded on OWNERSHIP, not editor access: SubscriptionController::show redirects a
        // non-owner, so showing an editor a to-do they cannot act on repeats a mistake this
        // dashboard already avoids elsewhere.
        if (config('app.hosted')) {
            foreach ($rolesById as $role) {
                if ($role->user_id !== auth()->id()) {
                    continue;
                }

                $limit = $role->ticketSaleLimit();

                // Null short-circuits before any counting, so paid schedules cost nothing here.
                if (is_null($limit) || $limit < 1) {
                    continue;
                }

                $used = $role->ticketsSoldThisMonth();

                // Nothing below 80%: the meters on the Plan and Sales pages already cover that,
                // and a to-do list is for things that need doing.
                if ($used / $limit < 0.8) {
                    continue;
                }

                $remaining = max(0, $limit - $used);

                $items->push([
                    'type' => 'ticket_quota',
                    'count' => $remaining,
                    'title' => $remaining > 0
                        ? trans_choice('messages.pending_action_ticket_quota_low', $remaining, ['count' => $remaining])
                        : __('messages.pending_action_ticket_quota_spent'),
                    'subtitle' => $role->name,
                    'url' => route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']),
                    'color' => 'amber',
                ]);
            }
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
     * What a schedule owner could do next, as the same row shape the to-do list uses.
     *
     * Kept apart from getPendingActionItems() on purpose. That list is reactive and its own
     * comment says a to-do list is for things that need doing; a suggestion sitting in it
     * would make a real queue impossible to trust. These render under their own heading.
     *
     * This is the in-app half of the activation nudges. The email half is bounded at both
     * ends so a first run cannot mailshot the whole base, which leaves every schedule that
     * stalled BEFORE those windows reachable only here - and that is most of them: 12 of the
     * 401 schedules created up to 2026-02 have had any event in the last 90 days.
     *
     * Ordered by what it is worth if acted on: a page with a date and no way to buy comes
     * before an empty page, because selling is what the 2026-08-30 export shows separates a
     * paying customer from a dormant one.
     *
     * Each row can be turned down, and a dismissal is permanent for that (user, schedule, step).
     * Deliberately not a flag on the user: this is the surface that reaches everyone the email
     * windows exclude, so someone who does not want to sell tickets on one venue must still be
     * told when a schedule they create next month has no dates on it.
     */
    private function getNextStepItems($roleIds)
    {
        $items = collect();

        if ($roleIds->isEmpty()) {
            return $items;
        }

        // $roleIds is already $user->editor(), i.e. owner and admin only, the same input
        // getPendingActionItems() trusts. A viewer cannot act on any of these steps and never
        // reaches here.
        $roles = Role::whereIn('id', $roleIds)->where('is_deleted', false)->get();

        if ($roles->isEmpty()) {
            return $items;
        }

        $ids = $roles->pluck('id');

        // Events a schedule is actually RESPONSIBLE for, mirroring Event::scopeManagedThrough()
        // and SendActivationNudges::ownedEvents(). Without it a schedule is offered steps for
        // events it does not own: a decline leaves the pivot in place at is_accepted = false, and
        // a curator that merely lists an event cannot even open the editor's Tickets panel, which
        // follows canViewEventData(). The accepted branch is deliberately not narrowed to
        // creator_role_id - a venue that accepted a talent's event CAN price it.
        $owned = fn ($query) => $query
            ->join('roles', 'roles.id', '=', 'event_role.role_id')
            ->where(fn ($q) => $q->whereColumn('event_role.role_id', 'events.creator_role_id')
                ->orWhere(fn ($w) => $w->where('event_role.is_accepted', true)
                    ->where('roles.type', '!=', 'curator')));

        // One query each rather than per schedule: the dashboard renders on every page load.
        $publicUpcoming = $owned(DB::table('event_role')
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->whereIn('event_role.role_id', $ids)
            ->where('events.is_draft', false)
            ->where('events.is_private', false)
            ->where('events.is_internal', false)
            ->where('events.starts_at', '>=', now()))
            ->distinct()->pluck('event_role.role_id')->flip();

        $anyEvent = $owned(DB::table('event_role')
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->whereIn('event_role.role_id', $ids))
            ->distinct()->pluck('event_role.role_id')->flip();

        // is_addon excluded to match Event::tickets(), which the email half goes through: an
        // add-on is not a thing anyone buys on its own, so it is not a ticket type.
        $ticketTypes = fn (bool $paidOnly) => $owned(DB::table('tickets')
            ->join('event_role', 'event_role.event_id', '=', 'tickets.event_id')
            ->join('events', 'events.id', '=', 'tickets.event_id')
            ->whereIn('event_role.role_id', $ids)
            ->where('tickets.is_deleted', false)
            ->where('tickets.is_addon', false)
            ->when($paidOnly, fn ($q) => $q->where('tickets.price', '>', 0)))
            ->distinct()->pluck('event_role.role_id')->flip();

        $withTicketType = $ticketTypes(false);
        $withPaidTicketType = $ticketTypes(true);

        $user = auth()->user();
        // The canonical check, and what the event form keys the same nudge off. Testing the
        // credential columns by hand missed users.payment_url and read stripe_account_id, which
        // is written when Connect onboarding STARTS rather than when it completes.
        $hasGateway = ! empty(payment_gateways()->connectedFor($user));

        // One query, like the four above: the dashboard renders on every page load. Keyed per
        // (schedule, step) rather than per user, so a schedule created after a dismissal still
        // gets its own suggestion, and a flat set so the branch checks below are plain isset().
        //
        // The or-clause is the exception: a payments dismissal is account-wide (see
        // DismissedNextStep::ACCOUNT_WIDE_STEP_TYPES), so it has to be found even when it was
        // taken on a schedule outside $ids - one since deleted, or simply a different one.
        $dismissedRows = DB::table('dismissed_next_steps')
            ->where('user_id', $user->id)
            ->where(fn ($q) => $q->whereIn('role_id', $ids)
                ->orWhereIn('step_type', DismissedNextStep::ACCOUNT_WIDE_STEP_TYPES))
            ->get(['role_id', 'step_type']);

        $dismissed = $dismissedRows->map(fn ($row) => $row->role_id.':'.$row->step_type)->flip();
        $paymentsDismissed = $dismissedRows->contains(fn ($row) => $row->step_type === 'next_step_payments');

        foreach ($roles as $role) {
            // 1) Something upcoming and no way to buy: the step that matters most.
            if (isset($publicUpcoming[$role->id]) && ! isset($withTicketType[$role->id])) {
                // A dismissal suppresses the row and the schedule keeps its slot: the continue
                // below still runs. Falling through would replace a dismissed suggestion with
                // the next-best one on the same schedule, which reads as the button not working.
                if (! isset($dismissed[$role->id.':next_step_tickets'])) {
                    $items->push([
                        'type' => 'next_step_tickets',
                        'count' => 1,
                        'title' => __('messages.next_step_add_ticket_type'),
                        'subtitle' => $role->name,
                        'url' => route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']),
                        'color' => 'blue',
                        // Both the dismiss form's payload and the opt-in signal for the row
                        // partial. getPendingActionItems() and AdminAlertService never set it,
                        // so their rows render no control.
                        'dismiss_schedule' => UrlUtils::encodeId($role->id),
                    ]);
                }

                continue;
            }

            // 2) Paid tickets set up with nothing to take the money with.
            if (isset($withPaidTicketType[$role->id]) && ! $hasGateway) {
                // Account-wide, not per schedule: $hasGateway is one gateway for the whole
                // account, so turning this down on any schedule answers it for all of them.
                // Otherwise an owner with five schedules selling tickets says no five times.
                if (! $paymentsDismissed) {
                    $items->push([
                        'type' => 'next_step_payments',
                        'count' => 1,
                        'title' => __('messages.next_step_connect_payments'),
                        'subtitle' => $role->name,
                        'url' => route('profile.edit').'#section-payment-methods',
                        'color' => 'blue',
                        'dismiss_schedule' => UrlUtils::encodeId($role->id),
                    ]);
                }

                continue;
            }

            // 3) An empty page, or one whose dates have all passed. Two step types rather than
            // one, keyed off the same condition that picks the copy: "never published" and "went
            // quiet" are different situations at opposite ends of a schedule's life, and a
            // dismissal is permanent, so folding them together lets a day-one "not ready yet"
            // silence the dormancy nudge on a schedule that later ran and stopped.
            $eventStep = isset($anyEvent[$role->id]) ? 'next_step_next_event' : 'next_step_first_event';

            if (! isset($publicUpcoming[$role->id]) && ! isset($dismissed[$role->id.':'.$eventStep])) {
                $items->push([
                    'type' => $eventStep,
                    'count' => 1,
                    'title' => isset($anyEvent[$role->id])
                        ? __('messages.next_step_add_next_event')
                        : __('messages.next_step_add_first_event'),
                    'subtitle' => $role->name,
                    'url' => route('event.create', ['subdomain' => $role->subdomain]),
                    'color' => 'blue',
                    'dismiss_schedule' => UrlUtils::encodeId($role->id),
                ]);
            }
        }

        // At most one step per schedule already (each branch continues), and a short list is
        // a suggestion while a long one is a chore.
        $priority = [
            'next_step_tickets' => 0,
            'next_step_payments' => 1,
            'next_step_first_event' => 2,
            'next_step_next_event' => 2,
        ];

        return $items->sortBy(fn ($item) => $priority[$item['type']] ?? 9)->values();
    }

    /**
     * Sort pending action items by type priority (requests, fan content, polls,
     * carpool), then by count descending within each type.
     */
    private function sortPendingActionItems($items)
    {
        $priority = ['installments_overdue' => 0, 'requests' => 1, 'fan_content' => 2, 'polls' => 3, 'carpool' => 4];

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
        $endOfGridUtc = $startOfMonth->copy()->endOfMonth()->endOfWeek(6)->addDays(2)->setTimezone('UTC');

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
            ->inMonth($startOfMonthUtc, $endOfGridUtc)
            ->orderBy('starts_at')
            ->get();

        return $this->buildCalendarResponse($events, collect(), false, null, null, (int) $month, (int) $year, 0);
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
            // creatorRole: the panel renders each date in its own schedule's timezone.
            ->with(['roles', 'tickets', 'creatorRole'])
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
                        // The sale's OWN currency, not the platform's. The panel used to print a
                        // literal '$', so a seller in ZAR saw R120 on the Revenue panel and $120
                        // on this one, for the same sale.
                        'currency_code' => $sale->event?->ticket_currency_code,
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

    /**
     * Permanently hide the federation suggestion for this user.
     *
     * redirect()->back() rather than a fixed route, because the banner appears on both
     * the dashboard and the schedule page and one action serves both.
     */
    public function dismissFederationPrompt(Request $request): RedirectResponse
    {
        if (is_demo_mode()) {
            return redirect()->back();
        }

        $user = $request->user();
        $user->federation_prompt_dismissed = true;
        // saveQuietly: dismissing a banner should not bump users.updated_at.
        $user->saveQuietly();

        return redirect()->back();
    }

    /**
     * Turn down one suggestion on the Next steps panel, permanently.
     *
     * No saveQuietly() question here, unlike the federation prompt above: this writes a row in
     * its own table rather than mutating users, so nothing touches users.updated_at or the
     * updating hook in User::boot().
     */
    public function dismissNextStep(Request $request): RedirectResponse
    {
        if (is_demo_mode()) {
            return redirect()->back();
        }

        $validated = $request->validate([
            'schedule' => ['required', 'string'],
            // Not decoration: without it the discriminator column accepts any string, and a
            // value that later collided with a real step type would silently suppress both a
            // panel row and an email.
            'type' => ['required', Rule::in(DismissedNextStep::STEP_TYPES)],
        ]);

        $user = $request->user();
        $roleId = UrlUtils::decodeId($validated['schedule']);

        // The same set the panel is built from: editor() is owner and admin only, and roles()
        // already filters is_deleted. A viewer can act on none of these steps and never sees a
        // row. decodeId() returns null on a malformed hash, which would otherwise fall through
        // to an unkeyed write.
        if (! $roleId || ! $user->editor()->where('roles.id', $roleId)->exists()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        DismissedNextStep::firstOrCreate([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'step_type' => $validated['type'],
        ]);

        return redirect()->back();
    }

    /**
     * Turn down every suggestion currently on the panel.
     *
     * Still one row per suggestion rather than a flag on the user, so this clears what is on the
     * panel today without silencing a schedule created tomorrow.
     */
    public function dismissAllNextSteps(Request $request): RedirectResponse
    {
        if (is_demo_mode()) {
            return redirect()->back();
        }

        $user = $request->user();

        // Recomputed rather than read from hidden inputs. The panel only renders the first
        // $limit rows and folds the rest into a "show more" details, so a form built from what
        // is on screen would leave everything past the eighth schedule behind. It also means
        // there is no per-item authorization to do: getNextStepItems() is only ever fed
        // $user->editor(), so the list cannot name a schedule this user does not edit.
        $rows = $this->getNextStepItems($user->editor()->pluck('roles.id'))
            // Every branch sets dismiss_schedule today. This is so that one added later without
            // it skips the row, rather than writing a null into a NOT NULL role_id - which is an
            // unhandled 500 on this action, not a missing dismissal.
            ->filter(fn ($item) => ! empty($item['dismiss_schedule']))
            ->map(fn ($item) => [
                'user_id' => $user->id,
                'role_id' => UrlUtils::decodeId($item['dismiss_schedule']),
                'step_type' => $item['type'],
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();

        if ($rows) {
            // One statement, not one per schedule: an owner on this install has 37 of them.
            // insertOrIgnore against dns_user_role_step_unique makes a double submit a no-op,
            // the same way SendActivationNudges claims a nudge. It bypasses the model, hence
            // the explicit timestamps above.
            DB::table('dismissed_next_steps')->insertOrIgnore($rows);
        }

        return redirect()->back();
    }
}
