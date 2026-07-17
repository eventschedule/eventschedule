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
            || PageView::isBot($request->userAgent())
            || PageView::isSuspiciousRequest($request)) {
            return $response;
        }

        // Raw page views: every qualifying (bot-filtered) view.
        MarketingDailyStat::record('page_views');

        // Unique visitors: dedup by a daily-salted IP+UA hash rather than a session cookie,
        // so cookieless bots (which get a fresh session per request) cannot each be counted
        // as a new visitor. Prefer Cloudflare's real client IP, matching PageView::recordView().
        $ip = $request->header('CF-Connecting-IP') ?? $request->ip();
        if (PageView::isFirstDailyVisit('mkt_visit', $ip, $request->userAgent())) {
            MarketingDailyStat::record('visitors');
        }

        return $response;
    }
}
