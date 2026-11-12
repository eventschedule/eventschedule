<?php

namespace App\Http\Controllers;

use App\Models\MarketingPageDaily;
use App\Models\PageView;
use Illuminate\Http\Request;

/**
 * Beacon target for the per-page marketing counters (MarketingPageDaily), sent by
 * partials/marketing-page-stats: a `view` when a page opens, `reached_end` when its last
 * section comes into view, `signup_click` on a click of a sign-up link.
 *
 * In routes/api.php for the reasons the Realtime and guest-funnel beacons are: no session, no
 * cookie and no CSRF, so a beacon from an edge-cached page never hands the visitor a session,
 * and it answers same-origin on every host, the blog's (blog.{domain}) included. The body is
 * JSON read from the raw content, `{"page": "<route name>", "kind": "<kind>", "w": <viewport
 * width>}`; the page is a route NAME checked against the router, never a path.
 *
 * Filtered like the visit and headline-test beacons (bots, requests that do not look like a
 * browser; a signed-in visitor is left out where the page is drawn, since this group has no
 * session to ask) and deduped per IP+UA per day, per page and kind, on the same daily-salted
 * hash. Counts only: nothing about the visitor is stored, so it needs no consent. Always 204 for
 * a well-formed body, counted or not.
 */
class MarketingPageStatController extends Controller
{
    /** Longer than any honest payload by a wide margin; nothing past it is parsed. */
    private const MAX_BODY = 512;

    public function __invoke(Request $request)
    {
        abort_unless(config('app.is_nexus'), 404);

        $raw = $request->getContent();
        $data = strlen($raw) <= self::MAX_BODY ? json_decode($raw, true) : null;

        if (! is_array($data)) {
            return response()->noContent(422);
        }

        $page = $data['page'] ?? null;
        $kind = $data['kind'] ?? null;
        $width = filter_var($data['w'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 20000]]);

        if (! is_string($kind) || ! isset(MarketingPageDaily::KINDS[$kind])
            || $width === false
            || ! MarketingPageDaily::isCountablePage($page)) {
            return response()->noContent(422);
        }

        // The partial's address has no host, so a real beacon is always same-origin. Anything else
        // is another site making its visitors' browsers post here. Browsers too old to send the
        // header are let through, as the guest-funnel beacon lets them.
        $fetchSite = $request->header('Sec-Fetch-Site');

        if (($fetchSite === null || $fetchSite === 'same-origin')
            && ! PageView::isBot($request->userAgent())
            && ! PageView::isSuspiciousRequest($request, false)
            && PageView::isFirstDailyVisit('mkt_page_'.$kind.'_'.$page, $request->header('CF-Connecting-IP') ?? $request->ip(), $request->userAgent())) {
            MarketingPageDaily::record($page, MarketingPageDaily::deviceFor($width), $kind);
        }

        return response()->noContent();
    }
}
