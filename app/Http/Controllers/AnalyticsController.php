<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\AnalyticsService;
use App\Utils\UrlUtils;
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
        $tab = in_array($request->tab, ['web', 'revenue', 'checkins'], true) ? $request->tab : 'web';
        $ownedDataOnly = $tab !== 'web';

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

        // Get events list for the dropdown (only when a schedule is selected)
        $events = $selectedRoleId ? $analytics->getEventsForSchedule($selectedRoleId, $ownedDataOnly) : collect();

        // Resolve the selected event's display name for the initial (pre-Vue-mount) render of the
        // picker. From the event itself, never from the list: the id below filters every panel
        // whether or not the picker lists it (a deep link outside the 30-day window, or the
        // narrower list the private tabs get), and labelling an applied filter "All events" is
        // exactly what reads as missing data.
        $selectedEventName = $selectedEvent ? $selectedEvent->translatedName() : __('messages.all_events');

        // Date range filter
        $range = $request->range ?? 'last_30_days';
        [$start, $end] = match ($range) {
            'last_7_days' => [now()->subDays(7)->startOfDay(), now()->endOfDay()],
            'last_30_days' => [now()->subDays(30)->startOfDay(), now()->endOfDay()],
            'last_90_days' => [now()->subDays(90)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfDay()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfDay()],
            'all_time' => [now()->subYears(10)->startOfDay(), now()->endOfDay()],
            default => [now()->subDays(30)->startOfDay(), now()->endOfDay()],
        };

        if ($tab === 'checkins') {
            $checkinStats = $analytics->getCheckinStats($user, $start, $end, $selectedRoleId, $selectedEventId);

            return view('analytics.index', compact(
                'roles',
                'selectedRoleId',
                'selectedEventId',
                'selectedEventName',
                'tabEventId',
                'events',
                'range',
                'tab',
                'checkinStats'
            ));
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
                'newsletterStats'
            ));
        }

        // Period determines chart grouping
        $period = $request->period ?? 'daily';

        // Get month-over-month comparison (for fixed stats cards)
        $momComparison = $analytics->getMonthOverMonthComparison($user, $selectedRoleId, $selectedEventId);

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

        // Get visitor locations
        $locationBreakdown = $analytics->getLocationBreakdown($user, 10, $start, $end, $selectedRoleId);

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

        return view('analytics.index', compact(
            'roles',
            'selectedRoleId',
            'selectedEventId',
            'selectedEventName',
            'tabEventId',
            'events',
            'totalViews',
            'momComparison',
            'periodComparison',
            'topEvents',
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
            'locationBreakdown'
        ));
    }
}
