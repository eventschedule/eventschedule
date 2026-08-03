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

        // Docs and selfhost pages are a SUBSET of the totals above, tracked separately because
        // a selfhoster reading /docs/installation is not a prospect for the hosted plans.
        // Without this split the headline visitor -> signup rate cannot distinguish a
        // conversion problem from a traffic-mix one.
        $isDocs = $request->routeIs('marketing.docs.*')
            || $request->routeIs('marketing.selfhost')
            || $request->routeIs('marketing.self_hosting_terms');

        if ($isDocs) {
            MarketingDailyStat::record('docs_page_views');
        }

        // Unique visitors: dedup by a daily-salted IP+UA hash rather than a session cookie,
        // so cookieless bots (which get a fresh session per request) cannot each be counted
        // as a new visitor. Prefer Cloudflare's real client IP, matching PageView::recordView().
        $ip = $request->header('CF-Connecting-IP') ?? $request->ip();
        if (PageView::isFirstDailyVisit('mkt_visit', $ip, $request->userAgent())) {
            MarketingDailyStat::record('visitors');
        }

        // Deduped independently of the total, so someone who reads the docs AND a product page
        // counts once in each. docs_visitors can therefore never exceed visitors, but the two
        // buckets do overlap - subtracting gives a lower bound on buyer-intent traffic.
        if ($isDocs && PageView::isFirstDailyVisit('mkt_docs_visit', $ip, $request->userAgent())) {
            MarketingDailyStat::record('docs_visitors');
        }

        return $response;
    }
}
