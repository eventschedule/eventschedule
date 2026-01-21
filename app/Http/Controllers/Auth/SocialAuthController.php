<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google OAuth.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['email' => __('messages.google_auth_failed')]);
        }

        $email = strtolower($googleUser->getEmail());
        $googleId = $googleUser->getId();

        // Check if user exists by google_id
        $user = User::where('google_id', $googleId)->first();

        if ($user) {
            // User found by google_id - log them in
            Auth::login($user, true);

            return redirect()->intended(route('home', absolute: false));
        }

        // Check if user exists by email
        $user = User::where('email', $email)->first();

        if ($user) {
            // User exists by email
            if ($user->google_id && $user->google_id !== $googleId) {
                // Email exists but linked to a different Google account
                return redirect()->route('login')
                    ->withErrors(['email' => __('messages.google_account_already_linked')]);
            }

            // Link Google account to existing user
            $user->google_id = $googleId;
            $user->save();

            Auth::login($user, true);

            return redirect()->intended(route('home', absolute: false));
        }

        // New user - create account
        $user = User::create([
            'name' => $googleUser->getName(),
            'email' => $email,
            'google_id' => $googleId,
            'email_verified_at' => now(), // Google verified the email
            'password' => null, // No password for Google-only users
        ]);

        Auth::login($user, true);

        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Redirect to Google OAuth for re-authentication to set password.
     * This is for users who signed up with Google and want to set a password.
     */
    public function redirectToGoogleForSetPassword(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->redirectUrl(route('auth.google.set_password.callback'))
            ->redirect();
    }

    /**
     * Handle Google OAuth callback for setting password.
     * Verifies the user's identity and allows them to set a password.
     */
    public function handleGoogleCallbackForSetPassword(): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('auth.google.set_password.callback'))
                ->user();
        } catch (\Exception $e) {
            return redirect()->to(route('profile.edit').'#section-password')
                ->withErrors(['password' => __('messages.google_auth_failed')], 'updatePassword');
        }

        $googleId = $googleUser->getId();

        // Verify this is the same Google account linked to the user
        if ($user->google_id !== $googleId) {
            return redirect()->to(route('profile.edit').'#section-password')
                ->withErrors(['password' => __('messages.google_account_mismatch')], 'updatePassword');
        }

        // Set session flag allowing password to be set (expires in 5 minutes)
        session(['can_set_password' => true]);

        return redirect()->to(route('profile.edit').'#section-password')
            ->with('status', 'google-verified');
    }
}
