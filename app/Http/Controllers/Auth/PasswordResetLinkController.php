<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\ValidTurnstile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'cf-turnstile-response' => [new ValidTurnstile],
        ]);

        // Per-email rate limit: at most 3 reset emails per 10 minutes for a
        // given address. Complements the existing per-IP throttle and blunts
        // targeted flooding of a single victim's inbox. Silently swallow the
        // attempt when hit so we don't leak which addresses are targeted.
        $emailKey = 'password-reset-email:'.strtolower((string) $request->email);
        if (RateLimiter::tooManyAttempts($emailKey, 3)) {
            return back()->with('status', __('passwords.sent'));
        }
        RateLimiter::hit($emailKey, 600);

        // Send reset link - silently handles non-existent or unverified emails.
        // Password::sendResetLink() already upserts the token row, so there is
        // no need to delete the existing row first (and doing so would widen an
        // enumeration timing window).
        Password::sendResetLink(
            $request->only('email')
        );

        // Always return the same generic message to prevent email enumeration
        return back()->with('status', __('passwords.sent'));
    }
}
