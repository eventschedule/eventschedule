<?php

namespace App\Services;

use App\Models\AnalyticsDaily;
use App\Models\MarketingDailyStat;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
    /** How many trailing months of per-schedule ticket volume to emit. */
    public const RECENT_MONTHS = 6;

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
     * Constraint for "has a real (non-demo) schedule", matching the demo exclusions used
     * by the schedules metric in getTrendData().
     */
    public function scheduleFilter(): \Closure
    {
        $demoRole = DemoService::DEMO_ROLE_SUBDOMAIN;

        return function ($query) use ($demoRole) {
            $query->where('subdomain', '!=', $demoRole)
                ->where('subdomain', 'not like', 'demo-%');
        };
    }

    /**
     * Constraint for "has a real (non-demo) event", matching getTrendData(): an event is
     * demo when any of its associated schedules is the demo schedule.
     */
    public function eventFilter(): \Closure
    {
        $demoRole = DemoService::DEMO_ROLE_SUBDOMAIN;

        return function ($query) use ($demoRole) {
            $query->whereDoesntHave('roles', function ($roleQuery) use ($demoRole) {
                $roleQuery->where('subdomain', $demoRole)
                    ->orWhere('subdomain', 'like', 'demo-%');
            });
        };
    }

    /**
     * Onboarding funnel: the 7 stage counts + conversions for the selected period, plus
     * the north-star (signup -> first event) with its period-over-period change and the
     * biggest onboarding leak. See the correctness rules in the funnel plan: stages 4/6 are
     * OR-defined so the funnel stays monotonic across all creation paths and history; the
     * anonymous traffic stages (1-2) are only shown for windows inside the tracked period.
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

        // Traffic stages (1-2): anonymous, only meaningful for a window inside the tracked period.
        $trackingStart = MarketingDailyStat::min('date');
        $rangeTracked = $trackingStart !== null
            && $startDate->toDateString() >= Carbon::parse($trackingStart)->toDateString();
        $visitors = null;
        $signupViews = null;
        if ($rangeTracked) {
            $sums = MarketingDailyStat::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->selectRaw('COALESCE(SUM(visitors), 0) as v, COALESCE(SUM(signup_views), 0) as s')
                ->first();
            // Visitors are only recorded on the nexus; keep n/a on other deployments.
            $visitors = config('app.is_nexus') ? (int) $sums->v : null;
            $signupViews = (int) $sums->s;
        }

        $stages = [
            ['key' => 'visited', 'group' => 'traffic', 'count' => $visitors],
            ['key' => 'signup_view', 'group' => 'traffic', 'count' => $signupViews],
            ['key' => 'account', 'group' => 'cohort', 'count' => $accounts],
            ['key' => 'reached_schedule', 'group' => 'cohort', 'count' => $reachedSchedule],
            ['key' => 'saved_schedule', 'group' => 'cohort', 'count' => $savedSchedule],
            ['key' => 'reached_event', 'group' => 'cohort', 'count' => $reachedEvent],
            ['key' => 'saved_event', 'group' => 'cohort', 'count' => $savedEvent],
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
        $prevCount = null;
        foreach ($stages as &$stage) {
            $c = $stage['count'];
            $stage['width'] = $c === null ? 0 : min(100, round($c / $widthDenom * 100, 1));
            $stage['step_conv'] = null;
            $stage['drop_count'] = null;
            if ($c !== null && $prevCount !== null && $prevCount > 0 && $stage['key'] !== 'account') {
                $stage['step_conv'] = round($c / $prevCount * 100, 1);
                $stage['drop_count'] = max(0, $prevCount - $c);
            }
            if ($c !== null) {
                $prevCount = $c;
            }
        }
        unset($stage);

        // Biggest onboarding leak: the worst adjacent drop among the cohort stages (3-7) by
        // absolute users lost. Traffic stages are excluded (anonymous, different population).
        $biggestDrop = null;
        $cohortStages = array_values(array_filter($stages, fn ($s) => $s['group'] === 'cohort'));
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
        $expr = fn (string $column) => match ($formatKey) {
            'daily' => DB::raw("DATE_FORMAT({$column}, '%Y-%m-%d') as period"),
            'weekly' => DB::raw("DATE_FORMAT({$column}, '%Y-%u') as period"),
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
                $parts = explode('-', $period);
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
            'visitor_to_signup' => $visitorToSignup,
            'signup_to_schedule' => $signupToSchedule,
            'signup_to_event' => $signupToEvent,
            'last_index' => count($labels) - 1,
            'has_traffic' => $isNexus && $trafficTrend->sum('visitors') > 0,
        ];
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
                'app_version' => config('self-update.version_installed'),
                'schema_version' => 1,
                'free_ticket_cap' => config('usage.ticket_sale_monthly_limit_free'),
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
            'retention' => $this->retentionFrom($schedules),
            'traffic' => $this->traffic(),
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
        return $prefix.':'.substr(hash_hmac('sha256', (string) $id, (string) config('app.key')), 0, 6);
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

    /** Demo exclusion applied to a roles query, matching funnelScheduleFilter(). */
    private function excludeDemoRoles($query)
    {
        return $query->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%');
    }

    /** Subquery of every event id attached to a demo schedule. */
    private function demoEventIds()
    {
        return DB::table('event_role')
            ->join('roles', 'roles.id', '=', 'event_role.role_id')
            ->where(function ($q) {
                $q->where('roles.subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)
                    ->orWhere('roles.subdomain', 'like', 'demo-%');
            })
            ->select('event_role.event_id');
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

        // Schedules and first-schedule timestamp, per user, in one pass.
        $rolesByUser = $this->excludeDemoRoles(
            DB::table('roles')->where('is_deleted', false)->whereNotNull('user_id')
        )
            ->selectRaw('user_id, COUNT(*) as c, MIN(created_at) as first_at')
            ->groupBy('user_id')
            ->get()->keyBy('user_id');

        $eventsByUser = DB::table('events')
            ->whereNotIn('id', $this->demoEventIds())
            ->selectRaw('user_id, COUNT(*) as c')
            ->groupBy('user_id')
            ->get()->keyBy('user_id');

        $rows = [];
        foreach ((clone $base)->orderByDesc('id')->limit($this->rowCap())->cursor() as $u) {
            $roleAgg = $rolesByUser[$u->id] ?? null;
            $firstAt = $roleAgg?->first_at ? Carbon::parse($roleAgg->first_at) : null;

            $rows[] = [
                $this->hashId('u', $u->id),
                $u->created_at?->format('Y-m'),
                $u->signup_intent,
                $u->utm_source,
                $u->utm_medium,
                $this->hostOf($u->referrer_url),
                $this->pathOf($u->landing_page),
                $u->google_oauth_id ? 'google' : ($u->password ? 'email' : 'other'),
                $u->schedule_form_viewed_at !== null,
                (int) ($roleAgg->c ?? 0) > 0,
                (int) ($eventsByUser[$u->id]->c ?? 0) > 0,
                (int) ($roleAgg->c ?? 0),
                ($firstAt && $u->created_at) ? max(0, $u->created_at->diffInDays($firstAt)) : null,
            ];
        }

        return [
            'columns' => ['uid', 'created_month', 'signup_intent', 'utm_source', 'utm_medium',
                'referrer_domain', 'landing_path', 'auth', 'reached_schedule_form', 'saved_schedule',
                'saved_event', 'schedules_count', 'days_to_first_schedule'],
            'rows' => $rows,
            'total' => $total,
            'truncated' => $total > $this->rowCap(),
        ];
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

    /** Landing page reduced to its path - same query-string reasoning as hostOf(). */
    private function pathOf(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $path = parse_url($url, PHP_URL_PATH);

        return $path ? mb_substr($path, 0, 120) : '/';
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

        $events = DB::table('event_role')
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->selectRaw('event_role.role_id, COUNT(*) as total, '
                .'SUM(CASE WHEN events.is_draft = 0 AND events.is_private = 0 AND events.is_internal = 0 THEN 1 ELSE 0 END) as public_total')
            ->groupBy('event_role.role_id')
            ->get()->keyBy('role_id');

        $ticketTypes = DB::table('tickets')
            ->join('event_role', 'event_role.event_id', '=', 'tickets.event_id')
            ->where('tickets.is_deleted', false)
            ->selectRaw('event_role.role_id, COUNT(*) as c')
            ->groupBy('event_role.role_id')
            ->get()->keyBy('role_id');

        $paidByMonth = $this->paidTicketsByRoleMonth();

        $views = AnalyticsDaily::query()
            ->where('date', '>=', now()->copy()->subDays(90)->toDateString())
            ->selectRaw('role_id, SUM(desktop_views + mobile_views + tablet_views + unknown_views) as v')
            ->groupBy('role_id')
            ->get()->keyBy('role_id');

        $followers = DB::table('role_user')->where('level', 'follower')
            ->selectRaw('role_id, COUNT(*) as c')->groupBy('role_id')
            ->get()->keyBy('role_id');

        $firstSub = DB::table('subscriptions')
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
            $perMonth = [];
            foreach ($months as $m) {
                $perMonth[] = (int) ($paidByMonth[$r->id][$m] ?? 0);
            }
            $subAt = $firstSub[$r->id]->first_at ?? null;

            $rows[] = [
                $this->hashId('s', $r->id),
                $this->hashId('u', $r->user_id),
                $r->created_at?->format('Y-m'),
                $r->type,
                $r->actualPlanTier(),
                $r->plan_source,
                (int) ($events[$r->id]->total ?? 0),
                (int) ($events[$r->id]->public_total ?? 0),
                (int) ($ticketTypes[$r->id]->c ?? 0),
                array_sum($paidByMonth[$r->id] ?? []),
                $perMonth,
                (int) ($views[$r->id]->v ?? 0),
                (int) ($followers[$r->id]->c ?? 0),
                (int) ($apptTypes[$r->id]->c ?? 0),
                (int) ($photos[$r->id]->c ?? 0),
                (int) ($newsletterEmails[$r->id]->c ?? 0),
                $this->featuresOf($r),
                ($subAt && $r->created_at) ? max(0, $r->created_at->diffInDays(Carbon::parse($subAt))) : null,
            ];
        }

        return [
            'columns' => ['sid', 'uid', 'created_month', 'type', 'plan', 'plan_source',
                'events_total', 'events_public', 'ticket_types', 'paid_tickets_total',
                'paid_tickets_recent', 'views_90d', 'followers', 'appointment_types',
                'photos', 'newsletter_emails_this_month', 'features', 'days_to_upgrade'],
            'rows' => $rows,
            'total' => $total,
            'truncated' => $total > $this->rowCap(),
        ];
    }

    /**
     * Paid tickets per schedule per calendar month, matching what Role::ticketSaleLimit()
     * enforcement counts: paid, not deleted, not an RSVP or bulk import, not an add-on,
     * priced above zero, and never an appointment booking. Windowed on sales.paid_at,
     * never created_at, or cash sales escape entirely.
     */
    private function paidTicketsByRoleMonth(): array
    {
        $rows = DB::table('sales')
            ->join('sale_tickets', 'sale_tickets.sale_id', '=', 'sales.id')
            ->join('tickets', 'tickets.id', '=', 'sale_tickets.ticket_id')
            ->join('events', 'events.id', '=', 'sales.event_id')
            ->join('event_role', 'event_role.event_id', '=', 'events.id')
            ->where('sales.status', 'paid')
            ->where('sales.is_deleted', false)
            ->whereNotIn('sales.payment_method', ['rsvp', 'import'])
            ->whereNotNull('sales.paid_at')
            ->where('tickets.is_addon', false)
            ->where('tickets.price', '>', 0)
            ->whereNull('events.appointment_type_id')
            // Group by the expression, never the select alias - an alias binds to a
            // same-named real column and raises 1055 under ONLY_FULL_GROUP_BY.
            ->groupBy('event_role.role_id', DB::raw("DATE_FORMAT(sales.paid_at, '%Y-%m')"))
            ->selectRaw("event_role.role_id as role_id, DATE_FORMAT(sales.paid_at, '%Y-%m') as ym, SUM(sale_tickets.quantity) as qty")
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->role_id][$row->ym] = (int) $row->qty;
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
                'with_ticket_type' => 0, 'with_paid_sale' => 0, 'paid_plan' => 0];
            $byType[$t]['schedules']++;
            $byType[$t]['with_event'] += $row[$si['events_total']] > 0 ? 1 : 0;
            $byType[$t]['with_public_event'] += $row[$si['events_public']] > 0 ? 1 : 0;
            $byType[$t]['with_ticket_type'] += $row[$si['ticket_types']] > 0 ? 1 : 0;
            $byType[$t]['with_paid_sale'] += $row[$si['paid_tickets_total']] > 0 ? 1 : 0;
            $byType[$t]['paid_plan'] += $row[$si['plan']] !== 'free' ? 1 : 0;
        }
        ksort($byType);

        return [
            'by_signup_intent' => $this->groupActivation($signups, 'signup_intent'),
            'by_schedule_type' => array_values($byType),
        ];
    }

    /** Count + activation rates for one signup column, biggest group first. */
    private function groupActivation(array $signups, string $column): array
    {
        $i = array_flip($signups['columns']);
        $by = [];
        foreach ($signups['rows'] as $row) {
            $k = $row[$i[$column]];
            $k = ($k === null || $k === '') ? '(none)' : (string) $k;
            $by[$k] ??= ['key' => $k, 'signups' => 0, 'saved_schedule' => 0, 'saved_event' => 0];
            $by[$k]['signups']++;
            $by[$k]['saved_schedule'] += $row[$i['saved_schedule']] ? 1 : 0;
            $by[$k]['saved_event'] += $row[$i['saved_event']] ? 1 : 0;
        }
        $out = array_values($by);
        usort($out, fn ($a, $b) => $b['signups'] <=> $a['signups']);

        return array_slice($out, 0, 50);
    }

    /**
     * Whether the free plan's limits ever actually bind. If the distribution piles up on
     * zero, the upgrade trigger never fires and the packaging boundary is the problem,
     * not the price.
     */
    private function freePressureFrom(array $schedules): array
    {
        $i = array_flip($schedules['columns']);
        $cap = (int) config('usage.ticket_sale_monthly_limit_free', 25);
        $buckets = ['0' => 0, '1-5' => 0, '6-15' => 0, '16-24' => 0, 'at_or_over_cap' => 0];
        $newsletter = ['0' => 0, '1-9' => 0, 'at_or_over_cap' => 0];
        $appt = ['0' => 0, '1' => 0, '2+' => 0];
        $photos = ['0' => 0, '1-24' => 0, 'at_or_over_cap' => 0];
        $freeCount = 0;
        $everHitCap = 0;

        foreach ($schedules['rows'] as $row) {
            if ($row[$i['plan']] !== 'free') {
                continue;
            }
            $freeCount++;

            $peak = max($row[$i['paid_tickets_recent']] ?: [0]);
            if ($peak >= $cap) {
                $buckets['at_or_over_cap']++;
                $everHitCap++;
            } elseif ($peak >= 16) {
                $buckets['16-24']++;
            } elseif ($peak >= 6) {
                $buckets['6-15']++;
            } elseif ($peak >= 1) {
                $buckets['1-5']++;
            } else {
                $buckets['0']++;
            }

            $n = (int) $row[$i['newsletter_emails_this_month']];
            $newsletter[$n === 0 ? '0' : ($n >= 10 ? 'at_or_over_cap' : '1-9')]++;

            $a = (int) $row[$i['appointment_types']];
            $appt[$a === 0 ? '0' : ($a === 1 ? '1' : '2+')]++;

            $p = (int) $row[$i['photos']];
            $photos[$p === 0 ? '0' : ($p >= 25 ? 'at_or_over_cap' : '1-24')]++;
        }

        return [
            'free_schedules' => $freeCount,
            'ticket_cap' => $cap,
            'peak_month_paid_tickets' => $buckets,
            'ever_hit_ticket_cap' => $everHitCap,
            'newsletter_emails_this_month' => $newsletter,
            'appointment_types' => $appt,
            'photos' => $photos,
        ];
    }

    /** What paying schedules do that free ones do not. */
    private function payersVsFreeFrom(array $schedules): array
    {
        $i = array_flip($schedules['columns']);
        $acc = ['paid' => ['n' => 0], 'free' => ['n' => 0]];

        foreach ($schedules['rows'] as $row) {
            $side = $row[$i['plan']] === 'free' ? 'free' : 'paid';
            $acc[$side]['n']++;
            foreach ($row[$i['features']] as $f) {
                $acc[$side][$f] = ($acc[$side][$f] ?? 0) + 1;
            }
            foreach (['events_total', 'events_public', 'ticket_types', 'paid_tickets_total',
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

        // Only NULL plan_source is a genuine Stripe conversion; admin grants and referral
        // credits pay nothing and would inflate MRR.
        $monthly = (float) config('services.stripe_platform.price_monthly_amount', 5);
        $yearly = (float) config('services.stripe_platform.price_yearly_amount', 50);
        $entMonthly = (float) config('services.stripe_platform.enterprise_price_monthly_amount', 15);
        $entYearly = (float) config('services.stripe_platform.enterprise_price_yearly_amount', 150);
        $mrr = 0.0;

        // lazy(), not get(): this is every schedule on the install, and actualPlanTier()
        // needs a hydrated model, so the whole table would otherwise sit in memory at once.
        $roles = $this->excludeDemoRoles(
            Role::query()->whereNotNull('user_id')->where('is_deleted', false)
        )->with('subscriptions')->lazy();

        foreach ($roles as $role) {
            $tier = $role->actualPlanTier();
            $tiers[$tier] = ($tiers[$tier] ?? 0) + 1;
            if ($tier === 'free') {
                continue;
            }
            $source = $role->plan_source ?? 'stripe';
            $bySource[$source] = ($bySource[$source] ?? 0) + 1;
            $byTerm[$role->plan_term ?? 'unknown'] = ($byTerm[$role->plan_term ?? 'unknown'] ?? 0) + 1;

            $first = $role->subscriptions->min('created_at');
            if ($first && $role->created_at) {
                $daysToUpgrade[] = max(0, $role->created_at->diffInDays(Carbon::parse($first)));
            }

            if ($role->plan_source === null) {
                $isYear = $role->plan_term === 'year';
                $mrr += $tier === 'enterprise'
                    ? ($isYear ? $entYearly / 12 : $entMonthly)
                    : ($isYear ? $yearly / 12 : $monthly);
            }
        }

        $statuses = DB::table('subscriptions')
            ->selectRaw('stripe_status, COUNT(*) as c')->groupBy('stripe_status')
            ->pluck('c', 'stripe_status');

        $paying = ($tiers['pro'] ?? 0) + ($tiers['enterprise'] ?? 0);

        return [
            'plan_counts' => $tiers,
            'by_plan_source' => $bySource,
            'by_plan_term' => $byTerm,
            'subscription_status' => $statuses,
            'mrr_usd' => round($mrr, 2),
            'arpu_usd' => $paying > 0 ? round($mrr / $paying, 2) : null,
            'list_prices_usd' => ['pro_monthly' => $monthly, 'pro_yearly' => $yearly,
                'enterprise_monthly' => $entMonthly, 'enterprise_yearly' => $entYearly],
            'median_days_to_upgrade' => $this->median($daysToUpgrade),
            'gmv_by_currency' => $this->gmvByCurrency(),
        ];
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
            ->groupBy('events.ticket_currency_code', DB::raw("DATE_FORMAT(sales.paid_at, '%Y-%m')"))
            ->selectRaw("events.ticket_currency_code as currency, DATE_FORMAT(sales.paid_at, '%Y-%m') as ym, "
                .'SUM(sales.payment_amount) as amount, COUNT(*) as sales_count')
            ->orderBy('ym')
            ->get()
            ->map(fn ($r) => ['currency' => $r->currency, 'month' => $r->ym,
                'amount' => round((float) $r->amount, 2), 'sales' => (int) $r->sales_count])
            ->all();
    }

    /** Are schedules still publishing months after they were created? */
    private function retentionFrom(array $schedules): array
    {
        $i = array_flip($schedules['columns']);
        $by = [];
        foreach ($schedules['rows'] as $row) {
            $m = $row[$i['created_month']] ?? 'unknown';
            $by[$m] ??= ['month' => $m, 'schedules' => 0, 'with_event' => 0,
                'active_recently' => 0, 'paid' => 0];
            $by[$m]['schedules']++;
            $by[$m]['with_event'] += $row[$i['events_total']] > 0 ? 1 : 0;
            $by[$m]['active_recently'] += array_sum($row[$i['paid_tickets_recent']]) > 0
                || $row[$i['views_90d']] > 0 ? 1 : 0;
            $by[$m]['paid'] += $row[$i['plan']] !== 'free' ? 1 : 0;
        }
        ksort($by);

        return array_values($by);
    }

    /** Monthly marketing traffic against verified signups. */
    private function traffic(): array
    {
        $isNexus = (bool) config('app.is_nexus');

        $stats = MarketingDailyStat::query()
            ->groupBy(DB::raw("DATE_FORMAT(date, '%Y-%m')"))
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as ym, SUM(visitors) as visitors, "
                .'SUM(page_views) as page_views, SUM(signup_views) as signup_views')
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

        return $months->map(fn ($m) => [
            'month' => $m,
            'visitors' => $isNexus ? (int) ($stats[$m]->visitors ?? 0) : null,
            'page_views' => $isNexus ? (int) ($stats[$m]->page_views ?? 0) : null,
            'signup_views' => (int) ($stats[$m]->signup_views ?? 0),
            'verified_signups' => (int) ($signups[$m]->c ?? 0),
        ])->all();
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
