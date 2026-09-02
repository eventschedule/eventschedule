<?php

namespace App\Http\Middleware;

use App\Models\MarketingDailyStat;
use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records top-of-funnel marketing (WP) traffic for the /admin/users onboarding funnel.
 *
 * Counts anonymous guest views of marketing pages only. Runs on the way out so it never
 * delays the response, and MarketingDailyStat::record() is self-contained/fail-safe so a
 * counter write can never break a public page.
 *
 * There is exactly ONE writer per page view. Anonymous marketing HTML is now cached at the
 * edge (CacheableMarketingResponse), so a cached page never reaches this middleware at all;
 * layouts/marketing.blade.php therefore ships a sendBeacon() to marketing.visit, which calls
 * record() below with the same filters and buckets. The layout flags the request when it
 * renders that beacon and this middleware then stands down, so the two can never both count
 * the same view. A marketing response that carries no beacon (a future marketing.* route
 * that does not use the layout, or a dynamically served page whose beacon was blocked) is
 * still counted here. NON_PAGE_ROUTES is counted by neither.
 *
 * Known limit, inherited from moving to a beacon: a visitor with JavaScript disabled is not
 * counted. Every such visitor was already inside the bot/suspicious-request filters' margin
 * of error, and the alternative - counting at the origin - is what made the site uncacheable.
 */
class TrackMarketingVisit
{
    /**
     * Set on the request by layouts/marketing.blade.php when it renders the visit beacon.
     */
    public const BEACON_ATTRIBUTE = 'marketing_visit_beacon';

    /**
     * The marketing.* routes that are not pages, and so are not page views.
     *
     * Both are fetched BY a marketing page rather than being one: the beacon this class
     * feeds, and the docs search widget's index, which a reader pulls on first focus while
     * already counted for the docs page they are reading. Counting the index made one docs
     * reader who used the search look like two.
     *
     * Shared by record() and isCountableRouteName(), which is also what the layout asks
     * before it ships a beacon at all - so no beacon is ever rendered for either, and a
     * hand-built one naming either is refused.
     *
     * The same two routes are CacheableMarketingResponse::STATELESS_ROUTES, for the same
     * underlying reason; MarketingEdgeCacheTest pins the two lists together.
     */
    public const NON_PAGE_ROUTES = [
        'marketing.visit',
        'marketing.docs.search_index',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // The page rendered the beacon, which is now the single writer for this view.
        if ($request->attributes->get(self::BEACON_ATTRIBUTE, false)) {
            return $response;
        }

        if (! $request->isMethod('GET')) {
            return $response;
        }

        self::record($request, $request->route()?->getName());

        return $response;
    }

    /**
     * Count one marketing page view into the daily funnel buckets.
     *
     * Shared by this middleware and the sendBeacon endpoint (MarketingController::recordVisit)
     * so the filters, the buckets and the dedup keys have one implementation.
     *
     * @param  string|null  $routeName  the marketing.* route whose page was viewed
     * @param  bool  $expectDocumentAccept  false for the beacon: a browser sends a wildcard
     *                                      Accept header on a sendBeacon/fetch and cannot be
     *                                      made to send anything else, so the document-
     *                                      navigation Accept heuristic would drop every real
     *                                      beacon. Every other filter (the UA blocklist, the
     *                                      Accept-Language check) applies unchanged.
     */
    public static function record(Request $request, ?string $routeName, bool $expectDocumentAccept = true): void
    {
        // Marketing pages only exist on the nexus; only count anonymous prospects
        // (skip logged-in users/admins), views of a marketing.* route, and skip bots.
        if (! config('app.is_nexus')
            || auth()->check()
            || $routeName === null
            || ! Str::startsWith($routeName, 'marketing.')
            || in_array($routeName, self::NON_PAGE_ROUTES, true)
            || PageView::isBot($request->userAgent())
            || PageView::isSuspiciousRequest($request, $expectDocumentAccept)) {
            return;
        }

        // Raw page views: every qualifying (bot-filtered) view.
        MarketingDailyStat::record('page_views');

        // Docs and selfhost pages are a SUBSET of the totals above, tracked separately because
        // a selfhoster reading /docs/installation is not a prospect for the hosted plans.
        // Without this split the headline visitor -> signup rate cannot distinguish a
        // conversion problem from a traffic-mix one.
        $isDocs = Str::is('marketing.docs.*', $routeName)
            || $routeName === 'marketing.selfhost'
            || $routeName === 'marketing.self_hosting_terms';

        if ($isDocs) {
            MarketingDailyStat::record('docs_page_views');
        }

        // /pricing on its own, because it is the one marketing page whose visit is an explicit
        // buying signal - and it was previously indistinguishable from any other page view, so
        // "did a price change move interest?" had no numerator. Also a SUBSET of page_views, and
        // overlapping the docs buckets rather than excluding them.
        $isPricing = $routeName === 'marketing.pricing';

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
    }

    /**
     * Is this the name of a registered marketing page that a beacon may report?
     *
     * The beacon carries a route NAME rather than a path so the endpoint can check it against
     * the router itself - the same list the middleware would have counted - instead of trusting
     * an arbitrary string from the client. Also what gates whether the layout ships the beacon
     * at all, so the layout and the endpoint can never disagree about which pages are counted.
     */
    public static function isCountableRouteName(?string $routeName): bool
    {
        if ($routeName === null || $routeName === '' || ! Str::startsWith($routeName, 'marketing.')) {
            return false;
        }

        if (in_array($routeName, self::NON_PAGE_ROUTES, true)) {
            return false;
        }

        $route = RouteFacade::getRoutes()->getByName($routeName);

        return $route !== null && in_array('GET', $route->methods(), true);
    }
}
