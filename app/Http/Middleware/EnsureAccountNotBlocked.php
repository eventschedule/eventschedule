<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * A blocked account (users.blocked_at, set at /admin/blocked) is signed out wherever it turns up.
 *
 * Asked twice on purpose. Before the request: an account that was signed in when it was blocked,
 * or that comes back on a remember cookie, is out on its next page. After it: some twelve places
 * sign a person in (a password, Google, Facebook, a two-factor code, a set-password link, a
 * subscriber's claim link, the account a guest form makes), and one more may be added by someone
 * who has never heard of this. Whatever signed the account in during this request, it does not
 * leave signed in.
 *
 * The doors that DO something in the same request as the sign-in say no before they do it
 * (LoginRequest, SocialAuthController, the guest forms); this is the net under all of them. The
 * API has no session and asks in ApiAuthentication.
 */
class EnsureAccountNotBlocked
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->blocked()) {
            return $this->turnAway($request);
        }

        $response = $next($request);

        if ($this->blocked()) {
            return $this->turnAway($request);
        }

        return $response;
    }

    private function blocked(): bool
    {
        $user = Auth::guard('web')->user();

        return $user !== null && $user->isBlocked();
    }

    private function turnAway(Request $request)
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => __('messages.account_blocked')], 403);
        }

        // The sign-in page is the platform's own, also for someone who was on a schedule's
        // address. Under `email`: the auth layout prints field errors and nothing else.
        return redirect(app_url(route('login', [], false)))
            ->withErrors(['email' => __('messages.account_blocked')]);
    }
}
