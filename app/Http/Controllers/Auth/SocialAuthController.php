<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
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
            AuditService::log(AuditService::AUTH_GOOGLE_LOGIN, $user->id);

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
            if (! $user->profile_image_url && $googleUser->getAvatar()) {
                $user->profile_image_url = $googleUser->getAvatar();
            }
            if (! $user->hasVerifiedEmail()) {
                $user->email_verified_at = now();
            }
            $user->save();

            Auth::login($user, true);
            AuditService::log(AuditService::AUTH_GOOGLE_LOGIN, $user->id);

            return redirect()->intended(route('home', absolute: false));
        }

        // New user - create account
        $utmParams = session('utm_params', []);

        // Fall back to cookie if session has no UTM data
        if (empty($utmParams) && request()->cookie('utm_params')) {
            $utmParams = json_decode(request()->cookie('utm_params'), true) ?? [];
        }

        $browserTimezone = request()->cookie('browser_timezone');
        $browserLanguage = request()->cookie('browser_language');
        $googleLocale = $googleUser->user['locale'] ?? null;
        $languageCode = null;

        if ($googleLocale && is_valid_language_code(substr($googleLocale, 0, 2))) {
            $languageCode = substr($googleLocale, 0, 2);
        } elseif (session()->has('guest_language') && is_valid_language_code(session('guest_language'))) {
            $languageCode = session('guest_language');
        } elseif ($browserLanguage && is_valid_language_code($browserLanguage)) {
            $languageCode = $browserLanguage;
        }

        $timezone = $browserTimezone ?: 'America/New_York';

        $user = User::create([
            'name' => $googleUser->getName(),
            'email' => $email,
            'google_oauth_id' => $googleId,
            'email_verified_at' => now(), // Google verified the email
            'password' => null, // No password for Google-only users
            'timezone' => $timezone,
            'language_code' => $languageCode ?? 'en',
            'use_24_hour_time' => detect_24_hour_time($timezone, $languageCode),
            'utm_source' => $utmParams['utm_source'] ?? null,
            'utm_medium' => $utmParams['utm_medium'] ?? null,
            'utm_campaign' => $utmParams['utm_campaign'] ?? null,
            'utm_content' => $utmParams['utm_content'] ?? null,
            'utm_term' => $utmParams['utm_term'] ?? null,
            'referrer_url' => session('utm_referrer_url') ?? request()->cookie('utm_referrer_url'),
            'landing_page' => session('utm_landing_page') ?? request()->cookie('utm_landing_page'),
        ]);

        $user->profile_image_url = $googleUser->getAvatar();
        $user->save();

        session()->forget(['utm_params', 'utm_referrer_url', 'utm_landing_page', 'guest_language']);

        Auth::login($user, true);
        AuditService::log(AuditService::AUTH_GOOGLE_LOGIN, $user->id, 'User', $user->id, null, null, 'new_account');

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

        // Set session flag allowing password to be set with timestamp (expires in 5 minutes)
        session(['can_set_password' => now()->timestamp]);

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
