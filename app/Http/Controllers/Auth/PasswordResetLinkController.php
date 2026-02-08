<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\ValidTurnstile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
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

        // Delete existing password reset token
        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Send reset link - silently handles non-existent or unverified emails
        Password::sendResetLink(
            $request->only('email')
        );

        // Always return the same generic message to prevent email enumeration
        return back()->with('status', __('passwords.sent'));
    }
}
