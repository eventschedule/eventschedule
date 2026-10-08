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
use App\Services\FederationService;
use App\Services\HomeDashboard;
use App\Services\ScheduleRealtime;
use App\Utils\DateUtils;
use App\Utils\LegacyRedirects;
use App\Utils\SetupGuide;
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
     * Whether getNextStepItems() found a suggestion this person turned down before. Set as a
     * side effect of that method, which already loads the dismissals, rather than queried twice.
     */
    private bool $nextStepsDismissedBefore = false;

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

        // The blog and the old WordPress URLs are eventschedule.com's, so only the nexus redirects
        // them. A selfhosted SaaS reaches this catch-all too, and has no blog host to send a post
        // slug to (it would 301 onto a 404) and no business sending visitors to eventschedule.com.
        if ($slug && config('app.is_nexus')) {
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
        }

        if ($slug) {
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

        // The onboarding email's button for someone who had already picked a type. It comes here
        // rather than straight to /new/{type} so the bounce above still applies: a days-old email
        // opened after they set up a schedule lands on the dashboard, not on a second new-schedule
        // form.
        $type = $request->query('type');
        if (in_array($type, ['talent', 'venue', 'curator'], true)) {
            return redirect()->route('new', ['type' => $type]);
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

        // Somebody who followed "Claim this page" from a schedule the app created for them. Beside
        // the handover above and for the same reason: their only tie to the app is the page they
        // were offered, so the new-user bounce to /getting-started further down would strand them.
        // Sending them back to the claim page settles it either way - the address matched and
        // registration already handed the schedule over, or it did not and the page is where the
        // masked hint saying which address does is rendered.
        if ($pendingClaim = session()->pull('pending_claim')) {
            return redirect()->route('role.claim.start', ['subdomain' => $pendingClaim]);
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

        // One period for the page, kept where it has always been stored: on the Views panel.
        $period = $this->resolvePanelPeriod($panelSettings['views']['period'] ?? 30);

        // Everything above the calendar (App\Services\HomeDashboard): the tiles, the schedules,
        // what is coming up and what just happened, or the page of someone who runs nothing. The
        // live figures come from the owner's Realtime, and are null wherever there is none.
        $dashboard = app(HomeDashboard::class)->build($user, $period, ScheduleRealtime::summary($user));

        // Read by tests that pin the comparison's window (DashboardViewsComparisonTest).
        $viewsInPeriod = $dashboard['views']['total'] ?? 0;
        $viewsChange = $dashboard['views']['change'] ?? 0;
        $viewsChangeLabel = 'vs_previous_'.$period.'_days';

        // The cards someone switched on in Customize. Skipped while they are off, and for anyone
        // who is not an organizer with something to count: nothing renders them then.
        $topEvents = collect();
        $latestNewsletters = collect();
        $boostCampaigns = collect();
        $trafficSources = collect();

        if (($dashboard['state'] ?? null) === 'organizer' && empty($dashboard['fresh'])) {
            $analyticsService = app(AnalyticsService::class);
            $now = now()->endOfDay();
            $periodStart = now()->subDays($period - 1)->startOfDay();

            if (in_array('top_events', $visiblePanels)) {
                $topEvents = $analyticsService->getTopEvents($user, $panelSettings['top_events']['count'] ?? 3, $periodStart, $now);
            }
            if (in_array('newsletters', $visiblePanels)) {
                $latestNewsletters = Newsletter::whereIn('role_id', $roleIds)
                    ->where('status', 'sent')
                    ->orderByDesc('sent_at')
                    ->limit($panelSettings['newsletters']['count'] ?? 3)
                    ->get();
            }
            if (in_array('boosts', $visiblePanels)) {
                $boostCampaigns = BoostCampaign::whereIn('role_id', $roleIds)
                    ->whereIn('status', ['active', 'paused'])
                    ->latest()
                    ->limit($panelSettings['boosts']['count'] ?? 3)
                    ->get();
            }
            if (in_array('traffic_sources', $visiblePanels)) {
                $trafficSources = $analyticsService->getTopReferrerDomains($user, $panelSettings['traffic_sources']['count'] ?? 5, $periodStart, $now);
            }
        }

        // The month calendar. An organizer may switch it off. Anyone else gets it only when it
        // would have something on it: it lists the events of schedules a person EDITS and events
        // they submitted to somebody else's, so for someone who may only view a schedule, or
        // who runs nothing, it is otherwise an empty grid under what they came for.
        $showCalendar = $dashboard['state'] === 'organizer'
            ? in_array('calendar', $visiblePanels)
            : Event::where('user_id', $user->id)->exists();

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
        //
        // Minus the rows a new organizer's setup guide is already asking for itself, so the
        // dashboard does not say "add your first event" twice (SetupGuide::withoutCoveredSteps()).
        $nextStepItems = SetupGuide::withoutCoveredSteps($this->getNextStepItems($roleIds));
        $nextStepsDismissedBefore = $this->nextStepsDismissedBefore;

        // Nudge admins to turn federation on once there is something worth sharing.
        $federation = app(FederationService::class);
        $showFederationPrompt = $federation->shouldPromptAdoption($user);

        // Once the install HAS joined: offer the owner their undecided schedules. Never both
        // prompts at once - one asks the operator to join, the other assumes they have.
        $federationListingSchedules = $showFederationPrompt
            ? collect()
            : $federation->listingPromptSchedules($user);

        return view('home', compact(
            'events',
            'month',
            'year',
            'startOfMonth',
            'roleIds',
            'dashboard',
            'showCalendar',
            'viewsInPeriod',
            'viewsChange',
            'viewsChangeLabel',
            'dashboardConfig',
            'panelSettings',
            'topEvents',
            'latestNewsletters',
            'boostCampaigns',
            'trafficSources',
            'canCreateSchedule',
            'schedules',
            'venues',
            'curators',
            'defaultCurrency',
            'pendingActionItems', 'nextStepItems', 'nextStepsDismissedBefore', 'showFederationPrompt', 'federationListingSchedules',
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

        // 1b) A free schedule has priced tickets that cannot be sold.
        //
        // The guest side is deliberately silent here (a missing buy button reads the same as sales
        // not being open yet, which is the least-shaming outcome), so the organizer has to get the
        // loud signal in this list instead. That reasoning got STRONGER when paid selling went back
        // to Pro: the silence is now permanent rather than lasting until the month rolled over.
        //
        // Guarded on OWNERSHIP, not editor access: SubscriptionController::show redirects a
        // non-owner, so showing an editor a to-do they cannot act on repeats a mistake this
        // dashboard already avoids elsewhere.
        if (config('app.hosted')) {
            foreach ($rolesById as $role) {
                if ($role->user_id !== auth()->id() || $role->canSellPaidTickets() || is_demo_role($role)) {
                    continue;
                }

                // Upcoming, non-draft, non-cancelled events this schedule created that carry a
                // priced row and were not grandfathered by the 2026_09_20 migration.
                $blocked = \App\Models\Event::where('creator_role_id', $role->id)
                    ->where('is_draft', false)
                    ->where('is_cancelled', false)
                    ->where('tickets_enabled', true)
                    ->whereNull('tickets_grandfathered_at')
                    ->whereNull('appointment_type_id')
                    // The recurring arm must be here: for a recurring event starts_at is the
                    // recurrence ANCHOR, which is in the past by design, so a bare starts_at
                    // window silently skipped every live weekly show - the loudest case this to-do
                    // exists for. Event::constrainToOccurrencesSince() also drops a series whose
                    // end date has passed, which a bare days_of_week test kept forever.
                    ->where(fn ($q) => $q->whereNull('starts_at')
                        ->orWhere(fn ($w) => \App\Models\Event::constrainToOccurrencesSince($w, now('UTC')->subDay())))
                    // whereExists on the bare table rather than whereHas('tickets'): that relation
                    // carries an orderBy('price'), which Laravel emits inside the EXISTS subquery.
                    ->whereExists(fn ($q) => $q->selectRaw('1')
                        ->from('tickets')
                        ->whereColumn('tickets.event_id', 'events.id')
                        ->where('tickets.is_deleted', false)
                        ->where('tickets.is_addon', false)
                        ->where('tickets.price', '>', 0))
                    ->count();

                if ($blocked < 1) {
                    continue;
                }

                $items->push([
                    'type' => 'ticket_quota',
                    'count' => $blocked,
                    'title' => trans_choice('messages.pending_action_tickets_need_pro', $blocked, ['count' => $blocked]),
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
        $this->nextStepsDismissedBefore = false;

        // "Turn off suggestions" (SetupGuide::suggest()): nothing is offered, on any schedule,
        // until the person turns it back on. Before any query.
        if ($roleIds->isEmpty() || ! auth()->user()?->wantsSuggestions()) {
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

        // Events on the schedule's PAGE, mirroring SendActivationNudges::listedEvents(). The
        // "is the page empty" questions use this instead of $owned: is_accepted is the gate the
        // guest page reads, so a curator's listed events are its page even though it cannot price
        // them. Same predicate as $owned for every other schedule type.
        $listed = fn ($query) => $query
            ->where(fn ($q) => $q->whereColumn('event_role.role_id', 'events.creator_role_id')
                ->orWhere('event_role.is_accepted', true));

        // One query each rather than per schedule: the dashboard renders on every page load.
        //
        // Recurring-aware: a series' starts_at is its anchor, so a bare starts_at test told a
        // schedule running a weekly show to "add your next date".
        $publicUpcomingBy = fn ($filter) => $filter(DB::table('event_role')
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->whereIn('event_role.role_id', $ids)
            ->where('events.is_draft', false)
            ->where('events.is_private', false)
            ->where('events.is_internal', false)
            ->where(fn ($q) => Event::constrainToOccurrencesSince($q, now('UTC'))))
            ->distinct()->pluck('event_role.role_id')->flip();

        // Something upcoming this schedule can sell (branch 1), and something upcoming anyone
        // visiting its page can see (branch 4). Only a curator can have the second without the
        // first, and it was told its page had no upcoming dates while it listed dozens.
        $publicUpcoming = $publicUpcomingBy($owned);
        $listedUpcoming = $publicUpcomingBy($listed);

        $anyEvent = $listed(DB::table('event_role')
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->whereIn('event_role.role_id', $ids))
            ->distinct()->pluck('event_role.role_id')->flip();

        // Each schedule's soonest upcoming draft, keyed to that event. A page whose only upcoming
        // date is a draft is not empty, it is unpublished, so "add your next date" is the wrong
        // ask - the first event someone saves as a draft got exactly that on day one. Internal
        // events are left out: there is nothing to publish, they are meant to stay off the page.
        $upcomingDraft = $owned(DB::table('event_role')
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->whereIn('event_role.role_id', $ids)
            ->where('events.is_draft', true)
            ->where('events.is_internal', false)
            ->where(fn ($q) => Event::constrainToOccurrencesSince($q, now('UTC'))))
            ->orderBy('events.starts_at')
            ->get(['event_role.role_id', 'events.id as event_id'])
            ->unique('role_id')
            ->pluck('event_id', 'role_id');

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

        // Schedules with an event that takes registrations. Undated, like the ticket types
        // above: "they know how" is about the schedule, not about its next date. This is the
        // rule SendActivationNudges::dueForNoTicketTypeFree() already has, and branch 1 did not:
        // a free schedule that took sign-ups was still told to add free registration.
        $withRegistration = $owned(DB::table('event_role')
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->whereIn('event_role.role_id', $ids)
            ->where('events.rsvp_enabled', true))
            ->distinct()->pluck('event_role.role_id')->flip();

        // Schedules with a grandfathered event (events.tickets_grandfathered_at) that is itself
        // still selling: not cancelled, a date to come, and a priced row. Any old stamp was not
        // enough - a free schedule with one grandfathered 2025 event and a NEW priced event that
        // cannot sell got "connect payments" beside the to-do saying those tickets need Pro.
        $grandfatheredSellers = DB::table('events')
            ->whereIn('events.creator_role_id', $ids)
            ->whereNotNull('events.tickets_grandfathered_at')
            ->where('events.is_cancelled', false)
            ->where(fn ($q) => Event::constrainToOccurrencesSince($q, now('UTC')))
            ->whereExists(fn ($q) => $q->selectRaw('1')
                ->from('tickets')
                ->whereColumn('tickets.event_id', 'events.id')
                ->where('tickets.is_deleted', false)
                ->where('tickets.is_addon', false)
                ->where('tickets.price', '>', 0))
            ->distinct()->pluck('events.creator_role_id')->flip();

        // How many DISTINCT people have asked to be told when each schedule's events go on sale.
        // One query, like the four above: the dashboard renders on every page load.
        //
        // This enriches the copy on branch 1 rather than adding a step type of its own, and that is
        // deliberate. Branch 1 already fires on exactly this population - something upcoming, no way
        // to buy - and then continues, so a new type could never reach them. Saying that real people
        // are waiting is something a generic nudge cannot do; the count is the whole point.
        $interestCounts = $owned(DB::table('event_interests')
            ->join('event_role', 'event_role.event_id', '=', 'event_interests.event_id')
            ->join('events', 'events.id', '=', 'event_interests.event_id')
            ->whereIn('event_role.role_id', $ids)
            ->whereNotNull('event_interests.confirmed_at')
            ->where(fn ($q) => Event::constrainToOccurrencesSince($q, now('UTC'))))
            ->groupBy('event_role.role_id')
            // selectRaw + an alias, NOT pluck(DB::raw(...)): pluck treats its first argument as a
            // column NAME, so the raw expression is looked up as a property on the result row and
            // throws "Undefined property: stdClass::$email".
            ->selectRaw('event_role.role_id as role_id, COUNT(DISTINCT event_interests.email) as waiting')
            ->pluck('waiting', 'role_id');

        // Each paid schedule's most recent event that ended in the last two weeks without a photo
        // gallery: the moment an organizer has photos to share. One query. Only events the
        // schedule created, since the owning schedule is the one whose plan the gallery follows,
        // and only public one-off dates - a recurring series' gallery is not about one night.
        $proIds = $roles->filter(fn (Role $role) => $role->isPro())->pluck('id');
        $recentWithoutGallery = $proIds->isEmpty() ? collect() : DB::table('events')
            ->whereIn('events.creator_role_id', $proIds)
            ->where('events.is_draft', false)
            ->where('events.is_private', false)
            ->where('events.is_internal', false)
            ->whereNull('events.days_of_week')
            ->whereBetween('events.starts_at', [now()->subDays(14), now()->subHours(6)])
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                ->from('gallery_images')
                ->whereColumn('gallery_images.event_id', 'events.id')
                ->whereNull('gallery_images.draft_token'))
            ->orderByDesc('events.starts_at')
            ->get(['events.creator_role_id', 'events.id', 'events.name'])
            ->unique('creator_role_id')
            ->keyBy('creator_role_id');

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

        // Read by the dashboard's card (SetupGuide::suggestions()): somebody who has turned a
        // suggestion down before and is being suggested to again is offered the off switch
        // outright. Only this panel's own step types: the network prompt's dismissals share the
        // table and are a different question.
        $this->nextStepsDismissedBefore = $dismissedRows
            ->contains(fn ($row) => in_array($row->step_type, DismissedNextStep::STEP_TYPES, true));

        foreach ($roles as $role) {
            // 1) Something upcoming and no way to buy: the step that matters most. On a schedule
            // that cannot sell a priced ticket the ask is registration, and one that already
            // takes registrations has answered it (see $withRegistration); a schedule that can
            // sell is still asked for a ticket type, as its email is.
            if (isset($publicUpcoming[$role->id]) && ! isset($withTicketType[$role->id])
                && ! (isset($withRegistration[$role->id]) && ! $role->canSellPaidTickets())) {
                // A dismissal suppresses the row and the schedule keeps its slot: the continue
                // below still runs. Falling through would replace a dismissed suggestion with
                // the next-best one on the same schedule, which reads as the button not working.
                if (! isset($dismissed[$role->id.':next_step_tickets'])) {
                    $waiting = (int) ($interestCounts[$role->id] ?? 0);

                    $items->push([
                        'type' => 'next_step_tickets',
                        'count' => 1,
                        // "So people can buy" is not true of a schedule that cannot sell a priced
                        // ticket: on Free the ask is registration or a $0 row, both unlimited.
                        'title' => $waiting > 0
                            ? trans_choice('messages.next_step_add_ticket_type_waiting', $waiting, ['count' => $waiting])
                            : ($role->canSellPaidTickets()
                                ? __('messages.next_step_add_ticket_type')
                                : __('messages.next_step_add_registration')),
                        'subtitle' => $role->name,
                        'url' => route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']),
                        'color' => 'blue',
                        // Both the dismiss form's payload and the opt-in signal for the row
                        // partial. getPendingActionItems() and AdminAlertService never set it,
                        // so their rows render no control.
                        'dismiss_schedule' => UrlUtils::encodeId($role->id),
                        // Read by SetupGuide::withoutCoveredSteps(): a row saying people are
                        // waiting to buy is never hidden behind the guide's own tickets step.
                        'waiting' => $waiting,
                    ]);
                }

                continue;
            }

            // 2) Paid tickets set up with nothing to take the money with. Only where they can
            // actually sell: telling a free schedule to connect a gateway, beside the to-do that
            // says those same tickets cannot be sold, was two contradictory messages at once.
            // A grandfathered event still sells on a free schedule, so it still counts.
            if (isset($withPaidTicketType[$role->id]) && ! $hasGateway
                && ($role->canSellPaidTickets() || isset($grandfatheredSellers[$role->id]))) {
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

            // 3) Something upcoming that is still a draft: publish it. Before the empty-page
            // branch, which would otherwise read the draft as nothing at all. Unlisted events are
            // deliberately not included: telling someone to publish an event they chose to keep
            // off their schedule page argues with that choice.
            if (! isset($publicUpcoming[$role->id]) && isset($upcomingDraft[$role->id])) {
                if (! isset($dismissed[$role->id.':next_step_publish_event'])) {
                    $items->push([
                        'type' => 'next_step_publish_event',
                        'count' => 1,
                        'title' => __('messages.next_step_publish_event'),
                        'subtitle' => $role->name,
                        'url' => route('event.edit', [
                            'subdomain' => $role->subdomain,
                            'hash' => UrlUtils::encodeId($upcomingDraft[$role->id]),
                        ]),
                        'color' => 'blue',
                        'dismiss_schedule' => UrlUtils::encodeId($role->id),
                    ]);
                }

                continue;
            }

            // 4) An empty page, or one whose dates have all passed. Two step types rather than
            // one, keyed off the same condition that picks the copy: "never published" and "went
            // quiet" are different situations at opposite ends of a schedule's life, and a
            // dismissal is permanent, so folding them together lets a day-one "not ready yet"
            // silence the dormancy nudge on a schedule that later ran and stopped.
            $eventStep = isset($anyEvent[$role->id]) ? 'next_step_next_event' : 'next_step_first_event';

            if (! isset($listedUpcoming[$role->id])) {
                if (! isset($dismissed[$role->id.':'.$eventStep])) {
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

                // Dismissed or not, the schedule keeps its slot, as in the branches above.
                continue;
            }

            // 5) A schedule with nothing more pressing whose event just ended: share its photos.
            // Last, because a page with no upcoming date (4) matters more than photos of the past.
            if (isset($recentWithoutGallery[$role->id]) && ! isset($dismissed[$role->id.':next_step_gallery'])) {
                $recent = $recentWithoutGallery[$role->id];
                $items->push([
                    'type' => 'next_step_gallery',
                    'count' => 1,
                    'title' => __('messages.next_step_add_gallery', ['event' => $recent->name]),
                    'subtitle' => $role->name,
                    'url' => route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($recent->id)]).'#section-gallery',
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
            'next_step_publish_event' => 2,
            'next_step_gallery' => 3,
        ];

        // Each row carries its schedule's face (photo or initial) for the dashboard's card.
        $faces = $roles->mapWithKeys(fn (Role $role) => [UrlUtils::encodeId($role->id) => SetupGuide::face($role)]);

        return $items
            ->sortBy(fn ($item) => $priority[$item['type']] ?? 9)
            ->map(fn ($item) => $item + ($faces[$item['dismiss_schedule']] ?? []))
            ->values();
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

        // From the grid's first day, not the 1st: the month's first week shows the last days
        // of the month before, and they were always empty here while a schedule's own month
        // (RoleController::adminCalendarEvents()) showed the same events on them.
        // A day of slack, as the end has: the bound is the VIEWER's midnight and an event is
        // placed on its schedule's day, so a morning in Berlin fell before a midnight in Los
        // Angeles and the grid's first cell was empty. Only grid days are placed (buildEventsMap()).
        $startOfGridUtc = $startOfMonth->copy()->startOfWeek(0)->subDay()->setTimezone('UTC');
        $endOfGridUtc = $startOfMonth->copy()->endOfMonth()->endOfWeek(6)->addDays(2)->setTimezone('UTC');

        $roleIds = $user->editor()->pluck('roles.id');

        // creatorRole: every row is placed and timed on its own schedule's clock.
        $events = Event::with('roles', 'parts', 'tickets', 'creatorRole')
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
            ->inMonth($startOfGridUtc, $endOfGridUtc)
            ->orderBy('starts_at')
            ->get();

        return $this->buildCalendarResponse($events, collect(), false, null, null, (int) $month, (int) $year, 0);
    }

    public function saveDashboardConfig(Request $request): JsonResponse
    {
        $request->validate([
            'panels' => 'required|array|max:11',
            'panels.*.id' => 'required|string|in:upcoming_count,views,followers,upcoming_events,recent_activity,revenue,top_events,newsletters,boosts,traffic_sources,calendar',
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

    /**
     * Panel periods are validated on write (saveDashboardConfig's in:7,14,30) but only int-cast on
     * read (getDashboardConfig), so a row persisted under a different rule set reaches the view
     * unchecked. Clamp to the set that has both a messages.last_N_days and a
     * messages.vs_previous_N_days translation: without one a tile's caption renders the raw key.
     * The comparison itself is HomeDashboard::views(), which takes any whole number of days.
     */
    private function resolvePanelPeriod($period): int
    {
        $period = (int) $period;

        return in_array($period, [7, 14, 30], true) ? $period : 30;
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
            // The month calendar under the cards. Not in a config saved before it could be
            // switched off, so it is appended as visible by the "missing panels" pass below.
            ['id' => 'calendar', 'visible' => true],
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
                $returnUrl = $event->fanContentReturnUrl($pending['subdomain'], $eventDate);
                session()->flash('scroll_to', 'pending-video-'.$video->id);
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
            $returnUrl = $event->fanContentReturnUrl($pending['subdomain'], $eventDate);
            session()->flash('scroll_to', 'pending-comment-'.$comment->id);

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

            if (($pending['return_to'] ?? null) === 'gallery') {
                $returnUrl = $event->fanContentReturnUrl($pending['subdomain'], $eventDate, gallery: true);
            } else {
                $returnUrl = $event->fanContentReturnUrl($pending['subdomain'], $eventDate);
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
     * List schedules on the Event Schedule network, from the "List on the network" prompt.
     *
     * Only the schedules posted, each of which must be undecided and editable by this user.
     * The prompt names every schedule it would list, and a hash that does not resolve is an
     * error rather than a quiet "all of them": decodeId() returns null for a malformed one.
     *
     * redirect()->back(), like the dismissals: the prompt is on the dashboard and on the
     * schedule page, and one action serves both.
     */
    public function listOnFederation(Request $request, FederationService $federation): RedirectResponse
    {
        if (is_demo_mode() || ! $federation->listingAvailable()) {
            return redirect()->back();
        }

        // Every box unticked: say so, rather than bouncing off validation with nothing on screen.
        if (empty($request->input('schedules'))) {
            return redirect()->back()->with('warning', __('messages.federation_listing_none_ticked'));
        }

        $roleIds = $this->federationScheduleIds($request);

        if ($roleIds === null) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $listed = $federation->listSchedulesFor($request->user(), $roleIds);

        // A double click, or a form left open while the schedule was answered somewhere else,
        // lists nothing and says nothing: there is no success to report, and no failure either.
        if ($listed->isEmpty()) {
            return redirect()->back();
        }

        $count = $listed->count();
        $replace = ['count' => $count, 'name' => $listed->first()->name];
        $heldBack = $federation->heldBack($listed);

        if ($heldBack->isNotEmpty()) {
            $heldCount = $heldBack->count();

            return redirect()->back()->with('warning', trans_choice(
                'messages.federation_listed_held_back',
                $heldCount,
                ['count' => $heldCount, 'name' => $heldBack->first()->name]
            ));
        }

        $key = $federation->status() === 'approved'
            ? 'messages.federation_listed_approved'
            : 'messages.federation_listed_pending';

        return redirect()->back()->with('message', trans_choice($key, $count, $replace));
    }

    /**
     * "Not now" on the listing prompt: one dismissal row per schedule shown, so a schedule
     * created later is still asked. The schedules stay undecided - an explicit "Not listed"
     * would veto co-listed events, which a dismissed banner must not do.
     */
    public function dismissFederationListing(Request $request, FederationService $federation): RedirectResponse
    {
        if (is_demo_mode()) {
            return redirect()->back();
        }

        $roleIds = $this->federationScheduleIds($request);

        if ($roleIds === null) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // One statement, and a second submit of the same form is a no-op rather than a
        // unique-index error.
        DB::table('dismissed_next_steps')->insertOrIgnore(array_map(fn ($roleId) => [
            'user_id' => $request->user()->id,
            'role_id' => $roleId,
            'step_type' => DismissedNextStep::FEDERATION_LISTING,
            'created_at' => now(),
            'updated_at' => now(),
        ], $roleIds));

        return redirect()->back();
    }

    /**
     * The schedule ids a listing form posted, or null when any of them is not a schedule this
     * user can edit. All or nothing: a form is never half-applied.
     *
     * Editable, not "editable and still undecided": a schedule answered a moment ago - a double
     * click, a second tab - is still one this user may act on. listSchedulesFor() skips it
     * quietly, which is the right answer to a repeat, where an authorization error is not.
     */
    private function federationScheduleIds(Request $request): ?array
    {
        $validated = $request->validate([
            'schedules' => ['required', 'array', 'min:1', 'max:500'],
            'schedules.*' => ['required', 'string'],
        ]);

        // editor() is owner and admin; roles() already leaves out deleted schedules.
        $allowed = $request->user()->editor()
            ->pluck('roles.id')
            ->map(fn ($id) => (int) $id)
            ->flip();

        $roleIds = [];

        foreach ($validated['schedules'] as $hash) {
            $roleId = (int) UrlUtils::decodeId($hash);

            if (! $roleId || ! isset($allowed[$roleId])) {
                return null;
            }

            $roleIds[$roleId] = $roleId;
        }

        return array_values($roleIds);
    }

    /**
     * Turn down one suggestion on the Next steps panel, permanently.
     *
     * No saveQuietly() question here, unlike the federation prompt above: this writes a row in
     * its own table rather than mutating users, so nothing touches users.updated_at or the
     * updating hook in User::boot().
     */
    public function dismissNextStep(Request $request): JsonResponse|RedirectResponse
    {
        if (is_demo_mode()) {
            return $this->nextStepsAnswer($request);
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
            return $request->expectsJson()
                ? response()->json(['ok' => false], 403)
                : redirect()->back()->with('error', __('messages.not_authorized'));
        }

        DismissedNextStep::firstOrCreate([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'step_type' => $validated['type'],
        ]);

        return $this->nextStepsAnswer($request);
    }

    /**
     * JSON to the dashboard's card, which folds the row in place; a redirect to the plain form
     * its still version posts. Without the JSON branch a fetch follows the redirect into a full
     * dashboard render to learn that one row was written.
     */
    private function nextStepsAnswer(Request $request, array $rows = []): JsonResponse|RedirectResponse
    {
        return $request->expectsJson()
            ? response()->json(['ok' => true, 'rows' => $rows])
            : redirect()->back();
    }

    /** The posted shape of a list of suggestions: what "Dismiss all" was looking at, and what Undo takes back. */
    private function nextStepRowRules(bool $required): array
    {
        return [
            // 200 is far above the owner with 37 schedules on this install, and a bound at all.
            'rows' => [$required ? 'required' : 'sometimes', 'array', 'max:200'],
            'rows.*.schedule' => ['required', 'string'],
            'rows.*.type' => ['required', Rule::in(DismissedNextStep::STEP_TYPES)],
        ];
    }

    /**
     * Turn down every suggestion currently in the list.
     *
     * Still one row per suggestion rather than a flag on the user, so this clears what is listed
     * today without silencing a schedule created tomorrow. (The flag exists too, and is its own
     * control: setSuggestions().)
     *
     * WHAT IT WRITES. The dashboard's card posts the rows its list holds, and this writes those
     * of them that are in the offer: withoutCoveredSteps(getNextStepItems()), the same set
     * home() renders. So what goes is what was on screen. The line inside an open guide is not
     * in the list and is not written; the quiet guide's own row is in it and is; and a finished
     * guide dismissed a moment ago cannot have its schedule's row dismissed unseen, which a
     * plain recompute would do, because that guide no longer holds anything back.
     *
     * The intersection is also the authorization: getNextStepItems() is only ever fed
     * $user->editor(), so the offer cannot name a schedule this user does not edit, and a posted
     * pair that is not in it writes nothing.
     *
     * It branches on whether `rows` was posted AT ALL: an empty list writes nothing rather than
     * falling back. Only the still panel's form posts none, and it alone gets the recompute -
     * needed there because that panel folds everything past the eighth row behind "show more" -
     * minus any row about a schedule whose guide is showing, which belongs to the guide and is
     * not in that panel either (partials/setup-guide).
     */
    public function dismissAllNextSteps(Request $request): JsonResponse|RedirectResponse
    {
        if (is_demo_mode()) {
            return $this->nextStepsAnswer($request);
        }

        $validated = $request->validate($this->nextStepRowRules(false));
        $user = $request->user();

        $offer = SetupGuide::withoutCoveredSteps($this->getNextStepItems($user->editor()->pluck('roles.id')))
            // Every branch sets dismiss_schedule today. This is so that one added later without
            // it skips the row, rather than writing a null into a NOT NULL role_id - which is an
            // unhandled 500 on this action, not a missing dismissal.
            ->filter(fn ($item) => ! empty($item['dismiss_schedule']));

        if ($request->has('rows')) {
            $listed = collect($validated['rows'] ?? [])
                ->map(fn ($row) => $row['schedule'].':'.$row['type'])
                ->flip();

            $offer = $offer->filter(fn ($item) => isset($listed[$item['dismiss_schedule'].':'.$item['type']]));
        } elseif (($state = SetupGuide::state()) && empty($state['hidden'])) {
            $own = UrlUtils::encodeId($state['role_id']);

            $offer = $offer->reject(fn ($item) => $item['dismiss_schedule'] === $own);
        }

        $rows = $offer->map(fn ($item) => [
            'user_id' => $user->id,
            'role_id' => UrlUtils::decodeId($item['dismiss_schedule']),
            'step_type' => $item['type'],
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->all();

        if ($rows) {
            // One statement, not one per schedule: an owner on this install has 37 of them.
            // insertOrIgnore against dns_user_role_step_unique makes a double submit a no-op,
            // the same way SendActivationNudges claims a nudge. It bypasses the model, hence
            // the explicit timestamps above.
            DB::table('dismissed_next_steps')->insertOrIgnore($rows);
        }

        // What it wrote, so the card's Undo can take back exactly that.
        return $this->nextStepsAnswer($request, $offer
            ->map(fn ($item) => ['schedule' => $item['dismiss_schedule'], 'type' => $item['type']])
            ->values()->all());
    }

    /**
     * Undo: take back dismissals this person has just made.
     *
     * Their OWN rows, which is the whole authorization: a dismissal is per user, so the user_id
     * in the query is what stops one editor undoing another's answer about a schedule they
     * share. Whether the caller still edits the schedule is deliberately not asked - it would
     * be a second query that changes no outcome, since the most this can do is show somebody a
     * suggestion they turned down themselves. Only this panel's step types: the "List on the
     * network" prompt shares the table and is not in STEP_TYPES, so this cannot touch it.
     */
    public function restoreNextSteps(Request $request): JsonResponse|RedirectResponse
    {
        if (is_demo_mode()) {
            return $this->nextStepsAnswer($request);
        }

        $validated = $request->validate($this->nextStepRowRules(true));
        $user = $request->user();

        foreach ($validated['rows'] as $row) {
            if ($roleId = UrlUtils::decodeId($row['schedule'])) {
                DismissedNextStep::where('user_id', $user->id)
                    ->where('role_id', $roleId)
                    ->where('step_type', $row['type'])
                    ->delete();
            }
        }

        return $this->nextStepsAnswer($request);
    }

    /**
     * "Turn off suggestions", and back on: the dashboard's card. The same switch is a toggle
     * in Account settings (ProfileController::update()); SetupGuide::suggest() is the one
     * writer behind both.
     */
    public function setSuggestions(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate(['on' => ['required', 'boolean']]);

        if (! is_demo_mode()) {
            SetupGuide::suggest($request->user(), (bool) $validated['on']);
        }

        return $this->nextStepsAnswer($request);
    }
}
