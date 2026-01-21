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
            ->redirectUrl(route('auth.google.callback'))
            ->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('auth.google.callback'))
                ->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['email' => __('messages.google_auth_failed')]);
        }

        $email = strtolower($googleUser->getEmail());
        $googleId = $googleUser->getId();

        // Check if user exists by google_oauth_id
        $user = User::where('google_oauth_id', $googleId)->first();

        if ($user) {
            // User found by google_id - log them in
            Auth::login($user, true);

            return redirect()->intended(route('home', absolute: false));
        }

        // Check if user exists by email
        $user = User::where('email', $email)->first();

        if ($user) {
            // User exists by email
            if ($user->google_oauth_id && $user->google_oauth_id !== $googleId) {
                // Email exists but linked to a different Google account
                return redirect()->route('login')
                    ->withErrors(['email' => __('messages.google_account_already_linked')]);
            }

            // Link Google account to existing user
            $user->google_oauth_id = $googleId;
            if (! $user->hasVerifiedEmail()) {
                $user->email_verified_at = now();
            }
            $user->save();

            Auth::login($user, true);

            return redirect()->intended(route('home', absolute: false));
        }

        // New user - create account
        $user = User::create([
            'name' => $googleUser->getName(),
            'email' => $email,
            'google_oauth_id' => $googleId,
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
        if ($user->google_oauth_id !== $googleId) {
            return redirect()->to(route('profile.edit').'#section-password')
                ->withErrors(['password' => __('messages.google_account_mismatch')], 'updatePassword');
        }

        // Set session flag allowing password to be set (expires in 5 minutes)
        session(['can_set_password' => true]);

        return redirect()->to(route('profile.edit').'#section-password')
            ->with('status', 'google-verified');
    }

    /**
     * Redirect to Google OAuth to connect account from settings.
     */
    public function redirectToGoogleConnect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->redirectUrl(route('auth.google.connect.callback'))
            ->redirect();
    }

    /**
     * Handle Google OAuth callback for connecting account from settings.
     */
    public function handleGoogleConnectCallback(): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('auth.google.connect.callback'))
                ->user();
        } catch (\Exception $e) {
            return redirect()->to(route('profile.edit').'#section-google-calendar')
                ->withErrors(['google' => __('messages.google_auth_failed')]);
        }

        $googleId = $googleUser->getId();
        $email = strtolower($googleUser->getEmail());

        // Check if this Google account is already linked to another user
        $existingUser = User::where('google_oauth_id', $googleId)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingUser) {
            return redirect()->to(route('profile.edit').'#section-google-calendar')
                ->withErrors(['google' => __('messages.google_account_already_linked')]);
        }

        // Link the Google account to this user
        $user->google_oauth_id = $googleId;
        if (! $user->hasVerifiedEmail() && $user->email === $email) {
            $user->email_verified_at = now();
        }
        $user->save();

        return redirect()->to(route('profile.edit').'#section-google-calendar')
            ->with('status', __('messages.google_account_connected'));
    }

    /**
     * Disconnect Google OAuth account.
     */
    public function disconnectGoogle(): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Check if user has a password - they cannot disconnect if they don't
        if (! $user->hasPassword()) {
            return redirect()->to(route('profile.edit').'#section-google-calendar')
                ->withErrors(['google' => __('messages.cannot_disconnect_google_no_password')]);
        }

        // Clear the Google OAuth ID
        $user->google_oauth_id = null;
        $user->save();

        return redirect()->to(route('profile.edit').'#section-google-calendar')
            ->with('status', __('messages.google_account_disconnected'));
    }
}
