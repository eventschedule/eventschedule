<?php

namespace App\Utils;

use App\Models\User;
use Illuminate\Support\Facades\Cookie;

/**
 * State that outlives a single social-login request: the "last used" hint on the login page, and
 * the Facebook identity waiting to be linked once its owner proves they own the matching account.
 */
class SocialLoginUtils
{
    public const LAST_METHOD_COOKIE = 'last_login_method';

    public const METHODS = ['password', 'google', 'facebook'];

    public const PENDING_LINK_KEY = 'pending_social_link';

    public const PENDING_LINK_TTL_SECONDS = 600;

    /**
     * Provider => the users column holding its account id. Only providers that can be stashed
     * for a deferred link are listed; Google links on its own terms in SocialAuthController.
     */
    private const LINKABLE = [
        'facebook' => 'facebook_id',
    ];

    /**
     * Remember how this browser last signed in, so the login page can mark that button.
     * Not sensitive (it names a method, never an account), so it lives a year.
     */
    public static function rememberMethod(string $method): void
    {
        if (in_array($method, self::METHODS, true)) {
            Cookie::queue(self::LAST_METHOD_COOKIE, $method, 60 * 24 * 365);
        }
    }

    public static function lastMethod(): ?string
    {
        $method = request()->cookie(self::LAST_METHOD_COOKIE);

        return is_string($method) && in_array($method, self::METHODS, true) ? $method : null;
    }

    /**
     * Hold a verified provider identity whose email matches an existing account that has its own
     * way in (a password or Google). Signing in to that account within the TTL links it, so the
     * person proves control of BOTH identities in this one browser session before they are joined.
     */
    public static function stashPendingLink(string $provider, string $providerId, string $email): void
    {
        session([self::PENDING_LINK_KEY => [
            'provider' => $provider,
            'id' => $providerId,
            'email' => strtolower($email),
            'expires' => now()->addSeconds(self::PENDING_LINK_TTL_SECONDS)->timestamp,
        ]]);
    }

    /**
     * Link the stashed identity to the account that just signed in, if it is still valid.
     * One-shot: the stash is pulled whether or not it links.
     *
     * @return string|null a confirmation message to flash, or null when nothing was linked
     */
    public static function consumePendingLink(User $user): ?string
    {
        $pending = session()->pull(self::PENDING_LINK_KEY);

        if (! is_array($pending) || ! isset(self::LINKABLE[$pending['provider'] ?? null])) {
            return null;
        }

        if (($pending['expires'] ?? 0) < now()->timestamp) {
            return null;
        }

        // The account that signed in must be the one the provider's email pointed at. Anything
        // else - a different account in the same browser - is not the person Facebook vouched for.
        if (strtolower((string) $user->email) !== ($pending['email'] ?? null)) {
            return null;
        }

        $column = self::LINKABLE[$pending['provider']];
        $providerId = (string) ($pending['id'] ?? '');

        if ($providerId === '' || ($user->{$column} && $user->{$column} !== $providerId)) {
            return null;
        }

        if (User::where($column, $providerId)->where('id', '!=', $user->id)->exists()) {
            return null;
        }

        $user->{$column} = $providerId;
        $user->save();

        return __('messages.'.$pending['provider'].'_account_connected');
    }
}
