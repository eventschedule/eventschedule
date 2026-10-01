<?php

namespace App\Listeners;

use App\Utils\RealtimeTracker;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Keep a site admin's own log-in off /admin/realtime.
 *
 * /admin/* never carries the beacon, but /login does, and before the log-in the admin is just an
 * anonymous visitor: without this, every admin log-in would leave a "Mac · Log in" stranger in the
 * list for half an hour. On an admin's Login event the sign-in pages this browser just viewed are
 * flagged is_admin, which the page hides unless "Show admins" is on.
 *
 * Only `auth` rows, on purpose: the admin's other anonymous browsing on the same browser (a private
 * window shares the IP and user agent) must stay visible, or "open a private window to see yourself
 * appear" stops working.
 *
 * Registered explicitly in AppServiceProvider with an UNTYPED handle(): a typed handler in
 * app/Listeners is also picked up by event discovery and would run twice.
 */
class MarkRealtimeAdminVisits
{
    public function handle($event): void
    {
        try {
            if (! ($event->user ?? null)?->isAdmin() || ! RealtimeTracker::enabled()) {
                return;
            }

            $keys = RealtimeTracker::visitorKeys(request());
            if (! $keys) {
                return;
            }

            DB::table('realtime_hits')
                ->whereIn('visitor_key', $keys)
                ->where('surface', 'auth')
                ->where('last_seen_at', '>=', RealtimeTracker::ts(RealtimeTracker::now()->subMinutes(30)))
                ->update(['is_admin' => true]);
        } catch (Throwable $e) {
            // A log-in must never fail because of a realtime bookkeeping write.
            report($e);
        }
    }
}
