<?php

namespace App\Services;

use App\Models\AnalyticsDaily;
use App\Models\MarketingDailyStat;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Cashier;

/**
 * Growth analytics: the onboarding funnel plus the wider activation/monetization picture.
 *
 * The funnel half of this class was lifted verbatim out of AdminController so that the
 * /admin/users page and the /admin/growth export share ONE definition of the cohort, the
 * demo exclusions and the signup_intent rule. If the two could drift, the export would
 * quietly disagree with the page it is meant to explain.
 */
class GrowthExportService
{
    /**
     * Bumped whenever the payload's shape OR the meaning of a field changes, so a reader diffing two
     * pulls knows whether they compare. Every bump is described in docs/GROWTH_DATA.md's changelog,
     * which GrowthDataDictionaryTest holds to this number.
     */
    public const SCHEMA_VERSION = 8;

    /** The month the schedule.claim audit action shipped; nothing before it can be counted. */
    private const CLAIMS_TRACKED_FROM = '2026-09';

    /** How many trailing months of per-schedule ticket volume to emit. */
    public const RECENT_MONTHS = 6;

    /**
     * How many signups must share an attribution value (utm, referrer host, landing path) before it
     * is exported as itself. Below it the value reads "(other)": a group of one or two describes a
     * person, and those values are visitor-controlled strings - a personal site, a customer's
     * domain, a forwarded link with a secret in it. Public pages and known platforms are exempt,
     * because naming them identifies nobody.
     */
    public const MIN_ATTRIBUTION_GROUP = 3;

    /** Rows past the top N of an acquisition/segment rollup are folded into one "(rest)" row. */
    private const ROLLUP_TOP = 50;

    /**
     * Row-table ceiling. Hit it and the export records the fact plus the true total in
     * meta.truncated - a silently short table would read as "this is everything".
     */
    private function rowCap(): int
    {
        return max(1, (int) config('usage.growth_row_cap', 20000));
    }

    /**
     * Base query for the onboarding funnel cohort: real (verified, non-demo) users who
     * created their account within the given period.
     */
    public function cohort(Carbon $startDate, Carbon $endDate)
    {
        return User::query()
            ->whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            // Attendee-intent signups (follow/ticket/request/...) never meant to
            // create a schedule; NULL = pre-tracking rows, treated as organizer.
            ->where(function ($query) {
                $query->whereNull('signup_intent')->orWhere('signup_intent', 'organizer');
            })
            ->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Constraint for "has a real (non-demo) schedule".
     *
     * Demo CONTENT (Role::constrainDemoContent()), not the `demo-%` subdomain shape it used to be:
     * that shape hid real schedules named before the prefix was reserved (a "Demo Night" got
     * `demo-night`) and kept the twelve /examples showcase schedules, which live on ordinary
     * subdomains and are fabricated. Every arm is null-safe, so whereNot() keeps a schedule with no
     * contact email.
     *
     * Deleted schedules still count, here and in the signup rows' saved_schedule: they were saved,
     * which is what the stage asks, and dropping them would let saved_event (whose events outlive a
     * deleted schedule) exceed saved_schedule.
     */
    public function scheduleFilter(): \Closure
    {
        return function ($query) {
            $query->whereNot(fn ($q) => Role::constrainDemoContent($q));
        };
    }

    /**
     * Constraint for "has a real (non-demo) event": an event is demo when any of its schedules is
     * demo content - the same predicate as scheduleFilter().
     */
    public function eventFilter(): \Closure
    {
        return function ($query) {
            $query->whereDoesntHave('roles', fn ($roleQuery) => Role::constrainDemoContent($roleQuery));
        };
    }

    /**
     * Onboarding funnel: the stage counts + conversions for the selected period, plus
     * the north-star (signup -> first event) with its period-over-period change and the
     * biggest onboarding leak. See the correctness rules in the funnel plan: stages 4/6 are
     * OR-defined so the funnel stays monotonic across all creation paths and history; the
     * anonymous traffic stages (1-2) are only shown for windows inside the tracked period.
     * Monotonicity holds WITHIN a group, not across the group boundaries - see $noStepConv
     * below, which is what stops a cross-population ratio being rendered as a conversion.
     */
    public function funnelData(Carbon $startDate, Carbon $endDate, Carbon $prevStartDate, Carbon $prevEndDate): array
    {
        $scheduleFilter = $this->scheduleFilter();
        $eventFilter = $this->eventFilter();

        // Cohort stages (3, 5, 7): account created, saved a schedule, saved an event.
        $accounts = $this->cohort($startDate, $endDate)->count();

        // Verified attendee-intent signups excluded from the cohort above, shown as a
        // note under the account stage so the funnel number stays explainable.
        $excludedIntents = User::query()
            ->whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('signup_intent')
            ->where('signup_intent', '!=', 'organizer')
            ->select('signup_intent', DB::raw('COUNT(*) as total'))
            ->groupBy('signup_intent')
            ->orderByDesc('total')
            ->pluck('total', 'signup_intent');
        $savedSchedule = $this->cohort($startDate, $endDate)->whereHas('createdRoles', $scheduleFilter)->count();
        $savedEvent = $this->cohort($startDate, $endDate)->whereHas('createdEvents', $eventFilter)->count();

        // Stages 4/6 are OR-defined (reached the form OR completed the save) so a stage can
        // never exceed the one above it, regardless of creation path or missing click history.
        $reachedSchedule = $this->cohort($startDate, $endDate)
            ->where(function ($query) use ($scheduleFilter) {
                $query->whereNotNull('schedule_form_viewed_at')
                    ->orWhereHas('createdRoles', $scheduleFilter);
            })->count();
        $reachedEvent = $this->cohort($startDate, $endDate)
            ->where(function ($query) use ($eventFilter) {
                $query->whereNotNull('event_form_viewed_at')
                    ->orWhereHas('createdEvents', $eventFilter);
            })->count();

        // Stages 8-10: the monetization half, which the funnel previously did not model at all -
        // it stopped at saved_event, so the largest drop in the business was invisible here.
        // Across the whole install only 21% of schedules ever get a ticket type and 4% ever take
        // money, and that cliff is what caps everything downstream.
        //
        // Ticket stages reuse the demo-excluding $eventFilter and Event::tickets(), which already
        // scopes out is_deleted and add-on rows, so "has a ticket type" means a live, sellable one.
        $savedTicket = $this->cohort($startDate, $endDate)
            ->whereHas('createdEvents', function ($query) use ($eventFilter) {
                $eventFilter($query);
                $query->whereHas('tickets');
            })->count();
        $savedPaidTicket = $this->cohort($startDate, $endDate)
            ->whereHas('createdEvents', function ($query) use ($eventFilter) {
                $eventFilter($query);
                $query->whereHas('tickets', fn ($t) => $t->where('price', '>', 0));
            })->count();

        // A conversion is not "has a subscriptions row". Cashier's subscriptions() relation
        // carries no status filter, so an `incomplete` row - a checkout whose card was declined
        // at creation - would count as a sale, which is the exact population the
        // stripe_subscription_failed counter exists to separate out. Cancelled and past_due DO
        // count: they converted once, which is what a conversion funnel measures.
        $converted = function ($query) {
            $query->whereNotIn('stripe_status', ['incomplete', 'incomplete_expired']);
        };

        // OR-defined like stages 4/6, so it can never undercut the stage below it: a user who
        // subscribed before this column existed still counts as having reached checkout.
        $reachedCheckout = $this->cohort($startDate, $endDate)
            ->where(function ($query) use ($converted) {
                $query->whereNotNull('subscribe_form_viewed_at')
                    ->orWhereHas('createdRoles.subscriptions', $converted);
            })->count();
        $subscribed = $this->cohort($startDate, $endDate)
            ->whereHas('createdRoles.subscriptions', $converted)->count();

        // Shown the paid-ticket paywall (users.ticket_paywall_viewed_at). NOT OR-defined with the
        // conversion the way reached_checkout is: subscribing does not imply having met this
        // paywall (most upgrades happen off the pricing page within the first hour), so there
        // is no outcome to fall back on, and a window older than the column is null instead.
        //
        // "Older than the column" is measured from the first stamp on THIS install, not from a
        // date in the code: the column starts filling when the release deploys, and a window
        // opening between a hard-coded date and that deploy would report zeros for days nothing
        // could be recorded.
        $paywallTrackedFrom = User::min('ticket_paywall_viewed_at');
        $hitTicketPaywall = $paywallTrackedFrom !== null
            && $startDate->toDateString() >= Carbon::parse($paywallTrackedFrom)->toDateString()
            ? $this->cohort($startDate, $endDate)->whereNotNull('ticket_paywall_viewed_at')->count()
            : null;

        // Traffic stages (1-2): anonymous, only meaningful for a window inside the tracked period.
        $trackingStart = MarketingDailyStat::min('date');
        $rangeTracked = $trackingStart !== null
            && $startDate->toDateString() >= Carbon::parse($trackingStart)->toDateString();
        $visitors = null;
        $signupViews = null;
        $codeCounts = ['signup_code_requests' => null, 'signup_code_verified' => null, 'signup_code_invalid' => null];
        if ($rangeTracked) {
            $sums = MarketingDailyStat::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->selectRaw('COALESCE(SUM(visitors), 0) as v, COALESCE(SUM(signup_views), 0) as s, '
                    .'COALESCE(SUM(signup_code_requests), 0) as signup_code_requests, '
                    .'COALESCE(SUM(signup_code_verified), 0) as signup_code_verified, '
                    .'COALESCE(SUM(signup_code_invalid), 0) as signup_code_invalid')
                ->first();
            // Visitors are only recorded on the nexus; keep n/a on other deployments.
            $visitors = config('app.is_nexus') ? (int) $sums->v : null;
            $signupViews = (int) $sums->s;

            // Per column, not per table: $rangeTracked only says the TABLE existed at the start
            // of the window. These columns arrived later with default(0), so a window opening
            // before a column's own start date would report its backfilled zeros as a
            // measurement. Null renders as "n/a" instead.
            foreach (array_keys($codeCounts) as $column) {
                $from = MarketingDailyStat::COLUMN_TRACKED_FROM[$column] ?? null;
                if ($from !== null && $startDate->toDateString() >= $from) {
                    $codeCounts[$column] = (int) $sums->{$column};
                }
            }
        }

        // The email-code wall, which sits between the sign-up page and an account on the email
        // path. Its own group, never part of the chain: Google sign-ups skip the code entirely,
        // so 'account' can legitimately exceed 'signup_code_verified' and a ratio drawn across
        // that boundary would be meaningless. Hosted only - selfhost has no code step.
        $codeStages = config('app.hosted') ? [
            ['key' => 'signup_code_requests', 'group' => 'email_code', 'count' => $codeCounts['signup_code_requests']],
            ['key' => 'signup_code_verified', 'group' => 'email_code', 'count' => $codeCounts['signup_code_verified']],
            ['key' => 'signup_code_invalid', 'group' => 'email_code', 'count' => $codeCounts['signup_code_invalid']],
        ] : [];

        $stages = [
            ['key' => 'visited', 'group' => 'traffic', 'count' => $visitors],
            ['key' => 'signup_view', 'group' => 'traffic', 'count' => $signupViews],
            ...$codeStages,
            ['key' => 'account', 'group' => 'cohort', 'count' => $accounts],
            ['key' => 'reached_schedule', 'group' => 'cohort', 'count' => $reachedSchedule],
            ['key' => 'saved_schedule', 'group' => 'cohort', 'count' => $savedSchedule],
            ['key' => 'reached_event', 'group' => 'cohort', 'count' => $reachedEvent],
            ['key' => 'saved_event', 'group' => 'cohort', 'count' => $savedEvent],
            // Two groups, not one. The ticket stages continue the cohort chain (a ticket needs
            // an event, so each is a genuine subset). The plan stages do NOT: buying Pro has
            // nothing to do with selling tickets, and of the nine real payers on this install
            // three have no paid ticket type and one has no events at all. Dividing one by the
            // other renders a conversion above 100% and a funnel that visibly widens.
            ['key' => 'saved_ticket', 'group' => 'tickets', 'count' => $savedTicket],
            ['key' => 'saved_paid_ticket', 'group' => 'tickets', 'count' => $savedPaidTicket],
            // Opens the plan group. Not a subset of saved_paid_ticket (the live banner fires on a
            // price that was typed and never saved) and reached_checkout is not a subset of it
            // (checkout is reachable from anywhere), so it takes no step ratio in either direction.
            ['key' => 'hit_ticket_paywall', 'group' => 'plan', 'count' => $hitTicketPaywall],
            ['key' => 'reached_checkout', 'group' => 'plan', 'count' => $reachedCheckout],
            ['key' => 'subscribed', 'group' => 'plan', 'count' => $subscribed],
        ];

        // Bar width denominator: stage-1 visitors when present, else the largest stage.
        $available = array_values(array_filter(array_column($stages, 'count'), fn ($c) => $c !== null));
        $maxCount = ! empty($available) ? max($available) : 0;
        $widthDenom = ($visitors && $visitors > 0) ? $visitors : $maxCount;
        if ($widthDenom < 1) {
            $widthDenom = 1;
        }

        // Per-stage width + step conversion vs the previous stage that has a value.
        // The 'account' stage is skipped: it is the first cohort stage, so comparing it to
        // 'signup_view' would draw a conversion/drop across the anonymous-traffic -> signup-cohort
        // divider (different populations, a cross-population ratio, not a real in-funnel drop).
        // 'hit_ticket_paywall' is skipped for exactly the same reason: it opens the plan group,
        // and the stage above it is about selling tickets, which is a different question.
        // 'reached_checkout' is skipped too: checkout is reachable from anywhere, so it is not a
        // subset of the paywall stage above it and a ratio between them could exceed 100%.
        //
        // In the email_code group, requests -> verified is the code wall's conversion, and
        // signup_view -> requests is the share of sign-up page visitors who chose email (all three
        // are deduped per IP + user agent per day, so those ratios compare like with like).
        // 'signup_code_invalid' is NOT a stage below verified - somebody can mistype and then
        // succeed, so it overlaps both - and gets no ratio of its own.
        $noStepConv = ['account', 'hit_ticket_paywall', 'reached_checkout', 'signup_code_invalid'];
        $prevCount = null;
        foreach ($stages as &$stage) {
            $c = $stage['count'];
            $stage['width'] = $c === null ? 0 : min(100, round($c / $widthDenom * 100, 1));
            $stage['step_conv'] = null;
            $stage['drop_count'] = null;
            if ($c !== null && $prevCount !== null && $prevCount > 0 && ! in_array($stage['key'], $noStepConv, true)) {
                $stage['step_conv'] = round($c / $prevCount * 100, 1);
                $stage['drop_count'] = max(0, $prevCount - $c);
            }
            if ($c !== null) {
                $prevCount = $c;
            }
        }
        unset($stage);

        // Biggest leak: the worst adjacent drop by absolute users lost.
        //
        // Traffic stages are excluded (anonymous, a different population). The TICKETS stages are
        // included, because each is a genuine subset of the one above it - a ticket needs an
        // event - and because saved_event -> saved_ticket is the largest drop in the business
        // (install-wide, 438 schedules publish an event and 144 ever create a ticket type). It is
        // the whole reason those stages exist, and scoping this to 'cohort' made it the one
        // transition the loop structurally could not name.
        //
        // The PLAN stages stay out: reached_checkout is not a subset of saved_paid_ticket, so an
        // adjacent "drop" there can be negative and would let this pick a meaningless pair. That
        // is the same boundary $noStepConv suppresses above.
        $biggestDrop = null;
        $leakGroups = ['cohort', 'tickets'];
        $cohortStages = array_values(array_filter($stages, fn ($s) => in_array($s['group'], $leakGroups, true)));
        for ($i = 1; $i < count($cohortStages); $i++) {
            $from = $cohortStages[$i - 1];
            $to = $cohortStages[$i];
            if ($from['count'] === null || $to['count'] === null || $from['count'] <= 0) {
                continue;
            }
            $lost = $from['count'] - $to['count'];
            if ($lost <= 0) {
                continue;
            }
            if ($biggestDrop === null || $lost > $biggestDrop['lost']) {
                $biggestDrop = [
                    'from_key' => $from['key'],
                    'to_key' => $to['key'],
                    'lost' => $lost,
                    'drop_pct' => round($lost / $from['count'] * 100, 1),
                ];
            }
        }

        // North-star: signup -> first event (%), with period-over-period change (points).
        $firstEventConv = $accounts > 0 ? round($savedEvent / $accounts * 100, 1) : null;
        $prevAccounts = $this->cohort($prevStartDate, $prevEndDate)->count();
        $prevSavedEvent = $prevAccounts > 0
            ? $this->cohort($prevStartDate, $prevEndDate)->whereHas('createdEvents', $eventFilter)->count()
            : 0;
        $prevFirstEventConv = $prevAccounts > 0 ? round($prevSavedEvent / $prevAccounts * 100, 1) : null;
        $firstEventConvChange = ($firstEventConv !== null && $prevFirstEventConv !== null)
            ? round($firstEventConv - $prevFirstEventConv, 1)
            : null;

        // Overall visitor -> first event (%), only when traffic is tracked for the window.
        $visitorToEventConv = ($visitors && $visitors > 0) ? round($savedEvent / $visitors * 100, 1) : null;

        return [
            'stages' => $stages,
            'cohort_size' => $accounts,
            'excluded_intents' => $excludedIntents,
            'first_event_conv' => $firstEventConv,
            'first_event_conv_change' => $firstEventConvChange,
            'visitor_to_event_conv' => $visitorToEventConv,
            'biggest_drop' => $biggestDrop,
            'traffic_tracked' => $rangeTracked,
            'tracking_started_at' => $trackingStart,
        ];
    }

    /**
     * Per-period conversion-rate series for the onboarding over-time chart. Set-based:
     * one grouped query per stage. The most recent period is always incomplete (cohort
     * maturation), so its index is returned for the view to mark it "in progress".
     */
    public function funnelTrendData(Carbon $startDate, Carbon $endDate): array
    {
        $daysDiff = $startDate->diffInDays($endDate);
        if ($daysDiff <= 31) {
            $formatKey = 'daily';
            $labelFormat = 'M d';
        } elseif ($daysDiff <= 90) {
            $formatKey = 'weekly';
            $labelFormat = 'W';
        } else {
            $formatKey = 'monthly';
            $labelFormat = 'M Y';
        }

        // $column is always a hardcoded literal ('created_at' or 'date'), never user input;
        // the format string is whitelisted by $formatKey (mirrors getTrendData()).
        //
        // Weeks are ISO (%x-W%v: ISO year + Monday-based week 01-53). %Y-%u was neither - a Monday
        // week numbered within the CALENDAR year, so the days around New Year split into a week 00
        // and a week 52/53 of two different years, and the key could not be joined to anything
        // else keyed by ISO week (owner_digests is).
        $expr = fn (string $column) => match ($formatKey) {
            'daily' => DB::raw("DATE_FORMAT({$column}, '%Y-%m-%d') as period"),
            'weekly' => DB::raw("DATE_FORMAT({$column}, '%x-W%v') as period"),
            'monthly' => DB::raw("DATE_FORMAT({$column}, '%Y-%m') as period"),
        };

        $scheduleFilter = $this->scheduleFilter();
        $eventFilter = $this->eventFilter();

        $accountsTrend = $this->cohort($startDate, $endDate)
            ->select($expr('created_at'), DB::raw('COUNT(*) as count'))
            ->groupBy('period')->orderBy('period')->get()->keyBy('period');
        $scheduleTrend = $this->cohort($startDate, $endDate)
            ->whereHas('createdRoles', $scheduleFilter)
            ->select($expr('created_at'), DB::raw('COUNT(*) as count'))
            ->groupBy('period')->orderBy('period')->get()->keyBy('period');
        $eventTrend = $this->cohort($startDate, $endDate)
            ->whereHas('createdEvents', $eventFilter)
            ->select($expr('created_at'), DB::raw('COUNT(*) as count'))
            ->groupBy('period')->orderBy('period')->get()->keyBy('period');
        $trafficTrend = MarketingDailyStat::query()
            ->select($expr('date'), DB::raw('SUM(visitors) as visitors'), DB::raw('SUM(signup_views) as signup_views'))
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('period')->orderBy('period')->get()->keyBy('period');

        $allPeriods = collect()
            ->merge($accountsTrend->keys())
            ->merge($trafficTrend->keys())
            ->unique()->sort()->values();

        $labels = $allPeriods->map(function ($period) use ($labelFormat, $formatKey) {
            if ($formatKey === 'weekly') {
                $parts = explode('-W', $period);
                if (count($parts) === 2) {
                    return 'Week '.ltrim($parts[1], '0');
                }
            }
            try {
                return Carbon::parse($period)->format($labelFormat);
            } catch (\Exception $e) {
                return $period;
            }
        })->toArray();

        $isNexus = (bool) config('app.is_nexus');
        $visitorToSignup = [];
        $signupToSchedule = [];
        $signupToEvent = [];
        foreach ($allPeriods as $period) {
            $acc = (int) ($accountsTrend[$period]->count ?? 0);
            $sch = (int) ($scheduleTrend[$period]->count ?? 0);
            $evt = (int) ($eventTrend[$period]->count ?? 0);
            $vis = (int) ($trafficTrend[$period]->visitors ?? 0);
            $visitorToSignup[] = ($isNexus && $vis > 0) ? round($acc / $vis * 100, 1) : null;
            $signupToSchedule[] = $acc > 0 ? round($sch / $acc * 100, 1) : null;
            $signupToEvent[] = $acc > 0 ? round($evt / $acc * 100, 1) : null;
        }

        return [
            'labels' => $labels,
            // The machine-readable keys behind the labels: YYYY-MM-DD, ISO YYYY-Www, or YYYY-MM.
            // "Week 39" alone cannot be placed in a year.
            'periods' => $allPeriods->all(),
            'granularity' => $formatKey,
            // visitor_to_signup divides by visitors, whose counting method changed on 2026-09-07
            // (server to a JS beacon). Same per-period basis as traffic[].visitors_basis, so a
            // ratio is never compared across that line by accident.
            'visitors_basis' => $allPeriods->map(fn ($period) => $isNexus
                ? $this->visitorsBasisFor((string) $period, $formatKey)
                : null)->all(),
            'visitor_to_signup' => $visitorToSignup,
            'signup_to_schedule' => $signupToSchedule,
            'signup_to_event' => $signupToEvent,
            'last_index' => count($labels) - 1,
            'has_traffic' => $isNexus && $trafficTrend->sum('visitors') > 0,
        ];
    }

    /**
     * server / mixed / beacon for one funnel_trend period, to the day - basisInMonth() would call
     * every September week "mixed" when only the one containing 2026-09-07 is.
     */
    private function visitorsBasisFor(string $period, string $granularity): ?string
    {
        $rebasedAt = MarketingDailyStat::COLUMN_REBASED_AT['visitors'] ?? null;
        if ($rebasedAt === null) {
            return null;
        }

        try {
            [$from, $to] = match ($granularity) {
                'daily' => [Carbon::parse($period), Carbon::parse($period)],
                'weekly' => (function () use ($period) {
                    [$year, $week] = array_map('intval', explode('-W', $period));
                    $monday = Carbon::now()->setISODate($year, $week)->startOfWeek(Carbon::MONDAY);

                    return [$monday, $monday->copy()->addDays(6)];
                })(),
                default => [Carbon::parse($period.'-01'), Carbon::parse($period.'-01')->endOfMonth()],
            };
        } catch (\Throwable $e) {
            return null;
        }

        if ($to->toDateString() < $rebasedAt) {
            return 'server';
        }

        return $from->toDateString() >= $rebasedAt ? 'beacon' : 'mixed';
    }

    // ---------------------------------------------------------------------
    // Export
    // ---------------------------------------------------------------------

    /**
     * The whole payload. Aggregates are derived from the two row tables rather than
     * queried separately, so a section can never disagree with the rows beneath it.
     */
    public function build(Carbon $startDate, Carbon $endDate, Carbon $prevStartDate, Carbon $prevEndDate): array
    {
        $months = $this->recentMonths();
        $signups = $this->signupRows();
        $schedules = $this->scheduleRows($months);

        $notes = [
            'Revenue is reported per currency: sales has no currency column, it comes from events.ticket_currency_code.',
            'There is no users.last_login_at; no activity proxy is reported here rather than a misleading one.',
            'marketing_daily_stats.visitors is nexus-only and is null elsewhere.',
            'The newest cohort is always immature - see funnel_trend.last_index.',
            'Row tables are columnar: read columns[] then rows[][].',
            'traffic[] columns are null for months before that counter existed, not 0. A real zero '
                .'and an untracked month are different answers - see MarketingDailyStat::COLUMN_TRACKED_FROM.',
            'activation, cohorts and acquisition count EVERY verified account. funnel counts only the '
                .'organizer cohort (signup_intent null or organizer), so its denominator is smaller and '
                .'the two sets of rates are not comparable.',
            'claims counts OWNERLESS schedules, which every other section deliberately excludes. Do '
                .'not add its totals to anything else. claims.claimed is null for months before the '
                .'claim feature shipped, not 0 - it comes from schedule.claim audit rows. '
                .'claims.auto_created comes from roles.created_at and is real for every month.',
            'reached_event means the visitor OPENED the event form of their own accord, or has an '
                .'event. Saving a first schedule redirects straight to that form, and that one visit is '
                .'deliberately not stamped - counting it would make reached_event equal saved_schedule '
                .'for every organizer and turn the stage into a 100% that measures nothing. Compare the '
                .'months either side of that change with care: before it, the stage also counted people '
                .'who only ever landed there.',
            'signup_code_requests, signup_code_verified and signup_code_invalid count the emailed-code '
                .'step of the email sign-up path only (Google sign-ups never see it). All three are deduped '
                .'per IP+user-agent per day and share the same bot filters, so verified/requests is the '
                .'code step\'s conversion. signup_code_invalid is visitors who had at least one code '
                .'rejected; it overlaps both others (a mistype followed by a success counts in each) and '
                .'is null before 2026-09-25.',
            'hit_ticket_paywall counts organizers shown the paid-ticket paywall in the event editor '
                .'(a priced row on a schedule that cannot sell it), including a price typed and never '
                .'saved. It is null for a window that opens before the first such view on this install. '
                .'It is not a subset of saved_paid_ticket, and reached_checkout is not a subset of it. '
                .'subscription.create audit rows carry new_values.source = "tickets" when the checkout '
                .'was opened from that paywall.',
            'churn counts subscription_cancellations, which began on 2026-09-28: cancellations before '
                .'that have no row. Comments are deliberately left out of this export (free text). '
                .'source is app (the plan tab form), portal (the Stripe portal OR dashboard, which Stripe '
                .'does not tell apart), payment_failed, payment_disputed, schedule_deleted or admin. Transfers '
                .'(a schedule changing hands) are counted in churn.transferred, not churn.cancelled. '
                .'nudges and owner_digests totals shrink when a schedule or user is deleted (cascade).',
            'gmv_by_currency and the schedule rows exclude the demo schedule\'s sales. Exports before '
                .'schema_version 6 did not exclude them from gmv_by_currency, and the hourly demo re-seed '
                .'put roughly $12k-15k of fake USD into whichever month was current: discard USD '
                .'gmv_by_currency from any earlier export. gmv_recent_by_currency (added in 6) gives each '
                .'schedule\'s revenue per currency, so a schedule that sold in more than one currency, '
                .'which reports gmv_currency null, can still be sized.',
            'traffic[].visitors_basis is server, mixed or beacon. From 2026-09-07 the visit counters '
                .'(visitors, page_views, docs_*, pricing_*) are counted by a JS beacon on edge-cached '
                .'pages, and daily visitors fell about 4x across that line. Whether that removed bots or '
                .'the beacon under-counts is not established - see MarketingDailyStat::COLUMN_REBASED_AT. '
                .'Do not compare visit counts across bases. signup_* counters are unaffected.',
            'monetization.mrr and .arr come from live Stripe subscriptions (active, past_due, or '
                .'cancelled but paid until ends_at) at their configured price, and match the /admin '
                .'dashboard ARR. From schema_version 7 trialing subscriptions are excluded (counted in '
                .'trialing_subscriptions), a paid plan with no live subscription counts 0, and a price '
                .'config no longer names counts 0 (unrecognized_price_subscriptions). Before 7, mrr '
                .'counted trials and booked the last two at a by-tier estimate: do not compare mrr '
                .'across that line. arpu divides by the priced subscriptions, not by plan_counts, '
                .'which include admin grants and referral credits.',
            'monetization.ticket_trials counts the card-free paid-selling trial, which is not a plan: '
                .'those schedules stay "free" in plan_counts. converted means a real subscription created '
                .'after the trial started and within 14 days of its end; sold_during means a paid sale '
                .'on a schedule-created event inside the trial window.',
            'Since schema_version 8, PAYING means billing: a live, non-trialing Stripe subscription (the '
                .'MRR definition, schedules.billing). plan is the tier, which also covers admin grants, '
                .'referral credits, legacy plan_expires rows and trials. payers_vs_free, retention.paid and '
                .'segments.by_schedule_type.billing use billing; paid_plan and free_pressure stay tier-based. '
                .'Before 8, "paid" meant the tier, so those sections were mostly comped-vs-free.',
            'Since schema_version 8, tickets, revenue and ticket types are credited to the schedule that '
                .'CREATED the event (events.creator_role_id), not to every schedule listed on it - a venue '
                .'and the talent playing there used to both get the same sale. Legacy events with no '
                .'creator fall back to every listed schedule. Event counts only include events the schedule '
                .'created or accepted, and events_recent_90d counts events CREATED in the window (it read '
                .'updated_at, which translation and sync writes bump).',
            'Since schema_version 8, attribution values (utm_source, utm_medium, referrer_domain, '
                .'landing_path) shared by fewer than '.self::MIN_ATTRIBUTION_GROUP.' signups read "(other)". '
                .'Exempt: our own marketing/docs pages, published blog posts (/blog/<slug>), and known '
                .'platforms, which are canonicalised (google.co.uk and the Android search app both read '
                .'google). referrer_channel groups referrers as search, ai, social, community, email, '
                .'messaging, auth, own, schedule or other, so a rare AI referrer still counts as ai. '
                .'Always replaced, whatever the count: a value containing @ ("(redacted)"), an IP referrer '
                .'("(ip)"), a schedule subdomain or customer domain ("(schedule)"), and token-shaped path '
                .'segments (":token"). Landing paths are lowercased with a leading slash. A personal site '
                .'that refers 3+ signups can still appear by name.',
            'Since schema_version 8, uid and sid are 12 hex characters (6 could collide at a few '
                .'thousand users), so ids do not match across that line. Rollups past the top '
                .self::ROLLUP_TOP.' groups end in one "(rest)" row, so their signups sum to the row total.',
            'meta.range applies only to funnel and funnel_trend (see meta.range_applies_to). Every '
                .'section derived from the row tables is all-time or trailing. meta.partial_month names the '
                .'month in progress: its counts are month-to-date, never compare them to a full month.',
            'demo exclusion is demo CONTENT (Role::constrainDemoContent: the demo account, its '
                .'contact address, the showcase schedules) since schema_version 8. Before, it was the '
                .'demo-% subdomain shape, which kept the fabricated showcase schedules and hid real '
                .'schedules named demo-something.',
        ];
        // Every derived section is computed from the row tables, so if those were capped
        // the sections describe the most recent N rows and not the whole population.
        if ($signups['truncated'] || $schedules['truncated']) {
            $notes[] = 'ROW TABLES WERE CAPPED. activation, cohorts, acquisition, segments, free_pressure, '
                .'payers_vs_free and retention are derived from the capped rows (newest first), not the full '
                .'population. funnel, monetization and traffic are queried directly and remain complete.';
        }

        return [
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'range' => ['start' => $startDate->toDateString(), 'end' => $endDate->toDateString()],
                'recent_months' => $months,
                'is_hosted' => (bool) config('app.hosted'),
                'is_nexus' => (bool) config('app.is_nexus'),
                'range_applies_to' => ['funnel', 'funnel_trend'],
                // The month in progress, and how far into it this pull is. Month-keyed counts for it
                // (gmv_by_currency, paid_tickets_recent, churn.by_month, claims, traffic,
                // newsletter_emails_this_month) are month-to-date.
                'partial_month' => [
                    'month' => now()->format('Y-m'),
                    'days_elapsed' => (int) now()->format('j'),
                    'days_in_month' => (int) now()->format('t'),
                ],
                'app_version' => config('self-update.version_installed'),
                'schema_version' => self::SCHEMA_VERSION,
                'row_cap' => $this->rowCap(),
                'truncated' => [
                    'signups' => ['capped' => $signups['truncated'], 'total' => $signups['total']],
                    'schedules' => ['capped' => $schedules['truncated'], 'total' => $schedules['total']],
                ],
                'notes' => $notes,
            ],
            'funnel' => $this->funnelData($startDate, $endDate, $prevStartDate, $prevEndDate),
            'funnel_trend' => $this->funnelTrendData($startDate, $endDate),
            'activation' => $this->activationFrom($signups),
            'cohorts' => $this->cohortsFrom($signups),
            'acquisition' => $this->acquisitionFrom($signups),
            'segments' => $this->segmentsFrom($signups, $schedules),
            'free_pressure' => $this->freePressureFrom($schedules),
            'payers_vs_free' => $this->payersVsFreeFrom($schedules),
            'monetization' => $this->monetization(),
            'churn' => $this->churn(),
            'nudges' => $this->nudges(),
            // Weekly owner digests (app:send-owner-digests) per ISO week: owners emailed, and how
            // many schedules those emails covered. Empty until the command has been run.
            'owner_digests' => DB::table('owner_digests')
                ->selectRaw('week, COUNT(*) as owners, SUM(schedules) as schedules')
                ->groupBy('week')->orderByDesc('week')->limit(12)->get()
                ->mapWithKeys(fn ($row) => [$row->week => ['owners' => (int) $row->owners, 'schedules' => (int) $row->schedules]])
                ->all(),
            'retention' => $this->retentionFrom($schedules),
            'traffic' => $this->traffic(),
            'claims' => $this->claims($months),
            'signups' => ['columns' => $signups['columns'], 'rows' => $signups['rows']],
            'schedules' => ['columns' => $schedules['columns'], 'rows' => $schedules['rows']],
        ];
    }

    /**
     * Pseudonymous, stable id. Salted with APP_KEY so it cannot be walked back to a
     * subdomain or an email, but identical across exports so two pulls can be diffed.
     * Rotating APP_KEY changes every id.
     */
    private function hashId(string $prefix, $id): string
    {
        // 12 hex characters, not 6: at 16.7M values six collide with even odds by ~5,000 users, and
        // a collision silently joins one person's schedule to another's signup row.
        return $prefix.':'.substr(hash_hmac('sha256', (string) $id, (string) config('app.key')), 0, 12);
    }

    /** The trailing months emitted in paid_tickets_recent, oldest first. */
    private function recentMonths(): array
    {
        $months = [];
        for ($i = self::RECENT_MONTHS - 1; $i >= 0; $i--) {
            $months[] = now()->copy()->startOfMonth()->subMonths($i)->format('Y-m');
        }

        return $months;
    }

    /** Demo exclusion applied to a roles query - the same predicate as scheduleFilter(). */
    private function excludeDemoRoles($query)
    {
        return $query->whereNot(fn ($q) => Role::constrainDemoContent($q));
    }

    /**
     * Subquery of every event id attached to a demo schedule - the same predicate as
     * excludeDemoRoles() and gmvByCurrency(), so the signup, schedule and revenue sections agree.
     */
    private function demoEventIds()
    {
        return DemoService::demoEventIdsQuery();
    }

    /**
     * One row per verified non-demo user. This is the table that targets the stated leak:
     * a schedule-only table structurally cannot see people who signed up and never made one.
     */
    private function signupRows(): array
    {
        $base = User::query()
            ->whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL);

        $total = (clone $base)->count();

        // Schedules and first-schedule timestamp, per user, in one pass. `c` is the live count
        // (schedules_count); `ever` includes deleted ones, because saved_schedule asks whether they
        // saved one - the funnel's stage counts them too, and so must this, or a user's events
        // (which outlive a deleted schedule) would put them in saved_event and not saved_schedule.
        $rolesByUser = $this->excludeDemoRoles(DB::table('roles')->whereNotNull('user_id'))
            ->selectRaw('user_id, SUM(CASE WHEN is_deleted = 0 THEN 1 ELSE 0 END) as c, '
                .'COUNT(*) as ever, MIN(created_at) as first_at')
            ->groupBy('user_id')
            ->get()->keyBy('user_id');

        $eventsByUser = DB::table('events')
            ->whereNotIn('id', $this->demoEventIds())
            ->selectRaw('user_id, COUNT(*) as c')
            ->groupBy('user_id')
            ->get()->keyBy('user_id');

        // The ticket stages, per user, in one pass. The two ticket clauses reproduce
        // Event::tickets(), which is what funnelData()'s saved_ticket / saved_paid_ticket go
        // through - without them a deleted type or an add-on row would read as "this user made
        // a ticket type" here while not counting there, and the two rails would disagree about
        // the same person. MAX() over the price test yields both stages from one query.
        $ticketsByUser = DB::table('events')
            ->join('tickets', 'tickets.event_id', '=', 'events.id')
            ->whereNotIn('events.id', $this->demoEventIds())
            ->where('tickets.is_deleted', false)
            ->where('tickets.is_addon', false)
            ->selectRaw('events.user_id, MAX(tickets.price > 0) as paid')
            ->groupBy('events.user_id')
            ->get()->keyBy('user_id');

        $rows = [];
        foreach ((clone $base)->orderByDesc('id')->limit($this->rowCap())->cursor() as $u) {
            $roleAgg = $rolesByUser[$u->id] ?? null;
            $ticketAgg = $ticketsByUser[$u->id] ?? null;
            $firstAt = $roleAgg?->first_at ? Carbon::parse($roleAgg->first_at) : null;

            $savedSchedule = (int) ($roleAgg->ever ?? 0) > 0;

            $rows[] = [
                $this->hashId('u', $u->id),
                $u->created_at?->format('Y-m'),
                $u->signup_intent,
                // The four attribution columns are RAW here and made safe to export by
                // anonymizeAttribution() below, which needs every row to count group sizes.
                $u->utm_source,
                $u->utm_medium,
                $this->hostOf($u->referrer_url),
                null, // referrer_channel, filled by anonymizeAttribution()
                $u->landing_page,
                $u->google_oauth_id ? 'google' : ($u->facebook_id ? 'facebook' : ($u->password ? 'email' : 'other')),
                // OR-defined, exactly as funnelData()'s reached_schedule stage is, so the step
                // can never come out BELOW the saved_schedule it contains. The timestamp alone
                // is only stamped by RoleController::create() and only since 2026-07-07, so a
                // user who arrived by any other path - or who signed up before that column
                // existed - read as "never reached the form" while also reading as "saved a
                // schedule". That made activation.reached_schedule_form 88 against 491 saves.
                // admin/users.blade.php already does it this way.
                $u->schedule_form_viewed_at !== null || $savedSchedule,
                $savedSchedule,
                (int) ($eventsByUser[$u->id]->c ?? 0) > 0,
                $ticketAgg !== null,
                (int) ($ticketAgg->paid ?? 0) > 0,
                (int) ($roleAgg->c ?? 0),
                ($firstAt && $u->created_at) ? max(0, $u->created_at->diffInDays($firstAt)) : null,
            ];
        }

        $columns = ['uid', 'created_month', 'signup_intent', 'utm_source', 'utm_medium',
            'referrer_domain', 'referrer_channel', 'landing_path', 'auth', 'reached_schedule_form',
            'saved_schedule', 'saved_event', 'saved_ticket', 'saved_paid_ticket', 'schedules_count',
            'days_to_first_schedule'];

        return [
            'columns' => $columns,
            'rows' => $this->anonymizeAttribution($rows, array_flip($columns)),
            'total' => $total,
            'truncated' => $total > $this->rowCap(),
        ];
    }

    /**
     * Make the four visitor-controlled attribution columns safe to export, in two passes.
     *
     * Pass one canonicalises each value, and replaces the ones that are never safe whatever the
     * count: a "@" in a utm, an IP or a schedule's own host as referrer, a token in a path. It also
     * marks values that identify nobody as exempt from pass two - our own pages, published blog
     * posts, known platforms, and the placeholders themselves.
     *
     * Pass two replaces every non-exempt value shared by fewer than MIN_ATTRIBUTION_GROUP signups
     * with "(other)". This is the part that catches what no rule can name in advance: a personal
     * site, a customer's domain we have no record of, a tenant event slug, a link forwarded by one
     * buyer. It runs over the (capped) row set, so a value's group size is counted on what is
     * exported - the rollups are built from these rows and inherit it.
     */
    private function anonymizeAttribution(array $rows, array $i): array
    {
        $context = [
            'marketing' => array_change_key_case(array_flip(array_keys((array) config('sitemap_lastmod', []))), CASE_LOWER),
            'blog' => $this->publishedBlogSlugs(),
            'customHosts' => $this->customDomainHosts(),
            'baseDomain' => strtolower((string) _base_domain()),
        ];

        $columns = ['utm_source', 'utm_medium', 'referrer_domain', 'landing_path'];
        $exempt = array_fill_keys($columns, []);

        foreach ($rows as &$row) {
            foreach (['utm_source', 'utm_medium'] as $column) {
                [$row[$i[$column]], $isExempt] = $this->cleanUtm($row[$i[$column]]);
                if ($isExempt) {
                    $exempt[$column][$row[$i[$column]]] = true;
                }
            }

            [$host, $channel, $isExempt] = $this->classifyReferrer($row[$i['referrer_domain']], $context);
            $row[$i['referrer_domain']] = $host;
            $row[$i['referrer_channel']] = $channel;
            if ($isExempt) {
                $exempt['referrer_domain'][$host] = true;
            }

            [$row[$i['landing_path']], $isExempt] = $this->classifyLanding($row[$i['landing_path']], $context);
            if ($isExempt) {
                $exempt['landing_path'][$row[$i['landing_path']]] = true;
            }
        }
        unset($row);

        foreach ($columns as $column) {
            $counts = [];
            foreach ($rows as $row) {
                $value = $row[$i[$column]];
                if ($value !== null && ! isset($exempt[$column][$value])) {
                    $counts[$value] = ($counts[$value] ?? 0) + 1;
                }
            }
            foreach ($rows as &$row) {
                $value = $row[$i[$column]];
                if ($value !== null && isset($counts[$value]) && $counts[$value] < self::MIN_ATTRIBUTION_GROUP) {
                    $row[$i[$column]] = '(other)';
                }
            }
            unset($row);
        }

        return $rows;
    }

    /**
     * Lowercased and trimmed so "Newsletter" and "newsletter" are one group. A utm containing "@"
     * is somebody's address (or a tracking id built from one): redacted however many share it.
     *
     * @return array{0: ?string, 1: bool} [value, exempt from the group-size rule]
     */
    private function cleanUtm(?string $value): array
    {
        $value = $value === null ? '' : mb_strtolower(trim($value));
        if ($value === '') {
            return [null, false];
        }
        if (str_contains($value, '@')) {
            return ['(redacted)', true];
        }

        return [mb_substr($value, 0, 64), false];
    }

    /**
     * Canonical platforms: [host pattern, exported name, channel]. Specific hosts come before the
     * generic ones that would also match them (gemini.google.com before google.*). A platform name
     * identifies nobody, so these are exempt from the group-size rule - which is what keeps a rare
     * but telling referrer (one signup from perplexity.ai) visible instead of folded into "(other)".
     */
    private const REFERRER_PLATFORMS = [
        // AI assistants
        ['/(^|\.)(chatgpt\.com|chat\.openai\.com|openai\.com)$/', 'chatgpt', 'ai'],
        ['/(^|\.)claude\.ai$/', 'claude', 'ai'],
        ['/(^|\.)perplexity\.ai$/', 'perplexity', 'ai'],
        ['/^gemini\.google\.com$/', 'gemini', 'ai'],
        ['/(^|\.)copilot\.microsoft\.com$/', 'copilot', 'ai'],
        ['/(^|\.)deepseek\.com$/', 'deepseek', 'ai'],
        ['/(^|\.)grok\.com$/', 'grok', 'ai'],
        // Email and messaging, before the generic google.* below
        ['/^(mail\.google\.com|com\.google\.android\.gm)$/', 'gmail', 'email'],
        ['/(^|\.)(outlook\.live\.com|outlook\.office\.com|outlook\.office365\.com)$/', 'outlook', 'email'],
        ['/^mail\.yahoo\.com$/', 'yahoo-mail', 'email'],
        ['/^messages\.google\.com$/', 'google-messages', 'messaging'],
        ['/(^|\.)(whatsapp\.com|com\.whatsapp)$/', 'whatsapp', 'messaging'],
        ['/(^|\.)(t\.me|telegram\.org|org\.telegram\.messenger)$/', 'telegram', 'messaging'],
        // Back from a sign-in provider: the real referrer was lost on the way.
        ['/^accounts\.google\.com$/', 'google-signin', 'auth'],
        // Search
        ['/(^|\.)google\.(com?\.)?[a-z]{2,3}$|^com\.google\.android\.googlequicksearchbox$/', 'google', 'search'],
        ['/(^|\.)bing\.com$/', 'bing', 'search'],
        ['/(^|\.)duckduckgo\.com$/', 'duckduckgo', 'search'],
        ['/(^|\.)search\.yahoo\.com$|^yahoo\.com$/', 'yahoo', 'search'],
        ['/(^|\.)ecosia\.org$/', 'ecosia', 'search'],
        ['/(^|\.)yandex\.[a-z.]+$/', 'yandex', 'search'],
        ['/(^|\.)baidu\.com$/', 'baidu', 'search'],
        ['/^search\.brave\.com$/', 'brave', 'search'],
        ['/(^|\.)qwant\.com$/', 'qwant', 'search'],
        ['/(^|\.)startpage\.com$/', 'startpage', 'search'],
        ['/(^|\.)naver\.com$/', 'naver', 'search'],
        // Social
        ['/(^|\.)facebook\.com$|^com\.facebook\.katana$/', 'facebook', 'social'],
        ['/(^|\.)instagram\.com$|^com\.instagram\.android$/', 'instagram', 'social'],
        ['/(^|\.)linkedin\.com$|^lnkd\.in$|^com\.linkedin\.android$/', 'linkedin', 'social'],
        ['/^(t\.co|twitter\.com|x\.com|mobile\.twitter\.com)$/', 'x', 'social'],
        ['/(^|\.)youtube\.com$|^youtu\.be$/', 'youtube', 'social'],
        ['/(^|\.)tiktok\.com$/', 'tiktok', 'social'],
        ['/(^|\.)pinterest\.[a-z.]+$/', 'pinterest', 'social'],
        ['/(^|\.)threads\.net$/', 'threads', 'social'],
        ['/^bsky\.app$/', 'bluesky', 'social'],
        // Communities and directories
        ['/(^|\.)reddit\.com$|^com\.reddit\.frontpage$/', 'reddit', 'community'],
        ['/^news\.ycombinator\.com$/', 'hackernews', 'community'],
        ['/^github\.com$/', 'github', 'community'],
        ['/(^|\.)producthunt\.com$/', 'producthunt', 'community'],
        ['/(^|\.)alternativeto\.net$/', 'alternativeto', 'community'],
        ['/(^|\.)medium\.com$/', 'medium', 'community'],
        ['/^dev\.to$/', 'dev.to', 'community'],
        ['/(^|\.)stackoverflow\.com$/', 'stackoverflow', 'community'],
        ['/(^|\.)discord\.com$/', 'discord', 'community'],
    ];

    /**
     * @return array{0: ?string, 1: ?string, 2: bool} [host or placeholder, channel, exempt]
     */
    private function classifyReferrer(?string $host, array $context): array
    {
        if ($host === null || $host === '') {
            return [null, null, false];
        }

        if (filter_var(trim($host, '[]'), FILTER_VALIDATE_IP)) {
            return ['(ip)', 'other', true];
        }

        // A schedule's custom domain, or a tenant subdomain of ours: "referred by a schedule page"
        // is the channel worth seeing, the schedule's name is not ours to export. Both capture
        // paths drop same-base-domain referrers, so the subdomain arm mostly guards old rows.
        $base = $context['baseDomain'];
        if (isset($context['customHosts'][$host])) {
            return ['(schedule)', 'schedule', true];
        }
        if ($base !== '' && str_ends_with($host, '.'.$base)) {
            $label = substr($host, 0, -strlen('.'.$base));
            if (! in_array($label, ['www', 'app', 'blog'], true)) {
                return ['(schedule)', 'schedule', true];
            }
        }
        if ($base !== '' && ($host === $base || str_ends_with($host, '.'.$base))) {
            return [$host, 'own', true];
        }

        foreach (self::REFERRER_PLATFORMS as [$pattern, $name, $channel]) {
            if (preg_match($pattern, $host) === 1) {
                return [$name, $channel, true];
            }
        }

        return [$host, 'other', false];
    }

    /**
     * Normalise a stored landing page (a bare path with no leading slash, as both capture paths
     * store it, or a full URL in older rows) and decide whether it may be exported as itself.
     *
     * @return array{0: ?string, 1: bool} [path, exempt from the group-size rule]
     */
    private function classifyLanding(?string $value, array $context): array
    {
        if ($value === null || trim($value) === '') {
            return [null, false];
        }

        $path = preg_match('#^[a-z][a-z0-9+.-]*://#i', $value) === 1
            ? (string) (parse_url($value, PHP_URL_PATH) ?? '')
            : (string) preg_split('/[?#]/', $value, 2)[0];

        // %E2%81%A0 (a word joiner) and other invisible format characters arrive from links pasted
        // out of documents and chat apps; real data had /guest-add twice over because of one.
        $path = preg_replace('/\p{Cf}/u', '', rawurldecode($path)) ?? $path;
        $path = '/'.trim(mb_strtolower(trim($path)), '/');

        if (isset($context['marketing'][$path])) {
            return [$path, true];
        }

        // Blog posts are served from blog.<domain>/<slug>, which records only "<slug>".
        $slug = ltrim($path, '/');
        if ($slug !== '' && ! str_contains($slug, '/') && isset($context['blog'][$slug])) {
            return ['/blog/'.$slug, true];
        }

        // A credential in the path survives no count: a ticket link forwarded to three friends who
        // all signed up would otherwise clear the group-size rule with its secret intact.
        $segments = array_map(fn ($segment) => $this->isTokenSegment($segment) ? ':token' : $segment, explode('/', $path));

        return [mb_substr(implode('/', $segments), 0, 120), false];
    }

    /**
     * A random-looking path segment: long, letters and digits, no hyphen (event and page slugs
     * have hyphens; Str::random() secrets do not) - or very long with a digit, which also catches
     * a UUID.
     */
    private function isTokenSegment(string $segment): bool
    {
        $len = strlen($segment);

        return ($len >= 16 && preg_match('/^[a-z0-9_]+$/i', $segment) === 1
                && preg_match('/\d/', $segment) === 1 && preg_match('/[a-z]/i', $segment) === 1)
            || ($len >= 32 && preg_match('/^[a-z0-9_-]+$/i', $segment) === 1 && preg_match('/\d/', $segment) === 1);
    }

    /** @return array<string, true> */
    private function publishedBlogSlugs(): array
    {
        return DB::table('blog_posts')->where('is_published', true)->whereNotNull('slug')
            ->pluck('slug')
            ->mapWithKeys(fn ($slug) => [mb_strtolower((string) $slug) => true])
            ->all();
    }

    /** Every host a schedule serves from on a custom domain. @return array<string, true> */
    private function customDomainHosts(): array
    {
        return DB::table('roles')->whereNotNull('custom_domain')->where('custom_domain', '!=', '')
            ->pluck('custom_domain')
            ->map(fn ($url) => $this->hostOf((string) $url))
            ->filter()
            ->mapWithKeys(fn ($host) => [$host => true])
            ->all();
    }

    /**
     * Strip a referrer down to its host. The raw column can carry query strings holding
     * tokens or email addresses, so the full value must never reach the export.
     */
    private function hostOf(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $host = parse_url($url, PHP_URL_HOST);

        return $host ? preg_replace('/^www\./', '', strtolower($host)) : null;
    }

    /**
     * One row per real schedule, with every metric coming from a pre-aggregated map keyed
     * by role_id. A per-row query here would be thousands of round trips and would time out.
     */
    private function scheduleRows(array $months): array
    {
        $base = $this->excludeDemoRoles(
            Role::query()->whereNotNull('user_id')->where('is_deleted', false)
        );

        $total = (clone $base)->count();

        // recent_total powers retention. A schedule is "still publishing" only if it has
        // touched an event lately - see retentionFrom(), which used to accept any page view
        // and therefore reported ~100% retention forever.
        $recentCutoff = now()->copy()->subDays(90)->toDateTimeString();

        // LISTED events: ones the schedule created, or accepted onto its page - the same rule as
        // claims below and SendActivationNudges::listedEvents(). A pending or declined request
        // from another schedule is not this schedule's event, and counting it made a venue that
        // ignores its inbox look busy.
        //
        // recent_total counts events CREATED in the window. It read updated_at, which system
        // writes bump (Translate::markChecked() touches every event it checks, and calendar sync
        // rewrites rows), so a schedule nobody had opened in a year could read as active.
        $events = DB::table('event_role')
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->where(fn ($q) => $q->where('event_role.is_accepted', true)
                ->orWhereColumn('events.creator_role_id', 'event_role.role_id'))
            ->selectRaw('event_role.role_id, COUNT(*) as total, '
                .'SUM(CASE WHEN events.is_draft = 0 AND events.is_private = 0 AND events.is_internal = 0 THEN 1 ELSE 0 END) as public_total, '
                .'SUM(CASE WHEN events.created_at >= ? THEN 1 ELSE 0 END) as recent_total', [$recentCutoff])
            ->groupBy('event_role.role_id')
            ->get()->keyBy('role_id');

        // paid_c is the commercial signal. COUNT(*) alone counts free RSVP/registration types
        // too, so it says nothing about whether a schedule takes money - which made it useless
        // for sizing anything gated on paid ticketing.
        //
        // Credited to the selling schedule and shaped like Event::tickets() (no deleted types, no
        // add-ons), so it means the same thing as the signup rows' saved_ticket and the funnel's
        // ticket stages. It used to count add-ons and credit every schedule on the event.
        $ticketTypes = $this->attributeToSeller(
            DB::table('tickets')->join('events', 'events.id', '=', 'tickets.event_id')
        )
            ->where('tickets.is_deleted', false)
            ->where('tickets.is_addon', false)
            ->groupBy(DB::raw(self::SELLER))
            ->selectRaw(self::SELLER.' as role_id, COUNT(*) as c, '
                .'SUM(CASE WHEN tickets.price > 0 THEN 1 ELSE 0 END) as paid_c')
            ->get()->keyBy('role_id');

        $paidByMonth = $this->paidTicketsByRoleMonth();
        $paid90d = $this->paidTickets90dByRole();
        $gmvByMonth = $this->gmvByRoleMonth();

        // Paying = billing (RecurringRevenue's definition), not the tier - see the schema 8 note.
        $billing = RecurringRevenue::billingRoleIds();

        // actualPlanTier() reads $role->subscription('default'), which lazy-loads the relation per
        // row, and cursor() cannot eager-load - one query per schedule. Preloaded and set below,
        // in the relation's own order (newest first), so the tier logic is untouched.
        $subscriptionsByRole = Cashier::$subscriptionModel::query()
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('role_id');

        $views = AnalyticsDaily::query()
            ->where('date', '>=', now()->copy()->subDays(90)->toDateString())
            ->selectRaw('role_id, SUM(desktop_views + mobile_views + tablet_views + unknown_views) as v')
            ->groupBy('role_id')
            ->get()->keyBy('role_id');

        $followers = DB::table('role_user')->where('level', 'follower')
            ->selectRaw('role_id, COUNT(*) as c')->groupBy('role_id')
            ->get()->keyBy('role_id');

        // Per-event interest capture, WINDOWED to 90 days so it divides cleanly by views_90d.
        //
        // `followers` above is deliberately left as it is - all-time, and the only capture number
        // the export has ever carried - but that is exactly why it cannot measure this feature.
        // Dividing an all-time count by a 90-day view count is a stock over a flow: it rises with
        // platform age whatever ships. Worse, it would not move at all here: checkout capture
        // writes role_subscribers and never calls linkAccount(), so no role_user pivot appears.
        //
        // Two columns, because they answer different questions: how many addresses arrived in the
        // window (the rate, against views_90d), and how many the schedule holds now (the asset).
        $interestRecent = DB::table('event_interests')
            ->join('event_role', 'event_role.event_id', '=', 'event_interests.event_id')
            ->whereNotNull('event_interests.confirmed_at')
            ->where('event_interests.created_at', '>=', now()->copy()->subDays(90))
            ->selectRaw('event_role.role_id as role_id, COUNT(DISTINCT event_interests.email) as c')
            ->groupBy('event_role.role_id')
            ->get()->keyBy('role_id');

        $interestTotal = DB::table('event_interests')
            ->join('event_role', 'event_role.event_id', '=', 'event_interests.event_id')
            ->whereNotNull('event_interests.confirmed_at')
            ->selectRaw('event_role.role_id as role_id, COUNT(DISTINCT event_interests.email) as c')
            ->groupBy('event_role.role_id')
            ->get()->keyBy('role_id');

        // Account-less audience rows, which the export has never carried either - so a fully
        // successful checkout-capture change would have shown up as a flat line.
        $subscribers = DB::table('role_subscribers')
            ->whereNotNull('confirmed_at')
            ->selectRaw('role_id, COUNT(*) as c')->groupBy('role_id')
            ->get()->keyBy('role_id');

        // A declined first checkout leaves an `incomplete` row: that is not an upgrade, and dating
        // days_to_upgrade from it made people look faster to pay than they were. The same
        // exclusion as the funnel's subscribed stage, which is also what ever_subscribed means.
        $firstSub = DB::table('subscriptions')
            ->whereNotIn('stripe_status', ['incomplete', 'incomplete_expired'])
            ->selectRaw('role_id, MIN(created_at) as first_at')->groupBy('role_id')
            ->get()->keyBy('role_id');

        $apptTypes = DB::table('appointment_types')->where('is_deleted', false)
            ->selectRaw('role_id, COUNT(*) as c')->groupBy('role_id')
            ->get()->keyBy('role_id');

        $photos = DB::table('event_photos')
            ->join('event_role', 'event_role.event_id', '=', 'event_photos.event_id')
            ->selectRaw('event_role.role_id, COUNT(*) as c')
            ->groupBy('event_role.role_id')
            ->get()->keyBy('role_id');

        $newsletterEmails = DB::table('usage_daily')
            ->where('operation', 'email_newsletter')
            ->where('date', '>=', now()->copy()->startOfMonth()->toDateString())
            ->selectRaw('role_id, SUM(count) as c')->groupBy('role_id')
            ->get()->keyBy('role_id');

        $rows = [];
        foreach ((clone $base)->orderByDesc('id')->limit($this->rowCap())->cursor() as $r) {
            $r->setRelation('subscriptions', $subscriptionsByRole[$r->id] ?? new EloquentCollection);

            $perMonth = [];
            foreach ($months as $m) {
                $perMonth[] = (int) ($paidByMonth[$r->id][$m] ?? 0);
            }

            // The month a schedule first took money. The single best predictor in this export
            // of whether it will ever pay for a plan, and nothing recorded it before.
            $paidMonths = array_keys($paidByMonth[$r->id] ?? []);
            $firstPaidMonth = $paidMonths ? min($paidMonths) : null;

            // One currency or none. Summing across currencies would produce a number that is
            // not an amount of anything, so a mixed-currency schedule reports null and is
            // counted through paid_tickets_recent instead.
            $currencies = array_keys($gmvByMonth[$r->id] ?? []);
            $gmvCurrency = count($currencies) === 1 ? $currencies[0] : null;
            $gmvPerMonth = null;
            if ($gmvCurrency !== null) {
                $gmvPerMonth = [];
                foreach ($months as $m) {
                    $gmvPerMonth[] = (float) ($gmvByMonth[$r->id][$gmvCurrency][$m] ?? 0);
                }
            }

            // Every currency separately, so a mixed-currency seller can still be sized. The
            // null above hid one of the four paying sellers entirely. gmv_currency/gmv_recent
            // keep their shape for anything already reading them.
            $gmvByCurrencyRecent = null;
            if ($currencies) {
                $gmvByCurrencyRecent = [];
                foreach ($currencies as $currency) {
                    $gmvByCurrencyRecent[($currency === null || $currency === '') ? 'unknown' : $currency] = array_map(
                        fn ($m) => (float) ($gmvByMonth[$r->id][$currency][$m] ?? 0),
                        $months
                    );
                }
            }

            $subAt = $firstSub[$r->id]->first_at ?? null;

            $rows[] = [
                $this->hashId('s', $r->id),
                $this->hashId('u', $r->user_id),
                $r->created_at?->format('Y-m'),
                $r->type,
                $r->actualPlanTier(),
                $r->plan_source,
                isset($billing[$r->id]),
                $subAt !== null,
                (int) ($events[$r->id]->total ?? 0),
                (int) ($events[$r->id]->public_total ?? 0),
                (int) ($events[$r->id]->recent_total ?? 0),
                (int) ($ticketTypes[$r->id]->c ?? 0),
                (int) ($ticketTypes[$r->id]->paid_c ?? 0),
                array_sum($paidByMonth[$r->id] ?? []),
                (int) ($paid90d[$r->id] ?? 0),
                $perMonth,
                $firstPaidMonth,
                $gmvCurrency,
                $gmvPerMonth,
                $gmvByCurrencyRecent,
                (int) ($views[$r->id]->v ?? 0),
                (int) ($followers[$r->id]->c ?? 0),
                (int) ($subscribers[$r->id]->c ?? 0),
                (int) ($interestRecent[$r->id]->c ?? 0),
                (int) ($interestTotal[$r->id]->c ?? 0),
                (int) ($apptTypes[$r->id]->c ?? 0),
                (int) ($photos[$r->id]->c ?? 0),
                (int) ($newsletterEmails[$r->id]->c ?? 0),
                $this->featuresOf($r),
                ($subAt && $r->created_at) ? max(0, $r->created_at->diffInDays(Carbon::parse($subAt))) : null,
            ];
        }

        return [
            'columns' => ['sid', 'uid', 'created_month', 'type', 'plan', 'plan_source',
                'billing', 'ever_subscribed',
                'events_total', 'events_public', 'events_recent_90d', 'ticket_types',
                'paid_ticket_types', 'paid_tickets_total', 'paid_tickets_90d',
                'paid_tickets_recent', 'first_paid_sale_month', 'gmv_currency', 'gmv_recent',
                'gmv_recent_by_currency',
                'views_90d', 'followers', 'subscribers', 'interests_90d', 'interests_total',
                'appointment_types',
                'photos', 'newsletter_emails_this_month', 'features', 'days_to_upgrade'],
            'rows' => $rows,
            'total' => $total,
            'truncated' => $total > $this->rowCap(),
        ];
    }

    /**
     * Paid tickets per schedule per calendar month. Counts the same shape the paid-ticket gate
     * and its 2026_09_20 grandfather backfill do: paid, not deleted, not an RSVP or bulk import,
     * not an add-on, priced above zero, and never an appointment booking. Windowed on
     * sales.paid_at, never created_at, or cash sales escape entirely.
     */
    private function paidTicketsByRoleMonth(): array
    {
        $rows = $this->paidTicketLines()
            // Group by the expression, never the select alias - an alias binds to a
            // same-named real column and raises 1055 under ONLY_FULL_GROUP_BY.
            ->groupBy(DB::raw(self::SELLER), DB::raw("DATE_FORMAT(sales.paid_at, '%Y-%m')"))
            ->selectRaw(self::SELLER." as role_id, DATE_FORMAT(sales.paid_at, '%Y-%m') as ym, SUM(sale_tickets.quantity) as qty")
            ->get();

        $map = [];
        foreach ($rows as $row) {
            if ($row->role_id !== null) {
                $map[$row->role_id][$row->ym] = (int) $row->qty;
            }
        }

        return $map;
    }

    /**
     * Paid tickets per schedule over the trailing 90 days by paid_at - what retention's
     * active_recently has always claimed to use. It used to read the six calendar months of
     * paid_tickets_recent instead, so a sale five months ago kept a schedule "active".
     *
     * @return array<int, int>
     */
    private function paidTickets90dByRole(): array
    {
        return $this->paidTicketLines()
            ->where('sales.paid_at', '>=', now()->copy()->subDays(90))
            ->groupBy(DB::raw(self::SELLER))
            ->selectRaw(self::SELLER.' as role_id, SUM(sale_tickets.quantity) as qty')
            ->get()
            ->filter(fn ($row) => $row->role_id !== null)
            ->mapWithKeys(fn ($row) => [(int) $row->role_id => (int) $row->qty])
            ->all();
    }

    /**
     * The paid-ticket shape the paid-ticket gate and its 2026_09_20 grandfather backfill count:
     * paid, not deleted, not an RSVP or bulk import, not an add-on, priced above zero, never an
     * appointment booking - attributed to the selling schedule (see attributeToSeller()).
     */
    private function paidTicketLines()
    {
        return $this->attributeToSeller(
            DB::table('sales')
                ->join('sale_tickets', 'sale_tickets.sale_id', '=', 'sales.id')
                ->join('tickets', 'tickets.id', '=', 'sale_tickets.ticket_id')
                ->join('events', 'events.id', '=', 'sales.event_id')
        )
            ->where('sales.status', 'paid')
            ->where('sales.is_deleted', false)
            ->whereNotIn('sales.payment_method', ['rsvp', 'import'])
            ->whereNotNull('sales.paid_at')
            ->where('tickets.is_addon', false)
            ->where('tickets.price', '>', 0)
            ->whereNull('events.appointment_type_id');
    }

    /**
     * The schedule a sale, ticket type or revenue belongs to: the one that CREATED the event
     * (events.creator_role_id), which is the seller - its gateway took the money.
     *
     * Every one of these used to join event_role, which credited the same sale to every schedule
     * on the event: the venue, each talent playing there, and any curator listing it. So a venue
     * that never sold a ticket read as a seller, per-schedule GMV summed to more than
     * gmv_by_currency, and first_paid_sale_month, ever_sold_paid and with_paid_sale overstated the
     * one cliff this export exists to measure. ticketTrials() already keyed on the creator.
     *
     * Legacy events with no creator_role_id fall back to every listed schedule, the old behaviour,
     * through a LEFT JOIN that only matches them: the join condition carries the null test, so an
     * event with a creator contributes exactly one row and never duplicates.
     */
    private function attributeToSeller($query)
    {
        return $query->leftJoin('event_role', function ($join) {
            $join->on('event_role.event_id', '=', 'events.id')
                ->whereNull('events.creator_role_id');
        });
    }

    /** The seller expression attributeToSeller() makes available. */
    private const SELLER = 'COALESCE(events.creator_role_id, event_role.role_id)';

    /**
     * Money taken per schedule per month, in the schedule's own currency.
     *
     * The export already reports gmv_by_currency, but only as a platform total, so nothing
     * connected the money to the schedule that earned it - and the whole ticketing business
     * turns out to be four accounts. Sizing that, or noticing one of them stop, needs the join.
     *
     * Per currency because sales has no currency column of its own: the amount means whatever
     * events.ticket_currency_code says. A schedule that has taken money in more than one
     * currency therefore gets null rather than a sum of unlike things - see the row builder.
     */
    private function gmvByRoleMonth(): array
    {
        $rows = $this->attributeToSeller(
            DB::table('sales')->join('events', 'events.id', '=', 'sales.event_id')
        )
            ->where('sales.status', 'paid')
            ->where('sales.is_deleted', false)
            ->whereNotIn('sales.payment_method', ['rsvp', 'import'])
            ->whereNotNull('sales.paid_at')
            // Group by the expression, never the select alias - an alias binds to a
            // same-named real column and raises 1055 under ONLY_FULL_GROUP_BY.
            ->groupBy(DB::raw(self::SELLER), 'events.ticket_currency_code', DB::raw("DATE_FORMAT(sales.paid_at, '%Y-%m')"))
            ->selectRaw(self::SELLER.' as role_id, events.ticket_currency_code as currency, '
                ."DATE_FORMAT(sales.paid_at, '%Y-%m') as ym, SUM(sales.payment_amount) as amount")
            ->get();

        $map = [];
        foreach ($rows as $row) {
            if ($row->role_id !== null) {
                $map[$row->role_id][$row->currency][$row->ym] = round((float) $row->amount, 2);
            }
        }

        return $map;
    }

    /** Feature adoption flags read straight off the schedule row. */
    private function featuresOf(Role $r): array
    {
        $flags = [];
        foreach ([
            'gcal' => $r->google_calendar_id,
            'mscal' => $r->microsoft_sync_token,
            'caldav' => $r->caldav_settings,
            'custom_domain' => $r->custom_domain,
            'custom_css' => $r->custom_css,
            'custom_fields' => $r->custom_fields,
            'banner' => $r->banner_enabled,
            'feedback' => $r->feedback_enabled,
            'carpool' => $r->carpool_enabled,
            'gift_cards' => $r->gift_cards_enabled,
            'accept_requests' => $r->accept_requests,
            'sponsors' => $r->sponsor_logos,
            'own_smtp' => $r->email_settings,
            // The "Notify me" card is opt-in, so without this a zero interest rate cannot tell
            // "switched off" from "on and not converting". The sign-up panel is on by default,
            // so it is the owners who turned it OFF that are worth a flag.
            'event_interest' => $r->show_event_interest,
            'no_subscribe_panel' => $r->show_subscribe_panel === false,
        ] as $key => $value) {
            if (! empty($value)) {
                $flags[] = $key;
            }
        }

        return $flags;
    }

    /** Absolute counts for the extended activation chain, derived from the signup rows. */
    private function activationFrom(array $signups): array
    {
        $i = array_flip($signups['columns']);
        $out = ['accounts' => 0, 'reached_schedule_form' => 0, 'saved_schedule' => 0, 'saved_event' => 0];
        foreach ($signups['rows'] as $row) {
            $out['accounts']++;
            $out['reached_schedule_form'] += $row[$i['reached_schedule_form']] ? 1 : 0;
            $out['saved_schedule'] += $row[$i['saved_schedule']] ? 1 : 0;
            $out['saved_event'] += $row[$i['saved_event']] ? 1 : 0;
        }

        return $out;
    }

    /** Activation rates by signup month. */
    private function cohortsFrom(array $signups): array
    {
        $i = array_flip($signups['columns']);
        $by = [];
        foreach ($signups['rows'] as $row) {
            $m = $row[$i['created_month']] ?? 'unknown';
            $by[$m] ??= ['month' => $m, 'accounts' => 0, 'reached_schedule_form' => 0,
                'saved_schedule' => 0, 'saved_event' => 0, 'days_to_first_schedule' => []];
            $by[$m]['accounts']++;
            $by[$m]['reached_schedule_form'] += $row[$i['reached_schedule_form']] ? 1 : 0;
            $by[$m]['saved_schedule'] += $row[$i['saved_schedule']] ? 1 : 0;
            $by[$m]['saved_event'] += $row[$i['saved_event']] ? 1 : 0;
            if ($row[$i['days_to_first_schedule']] !== null) {
                $by[$m]['days_to_first_schedule'][] = $row[$i['days_to_first_schedule']];
            }
        }
        ksort($by);

        return array_values(array_map(function ($c) {
            $c['median_days_to_first_schedule'] = $this->median($c['days_to_first_schedule']);
            unset($c['days_to_first_schedule']);

            return $c;
        }, $by));
    }

    /** Which channels and landing pages produce signups that actually activate. */
    private function acquisitionFrom(array $signups): array
    {
        return [
            'by_utm_source' => $this->groupActivation($signups, 'utm_source'),
            'by_utm_medium' => $this->groupActivation($signups, 'utm_medium'),
            'by_referrer_domain' => $this->groupActivation($signups, 'referrer_domain'),
            'by_referrer_channel' => $this->groupActivation($signups, 'referrer_channel'),
            'by_landing_path' => $this->groupActivation($signups, 'landing_path'),
            'by_auth' => $this->groupActivation($signups, 'auth'),
        ];
    }

    /** Signup segments: intent, and schedule type for those who got that far. */
    private function segmentsFrom(array $signups, array $schedules): array
    {
        $si = array_flip($schedules['columns']);
        $byType = [];
        foreach ($schedules['rows'] as $row) {
            $t = $row[$si['type']] ?? 'unknown';
            $byType[$t] ??= ['key' => $t, 'schedules' => 0, 'with_event' => 0, 'with_public_event' => 0,
                'with_ticket_type' => 0, 'with_paid_ticket_type' => 0, 'with_paid_sale' => 0,
                'paid_plan' => 0, 'billing' => 0];
            $byType[$t]['schedules']++;
            $byType[$t]['with_event'] += $row[$si['events_total']] > 0 ? 1 : 0;
            $byType[$t]['with_public_event'] += $row[$si['events_public']] > 0 ? 1 : 0;
            // with_ticket_type includes free RSVP/registration types; only with_paid_ticket_type
            // says the schedule intends to take money.
            $byType[$t]['with_ticket_type'] += $row[$si['ticket_types']] > 0 ? 1 : 0;
            $byType[$t]['with_paid_ticket_type'] += $row[$si['paid_ticket_types']] > 0 ? 1 : 0;
            $byType[$t]['with_paid_sale'] += $row[$si['paid_tickets_total']] > 0 ? 1 : 0;
            // paid_plan is the TIER (grants, credits and trials included); billing is who pays.
            $byType[$t]['paid_plan'] += $row[$si['plan']] !== 'free' ? 1 : 0;
            $byType[$t]['billing'] += $row[$si['billing']] ? 1 : 0;
        }
        ksort($byType);

        return [
            'by_signup_intent' => $this->groupActivation($signups, 'signup_intent'),
            'by_schedule_type' => array_values($byType),
        ];
    }

    /**
     * Count + activation rates for one signup column, biggest group first.
     *
     * The ticket stages are here rather than only in funnelData() because activation stopping at
     * saved_event cannot answer the question the export exists to answer: selling is what
     * produces a payer, so a breakdown that ends at "made an event" ranks channels and landing
     * pages by an outcome that does not predict revenue.
     */
    private function groupActivation(array $signups, string $column): array
    {
        $i = array_flip($signups['columns']);
        $by = [];
        foreach ($signups['rows'] as $row) {
            $k = $row[$i[$column]];
            $k = ($k === null || $k === '') ? '(none)' : (string) $k;
            $by[$k] ??= ['key' => $k, 'signups' => 0, 'saved_schedule' => 0, 'saved_event' => 0,
                'saved_ticket' => 0, 'saved_paid_ticket' => 0];
            $by[$k]['signups']++;
            $by[$k]['saved_schedule'] += $row[$i['saved_schedule']] ? 1 : 0;
            $by[$k]['saved_event'] += $row[$i['saved_event']] ? 1 : 0;
            $by[$k]['saved_ticket'] += $row[$i['saved_ticket']] ? 1 : 0;
            $by[$k]['saved_paid_ticket'] += $row[$i['saved_paid_ticket']] ? 1 : 0;
        }
        $out = array_values($by);
        usort($out, fn ($a, $b) => $b['signups'] <=> $a['signups']);

        if (count($out) <= self::ROLLUP_TOP) {
            return $out;
        }

        // The long tail folded into one row rather than dropped, so the column's signups always sum
        // to the row total - a top-N cut with no remainder reads as "this is everyone".
        $rest = ['key' => '(rest)', 'signups' => 0, 'saved_schedule' => 0, 'saved_event' => 0,
            'saved_ticket' => 0, 'saved_paid_ticket' => 0];
        $tail = array_slice($out, self::ROLLUP_TOP);
        foreach ($tail as $group) {
            foreach (['signups', 'saved_schedule', 'saved_event', 'saved_ticket', 'saved_paid_ticket'] as $metric) {
                $rest[$metric] += $group[$metric];
            }
        }
        $rest['groups'] = count($tail);

        return [...array_slice($out, 0, self::ROLLUP_TOP), $rest];
    }

    /**
     * Whether the free plan's limits ever actually bind. If the distribution piles up on
     * zero, the upgrade trigger never fires and the packaging boundary is the problem,
     * not the price.
     */
    private function freePressureFrom(array $schedules): array
    {
        $i = array_flip($schedules['columns']);
        $buckets = ['0' => 0, '1-5' => 0, '6-15' => 0, '16+' => 0];
        $newsletter = ['0' => 0, '1-9' => 0, 'at_or_over_cap' => 0];
        $appt = ['0' => 0, '1' => 0, '2+' => 0];
        $photos = ['0' => 0, '1-24' => 0, 'at_or_over_cap' => 0];
        $freeCount = 0;
        $everSoldPaid = 0;

        foreach ($schedules['rows'] as $row) {
            if ($row[$i['plan']] !== 'free') {
                continue;
            }
            $freeCount++;

            $peak = max($row[$i['paid_tickets_recent']] ?: [0]);
            if ($peak >= 16) {
                $buckets['16+']++;
            } elseif ($peak >= 6) {
                $buckets['6-15']++;
            } elseif ($peak >= 1) {
                $buckets['1-5']++;
            } else {
                $buckets['0']++;
            }

            // EVER, from the all-time first sale month. It used to be "sold in one of the six
            // months above", so a free schedule that sold last year read as never having sold.
            if ($row[$i['first_paid_sale_month']] !== null) {
                $everSoldPaid++;
            }

            $n = (int) $row[$i['newsletter_emails_this_month']];
            $newsletter[$n === 0 ? '0' : ($n >= 10 ? 'at_or_over_cap' : '1-9')]++;

            // Split at the free plan's one-type allowance, so the population pressed against the
            // limit is legible. Labelled by COUNT rather than by cap state, unlike the newsletter and
            // photo columns above: the appointment_types column is filtered on is_deleted only, while
            // the allowance (Role::appointmentTypeCount()) also requires is_active, so a schedule
            // with one live type and one paused draft reads as 2 here while nothing is clamped at
            // all. A label like "over_cap" would assert a plan state this number cannot support.
            // Do not rename these without filtering the column - and note it is also exported as-is.
            $a = (int) $row[$i['appointment_types']];
            $appt[$a === 0 ? '0' : ($a === 1 ? '1' : '2+')]++;

            $p = (int) $row[$i['photos']];
            $photos[$p === 0 ? '0' : ($p >= 25 ? 'at_or_over_cap' : '1-24')]++;
        }

        return [
            'free_schedules' => $freeCount,
            'peak_month_paid_tickets' => $buckets,
            'ever_sold_paid' => $everSoldPaid,
            'newsletter_emails_this_month' => $newsletter,
            'appointment_types' => $appt,
            'photos' => $photos,
        ];
    }

    /**
     * What paying schedules do that the rest do not. "paid" is BILLING (a live, non-trialing
     * subscription); "free" is everyone else, comps and trials included. Splitting on the tier
     * instead put ~350 admin grants on the paid side, so this compared comped schedules - mostly
     * dormant - with free ones and said nothing about customers.
     */
    private function payersVsFreeFrom(array $schedules): array
    {
        $i = array_flip($schedules['columns']);
        $acc = ['paid' => ['n' => 0], 'free' => ['n' => 0]];

        foreach ($schedules['rows'] as $row) {
            $side = $row[$i['billing']] ? 'paid' : 'free';
            $acc[$side]['n']++;
            foreach ($row[$i['features']] as $f) {
                $acc[$side][$f] = ($acc[$side][$f] ?? 0) + 1;
            }
            foreach (['events_total', 'events_public', 'events_recent_90d', 'ticket_types',
                'paid_ticket_types', 'paid_tickets_total',
                'views_90d', 'followers'] as $metric) {
                $acc[$side]['sum_'.$metric] = ($acc[$side]['sum_'.$metric] ?? 0) + (int) $row[$i[$metric]];
            }
        }

        $shape = function (array $side) {
            $n = max(1, $side['n']);
            $out = ['schedules' => $side['n'], 'features' => [], 'averages' => []];
            foreach ($side as $k => $v) {
                if ($k === 'n') {
                    continue;
                }
                if (str_starts_with($k, 'sum_')) {
                    $out['averages'][substr($k, 4)] = round($v / $n, 2);
                } else {
                    $out['features'][$k] = round($v / $n * 100, 1);
                }
            }

            return $out;
        };

        return ['paid' => $shape($acc['paid']), 'free' => $shape($acc['free'])];
    }

    /**
     * Subscription and revenue shape. Revenue is keyed by currency because sales has no
     * currency column - it lives on events.ticket_currency_code, and summing across it
     * would produce a meaningless number.
     */
    private function monetization(): array
    {
        $tiers = ['free' => 0, 'pro' => 0, 'enterprise' => 0];
        $bySource = [];
        $byTerm = [];
        $daysToUpgrade = [];

        // config(), NOT PlatformPricing - do not "fix" this. list_prices reports what subscribers
        // are billed, beside an MRR built from the same config through PlanPriceUtils::amountFor().
        // Sourcing these from the admin-settable amounts would let a marketing change restate
        // revenue that was already booked. MarketingPriceTest pins this in both directions.
        $monthly = (float) config('services.stripe_platform.price_monthly_amount', 5);
        $yearly = (float) config('services.stripe_platform.price_yearly_amount', 50);
        $entMonthly = (float) config('services.stripe_platform.enterprise_price_monthly_amount', 15);
        $entYearly = (float) config('services.stripe_platform.enterprise_price_yearly_amount', 150);

        // lazy(), not get(): this is every schedule on the install, and actualPlanTier()
        // needs a hydrated model, so the whole table would otherwise sit in memory at once.
        $roles = $this->excludeDemoRoles(
            Role::query()->whereNotNull('user_id')->where('is_deleted', false)
        )->with('subscriptions')->lazy();

        $billing = RecurringRevenue::billingRoleIds();

        foreach ($roles as $role) {
            // Before the free-tier skip: everyone who ever upgraded, including those who have
            // since churned back to free. Measuring only current payers made the median a
            // survivor statistic. A declined first checkout (incomplete) is not an upgrade.
            $first = $role->subscriptions
                ->whereNotIn('stripe_status', ['incomplete', 'incomplete_expired'])
                ->min('created_at');
            if ($first && $role->created_at) {
                $daysToUpgrade[] = max(0, $role->created_at->diffInDays(Carbon::parse($first)));
            }

            $tier = $role->actualPlanTier();
            $tiers[$tier] = ($tiers[$tier] ?? 0) + 1;
            if ($tier === 'free') {
                continue;
            }
            // A null plan_source used to be labelled 'stripe' whatever was behind the tier. Only a
            // billing subscription is; a Stripe trial, a generic trial and a legacy plan_expires
            // row are named for what they are, so 'stripe' here matches billing_subscriptions.
            $source = $role->plan_source ?? match (true) {
                isset($billing[$role->id]) => 'stripe',
                $role->subscriptions->contains('stripe_status', 'trialing') => 'stripe_trial',
                $role->onGenericTrial() => 'trial',
                default => 'legacy',
            };
            $bySource[$source] = ($bySource[$source] ?? 0) + 1;
            $byTerm[$role->plan_term ?? 'unknown'] = ($byTerm[$role->plan_term ?? 'unknown'] ?? 0) + 1;
        }

        // The same population as every other section: no demo or deleted schedules.
        $statuses = $this->excludeDemoRoles(
            DB::table('subscriptions')
                ->join('roles', 'roles.id', '=', 'subscriptions.role_id')
                ->where('roles.is_deleted', false)
        )
            ->selectRaw('subscriptions.stripe_status, COUNT(*) as c')
            ->groupBy('subscriptions.stripe_status')
            ->pluck('c', 'stripe_status');

        // The same figure the dashboard reports as ARR, from the same class - see RecurringRevenue
        // for what it counts. ARPU divides by the subscriptions that figure is made of, not by the
        // Pro + Enterprise counts above, which include admin grants and referral credits that pay
        // nothing - and not by the ones on an unrecognized price either, which it books at zero.
        $revenue = RecurringRevenue::summary();
        $priced = $revenue['billing_count'] - $revenue['unrecognized_count'];

        return [
            'plan_counts' => $tiers,
            'by_plan_source' => $bySource,
            'by_plan_term' => $byTerm,
            'subscription_status' => $statuses,
            // Not *_usd: these are in the platform currency, whatever the operator set.
            'mrr' => $revenue['mrr'],
            'arr' => $revenue['arr'],
            // From the exact annual sum, not the rounded mrr, so the cent is rounded once.
            'arpu' => $priced > 0 ? round($revenue['arr'] / 12 / $priced, 2) : null,
            'billing_subscriptions' => $revenue['billing_count'],
            'trialing_subscriptions' => $revenue['trialing_count'],
            'unrecognized_price_subscriptions' => $revenue['unrecognized_count'],
            'list_prices' => ['pro_monthly' => $monthly, 'pro_yearly' => $yearly,
                'enterprise_monthly' => $entMonthly, 'enterprise_yearly' => $entYearly],
            'median_days_to_upgrade' => $this->median($daysToUpgrade),
            'gmv_by_currency' => $this->gmvByCurrency(),
            'ticket_trials' => $this->ticketTrials(),
        ];
    }

    /**
     * What app:send-activation-nudges has sent, per key, from its claim table. An empty result
     * means it has never sent anything on this install - on a scheduled install, that the
     * scheduler is not reaching it.
     */
    private function nudges(): array
    {
        return DB::table('schedule_nudges')
            ->selectRaw('nudge_key, COUNT(*) as total, SUM(created_at >= ?) as last_7_days, MAX(created_at) as last_sent_at', [now()->subDays(7)])
            ->groupBy('nudge_key')
            ->orderBy('nudge_key')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->nudge_key => [
                'total' => (int) $row->total,
                'last_7_days' => (int) $row->last_7_days,
                'last_sent_at' => $row->last_sent_at ? Carbon::parse($row->last_sent_at)->toIso8601String() : null,
            ]])
            ->all();
    }

    /**
     * Why subscribers leave, from subscription_cancellations: one row per cancelled subscription,
     * whichever cancel path saw it first. A cancel resumed during its grace period is counted
     * apart, not as churn. Demo schedules never subscribe, so nothing is excluded here.
     */
    private function churn(): array
    {
        $rows = DB::table('subscription_cancellations');

        // A transfer cancels the old owner's subscription, but the schedule changed hands rather
        // than left, so it is counted apart.
        $churned = (clone $rows)->whereNull('resumed_at')
            ->whereNotIn('source', \App\Models\SubscriptionCancellation::NOT_CHURN_SOURCES);

        return [
            'cancelled' => (clone $churned)->count(),
            'transferred' => (clone $rows)->whereIn('source', \App\Models\SubscriptionCancellation::NOT_CHURN_SOURCES)->count(),
            'resumed' => (clone $rows)->whereNotNull('resumed_at')->count(),
            'with_reason' => (clone $churned)->whereNotNull('reason')->count(),
            'by_reason' => (clone $churned)->selectRaw("COALESCE(reason, 'none') as k, COUNT(*) as c")
                ->groupBy('k')->orderByDesc('c')->pluck('c', 'k'),
            'by_source' => (clone $churned)->selectRaw('source as k, COUNT(*) as c')
                ->groupBy('k')->orderByDesc('c')->pluck('c', 'k'),
            'by_month' => (clone $churned)->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as k, COUNT(*) as c")
                ->groupBy('k')->orderBy('k')->pluck('c', 'k'),
        ];
    }

    /**
     * The card-free selling trial (roles.ticket_trial_ends_at), started from the event editor's
     * paid-ticket paywall. It is not a plan, so plan_counts above still reads these schedules as
     * free; this is the only place they are counted.
     *
     * The start is the subscription.ticket_trial_start audit row, not ends_at minus TRIAL_DAYS,
     * so a later change to the trial length cannot move it. A conversion is a real subscription
     * (not incomplete, as the funnel counts it) created after the start and within 14 days of the
     * end, which is when the reminders have done their work.
     */
    private function ticketTrials(): array
    {
        $roles = $this->excludeDemoRoles(
            Role::query()->whereNotNull('ticket_trial_ends_at')->where('is_deleted', false)
        )
            ->with('subscriptions')
            ->get(['id', 'ticket_trial_ends_at']);

        $starts = DB::table('audit_logs')
            ->where('action', AuditService::TICKET_TRIAL_START)
            ->where('model_type', 'Role')
            ->whereIn('model_id', $roles->pluck('id'))
            ->selectRaw('model_id, MIN(created_at) as started_at')
            ->groupBy('model_id')
            ->pluck('started_at', 'model_id');

        $counts = ['started' => 0, 'running' => 0, 'sold_during' => 0, 'converted' => 0, 'expired_unconverted' => 0,
            'started_from' => []];

        // Where each was started (the editor's paywall or the plan tab), from the same audit rows.
        foreach (DB::table('audit_logs')->where('action', AuditService::TICKET_TRIAL_START)->pluck('new_values') as $values) {
            $from = json_decode((string) $values, true)['source'] ?? 'unknown';
            $counts['started_from'][$from] = ($counts['started_from'][$from] ?? 0) + 1;
        }

        $windows = [];
        foreach ($roles as $role) {
            $ends = $role->ticket_trial_ends_at;
            $windows[$role->id] = [
                isset($starts[$role->id])
                    ? Carbon::parse($starts[$role->id])
                    : $ends->copy()->subDays((int) config('app.trial_days', 7)),
                $ends,
            ];
        }

        // Every real paid sale on a trial schedule's own events, between the earliest trial start
        // and the latest end, in ONE query - this used to be an exists() per trial. Same "real paid
        // sale" filter as the first_sale nudge: RSVPs and imports are not money.
        $salesByRole = [];
        if ($windows) {
            $salesByRole = DB::table('sales')
                ->join('events', 'events.id', '=', 'sales.event_id')
                ->whereIn('events.creator_role_id', array_keys($windows))
                ->where('sales.status', 'paid')
                ->where('sales.is_deleted', false)
                ->whereNotIn('sales.payment_method', ['rsvp', 'import'])
                ->where('sales.payment_amount', '>', 0)
                ->whereBetween('sales.paid_at', [
                    min(array_map(fn ($w) => $w[0], $windows)),
                    max(array_map(fn ($w) => $w[1], $windows)),
                ])
                ->get(['events.creator_role_id', 'sales.paid_at'])
                ->groupBy('creator_role_id')
                ->all();
        }

        foreach ($roles as $role) {
            [$start, $ends] = $windows[$role->id];

            $counts['started']++;

            $converted = $role->subscriptions->contains(fn ($sub) => ! in_array($sub->stripe_status, ['incomplete', 'incomplete_expired'], true)
                && $sub->created_at >= $start
                && $sub->created_at <= $ends->copy()->addDays(14));

            if ($converted) {
                $counts['converted']++;
            } elseif ($ends->isFuture()) {
                $counts['running']++;
            } else {
                $counts['expired_unconverted']++;
            }

            $soldDuring = collect($salesByRole[$role->id] ?? [])->contains(function ($sale) use ($start, $ends) {
                $paidAt = Carbon::parse($sale->paid_at);

                return $paidAt->betweenIncluded($start, $ends);
            });

            if ($soldDuring) {
                $counts['sold_during']++;
            }
        }

        return $counts;
    }

    /** Gross ticket revenue by month and currency (what organizers sold, not our revenue). */
    private function gmvByCurrency(): array
    {
        return DB::table('sales')
            ->join('events', 'events.id', '=', 'sales.event_id')
            ->where('sales.status', 'paid')
            ->where('sales.is_deleted', false)
            ->whereNotIn('sales.payment_method', ['rsvp', 'import'])
            ->whereNotNull('sales.paid_at')
            // The demo's hourly re-seed would otherwise be most of this - see
            // DemoService::demoEventIdsQuery() for why this is not the demo-% shape.
            ->whereNotIn('sales.event_id', DemoService::demoEventIdsQuery())
            ->groupBy('events.ticket_currency_code', DB::raw("DATE_FORMAT(sales.paid_at, '%Y-%m')"))
            ->selectRaw("events.ticket_currency_code as currency, DATE_FORMAT(sales.paid_at, '%Y-%m') as ym, "
                .'SUM(sales.payment_amount) as amount, COUNT(*) as sales_count')
            ->orderBy('ym')
            ->get()
            ->map(fn ($r) => ['currency' => $r->currency, 'month' => $r->ym,
                'amount' => round((float) $r->amount, 2), 'sales' => (int) $r->sales_count])
            ->all();
    }

    /**
     * Are schedules still publishing months after they were created?
     *
     * active_recently means the schedule did something: created an event, or sold a paid
     * ticket, in the last 90 days. It deliberately no longer counts page views. Views measure
     * whether anyone visited the public page - including crawlers and a single stray click - so
     * every cohort scored ~100% retained and the metric could not fall, which made it worthless
     * as a health signal. `visited_recently` keeps the old audience-side reading alongside it.
     *
     * paid is BILLING since schema 8, not the tier.
     */
    private function retentionFrom(array $schedules): array
    {
        $i = array_flip($schedules['columns']);
        $by = [];
        foreach ($schedules['rows'] as $row) {
            $m = $row[$i['created_month']] ?? 'unknown';
            $by[$m] ??= ['month' => $m, 'schedules' => 0, 'with_event' => 0,
                'active_recently' => 0, 'visited_recently' => 0, 'paid' => 0];
            $by[$m]['schedules']++;
            $by[$m]['with_event'] += $row[$i['events_total']] > 0 ? 1 : 0;
            $by[$m]['active_recently'] += $row[$i['events_recent_90d']] > 0
                || $row[$i['paid_tickets_90d']] > 0 ? 1 : 0;
            $by[$m]['visited_recently'] += $row[$i['views_90d']] > 0 ? 1 : 0;
            $by[$m]['paid'] += $row[$i['billing']] ? 1 : 0;
        }
        ksort($by);

        return array_values($by);
    }

    /** Monthly marketing traffic against verified signups. */
    /**
     * The placeholder schedules the app mints, and how many of them get claimed.
     *
     * Its own section because every other section here is about OWNED schedules and deliberately
     * filters these out. Counting them in with the rest would silently double the denominator of
     * every activation rate; leaving them out entirely is why the claim feature shipped unable to
     * report on itself.
     *
     * auto_created is answerable for every past month because it comes from roles.created_at - the
     * same property that makes verified_signups the one traffic column that never goes null.
     * claimed is not: it is counted from schedule.claim audit rows, which did not exist before the
     * feature, so earlier months emit null rather than a zero that would read as "nobody ever
     * claimed anything".
     */
    private function claims(array $months): array
    {
        $ownerless = fn () => Role::query()->ownerless()->notDemoSchedule()->where('roles.is_deleted', false);

        $created = $ownerless()
            ->groupBy(DB::raw("DATE_FORMAT(roles.created_at, '%Y-%m')"))
            ->selectRaw("DATE_FORMAT(roles.created_at, '%Y-%m') as ym, COUNT(*) as c")
            ->pluck('c', 'ym');

        $claimed = DB::table('audit_logs')
            ->where('action', AuditService::SCHEDULE_CLAIM)
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as c")
            ->pluck('c', 'ym');

        return [
            'unclaimed_total' => $ownerless()->count(),
            'unclaimed_with_event' => $ownerless()
                ->whereHas('events', fn ($q) => $q->where('event_role.is_accepted', true))
                ->count(),
            'auto_created' => collect($months)->mapWithKeys(fn ($m) => [$m => (int) ($created[$m] ?? 0)])->all(),
            'claimed' => collect($months)
                ->mapWithKeys(fn ($m) => [$m => $m < self::CLAIMS_TRACKED_FROM ? null : (int) ($claimed[$m] ?? 0)])
                ->all(),
        ];
    }

    private function traffic(): array
    {
        $isNexus = (bool) config('app.is_nexus');

        $stats = MarketingDailyStat::query()
            ->groupBy(DB::raw("DATE_FORMAT(date, '%Y-%m')"))
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as ym, SUM(visitors) as visitors, "
                .'SUM(page_views) as page_views, SUM(signup_views) as signup_views, '
                .'SUM(docs_page_views) as docs_page_views, SUM(docs_visitors) as docs_visitors, '
                .'SUM(pricing_views) as pricing_views, SUM(pricing_visitors) as pricing_visitors, '
                .'SUM(signup_code_requests) as signup_code_requests, '
                .'SUM(signup_code_verified) as signup_code_verified, '
                .'SUM(signup_code_invalid) as signup_code_invalid')
            ->orderBy('ym')
            ->get()->keyBy('ym');

        $signups = User::query()
            ->whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as c")
            ->orderBy('ym')
            ->get()->keyBy('ym');

        $months = $stats->keys()->merge($signups->keys())->unique()->sort()->values();

        return $months->map(function ($m) use ($stats, $signups, $isNexus) {
            // null, not 0, for a month before the column existed. Every counter after the
            // first three was added by a later migration with default(0), which MySQL
            // backfills onto the rows already there, so an untracked month is indistinguishable
            // from a real zero in the stored data - see MarketingDailyStat::COLUMN_TRACKED_FROM.
            // Reading those defaults as measurements is how "pricing_views: 0" was mistaken for
            // a broken counter when the column was two days old.
            $count = function (string $column) use ($stats, $m): ?int {
                if (! MarketingDailyStat::trackedInMonth($column, $m)) {
                    return null;
                }

                return (int) ($stats[$m]->{$column} ?? 0);
            };

            $visitors = $count('visitors');
            $docsVisitors = $count('docs_visitors');

            return [
                'month' => $m,
                // server, mixed or beacon - see MarketingDailyStat::COLUMN_REBASED_AT. It covers
                // every visit counter below (visitors, page_views, docs_*, pricing_*) but not the
                // signup_* ones, which never changed. Only months with the same basis compare.
                'visitors_basis' => ($isNexus && $visitors !== null)
                    ? MarketingDailyStat::basisInMonth('visitors', $m)
                    : null,
                'visitors' => $isNexus ? $visitors : null,
                'page_views' => $isNexus ? $count('page_views') : null,
                // Docs/selfhost readers are a subset of the totals above, not prospects for the
                // hosted plans. Subtracting gives a lower bound on buyer-intent traffic - a
                // lower bound rather than an exact figure because someone who reads both the
                // docs and a product page is deduped into each bucket separately.
                'docs_visitors' => $isNexus ? $docsVisitors : null,
                'docs_page_views' => $isNexus ? $count('docs_page_views') : null,
                // Untracked docs months must NOT fall back to zero here: subtracting a
                // not-yet-counted subset from a real total silently republishes the total as
                // buyer intent, which overstated every month before 2026-08.
                'commercial_visitors' => ($isNexus && $visitors !== null && $docsVisitors !== null)
                    ? max(0, $visitors - $docsVisitors)
                    : null,
                // Also a subset of the totals, and overlapping the docs buckets rather than
                // excluding them - never add the three together. This is the one page whose
                // visit is an explicit buying signal, and the only acquisition-side number that
                // moves fast enough to read a pricing change against: the conversions below it
                // run at about one a month, where nothing is distinguishable from noise.
                // It cannot see SIGNED-IN owners weighing an upgrade - TrackMarketingVisit
                // skips auth()->check() - which is what users.subscribe_form_viewed_at covers.
                'pricing_visitors' => $isNexus ? $count('pricing_visitors') : null,
                'pricing_views' => $isNexus ? $count('pricing_views') : null,
                'signup_views' => $count('signup_views'),
                // The 6-digit-code wall, which sits between signup_views and an account on the
                // email path. See the notes emitted with this export for how to read the three.
                'signup_code_requests' => $count('signup_code_requests'),
                'signup_code_verified' => $count('signup_code_verified'),
                'signup_code_invalid' => $count('signup_code_invalid'),
                // Queried from users, not a counter column, so this one is tracked all the way
                // back and is the only column here that never goes null.
                'verified_signups' => (int) ($signups[$m]->c ?? 0),
            ];
        })->all();
    }

    /** Median of an int list, or null when empty. */
    private function median(array $values): ?float
    {
        if (empty($values)) {
            return null;
        }
        sort($values);
        $n = count($values);
        $mid = intdiv($n, 2);

        return $n % 2 ? (float) $values[$mid] : round(($values[$mid - 1] + $values[$mid]) / 2, 1);
    }
}
