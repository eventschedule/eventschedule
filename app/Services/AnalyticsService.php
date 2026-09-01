<?php

namespace App\Services;

use App\Models\AnalyticsAppearancesDaily;
use App\Models\AnalyticsDaily;
use App\Models\AnalyticsEventsDaily;
use App\Models\AnalyticsLocationsDaily;
use App\Models\AnalyticsReferrersDaily;
use App\Models\AnalyticsSocialClicksDaily;
use App\Models\AnalyticsUtmDaily;
use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\Newsletter;
use App\Models\PageView;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Models\User;
use App\Utils\CountryUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Record a page view (returns false if bot detected)
     */
    public function recordView(Role $role, ?Event $event, Request $request): bool
    {
        return PageView::recordView($role, $event, $request);
    }

    /**
     * Get statistics for a user's schedules
     */
    public function getStatsForUser(User $user, Carbon $start, Carbon $end): array
    {
        $roleIds = $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return $this->emptyStats();
        }

        $totalViews = $this->getTotalViewsForRoles($roleIds);
        $periodViews = $this->getPeriodViewsForRoles($roleIds, $start, $end);

        return [
            'total_views' => $totalViews,
            'period_views' => $periodViews,
        ];
    }

    /**
     * Get statistics for a specific role
     */
    public function getStatsForRole(Role $role, Carbon $start, Carbon $end): array
    {
        $totalViews = $this->getTotalViewsForRoles(collect([$role->id]));
        $periodViews = $this->getPeriodViewsForRoles(collect([$role->id]), $start, $end);

        return [
            'total_views' => $totalViews,
            'period_views' => $periodViews,
        ];
    }

    /**
     * Get top events by view count
     */
    public function getTopEvents(User $user, int $limit, Carbon $start, Carbon $end, ?int $eventId = null, ?int $roleId = null): Collection
    {
        // $roleId last so HomeController's dashboard call keeps working unchanged. Scoped the way
        // getTrafficSources() is: without it this panel aggregated every schedule the user owns
        // while the picker beside it was filtered to one.
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        // Get event IDs that belong to user's roles (via pivot table)
        $eventIds = $eventId ? collect([$eventId]) : DB::table('event_role')
            ->whereIn('role_id', $roleIds)
            ->pluck('event_id')
            ->unique();

        if ($eventIds->isEmpty()) {
            return collect();
        }

        return AnalyticsEventsDaily::select(
            'event_id',
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as view_count')
        )
            ->forEvents($eventIds)
            ->inDateRange($start, $end)
            ->groupBy('event_id')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->with('event.roles')
            ->get()
            ->filter(fn ($item) => $item->event !== null)
            ->map(fn ($item) => [
                'event' => $item->event,
                'view_count' => (int) $item->view_count,
            ]);
    }

    /**
     * Get views grouped by period (daily, weekly, monthly)
     */
    public function getViewsByPeriod(User $user, string $period, Carbon $start, Carbon $end, ?int $roleId = null, ?int $eventId = null): Collection
    {
        // Use whitelisted format expressions to prevent SQL injection
        // The period parameter comes from user input, so we must validate it
        $dateFormatExpr = match ($period) {
            'daily' => DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as period"),
            'weekly' => DB::raw("DATE_FORMAT(date, '%x-%v') as period"),
            'monthly' => DB::raw("DATE_FORMAT(date, '%Y-%m') as period"),
            default => DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as period"),
        };

        if ($eventId) {
            return AnalyticsEventsDaily::select(
                $dateFormatExpr,
                DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as view_count')
            )
                ->byEvent($eventId)
                ->inDateRange($start, $end)
                ->groupBy('period')
                ->orderBy('period')
                ->get();
        }

        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return AnalyticsDaily::select(
            $dateFormatExpr,
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as view_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Get month-over-month comparison
     */
    public function getMonthOverMonthComparison(User $user, ?int $roleId = null, ?int $eventId = null): array
    {
        $thisMonthStart = now()->startOfMonth();
        $thisMonthEnd = now()->endOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        if ($eventId) {
            $thisMonthViews = $this->getPeriodViewsForEvent($eventId, $thisMonthStart, $thisMonthEnd);
            $lastMonthViews = $this->getPeriodViewsForEvent($eventId, $lastMonthStart, $lastMonthEnd);
        } else {
            $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

            if ($roleIds->isEmpty()) {
                return [
                    'this_month' => 0,
                    'last_month' => 0,
                    'percentage_change' => 0,
                ];
            }

            $thisMonthViews = $this->getPeriodViewsForRoles($roleIds, $thisMonthStart, $thisMonthEnd);
            $lastMonthViews = $this->getPeriodViewsForRoles($roleIds, $lastMonthStart, $lastMonthEnd);
        }

        $percentageChange = $lastMonthViews > 0
            ? round((($thisMonthViews - $lastMonthViews) / $lastMonthViews) * 100, 1)
            : ($thisMonthViews > 0 ? 100 : 0);

        return [
            'this_month' => $thisMonthViews,
            'last_month' => $lastMonthViews,
            'percentage_change' => $percentageChange,
        ];
    }

    /**
     * Get period comparison based on date range
     */
    public function getPeriodComparison(User $user, string $range, Carbon $start, Carbon $end, ?int $roleId = null, ?int $eventId = null): array
    {
        // Calculate previous period based on range
        [$previousStart, $previousEnd, $label] = match ($range) {
            'last_7_days' => [
                now()->subDays(14)->startOfDay(),
                now()->subDays(8)->endOfDay(),
                'vs_previous_7_days',
            ],
            'last_30_days' => [
                now()->subDays(60)->startOfDay(),
                now()->subDays(31)->endOfDay(),
                'vs_previous_30_days',
            ],
            'last_90_days' => [
                now()->subDays(180)->startOfDay(),
                now()->subDays(91)->endOfDay(),
                'vs_previous_90_days',
            ],
            'this_month' => [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
                'vs_last_month',
            ],
            'last_month' => [
                now()->subMonths(2)->startOfMonth(),
                now()->subMonths(2)->endOfMonth(),
                'vs_previous_month',
            ],
            'this_year' => [
                now()->subYear()->startOfYear(),
                now()->subYear()->endOfYear(),
                'vs_last_year',
            ],
            default => [
                now()->subDays(60)->startOfDay(),
                now()->subDays(31)->endOfDay(),
                'vs_previous_30_days',
            ],
        };

        if ($eventId) {
            $currentViews = $this->getPeriodViewsForEvent($eventId, $start, $end);
            $previousViews = $this->getPeriodViewsForEvent($eventId, $previousStart, $previousEnd);
        } else {
            $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

            if ($roleIds->isEmpty()) {
                return [
                    'current_period' => 0,
                    'previous_period' => 0,
                    'percentage_change' => 0,
                    'comparison_label' => '',
                ];
            }

            $currentViews = $this->getPeriodViewsForRoles($roleIds, $start, $end);
            $previousViews = $this->getPeriodViewsForRoles($roleIds, $previousStart, $previousEnd);
        }

        $percentageChange = $previousViews > 0
            ? round((($currentViews - $previousViews) / $previousViews) * 100, 1)
            : ($currentViews > 0 ? 100 : 0);

        return [
            'current_period' => $currentViews,
            'previous_period' => $previousViews,
            'percentage_change' => $percentageChange,
            'comparison_label' => $label,
        ];
    }

    /**
     * Get device breakdown
     */
    public function getDeviceBreakdown(User $user, Carbon $start, Carbon $end, ?int $roleId = null, ?int $eventId = null): Collection
    {
        if ($eventId) {
            $result = AnalyticsEventsDaily::select(
                DB::raw('SUM(desktop_views) as desktop'),
                DB::raw('SUM(mobile_views) as mobile'),
                DB::raw('SUM(tablet_views) as tablet'),
                DB::raw('SUM(unknown_views) as unknown')
            )
                ->byEvent($eventId)
                ->inDateRange($start, $end)
                ->first();
        } else {
            $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

            if ($roleIds->isEmpty()) {
                return collect();
            }

            $result = AnalyticsDaily::select(
                DB::raw('SUM(desktop_views) as desktop'),
                DB::raw('SUM(mobile_views) as mobile'),
                DB::raw('SUM(tablet_views) as tablet'),
                DB::raw('SUM(unknown_views) as unknown')
            )
                ->forRoles($roleIds)
                ->inDateRange($start, $end)
                ->first();
        }

        if (! $result) {
            return collect();
        }

        return collect([
            'desktop' => (int) $result->desktop,
            'mobile' => (int) $result->mobile,
            'tablet' => (int) $result->tablet,
            'unknown' => (int) $result->unknown,
        ])->filter(fn ($count) => $count > 0);
    }

    /**
     * Get views by schedule (role)
     */
    public function getViewsBySchedule(User $user, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return AnalyticsDaily::select(
            'role_id',
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as view_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->groupBy('role_id')
            ->with('role')
            ->get()
            ->filter(fn ($item) => $item->role !== null)
            ->map(fn ($item) => [
                'role' => $item->role,
                'view_count' => (int) $item->view_count,
            ]);
    }

    /**
     * Get total views for given roles (all time)
     */
    public function getTotalViewsForRoles(Collection $roleIds): int
    {
        return (int) AnalyticsDaily::forRoles($roleIds)
            ->sum(DB::raw('desktop_views + mobile_views + tablet_views + unknown_views'));
    }

    /**
     * Get total views for a specific event (all time)
     */
    public function getTotalViewsForEvent(int $eventId): int
    {
        return (int) AnalyticsEventsDaily::byEvent($eventId)
            ->sum(DB::raw('desktop_views + mobile_views + tablet_views + unknown_views'));
    }

    /**
     * Get views for given roles in a date range
     */
    protected function getPeriodViewsForRoles(Collection $roleIds, Carbon $start, Carbon $end): int
    {
        return (int) AnalyticsDaily::forRoles($roleIds)
            ->inDateRange($start, $end)
            ->sum(DB::raw('desktop_views + mobile_views + tablet_views + unknown_views'));
    }

    /**
     * Get views for a specific event in a date range
     */
    protected function getPeriodViewsForEvent(int $eventId, Carbon $start, Carbon $end): int
    {
        return (int) AnalyticsEventsDaily::byEvent($eventId)
            ->inDateRange($start, $end)
            ->sum(DB::raw('desktop_views + mobile_views + tablet_views + unknown_views'));
    }

    /**
     * Get events for the analytics filter dropdown (future, live recurrences, and the past 30 days).
     *
     * Two lists, not one. $ownedDataOnly is the revenue and check-ins tabs, which read the
     * creator's private data: a curator that only lists an event does not own it, so those tabs
     * offer just what the curator created, matching the totals ownedEventIdsForRoles() computes
     * beside the picker. It defaults to the strict list so a new caller cannot widen by accident.
     *
     * The web tab is page traffic. A curator's own site served the view that incremented the row,
     * and getTopEvents() already counts curated events in the Top events table on that same tab -
     * excluding them from the picker only made the curator's schedule look like it was missing
     * events. (analytics_events_daily carries no schedule dimension, so the count is the event's
     * traffic everywhere it appears; that is what the Top events table has always shown.)
     */
    public function getEventsForSchedule(int $roleId, bool $ownedDataOnly = true): Collection
    {
        $cutoff = now()->subDays(30)->startOfDay();

        $isCurator = Role::where('id', $roleId)->where('type', 'curator')->exists();

        // creatorRole: the top-events list renders getShortDateRangeDisplay() per row.
        $query = Event::query()
            ->with('creatorRole')
            ->where('is_draft', false)
            ->where(function ($q) use ($cutoff) {
                $q->where('starts_at', '>=', $cutoff)
                    ->orWhereNull('starts_at')
                    // starts_at holds only the FIRST occurrence of a recurring event; the rest are
                    // computed by matchesDate(). Without this the clause above drops a live weekly
                    // residency the day it turns 30 days old, which is how a venue's regular nights
                    // went missing from this picker while still collecting views.
                    //
                    // Bounded, unlike Event::scopeInMonth()'s bare orWhereNotNull('days_of_week'):
                    // that scope's callers re-filter every row through matchesDate() per grid day,
                    // so a finished series is loaded and then discarded. Nothing re-filters this
                    // list, so unbounded would mean a series that ended in 2019 sits in the
                    // dropdown forever. 'on_date' is the only end SQL can evaluate - 'never' has no
                    // end and 'after_events' counts occurrences in PHP (countOccurrences()) - so
                    // both of those stay in.
                    ->orWhere(function ($q2) use ($cutoff) {
                        $q2->whereNotNull('days_of_week')
                            ->where(function ($q3) use ($cutoff) {
                                $q3->where('recurring_end_type', '!=', 'on_date')
                                    ->orWhereNull('recurring_end_type')
                                    ->orWhereNull('recurring_end_value')
                                    ->orWhere('recurring_end_value', '>=', $cutoff->toDateString());
                            });
                    })
                    // Multi-day event that started before the window and is still running.
                    ->orWhere(function ($q2) use ($cutoff) {
                        $q2->where('duration', '>=', 24)
                            ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [$cutoff]);
                    });
            });

        if ($isCurator && $ownedDataOnly) {
            $query->where('creator_role_id', $roleId);
        } elseif ($isCurator) {
            // Strictly the old list PLUS what this curator accepted, so nothing can vanish.
            //
            // The creator_role_id arm carries NO pivot requirement, on purpose. An event whose
            // creator_role_id names a schedule with no matching event_role row is a real state
            // (CheckData::checkEventCreatorRoles() finds it and deliberately never repairs it) and
            // it still works here, because canViewEventTraffic() short-circuits on events.user_id
            // exactly as Event::scopeManagedThrough()'s first arm does. That scope can afford its
            // "a pivot row is REQUIRED" rule because it carries that user_id arm; this list does
            // not, so requiring a pivot would hide the curator's own event instead.
            //
            // Somebody else's event counts once this curator ACCEPTED it: acceptance is what put
            // it on the page whose views these are, and every listing query in RoleController
            // filters the same way. A decline leaves is_accepted = false rather than detaching, so
            // it stays out, and syncCuratorSources()'s pending auto-attachments cannot flood the
            // picker.
            $query->where(function ($q) use ($roleId) {
                $q->where('creator_role_id', $roleId)
                    ->orWhereHas('roles', fn ($r) => $r->where('roles.id', $roleId)
                        ->where('event_role.is_accepted', true));
            });
        } else {
            // Same creator arm as the curator branch above, for the same reason: an event whose
            // creator lost its event_role row still resolves through events.user_id, and CheckData
            // reports that state more often for venues than for curators.
            $query->where(function ($q) use ($roleId) {
                $q->where('creator_role_id', $roleId)
                    ->orWhereHas('roles', fn ($r) => $r->where('roles.id', $roleId));
            });
        }

        return $query->orderBy('starts_at')
            ->get()
            ->map(fn ($event) => [
                'id' => UrlUtils::encodeId($event->id),
                'raw_id' => $event->id,
                'name' => $event->translatedName(),
                // A recurring row's starts_at is its first occurrence, so on its own it reads as
                // a stale date sitting next to tonight's shows. Label it rather than replace it:
                // the date is still the only hint a reader gets that a series is long finished,
                // and the 'after_events' end type cannot be evaluated here to drop those (its
                // countOccurrences() walks day by day from starts_at, thousands of iterations per
                // row), so the SQL above keeps them.
                'starts_at' => $event->days_of_week
                    ? __('messages.recurring').' · '.$event->getShortDateRangeDisplay('M j, Y')
                    : $event->getShortDateRangeDisplay('D, M j, Y'),
                'image_url' => $event->getImageUrl(),
            ]);
    }

    /**
     * Get the role IDs of schedules the user owns or administers.
     */
    protected function getUserRoleIds(User $user): Collection
    {
        return $user->editor()->pluck('roles.id');
    }

    /**
     * Get IDs of events whose private data (revenue, sales, check-ins) is
     * visible to this user, scoped to the given role set. Events attached
     * via a curator role are excluded unless the curator also created the
     * event — curators that only list/promote an event don't own the
     * creator's private data.
     */
    protected function ownedEventIdsForRoles(User $user, Collection $roleIds): Collection
    {
        return DB::table('event_role')
            ->join('roles', 'roles.id', '=', 'event_role.role_id')
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->whereIn('event_role.role_id', $roleIds)
            ->where(function ($q) {
                $q->where('roles.type', '!=', 'curator')
                    ->orWhereColumn('events.creator_role_id', 'event_role.role_id');
            })
            ->pluck('event_role.event_id')
            ->unique();
    }

    /**
     * Return empty stats structure
     */
    protected function emptyStats(): array
    {
        return [
            'total_views' => 0,
            'period_views' => 0,
        ];
    }

    /**
     * Get top talents/venues by appearance views on a specific schedule
     */
    public function getTopAppearancesForSchedule(Role $schedule, int $limit, Carbon $start, Carbon $end): Collection
    {
        return AnalyticsAppearancesDaily::select(
            'role_id',
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as view_count')
        )
            ->forSchedule($schedule->id)
            ->inDateRange($start, $end)
            ->groupBy('role_id')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->with('role')
            ->get()
            ->filter(fn ($item) => $item->role !== null)
            ->map(fn ($item) => [
                'role' => $item->role,
                'view_count' => (int) $item->view_count,
            ]);
    }

    /**
     * Get total appearance views on a schedule
     */
    public function getTotalAppearanceViewsForSchedule(Role $schedule, Carbon $start, Carbon $end): int
    {
        return (int) AnalyticsAppearancesDaily::forSchedule($schedule->id)
            ->inDateRange($start, $end)
            ->sum(DB::raw('desktop_views + mobile_views + tablet_views + unknown_views'));
    }

    /**
     * Get appearance views by period for a specific schedule
     */
    public function getAppearanceViewsByPeriod(Role $schedule, string $period, Carbon $start, Carbon $end): Collection
    {
        $dateFormat = match ($period) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%x-%v',  // ISO year and week
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return AnalyticsAppearancesDaily::select(
            DB::raw("DATE_FORMAT(date, '{$dateFormat}') as period"),
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as view_count')
        )
            ->forSchedule($schedule->id)
            ->inDateRange($start, $end)
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Get total appearance views for a talent/venue (views from appearing on other schedules)
     */
    public function getTotalAppearanceViewsForRole(int $roleId, Carbon $start, Carbon $end): int
    {
        return (int) AnalyticsAppearancesDaily::forRole($roleId)
            ->inDateRange($start, $end)
            ->sum(DB::raw('desktop_views + mobile_views + tablet_views + unknown_views'));
    }

    /**
     * Get schedules where this talent/venue appeared and received views
     */
    public function getAppearancesByScheduleForRole(int $roleId, int $limit, Carbon $start, Carbon $end): Collection
    {
        return AnalyticsAppearancesDaily::select(
            'schedule_role_id',
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as view_count')
        )
            ->forRole($roleId)
            ->inDateRange($start, $end)
            ->groupBy('schedule_role_id')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->with('scheduleRole')
            ->get()
            ->filter(fn ($item) => $item->scheduleRole !== null)
            ->map(fn ($item) => [
                'role' => $item->scheduleRole,
                'view_count' => (int) $item->view_count,
            ]);
    }

    /**
     * Group paid sales by the linked event's currency.
     * Returns a list of records [['currency_code' => 'USD', 'sales_count' => 2, 'amount' => 100.5], ...]
     * sorted by amount desc, with zero-amount currencies filtered out.
     */
    private function salesByCurrency(Collection $eventIds, Carbon $start, Carbon $end, string $column = 'payment_amount', ?callable $modifier = null): array
    {
        if (! in_array($column, ['payment_amount', 'discount_amount'], true)) {
            throw new \InvalidArgumentException("Invalid column: $column");
        }

        $query = DB::table('sales')
            ->join('events', 'sales.event_id', '=', 'events.id')
            ->whereIn('sales.event_id', $eventIds->toArray())
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$start, $end]);

        if ($modifier) {
            $modifier($query);
        }

        // COUNT of PURCHASES, not of rows. One checkout writes a row per named guest, and now also
        // a row per event in a cart, so a plain COUNT(*) reports a single four-guest purchase as
        // four sales - inflating the conversion counts behind the boost and newsletter stats while
        // the revenue sums stay right. Counting distinct order/group anchors collapses each
        // checkout back to one, and an ungrouped sale is its own anchor via its id.
        return $query
            ->groupBy('events.ticket_currency_code')
            ->selectRaw('events.ticket_currency_code as currency_code, COUNT(DISTINCT COALESCE(sales.order_id, sales.group_id, sales.id)) as sales_count, COALESCE(SUM(sales.'.$column.'), 0) as amount')
            ->orderByDesc('amount')
            ->get()
            ->map(fn ($row) => [
                'currency_code' => $row->currency_code ?: 'USD',
                'sales_count' => (int) $row->sales_count,
                'amount' => (float) $row->amount,
            ])
            ->filter(fn ($row) => $row['amount'] > 0)
            ->values()
            ->all();
    }

    /**
     * Get conversion statistics (views to sales)
     */
    public function getConversionStats(User $user, Carbon $start, Carbon $end, ?int $roleId = null, ?int $eventId = null): array
    {
        $emptyStats = [
            'total_views' => 0,
            'total_sales' => 0,
            'conversion_rate' => 0,
            'total_revenue' => 0,
            'total_revenue_by_currency' => [],
            'currency_count' => 0,
            'primary_currency' => null,
            'revenue_per_view' => null,
            'promo_sales' => 0,
            'promo_discounts' => 0,
            'promo_discounts_by_currency' => [],
        ];

        if ($eventId) {
            $eventIds = collect([$eventId]);
        } else {
            $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

            if ($roleIds->isEmpty()) {
                return $emptyStats;
            }

            // Only include events the user owns the data for (created by them,
            // or by a role they own/admin). Curated-but-not-created events are excluded.
            $eventIds = $this->ownedEventIdsForRoles($user, $roleIds);
        }

        if ($eventIds->isEmpty()) {
            return $emptyStats;
        }

        $stats = AnalyticsEventsDaily::select(
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as total_views'),
            DB::raw('SUM(promo_sales_count) as promo_sales')
        )
            ->forEvents($eventIds)
            ->inDateRange($start, $end)
            ->first();

        $revenueByCurrency = $this->salesByCurrency($eventIds, $start, $end, 'payment_amount');
        $discountsByCurrency = $this->salesByCurrency($eventIds, $start, $end, 'discount_amount',
            fn ($q) => $q->whereNotNull('sales.promo_code_id')
        );

        // Count all paid sales separately so zero-payment sales (e.g. 100%-off promos)
        // aren't dropped along with their currency group in $revenueByCurrency.
        //
        // PURCHASES, not rows - the same collapse salesByCurrency() above does, and for the same
        // reason: one checkout writes a row per named guest and a row per event in a cart, so a
        // plain count reported a single purchase as four and inflated conversion_rate with it.
        // The two figures sit side by side on the analytics page and used to disagree.
        $totalSales = (int) Sale::whereIn('event_id', $eventIds->toArray())
            ->where('status', 'paid')
            ->where('is_deleted', false)
            ->whereBetween('created_at', [$start, $end])
            ->distinct()
            ->count(DB::raw('COALESCE(sales.order_id, sales.group_id, sales.id)'));

        $totalViews = (int) ($stats->total_views ?? 0);
        $totalRevenue = (float) array_sum(array_column($revenueByCurrency, 'amount'));
        $totalDiscounts = (float) array_sum(array_column($discountsByCurrency, 'amount'));
        $promoSales = (int) ($stats->promo_sales ?? 0);

        $currencyCount = count($revenueByCurrency);
        $primaryCurrency = $revenueByCurrency[0]['currency_code'] ?? null;

        // Revenue-per-view is only meaningful when revenue is in a single currency.
        $revenuePerView = ($totalViews > 0 && $currencyCount === 1)
            ? round($totalRevenue / $totalViews, 2)
            : null;

        return [
            'total_views' => $totalViews,
            'total_sales' => $totalSales,
            'conversion_rate' => $totalViews > 0 ? round(($totalSales / $totalViews) * 100, 2) : 0,
            'total_revenue' => $totalRevenue,
            'total_revenue_by_currency' => $revenueByCurrency,
            'currency_count' => $currencyCount,
            'primary_currency' => $primaryCurrency,
            'revenue_per_view' => $revenuePerView,
            'promo_sales' => $promoSales,
            'promo_discounts' => $totalDiscounts,
            'promo_discounts_by_currency' => $discountsByCurrency,
        ];
    }

    /**
     * Get top events by revenue
     */
    public function getTopEventsByRevenue(User $user, int $limit, Carbon $start, Carbon $end, ?int $eventId = null, ?int $roleId = null): Collection
    {
        if ($eventId) {
            $eventIds = collect([$eventId]);
        } else {
            $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

            if ($roleIds->isEmpty()) {
                return collect();
            }

            // Only include events the user owns the data for; excludes
            // curated-but-not-created events.
            $eventIds = $this->ownedEventIdsForRoles($user, $roleIds);
        }

        if ($eventIds->isEmpty()) {
            return collect();
        }

        // Get top events by revenue from the sales table (source of truth)
        $salesData = Sale::whereIn('event_id', $eventIds->toArray())
            ->where('status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('event_id')
            ->selectRaw('event_id, COUNT(*) as sales_count, COALESCE(SUM(payment_amount), 0) as revenue')
            ->havingRaw('COALESCE(SUM(payment_amount), 0) > 0')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        if ($salesData->isEmpty()) {
            return collect();
        }

        // Get view counts from analytics for matched events
        $salesEventIds = $salesData->pluck('event_id');
        $viewCounts = AnalyticsEventsDaily::select(
            'event_id',
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as view_count')
        )
            ->forEvents($salesEventIds)
            ->inDateRange($start, $end)
            ->groupBy('event_id')
            ->get()
            ->keyBy('event_id');

        $events = Event::whereIn('id', $salesEventIds)->get()->keyBy('id');

        return $salesData
            ->filter(fn ($item) => isset($events[$item->event_id]))
            ->map(function ($item) use ($viewCounts, $events) {
                $viewCount = isset($viewCounts[$item->event_id]) ? (int) $viewCounts[$item->event_id]->view_count : 0;
                $event = $events[$item->event_id];

                return [
                    'event' => $event,
                    'view_count' => $viewCount,
                    'sales_count' => (int) $item->sales_count,
                    'revenue' => (float) $item->revenue,
                    'currency_code' => $event->ticket_currency_code ?: 'USD',
                    'conversion_rate' => $viewCount > 0 ? round(($item->sales_count / $viewCount) * 100, 2) : 0,
                ];
            })
            ->values();
    }

    /**
     * Get traffic sources breakdown
     */
    public function getTrafficSources(User $user, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return AnalyticsReferrersDaily::select(
            'source',
            DB::raw('SUM(views) as view_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->groupBy('source')
            ->orderByDesc('view_count')
            ->get()
            ->map(fn ($item) => [
                'source' => $item->source,
                'view_count' => (int) $item->view_count,
            ]);
    }

    /**
     * Get top referrer domains
     */
    public function getTopReferrerDomains(User $user, int $limit, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return AnalyticsReferrersDaily::select(
            'domain',
            'source',
            DB::raw('SUM(views) as view_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->whereNotNull('domain')
            ->where('domain', '!=', '')
            ->groupBy('domain', 'source')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'domain' => $item->domain,
                'source' => $item->source,
                'view_count' => (int) $item->view_count,
            ]);
    }

    /**
     * Get top UTM parameter values for a given param type
     */
    public function getTopUtmParams(User $user, string $paramType, int $limit, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return AnalyticsUtmDaily::select(
            'param_value',
            DB::raw('SUM(views) as view_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->byParamType($paramType)
            ->groupBy('param_value')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'param_value' => $item->param_value,
                'view_count' => (int) $item->view_count,
            ]);
    }

    /**
     * Get social link click stats grouped by platform
     */
    public function getSocialClickStats(User $user, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return AnalyticsSocialClicksDaily::select(
            'platform',
            DB::raw('SUM(clicks) as click_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->groupBy('platform')
            ->orderByDesc('click_count')
            ->get()
            ->map(fn ($item) => [
                'platform' => $item->platform,
                'click_count' => (int) $item->click_count,
            ]);
    }

    /**
     * Get visitor location breakdown by country
     */
    public function getLocationBreakdown(User $user, int $limit, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return AnalyticsLocationsDaily::select(
            'country_code',
            DB::raw('SUM(views) as view_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->groupBy('country_code')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'country_code' => $item->country_code,
                'country_name' => CountryUtils::getName($item->country_code),
                'view_count' => (int) $item->view_count,
            ]);
    }

    /**
     * Get boost-attributed page views for given roles in a date range
     */
    public function getBoostAttributedViews(Collection $roleIds, Carbon $start, Carbon $end): int
    {
        return (int) AnalyticsReferrersDaily::forRoles($roleIds)
            ->inDateRange($start, $end)
            ->bySource('boost')
            ->sum('views');
    }

    /**
     * Get boost-attributed sales and revenue for given roles in a date range
     */
    public function getBoostSalesStats(Collection $roleIds, Carbon $start, Carbon $end): array
    {
        $eventIds = DB::table('event_role')
            ->whereIn('role_id', $roleIds)
            ->pluck('event_id')
            ->unique();

        $empty = [
            'sales' => 0,
            'revenue' => 0,
            'revenue_by_currency' => [],
            'currency_count' => 0,
            'primary_currency' => null,
        ];

        if ($eventIds->isEmpty()) {
            return $empty;
        }

        $byCurrency = $this->salesByCurrency($eventIds, $start, $end, 'payment_amount',
            fn ($q) => $q->whereNotNull('sales.boost_campaign_id')
        );

        return [
            'sales' => (int) array_sum(array_column($byCurrency, 'sales_count')),
            'revenue' => (float) array_sum(array_column($byCurrency, 'amount')),
            'revenue_by_currency' => $byCurrency,
            'currency_count' => count($byCurrency),
            'primary_currency' => $byCurrency[0]['currency_code'] ?? null,
        ];
    }

    /**
     * Get boost views grouped by period for chart overlay
     */
    public function getBoostViewsByPeriod(User $user, string $period, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        $dateFormatExpr = match ($period) {
            'daily' => DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as period"),
            'weekly' => DB::raw("DATE_FORMAT(date, '%x-%v') as period"),
            'monthly' => DB::raw("DATE_FORMAT(date, '%Y-%m') as period"),
            default => DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as period"),
        };

        return AnalyticsReferrersDaily::select(
            $dateFormatExpr,
            DB::raw('SUM(views) as view_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->bySource('boost')
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Get boost campaign stats for the analytics dashboard
     */
    public function getBoostStats(User $user, Carbon $start, Carbon $end, ?int $roleId = null): array
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return ['has_data' => false];
        }

        $campaigns = BoostCampaign::with('event:id,name')
            ->whereIn('role_id', $roleIds)
            ->whereIn('status', ['active', 'paused', 'completed'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('scheduled_start', '<=', $end)
                            ->where(function ($q3) use ($start) {
                                $q3->whereNull('scheduled_end')
                                    ->orWhere('scheduled_end', '>=', $start);
                            });
                    });
            })
            ->orderByDesc('created_at')
            ->get();

        if ($campaigns->isEmpty()) {
            return ['has_data' => false];
        }

        $totalSpend = $campaigns->sum('actual_spend') ?? 0;
        $totalBudget = $campaigns->sum('user_budget') ?? 0;
        $totalImpressions = $campaigns->sum('impressions') ?? 0;
        $totalClicks = $campaigns->sum('clicks') ?? 0;
        $totalConversions = $campaigns->sum('conversions') ?? 0;

        $avgCtr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;
        $avgCpc = $totalClicks > 0 ? round($totalSpend / $totalClicks, 2) : 0;

        // Boost-attributed page views and sales
        $boostViews = $this->getBoostAttributedViews($roleIds, $start, $end);
        $boostSalesStats = $this->getBoostSalesStats($roleIds, $start, $end);

        $costPerView = $boostViews > 0 ? round((float) $totalSpend / $boostViews, 2) : 0;
        $costPerSale = $boostSalesStats['sales'] > 0 ? round((float) $totalSpend / $boostSalesStats['sales'], 2) : 0;
        // What the operator is billed in. Campaigns snapshot it at purchase, and the set can mix
        // Meta boosts (META_DEFAULT_CURRENCY) with network promotions (PROMOTIONS_CURRENCY), so
        // take the most COMMON code rather than whichever campaign happens to be newest - the
        // list is ordered by created_at, so "first" made the label on a fixed historical total
        // flip every time a campaign in the other currency was created.
        $spendCurrency = $campaigns->pluck('currency_code')->filter()->countBy()->sortDesc()->keys()->first()
            ?: config('services.meta.default_currency', 'USD');

        // ROAS (revenue / spend) only makes sense when boost revenue is in the same currency as
        // the spend. That used to be hardcoded to USD on both sides, which hid the ratio from
        // every operator not billing in dollars even when the two agreed. Hide on mismatch.
        $roas = ((float) $totalSpend > 0
                && $boostSalesStats['currency_count'] === 1
                && $boostSalesStats['primary_currency'] === $spendCurrency)
            ? round($boostSalesStats['revenue'] / (float) $totalSpend, 2)
            : null;

        return [
            'has_data' => true,
            'spend_currency' => $spendCurrency,
            'total_spend' => (float) $totalSpend,
            'total_budget' => (float) $totalBudget,
            'total_impressions' => (int) $totalImpressions,
            'total_clicks' => (int) $totalClicks,
            'total_conversions' => (int) $totalConversions,
            'avg_ctr' => $avgCtr,
            'avg_cpc' => $avgCpc,
            'boost_views' => $boostViews,
            'boost_sales' => $boostSalesStats['sales'],
            'boost_revenue' => $boostSalesStats['revenue'],
            'boost_revenue_by_currency' => $boostSalesStats['revenue_by_currency'],
            'currency_count' => $boostSalesStats['currency_count'],
            'primary_currency' => $boostSalesStats['primary_currency'],
            'cost_per_view' => $costPerView,
            'cost_per_sale' => $costPerSale,
            'roas' => $roas,
            'campaigns' => $campaigns->map(fn ($c) => [
                'hash' => $c->hashedId(),
                'name' => $c->name,
                'event_name' => $c->event?->translatedName() ?? 'N/A',
                'status' => $c->status,
                'spend' => (float) ($c->actual_spend ?? 0),
                'spend_currency' => $c->currency_code,
                'impressions' => (int) ($c->impressions ?? 0),
                'clicks' => (int) ($c->clicks ?? 0),
            ])->toArray(),
        ];
    }

    /**
     * Get newsletter-attributed page views for given roles in a date range
     */
    public function getNewsletterAttributedViews(Collection $roleIds, Carbon $start, Carbon $end): int
    {
        return (int) AnalyticsReferrersDaily::forRoles($roleIds)
            ->inDateRange($start, $end)
            ->bySource('newsletter')
            ->sum('views');
    }

    /**
     * Get newsletter-attributed sales and revenue for given roles in a date range
     */
    public function getNewsletterSalesStats(Collection $roleIds, Carbon $start, Carbon $end): array
    {
        $eventIds = DB::table('event_role')
            ->whereIn('role_id', $roleIds)
            ->pluck('event_id')
            ->unique();

        $empty = [
            'sales' => 0,
            'revenue' => 0,
            'revenue_by_currency' => [],
            'currency_count' => 0,
            'primary_currency' => null,
        ];

        if ($eventIds->isEmpty()) {
            return $empty;
        }

        $byCurrency = $this->salesByCurrency($eventIds, $start, $end, 'payment_amount',
            fn ($q) => $q->whereNotNull('sales.newsletter_id')
        );

        return [
            'sales' => (int) array_sum(array_column($byCurrency, 'sales_count')),
            'revenue' => (float) array_sum(array_column($byCurrency, 'amount')),
            'revenue_by_currency' => $byCurrency,
            'currency_count' => count($byCurrency),
            'primary_currency' => $byCurrency[0]['currency_code'] ?? null,
        ];
    }

    /**
     * Get newsletter views grouped by period for chart overlay
     */
    public function getNewsletterViewsByPeriod(User $user, string $period, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        $dateFormatExpr = match ($period) {
            'daily' => DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as period"),
            'weekly' => DB::raw("DATE_FORMAT(date, '%x-%v') as period"),
            'monthly' => DB::raw("DATE_FORMAT(date, '%Y-%m') as period"),
            default => DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as period"),
        };

        return AnalyticsReferrersDaily::select(
            $dateFormatExpr,
            DB::raw('SUM(views) as view_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->bySource('newsletter')
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Get per-promo-code usage breakdown
     */
    public function getPromoCodeStats(User $user, Carbon $start, Carbon $end, ?int $roleId = null, ?int $eventId = null): Collection
    {
        if ($eventId) {
            $eventIds = collect([$eventId]);
        } else {
            $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

            if ($roleIds->isEmpty()) {
                return collect();
            }

            // Only include events the user owns the data for; excludes
            // curated-but-not-created events.
            $eventIds = $this->ownedEventIdsForRoles($user, $roleIds);
        }

        if ($eventIds->isEmpty()) {
            return collect();
        }

        $stats = DB::table('sales')
            ->join('events', 'sales.event_id', '=', 'events.id')
            ->select(
                'sales.promo_code_id',
                DB::raw('events.ticket_currency_code as currency_code'),
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('SUM(sales.discount_amount) as total_discount')
            )
            ->whereIn('sales.event_id', $eventIds)
            ->whereNotNull('sales.promo_code_id')
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$start, $end])
            ->groupBy('sales.promo_code_id', 'events.ticket_currency_code')
            ->orderByDesc('sales_count')
            ->get();

        if ($stats->isEmpty()) {
            return collect();
        }

        $promoCodes = PromoCode::whereIn('id', $stats->pluck('promo_code_id'))->get()->keyBy('id');

        return $stats->map(fn ($row) => [
            'code' => $promoCodes[$row->promo_code_id]->code ?? '—',
            'type' => $promoCodes[$row->promo_code_id]->type ?? null,
            'value' => $promoCodes[$row->promo_code_id]->value ?? null,
            'currency_code' => $row->currency_code ?: 'USD',
            'sales_count' => (int) $row->sales_count,
            'total_discount' => (float) $row->total_discount,
        ]);
    }

    /**
     * Get check-in analytics stats
     */
    public function getCheckinStats(User $user, Carbon $start, Carbon $end, ?int $roleId = null, ?int $eventId = null): array
    {
        // Assigned only in the else branch below, but read again by the timezone lookup, which
        // runs on BOTH paths. An ?event_id= URL carrying no role_id lands here with it unset -
        // reachable because encodeId(null) is null and the tab links array_filter() it away, and
        // because the controller auto-fills role_id only for a user with exactly one schedule.
        // Left undefined it fatals on ->isNotEmpty(), but only once there are sale tickets to
        // show, since the empty case returns before the lookup.
        $roleIds = collect();

        if ($eventId) {
            $eventIds = collect([$eventId]);
        } else {
            $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

            if ($roleIds->isEmpty()) {
                return ['has_data' => false];
            }

            // Only include events the user owns the data for; excludes
            // curated-but-not-created events.
            $eventIds = $this->ownedEventIdsForRoles($user, $roleIds);
        }

        if ($eventIds->isEmpty()) {
            return ['has_data' => false];
        }

        // Get all sale tickets for paid sales in the date range
        $saleTickets = SaleTicket::whereHas('sale', function ($q) use ($eventIds, $start, $end) {
            $q->whereIn('event_id', $eventIds)
                ->where('status', 'paid')
                ->whereBetween('event_date', [$start->toDateString(), $end->toDateString()]);
        })
            ->with(['sale:id,event_id,event_date', 'sale.event:id,name,name_en,creator_role_id', 'ticket:id,type'])
            ->get();

        if ($saleTickets->isEmpty()) {
            return ['has_data' => false];
        }

        // Determine timezone for arrival hours
        $timezone = null;
        if ($roleId) {
            $timezone = Role::where('id', $roleId)->value('timezone');
        } elseif ($roleIds->isNotEmpty()) {
            $timezone = Role::where('id', $roleIds->first())->value('timezone');
        } elseif ($eventId) {
            // Single-event view with no schedule filter - the path that used to fatal here. An
            // arrival hour belongs to where the event happens, not to whoever is reading the
            // page, so resolve the event's own schedule rather than dropping through to the
            // app timezone that the null branch below uses.
            $timezone = $saleTickets->first()?->sale?->event?->scheduleTimezone();
        }

        $totalSold = 0;
        $totalCheckedIn = 0;
        $arrivalHours = array_fill(0, 24, 0);
        $eventsData = [];
        $ticketTypesData = [];

        foreach ($saleTickets as $saleTicket) {
            $event = $saleTicket->sale?->event;
            if (! $event) {
                continue;
            }

            $seats = json_decode($saleTicket->seats, true) ?? [];
            $quantity = $saleTicket->quantity;
            $checkedIn = 0;

            foreach ($seats as $timestamp) {
                if ($timestamp !== null) {
                    $checkedIn++;
                    $hour = $timezone
                        ? Carbon::createFromTimestamp($timestamp)->setTimezone($timezone)->hour
                        : (int) date('G', $timestamp);
                    $arrivalHours[$hour]++;
                }
            }

            $totalSold += $quantity;
            $totalCheckedIn += $checkedIn;

            // Events breakdown
            $eventId = $saleTicket->sale->event_id;
            if (! isset($eventsData[$eventId])) {
                $eventsData[$eventId] = [
                    'event_name' => $event->translatedName() ?? '',
                    'event_date' => $saleTicket->sale->event_date,
                    'sold' => 0,
                    'checked_in' => 0,
                ];
            }
            $eventsData[$eventId]['sold'] += $quantity;
            $eventsData[$eventId]['checked_in'] += $checkedIn;

            // Ticket type breakdown
            $ticketId = $saleTicket->ticket_id;
            $ticketName = $saleTicket->ticket?->type ?? __('messages.unknown');
            if (! isset($ticketTypesData[$ticketId])) {
                $ticketTypesData[$ticketId] = [
                    'name' => $ticketName,
                    'sold' => 0,
                    'checked_in' => 0,
                ];
            }
            $ticketTypesData[$ticketId]['sold'] += $quantity;
            $ticketTypesData[$ticketId]['checked_in'] += $checkedIn;
        }

        // Calculate rates
        $attendanceRate = $totalSold > 0 ? round(($totalCheckedIn / $totalSold) * 100, 1) : 0;

        // Sort events by date descending
        $eventsBreakdown = collect($eventsData)->map(function ($event) {
            $event['attendance_rate'] = $event['sold'] > 0
                ? round(($event['checked_in'] / $event['sold']) * 100, 1) : 0;

            return $event;
        })->sortByDesc('event_date')->values()->toArray();

        // Add attendance rate to ticket types
        $ticketTypes = collect($ticketTypesData)->map(function ($type) {
            $type['attendance_rate'] = $type['sold'] > 0
                ? round(($type['checked_in'] / $type['sold']) * 100, 1) : 0;

            return $type;
        })->sortByDesc('sold')->values()->toArray();

        return [
            'has_data' => true,
            'total_sold' => $totalSold,
            'total_checked_in' => $totalCheckedIn,
            'attendance_rate' => $attendanceRate,
            'no_show_rate' => round(100 - $attendanceRate, 1),
            'events_breakdown' => $eventsBreakdown,
            'ticket_types' => $ticketTypes,
            'arrival_hours' => $arrivalHours,
        ];
    }

    /**
     * Get newsletter stats for the analytics dashboard
     */
    public function getNewsletterStats(User $user, Carbon $start, Carbon $end, ?int $roleId = null): array
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return ['has_data' => false];
        }

        $newsletters = Newsletter::whereIn('role_id', $roleIds)
            ->where('status', 'sent')
            ->whereBetween('sent_at', [$start, $end])
            ->orderByDesc('sent_at')
            ->get();

        if ($newsletters->isEmpty()) {
            return ['has_data' => false];
        }

        $totalSent = $newsletters->sum('sent_count') ?? 0;
        $totalOpens = $newsletters->sum('open_count') ?? 0;
        $totalClicks = $newsletters->sum('click_count') ?? 0;

        $openRate = $totalSent > 0 ? round(($totalOpens / $totalSent) * 100, 1) : 0;
        $clickRate = $totalSent > 0 ? round(($totalClicks / $totalSent) * 100, 1) : 0;

        // Newsletter-attributed page views and sales
        $newsletterViews = $this->getNewsletterAttributedViews($roleIds, $start, $end);
        $newsletterSalesStats = $this->getNewsletterSalesStats($roleIds, $start, $end);

        return [
            'has_data' => true,
            'total_sent' => (int) $totalSent,
            'total_opens' => (int) $totalOpens,
            'total_clicks' => (int) $totalClicks,
            'open_rate' => $openRate,
            'click_rate' => $clickRate,
            'newsletter_views' => $newsletterViews,
            'newsletter_sales' => $newsletterSalesStats['sales'],
            'newsletter_revenue' => $newsletterSalesStats['revenue'],
            'newsletter_revenue_by_currency' => $newsletterSalesStats['revenue_by_currency'],
            'currency_count' => $newsletterSalesStats['currency_count'],
            'primary_currency' => $newsletterSalesStats['primary_currency'],
            'campaigns' => $newsletters->map(fn ($n) => [
                'hash' => UrlUtils::encodeId($n->id),
                'subject' => $n->subject,
                'sent_at' => $n->sent_at,
                'sent_count' => (int) ($n->sent_count ?? 0),
                'open_count' => (int) ($n->open_count ?? 0),
                'click_count' => (int) ($n->click_count ?? 0),
            ])->toArray(),
        ];
    }
}
