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

        // /pricing on its own, because it is the one marketing page whose visit is an explicit
        // buying signal - and it was previously indistinguishable from any other page view, so
        // "did a price change move interest?" had no numerator. Also a SUBSET of page_views, and
        // overlapping the docs buckets rather than excluding them.
        $isPricing = $request->routeIs('marketing.pricing');

        if ($isPricing) {
            MarketingDailyStat::record('pricing_views');
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

        // Its own dedup key, for the same reason docs has one: someone who reads the docs and
        // then /pricing must count once in each bucket.
        if ($isPricing && PageView::isFirstDailyVisit('mkt_pricing_visit', $ip, $request->userAgent())) {
            MarketingDailyStat::record('pricing_visitors');
        }

        return $response;
    }
}
