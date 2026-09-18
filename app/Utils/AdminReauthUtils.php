<?php

namespace App\Utils;

use Illuminate\Contracts\Session\Session;

/**
 * The /admin password re-confirmation window.
 *
 * Two clocks, both stored in the session, both therefore bounded above by session.lifetime:
 *
 *  - SESSION_KEY slides. Every admin request rewrites it, so the idle window restarts when the
 *    page is refreshed. This is the one an admin actually feels.
 *  - FIRST_KEY does not. Only entering the password again moves it, which is what stops
 *    /admin/support - polling every 5 seconds - from renewing admin rights forever on an
 *    unattended machine.
 *
 * Lives here rather than inside EnsureUserIsAdmin because the middleware is not the only reader:
 * MarketingController's two platform-moderation actions are served from the base domain, outside
 * the admin route group, and have to ask the same question. One definition, not two.
 */
class AdminReauthUtils
{
    public const SESSION_KEY = 'admin_password_confirmed_at';

    public const FIRST_KEY = 'admin_password_confirmed_first_at';

    public const USER_AGENT_KEY = 'admin_user_agent';

    /**
     * How far a stored timestamp may sit in the future before it is treated as bogus.
     *
     * The value is written only by this app, into server-side session state, so a visitor cannot
     * set it - which means a future value can realistically only come from the clock stepping
     * backwards (NTP, or a second web container). Rejecting it outright would turn that into a
     * spurious re-auth for a threat that is not reachable, so allow a little slack.
     */
    private const FUTURE_TOLERANCE = 60;

    /**
     * Whether the admin confirmation is still good. Pure read - no writes, no sliding.
     *
     * Defaults live HERE and not only in config/auth.php: an install that upgraded with a stale
     * bootstrap/cache/config.php has no such key, and (int) null is 0, which must never be read
     * as "no timeout". Fail closed.
     */
    public static function isCurrent(Session $session): bool
    {
        $confirmedAt = (int) $session->get(self::SESSION_KEY);

        if ($confirmedAt <= 0) {
            return false;
        }

        $now = now()->timestamp;

        if ($confirmedAt > $now + self::FUTURE_TOLERANCE) {
            return false;
        }

        if (($now - $confirmedAt) > (int) config('auth.admin_reauth_timeout', 86400)) {
            return false;
        }

        // A session predating this feature carries no ceiling; treat the confirmation itself as
        // the start. firstConfirmedAt() holds that rule so this path and slide() cannot drift.
        $firstConfirmedAt = self::firstConfirmedAt($session, $confirmedAt);

        return ($now - $firstConfirmedAt) <= (int) config('auth.admin_reauth_max_lifetime', 2592000);
    }

    /**
     * Record a fresh password entry. Resets BOTH clocks - re-entering the password is exactly the
     * challenge the ceiling exists to force.
     */
    public static function markConfirmed(Session $session): void
    {
        $now = now()->timestamp;

        $session->put(self::SESSION_KEY, $now);
        $session->put(self::FIRST_KEY, $now);
    }

    /**
     * Restart the idle window, and persist the ceiling for a session that predates it.
     *
     * The backfill has to WRITE, not merely default: the sliding key is rewritten to now on every
     * request, so a ceiling recomputed from it each time would restart every time and never fire.
     */
    public static function slide(Session $session): void
    {
        $confirmedAt = (int) $session->get(self::SESSION_KEY);

        if ($confirmedAt > 0) {
            $session->put(self::FIRST_KEY, self::firstConfirmedAt($session, $confirmedAt));
        }

        $session->put(self::SESSION_KEY, now()->timestamp);
    }

    /**
     * Drop the confirmation and the browser binding together, so re-confirming rebinds.
     */
    public static function clear(Session $session): void
    {
        $session->forget([self::USER_AGENT_KEY, self::SESSION_KEY, self::FIRST_KEY]);
    }

    /**
     * Where the ceiling starts counting. Pure: slide() is what persists the backfill.
     */
    private static function firstConfirmedAt(Session $session, int $confirmedAt): int
    {
        $firstConfirmedAt = (int) $session->get(self::FIRST_KEY);

        return $firstConfirmedAt > 0 ? $firstConfirmedAt : $confirmedAt;
    }
}
