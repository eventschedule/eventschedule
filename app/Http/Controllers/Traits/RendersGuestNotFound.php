<?php

namespace App\Http\Controllers\Traits;

use App\Models\AnalyticsMissingDaily;
use App\Models\PageView;
use App\Models\Role;
use App\Support\EmbedEventView;
use App\Utils\RealtimeTracker;
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
 *
 * platformNotFound() is the other answer, for a schedule the visitor may not know exists: a deleted
 * one, or one nobody has published. The tenant 404 names the schedule, which confirms the very
 * thing the 404 is there to deny, so those get exactly what an address matching no schedule gets.
 */
trait RendersGuestNotFound
{
    /**
     * What an address that matches no schedule gets: the platform's 404, from abort(404) itself so
     * nothing can tell the two apart (on a custom domain ResolveCustomDomain rewrites it, as it
     * does an unknown custom domain's), and in an embed an empty 404, as guestNotFound() gives one.
     */
    protected function platformNotFound(): Response
    {
        if (request()->embed) {
            return response('', 404);
        }

        abort(404);
    }

    protected function guestNotFound(Role $role, ?string $slug = null): Response
    {
        $this->recordMissingAddress($role, $slug);

        // An event opened inside an embedded calendar that is not there any more (deleted, made a
        // draft, a stale link on the host page's #es-event=): a quiet card that says so, with the
        // way back to the calendar it came from, in place of a blank frame or overlay.
        if (EmbedEventView::requested()) {
            return $this->renderSignedOut(fn () => response()->view('event/embed-unavailable', [
                'role' => $role,
                'event' => null,
                'reason' => 'missing',
                'pageUrl' => null,
                'backUrl' => EmbedEventView::calendarUrl($role),
            ], 404));
        }

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
     * Render as a signed-out visitor sees it, whoever is signed in: what a page any site may frame
     * shows (the framed event view and its "not available" card, see EmbedEventView). Every
     * `auth()->user()`, `@auth` and `$request->user()` the views ask is answered by a guard that
     * knows nobody, for the length of the render and no longer: the session is not touched, so
     * the person is still signed in on the next request.
     *
     * The response is built inside the closure, and Illuminate\Http\Response renders a view as it
     * is handed one, so nothing is left to render after the guard is put back.
     *
     * Who is really there is kept on the request (RealtimeTracker::VIEWER_ATTRIBUTE) for the one
     * reader that must not be fooled: the live view, which leaves out a schedule's own team and the
     * installation's admins, as the page-view counters and the guest funnel do.
     */
    protected function renderSignedOut(callable $render)
    {
        $auth = auth();
        $previous = $auth->getDefaultDriver();
        request()->attributes->set(RealtimeTracker::VIEWER_ATTRIBUTE, $auth->user() ?? false);

        $auth->viaRequest('embed-signed-out', fn () => null);
        config(['auth.guards.embed_signed_out' => [
            'driver' => 'embed-signed-out',
            'provider' => config('auth.guards.'.$previous.'.provider', 'users'),
        ]]);
        $auth->shouldUse('embed_signed_out');

        try {
            return $render();
        } finally {
            $auth->shouldUse($previous);
        }
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
