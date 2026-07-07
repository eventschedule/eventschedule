<?php

namespace App\Http\Middleware;

use App\Models\MarketingDailyStat;
use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records top-of-funnel marketing (WP) traffic for the /admin/users onboarding funnel.
 *
 * Counts anonymous guest views of marketing pages only. Runs on the way out so it never
 * delays the response, and MarketingDailyStat::record() is self-contained/fail-safe so a
 * counter write can never break a public page.
 */
class TrackMarketingVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Marketing pages only exist on the nexus; only count anonymous prospects
        // (skip logged-in users/admins), GET views of a marketing.* route, and skip bots.
        if (! config('app.is_nexus')
            || ! $request->isMethod('GET')
            || auth()->check()
            || ! $request->routeIs('marketing.*')
            || PageView::isBot($request->userAgent())) {
            return $response;
        }

        // Raw page views: every qualifying view.
        MarketingDailyStat::record('page_views');

        // Unique visitors: once per session per UTC day. The flag and the DB row date
        // use the same UTC day so a near-midnight request cannot split them.
        $dayKey = 'mkt_visit_'.now()->format('Ymd');
        if (! $request->session()->has($dayKey)) {
            MarketingDailyStat::record('visitors');
            $request->session()->put($dayKey, true);
        }

        return $response;
    }
}
