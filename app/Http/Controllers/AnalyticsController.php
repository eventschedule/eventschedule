<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\AnalyticsService;
use App\Services\ScheduleActivity;
use App\Services\ScheduleRealtime;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request, AnalyticsService $analytics)
    {
        $user = auth()->user();
        $roleIds = $user->editor()->pluck('roles.id');
        $roles = $user->editor()->get();

        // Get selected role for filtering (decode from URL-safe format)
        // Auto-select when user has only one schedule
        $selectedRoleId = $request->role_id ? UrlUtils::decodeId($request->role_id) : null;
        if (! $selectedRoleId && $roles->count() === 1) {
            $selectedRoleId = $roles->first()->id;
        }

        // Security: Validate that the selected role ID belongs to the authenticated user
        // This prevents enumeration attacks where attackers try to view analytics for other users' roles
        if ($selectedRoleId && ! $roleIds->contains($selectedRoleId)) {
            abort(403, 'Unauthorized access to analytics');
        }

        // Tab first: the revenue and check-ins tabs read the creator's private data rather than
        // page traffic, so they authorize the selected event more strictly and get a narrower
        // picker list. Whitelisted rather than passed through, so the strictness flag and the
        // dispatch branches below can never be reading different values.
        //
        // Realtime is the second tab, and only for someone who has a live view of their own pages
        // (ScheduleRealtime::available(): Realtime and the owners' switch both on, a schedule they
        // manage, not the shared demo account). For anyone else the value is not a tab at all, so
        // a link to it shows Web, like any other value this page does not know.
        $realtimeAvailable = ScheduleRealtime::available($user);
        $tabs = $realtimeAvailable ? ['web', 'realtime', 'revenue', 'checkins'] : ['web', 'revenue', 'checkins'];
        $tab = in_array($request->tab, $tabs, true) ? $request->tab : 'web';
        $ownedDataOnly = $tab !== 'web';

        // The schedule the Realtime tab is narrowed to, and what the tab strip's link to it
        // carries. Live traffic follows manageableRoles(), which on hosted is narrower than the
        // schedules this page lists: a schedule whose plan no longer includes a team is still on
        // the picker here and is closed to its admins there. Such a selection is dropped on the
        // way to that tab, never refused: the tab strip carries the selection forward, and a 403
        // for pressing a tab would be this page's own fault. (The poll, which is asked for one
        // schedule by id, does refuse it: RealtimeController::data().)
        $realtimeRoleId = $realtimeAvailable && $selectedRoleId && $user->manageableRoles()->contains('id', $selectedRoleId)
            ? $selectedRoleId
            : null;
        // What every return below hands the tab strip.
        $realtimeTab = ['realtimeAvailable' => $realtimeAvailable, 'realtimeRoleId' => $realtimeRoleId];

        // Get selected event for filtering (decode from URL-safe format)
        $selectedEventId = $request->event_id ? UrlUtils::decodeId($request->event_id) : null;
        $selectedEvent = null;
        // What the tab links carry. The private tabs drop an event whose data belongs to its
        // creator, and the tab strip rebuilds every href from the selection, so without this a
        // Web -> Revenue -> Web round trip would lose the filter for good.
        $tabEventId = null;

        if ($selectedEventId) {
            $selectedEvent = Event::with('roles')->find($selectedEventId);

            // Security: page traffic is the floor - owner/admin on any schedule the event is
            // attached to, curators included. A curator that lists an event served the view that
            // incremented the row, and the Top events table on this tab already counts it.
            if (! $selectedEvent || ! $user->canViewEventTraffic($selectedEvent)) {
                abort(403, 'Unauthorized access to analytics');
            }

            // Authorized for traffic, so it is safe to keep offering across the tab strip.
            $tabEventId = $selectedEventId;

            // The money still belongs to whoever created it. Drop the filter rather than aborting:
            // the tab links carry event_id forward, so a curator holding a curated event selected
            // would hit a 403 page just by clicking Revenue.
            if ($ownedDataOnly && ! $user->canViewEventData($selectedEvent)) {
                $selectedEventId = null;
                $selectedEvent = null;
            }
        }

        // If a schedule is selected, ensure the event belongs to it. On the private tabs a curator
        // "belongs to" only what it created; on the web tab an attached event is on the curator's
        // own page, which is whose traffic this is. Deliberately looser than the picker list (no
        // is_accepted, no date window) so no listed row can be rejected and a saved deep link to an
        // older event keeps filtering. The creator_role_id arm matches the picker's: an event whose
        // creator lost its event_role row must not lose its filter either.
        if ($selectedEventId && $selectedRoleId) {
            $selectedRole = $roles->firstWhere('id', $selectedRoleId);

            $attachedToRole = $selectedEvent->creator_role_id == $selectedRoleId
                || DB::table('event_role')
                    ->where('event_id', $selectedEventId)
                    ->where('role_id', $selectedRoleId)
                    ->exists();

            $belongsToRole = $selectedRole && $selectedRole->isCurator() && $ownedDataOnly
                ? $selectedEvent->creator_role_id == $selectedRoleId
                : $attachedToRole;

            // Two different failures. Not attached at all means no tab can show it, so the tab
            // strip must stop offering it. Attached but not owned is the private-data rule again,
            // and the web tab can still show its traffic, so the link keeps the id.
            if (! $attachedToRole) {
                $tabEventId = null;
            }

            if (! $belongsToRole) {
                $selectedEventId = null;
                $selectedEvent = null;
            }
        }

        // Get events list for the dropdown (only when a schedule is selected). The Realtime tab
        // has no event picker: it is every page of the schedule, for the last half hour.
        $events = $selectedRoleId && $tab !== 'realtime' ? $analytics->getEventsForSchedule($selectedRoleId, $ownedDataOnly) : collect();

        // Resolve the selected event's display name for the initial (pre-Vue-mount) render of the
        // picker. From the event itself, never from the list: the id below filters every panel
        // whether or not the picker lists it (a deep link outside the 30-day window, or the
        // narrower list the private tabs get), and labelling an applied filter "All events" is
        // exactly what reads as missing data.
        $selectedEventName = $selectedEvent ? $selectedEvent->translatedName() : __('messages.all_events');

        // Date range filter
        $range = $request->range ?? 'last_30_days';
        // "Last N days" is today and the N - 1 before it: N dates, the same span as the N it is
        // compared with (AnalyticsService::getPeriodComparison()). subDays(N) made it N + 1 dates
        // against N, a head start of one day in every comparison.
        [$start, $end] = match ($range) {
            'last_7_days' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'last_30_days' => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'last_90_days' => [now()->subDays(89)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfDay()],
            // NoOverflow: on Oct 31 subMonth() is Oct 1 and on Mar 30 it is Mar 2, so "last
            // month" was this one.
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfDay()],
            'all_time' => [now()->subYears(10)->startOfDay(), now()->endOfDay()],
            default => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
        };

        // Every preset above except 'last_month' ends at now(), because they window things that
        // HAPPENED - views, sales. The check-ins tab is the one panel that windows the event's own
        // date instead, and a "…to now" upper bound there hides an event that has not happened yet:
        // its tickets are sold and its door list exists, but it stays invisible until the morning
        // of the show and then appears, which reads exactly like data going missing. Null means
        // "no upper bound"; a closed historical range keeps both ends.
        $eventDateEnd = $range === 'last_month' ? $end : null;

        // How the charts group: by day, week or month. Whitelisted here because the tab links and
        // the switch in each chart carry it from tab to tab. All Time groups by month unless asked:
        // by day it was a thousand points for a schedule a few years old.
        $defaultPeriod = $range === 'all_time' ? 'monthly' : 'daily';
        $period = in_array($request->period, ['daily', 'weekly', 'monthly'], true) ? $request->period : $defaultPeriod;

        if ($tab === 'realtime') {
            // The picker on this tab lists the schedules the live view covers, and nothing it
            // would have to turn away. $range and $tabEventId stay as they came, so the links back
            // to the other tabs still carry them.
            $roles = $user->manageableRoles()->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
            $selectedRoleId = $realtimeRoleId;
            $realtime = [
                'payload' => ScheduleActivity::trafficPayload($request, $user, $realtimeRoleId),
                'activity' => ScheduleActivity::railPayload($request, $user, $realtimeRoleId),
                'schedules' => ScheduleRealtime::scheduleOptions($user),
                'selected' => $realtimeRoleId ? UrlUtils::encodeId($realtimeRoleId) : null,
                // manageableRoles() leaves out a schedule whose plan has no team, so a team
                // member would find it missing from this tab with no reason given. Sales and
                // Check-in say why; so does this.
                'planBlockedRoles' => $user->planBlockedRoles(),
            ];

            return view('analytics.index', compact(
                'roles',
                'selectedRoleId',
                'selectedEventId',
                'selectedEventName',
                'tabEventId',
                'events',
                'range',
                'period',
                'defaultPeriod',
                'tab',
                'realtime'
            ))->with($realtimeTab);
        }

        if ($tab === 'checkins') {
            $checkinStats = $analytics->getCheckinStats($user, $start, $end, $selectedRoleId, $selectedEventId, $eventDateEnd);

            return view('analytics.index', compact(
                'roles',
                'selectedRoleId',
                'selectedEventId',
                'selectedEventName',
                'tabEventId',
                'events',
                'range',
                'period',
                'defaultPeriod',
                'tab',
                'checkinStats'
            ))->with($realtimeTab);
        }

        if ($tab === 'revenue') {
            // Get conversion stats
            $conversionStats = $analytics->getConversionStats($user, $start, $end, $selectedRoleId, $selectedEventId);

            // Get per-promo-code breakdown
            $promoCodeStats = $conversionStats['promo_sales'] > 0
                ? $analytics->getPromoCodeStats($user, $start, $end, $selectedRoleId, $selectedEventId)
                : collect();

            // Get top events by revenue
            $topEventsByRevenue = $analytics->getTopEventsByRevenue($user, 10, $start, $end, $selectedEventId, $selectedRoleId);

            // Get boost stats
            $boostStats = $analytics->getBoostStats($user, $start, $end, $selectedRoleId);

            // Get newsletter stats
            $newsletterStats = $analytics->getNewsletterStats($user, $start, $end, $selectedRoleId);

            // The money over time, and what it was the window before. Only in one currency: a bar
            // cannot add euros to dollars, and with more than one the figures above say each.
            $revenueChart = null;
            $revenueComparison = null;
            if ($conversionStats['currency_count'] === 1) {
                $currency = $conversionStats['primary_currency'];
                // All Time begins at the first month that took money (by month: a few dozen rows,
                // where by day it was every day of ten years).
                $chartStart = $start;
                if ($range === 'all_time') {
                    $months = array_keys($analytics->getRevenueByPeriod($user, 'monthly', $start, $end, $currency, $selectedRoleId, $selectedEventId));
                    sort($months);
                    $chartStart = $months ? Carbon::createFromFormat('Y-m-d', $months[0].'-01')->startOfDay() : $end->copy()->subDays(29)->startOfDay();
                }
                [$keys, $values] = AnalyticsService::fillPeriods(
                    $analytics->getRevenueByPeriod($user, $period, $chartStart, $end, $currency, $selectedRoleId, $selectedEventId),
                    $period, $chartStart, $end
                );
                $revenueChart = ['keys' => $keys, 'values' => $values, 'currency' => $currency, 'previous' => [], 'previousKeys' => []];
                $revenueChart['events'] = $this->chartEvents($analytics->getChartEvents($user, $chartStart, $end, $selectedRoleId, $selectedEventId, true), $period);

                if ($range !== 'all_time') {
                    [$previousStart, $previousEnd, $label] = $analytics->previousWindow($range);
                    $previous = $analytics->getRevenueTotal($user, $previousStart, $previousEnd, $currency, $selectedRoleId, $selectedEventId);
                    [$previousKeys, $previousValues] = AnalyticsService::fillPeriods(
                        $analytics->getRevenueByPeriod($user, $period, $previousStart, $previousEnd, $currency, $selectedRoleId, $selectedEventId),
                        $period, $previousStart, $previousEnd
                    );
                    $revenueChart['previous'] = array_slice($previousValues, 0, count($keys));
                    $revenueChart['previousKeys'] = array_slice($previousKeys, 0, count($keys));
                    $revenueComparison = [
                        'previous' => $previous,
                        'percentage_change' => $previous > 0
                            ? round((($conversionStats['total_revenue'] - $previous) / $previous) * 100, 1)
                            : null,
                        'label' => $label,
                    ];
                }
            }

            return view('analytics.index', compact(
                'roles',
                'selectedRoleId',
                'selectedEventId',
                'selectedEventName',
                'tabEventId',
                'events',
                'range',
                'tab',
                'conversionStats',
                'promoCodeStats',
                'topEventsByRevenue',
                'boostStats',
                'newsletterStats',
                'period',
                'defaultPeriod',
                'revenueChart',
                'revenueComparison'
            ))->with($realtimeTab);
        }

        // Get period comparison based on selected range (for dynamic comparison card)
        $periodComparison = $range !== 'all_time'
            ? $analytics->getPeriodComparison($user, $range, $start, $end, $selectedRoleId, $selectedEventId)
            : null;

        // Get total views (all time)
        $totalViews = $selectedEventId
            ? $analytics->getTotalViewsForEvent($selectedEventId)
            : $analytics->getTotalViewsForRoles(
                $selectedRoleId ? collect([$selectedRoleId]) : $roleIds
            );

        // Get top events
        $topEvents = $analytics->getTopEvents($user, 10, $start, $end, $selectedEventId, $selectedRoleId);

        // Broken links: schedule-scoped and unfiltered by event, because a miss has no event to
        // attribute it to. Only shown when a schedule is selected, for the same reason the event
        // picker is.
        $missingLinks = $selectedRoleId && ! $selectedEventId
            ? $analytics->getMissingLinks($selectedRoleId, $start, $end)
            : collect();

        // Get views by period for chart
        $viewsByPeriod = $analytics->getViewsByPeriod($user, $period, $start, $end, $selectedRoleId, $selectedEventId);

        // Get device breakdown
        $deviceBreakdown = $analytics->getDeviceBreakdown($user, $start, $end, $selectedRoleId, $selectedEventId);

        // Get views by schedule
        $viewsBySchedule = $analytics->getViewsBySchedule($user, $start, $end, $selectedRoleId);

        // Get top associated talents/venues (appearance views on this schedule)
        $topAppearances = collect();
        if ($selectedRoleId) {
            $selectedRole = $roles->firstWhere('id', $selectedRoleId);
            if ($selectedRole) {
                $topAppearances = $analytics->getTopAppearancesForSchedule($selectedRole, 10, $start, $end);
            }
        }

        // Get appearance views for talents/venues (views from appearing on other schedules)
        $appearanceViews = 0;
        $topSchedulesAppearedOn = collect();
        if ($selectedRoleId) {
            $selectedRole = $roles->firstWhere('id', $selectedRoleId);
            if ($selectedRole && ($selectedRole->isTalent() || $selectedRole->isVenue())) {
                $appearanceViews = $analytics->getTotalAppearanceViewsForRole($selectedRoleId, $start, $end);
                $topSchedulesAppearedOn = $analytics->getAppearancesByScheduleForRole($selectedRoleId, 10, $start, $end);
            }
        }

        // Get traffic sources
        $trafficSources = $analytics->getTrafficSources($user, $start, $end, $selectedRoleId);

        // Get top referrer domains
        $topReferrers = $analytics->getTopReferrerDomains($user, 10, $start, $end, $selectedRoleId);

        // Get top UTM parameters
        $topUtmSources = $analytics->getTopUtmParams($user, 'source', 10, $start, $end, $selectedRoleId);
        $topUtmMediums = $analytics->getTopUtmParams($user, 'medium', 10, $start, $end, $selectedRoleId);
        $topUtmCampaigns = $analytics->getTopUtmParams($user, 'campaign', 10, $start, $end, $selectedRoleId);

        // Get social link click stats
        $socialClickStats = $analytics->getSocialClickStats($user, $start, $end, $selectedRoleId);

        // Get visitor locations, and the whole each listed country is a share of
        $locationBreakdown = $analytics->getLocationBreakdown($user, 10, $start, $end, $selectedRoleId);
        $locationTotal = $locationBreakdown->isNotEmpty() ? $analytics->getLocationTotal($user, $start, $end, $selectedRoleId) : 0;

        // Get boost views by period for chart overlay
        $boostStats = $analytics->getBoostStats($user, $start, $end, $selectedRoleId);
        $boostViewsByPeriod = $boostStats['has_data']
            ? $analytics->getBoostViewsByPeriod($user, $period, $start, $end, $selectedRoleId)
            : collect();

        // Get newsletter views by period for chart overlay
        $newsletterStats = $analytics->getNewsletterStats($user, $start, $end, $selectedRoleId);
        $newsletterViewsByPeriod = $newsletterStats['has_data']
            ? $analytics->getNewsletterViewsByPeriod($user, $period, $start, $end, $selectedRoleId)
            : collect();

        // The hero chart's series, one point for every day, week or month of the range, and the
        // range before it laid under it point for point. "All time" starts where the data does
        // and not ten years back. The overlays are views that came from a boost or a newsletter.
        [$chartStart] = $this->chartStart($range, $start, $period, $viewsByPeriod->pluck('period')->all());
        $pluck = fn ($rows) => collect($rows)->pluck('view_count', 'period')->all();
        [$keys, $values] = AnalyticsService::fillPeriods($pluck($viewsByPeriod), $period, $chartStart, $end);
        $viewsChart = [
            'keys' => $keys,
            'views' => $values,
            'boost' => $boostViewsByPeriod->isNotEmpty() ? AnalyticsService::fillPeriods($pluck($boostViewsByPeriod), $period, $chartStart, $end)[1] : [],
            'newsletter' => $newsletterViewsByPeriod->isNotEmpty() ? AnalyticsService::fillPeriods($pluck($newsletterViewsByPeriod), $period, $chartStart, $end)[1] : [],
            'previous' => [],
            'previousKeys' => [],
        ];
        // The events of the window, marked on the chart under the period they fall in.
        $viewsChart['events'] = $this->chartEvents($analytics->getChartEvents($user, $chartStart, $end, $selectedRoleId, $selectedEventId), $period);
        if ($periodComparison) {
            [$previousStart, $previousEnd] = $analytics->previousWindow($range);
            [$previousKeys, $previousValues] = AnalyticsService::fillPeriods(
                $pluck($analytics->getViewsByPeriod($user, $period, $previousStart, $previousEnd, $selectedRoleId, $selectedEventId)),
                $period, $previousStart, $previousEnd
            );
            // Day for day from the start of each: "this month" so far is laid over the same
            // days of last month, not all of it.
            $viewsChart['previous'] = array_slice($previousValues, 0, count($keys));
            $viewsChart['previousKeys'] = array_slice($previousKeys, 0, count($keys));
        }

        return view('analytics.index', compact(
            'roles',
            'selectedRoleId',
            'selectedEventId',
            'selectedEventName',
            'tabEventId',
            'events',
            'totalViews',
            'periodComparison',
            'topEvents',
            'missingLinks',
            'viewsByPeriod',
            'deviceBreakdown',
            'viewsBySchedule',
            'topAppearances',
            'appearanceViews',
            'topSchedulesAppearedOn',
            'period',
            'range',
            'tab',
            'trafficSources',
            'topReferrers',
            'boostViewsByPeriod',
            'newsletterViewsByPeriod',
            'topUtmSources',
            'topUtmMediums',
            'topUtmCampaigns',
            'socialClickStats',
            'locationBreakdown',
            'locationTotal',
            'defaultPeriod',
            'viewsChart',
            'boostStats',
            'newsletterStats'
        ))->with($realtimeTab);
    }

    /**
     * The events of a chart's window, each given the key of the period it falls in, so the chart
     * can mark it under that day, week or month.
     */
    private function chartEvents(array $events, string $period): array
    {
        return array_map(fn ($e) => $e + ['key' => match ($period) {
            'weekly' => Carbon::parse($e['date'])->format('o-W'),
            'monthly' => substr($e['date'], 0, 7),
            default => $e['date'],
        }], $events);
    }

    /**
     * Where a chart begins. "All time" is ten years back as a WHERE clause, and as a chart that
     * is ten years of empty days before the first view: it begins at the first period that has
     * data instead. $keys are the period keys of the rows (any order).
     */
    private function chartStart(string $range, Carbon $start, string $period, array $keys): array
    {
        if ($range !== 'all_time') {
            return [$start];
        }
        // All Time with no views at all (only appearances or broken links): the last 30 days,
        // not ten years of empty days.
        if (! $keys) {
            return [now()->subDays(29)->startOfDay()];
        }

        sort($keys);
        $first = (string) $keys[0];

        return [match (true) {
            (bool) preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $first) => Carbon::parse($first)->startOfDay(),
            (bool) preg_match('/^(\d{4})-(\d{2})$/', $first, $m) && $period === 'monthly' => Carbon::create((int) $m[1], (int) $m[2], 1)->startOfDay(),
            (bool) preg_match('/^(\d{4})-(\d{2})$/', $first, $m) => Carbon::now()->setISODate((int) $m[1], (int) $m[2])->startOfWeek(Carbon::MONDAY)->startOfDay(),
            default => $start,
        }];
    }
}
