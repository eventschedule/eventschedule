<?php

namespace App\Http\Controllers\Traits;

use App\Models\AnalyticsMissingDaily;
use App\Models\PageView;
use App\Models\Role;
use Illuminate\Http\Response;

/**
 * The tenant "this address matches nothing" response.
 *
 * viewGuest() and photoGallery() both used to end with redirect($role->getGuestUrl()), which made a
 * dead event link indistinguishable from a live one: the visitor landed on a working calendar, the
 * owner saw a 302 rather than an error, and PageView::recordView() booked the visit against the
 * schedule and against no event. A schedule whose share links had rotted therefore looked like a
 * schedule whose analytics were broken - which is exactly how it was reported.
 *
 * Not abort(404): that renders errors/404.blade.php, the PLATFORM page, whose every link is a
 * marketing_url() to eventschedule.com. Serving that on a customer's custom domain hands their
 * visitors to us. ResolveCustomDomain::isHtmlResponse() now permits 404 so this body still gets
 * host-rewritten onto the custom domain.
 */
trait RendersGuestNotFound
{
    protected function guestNotFound(Role $role, ?string $slug = null): Response
    {
        $this->recordMissingAddress($role, $slug);

        // An embed is an iframe on somebody else's site; a full-bleed 404 inside it is louder than
        // the failure warrants, and there is no useful "back to the schedule" from in there.
        if (request()->embed) {
            return response('', 404);
        }

        return response()->view('role.not-found', [
            'role' => $role,
            // getCanonicalUrl() -> getGuestUrl(), which returns '' for a schedule failing
            // isClaimed() - and role/not-found.blade.php renders it straight into href="", which
            // just reloads the 404. That is not a corner case any more: this release gives
            // auto-created, unclaimed schedules real public pages, and photoGallery() reaches here
            // for exactly those. app_url() at least lands the visitor somewhere real.
            'homeUrl' => $role->getCanonicalUrl() ?: app_url(),
        ], 404);
    }

    /**
     * Count the miss, so a rotted link is something an owner can SEE on /analytics rather than
     * something they infer from an event that mysteriously has no views.
     *
     * Same bot and suspicious-request filters the view counters use - a 404 log full of scanner
     * traffic would bury the one real broken link it exists to surface.
     */
    private function recordMissingAddress(Role $role, ?string $slug): void
    {
        if (! $slug || ! $role->exists) {
            return;
        }

        $request = request();

        if (PageView::isBot($request->userAgent()) || PageView::isSuspiciousRequest($request)) {
            return;
        }

        $ip = PageView::clientIp($request);

        AnalyticsMissingDaily::incrementView(
            $role->id,
            $slug,
            $ip ? PageView::getIpHash($ip) : null
        );
    }
}
