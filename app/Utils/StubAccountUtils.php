<?php

namespace App\Utils;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Getting somebody who has an account but no password back into it.
 *
 * A passwordless stub is minted by several paths - a confirmed newsletter subscription
 * (RoleSubscriberController::linkAccount), an owner's newsletter import, a team invite - and every
 * one of them leaves a person who cannot sign in and, at the login form, cannot be told why:
 * EloquentUserProvider::validateCredentials() returns false on a null hash, so a stub attempt is
 * indistinguishable from a wrong password.
 *
 * Shared by the surfaces that offer a way out, so they cannot drift apart on either the budget or
 * the statuses they have to handle.
 */
class StubAccountUtils
{
    /**
     * Deliberately the SAME bucket PasswordResetLinkController uses, so login, forgot-password and
     * the subscriber manage page draw on ONE budget of 3 mails per address per 10 minutes rather
     * than three budgets that add up to nine.
     */
    public const EMAIL_BUCKET = 'password-reset-email:';

    public const PER_EMAIL_LIMIT = 3;

    public const PER_EMAIL_DECAY = 600;

    /** A link is on its way. */
    public const SENT = 'sent';

    /** One went recently. Never say a fresh mail was sent - see send(). */
    public const THROTTLED = 'throttled';

    /** Nothing to send to. */
    public const NONE = 'none';

    /**
     * The passwordless account for an address, if there is one.
     *
     * Returns null for a real account, so a caller can never accidentally offer somebody else's
     * password a way to be replaced.
     */
    public static function find(mixed $email): ?User
    {
        // is_string(): input() hands back `email[]=x` as an array untouched.
        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        $user = User::where('email', strtolower(trim($email)))->first();

        return $user && $user->isStub() ? $user : null;
    }

    /**
     * Mail a set-password link, and say what actually happened.
     *
     * The caller MUST branch on the return value rather than assume a send. There are two separate
     * throttles in play and the second is easy to walk into: PasswordBroker::sendResetLink()
     * refuses with RESET_THROTTLED while recentlyCreatedToken() is true, which config/auth.php sets
     * to 60 seconds - and RoleSubscriberController::claimState() calls Password::createToken() on
     * EVERY confirm. So for a minute after confirming a subscription, the most likely moment for
     * somebody to try one of these doors, nothing is sent. Telling them to check their inbox would
     * be a lie with a support ticket attached.
     *
     * Note this also invalidates any claim token sitting in a /sub/done tab:
     * DatabaseTokenRepository::create() calls deleteExisting() first. That surfaces correctly as
     * the expired-link message on that page rather than as a failure.
     */
    public static function send(User $user): string
    {
        $key = self::EMAIL_BUCKET.strtolower($user->email);

        if (RateLimiter::tooManyAttempts($key, self::PER_EMAIL_LIMIT)) {
            return self::THROTTLED;
        }

        // User::sendPasswordResetNotification() rewords this for a stub - see the override there.
        $status = Password::sendResetLink(['email' => $user->email]);

        // Spend the budget only on a mail that actually went. Hitting first - which is what
        // PasswordResetLinkController does - means the broker's own 60-second refusal costs a slot
        // and delivers nothing, and that window is not exotic: claimState() mints a token on EVERY
        // confirm, so it is the minute right after somebody subscribes, which is exactly when they
        // try these doors. Three attempts there used to burn the whole ten-minute allowance with
        // zero mail sent, leaving the address locked out and the copy insisting a link was on its
        // way.
        if ($status === Password::RESET_LINK_SENT) {
            RateLimiter::hit($key, self::PER_EMAIL_DECAY);

            return self::SENT;
        }

        return $status === Password::RESET_THROTTLED ? self::THROTTLED : self::NONE;
    }
}
