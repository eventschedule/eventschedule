<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use App\Services\AuditService;
use App\Utils\SocialLoginUtils;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\FacebookProvider;

class SocialAuthController extends Controller
{
    /**
     * Per-provider wiring. Everything else about the sign-in flow is shared.
     *
     * 'settings' is the Settings section that hosts the provider's connect/disconnect panel.
     * Its outcomes flash as 'message' / 'error', the two keys layouts/app.blade.php turns into a
     * toast; the panels render no error bag of their own.
     */
    private const PROVIDERS = [
        'google' => [
            'column' => 'google_oauth_id',
            'audit' => AuditService::AUTH_GOOGLE_LOGIN,
            'scopes' => ['openid', 'email', 'profile'],
            'settings' => '#section-google-calendar',
        ],
        'facebook' => [
            'column' => 'facebook_id',
            'audit' => AuditService::AUTH_FACEBOOK_LOGIN,
            'scopes' => ['email'],
            'settings' => '#section-facebook',
        ],
    ];

    /**
     * Where a cancelled or failed sign-in returns to: the sign-up page when that is where the
     * button was pressed, the login page otherwise.
     */
    private const ORIGIN_KEY = 'social_auth_origin';

    /**
     * Redirect to Google OAuth.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return $this->redirectToProvider('google', 'auth.google.callback');
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        return $this->handleLoginCallback('google', 'auth.google.callback');
    }

    /**
     * Redirect to Facebook Login. `?rerequest=1` asks again for a permission the person declined
     * (in practice, email): without auth_type=rerequest Facebook silently remembers the refusal.
     */
    public function redirectToFacebook(): RedirectResponse
    {
        abort_unless(facebook_login_enabled(), 404);

        return $this->redirectToProvider('facebook', 'auth.facebook.callback', request()->boolean('rerequest'));
    }

    /**
     * Handle Facebook Login callback.
     */
    public function handleFacebookCallback(): RedirectResponse
    {
        abort_unless(facebook_login_enabled(), 404);

        return $this->handleLoginCallback('facebook', 'auth.facebook.callback');
    }

    private function redirectToProvider(string $provider, string $callbackRoute, bool $reRequest = false): RedirectResponse
    {
        $previous = url()->previous();
        session([self::ORIGIN_KEY => $previous && parse_url($previous, PHP_URL_PATH) === parse_url(route('sign_up'), PHP_URL_PATH)
            ? 'sign_up'
            : 'login']);

        // Socialite caches one driver instance per provider, and reRequest() is a flag on it, so
        // the re-request gets a provider of its own rather than marking the shared one.
        $driver = $reRequest && $provider === 'facebook'
            ? Socialite::buildProvider(FacebookProvider::class, config('services.facebook'))->reRequest()
            : Socialite::driver($provider);

        return $driver
            ->scopes(self::PROVIDERS[$provider]['scopes'])
            ->redirectUrl(route($callbackRoute))
            ->redirect();
    }

    /**
     * The route a cancelled or failed sign-in goes back to.
     */
    private function originRoute(): string
    {
        return session()->pull(self::ORIGIN_KEY) === 'sign_up' ? 'sign_up' : 'login';
    }

    /**
     * Pressing Cancel on the provider's consent screen comes back as ?error=access_denied with
     * no code. That is a choice, not a failure, so it earns no error message.
     */
    private function wasCancelled(): bool
    {
        return request()->filled('error') && ! request()->filled('code');
    }

    private function handleLoginCallback(string $provider, string $callbackRoute): RedirectResponse
    {
        if ($this->wasCancelled()) {
            return redirect()->route($this->originRoute());
        }

        try {
            $socialUser = Socialite::driver($provider)
                ->redirectUrl(route($callbackRoute))
                ->user();
        } catch (\Exception $e) {
            return redirect()->route($this->originRoute())
                ->withErrors(['email' => __('messages.'.$provider.'_auth_failed')]);
        }

        session()->forget(self::ORIGIN_KEY);
        $column = self::PROVIDERS[$provider]['column'];
        $providerId = (string) $socialUser->getId();
        $email = $socialUser->getEmail() ? strtolower($socialUser->getEmail()) : null;

        // Check if user exists by the provider's account id
        $user = User::where($column, $providerId)->first();

        if ($user) {
            return $this->completeLogin($user, $provider);
        }

        // Facebook accounts can exist without an email (phone sign-ups), and the person can
        // untick the email permission. Neither can be matched or given an account; offer the
        // re-request instead of a dead end. Always the login page: that is where the retry
        // panel is rendered.
        if (! $email) {
            return redirect()->route('login')->with('social_email_required', $provider);
        }

        // Check if user exists by email
        $user = User::where('email', $email)->first();

        if ($user) {
            // Email exists but linked to a different account at this provider
            if ($user->{$column} && $user->{$column} !== $providerId) {
                return redirect()->route('login')
                    ->withErrors(['email' => __('messages.'.$provider.'_account_already_linked')]);
            }

            if ($provider === 'google') {
                // If the existing local account has a password, we cannot safely
                // auto-link a Google account on first SSO attempt — that would let
                // an attacker who controls a matching Google mailbox hijack a
                // password-protected account they never owned. Require the user to
                // sign in with their password first and link from settings.
                if ($user->hasPassword()) {
                    return redirect()->route('login')
                        ->withErrors(['email' => __('messages.google_link_requires_password_login')]);
                }
            } elseif (! $user->isStub()) {
                // Stricter than Google: only an invited placeholder with no way in of its own
                // is linked on the email alone. Any real account (a password, Google) is linked
                // only after its owner signs in to it, which SocialLoginUtils::consumePendingLink()
                // does - so the person proves control of both identities before they are joined.
                SocialLoginUtils::stashPendingLink($provider, $providerId, $email);

                return redirect()->route('login', ['email' => $email])
                    ->with('social_link_pending', $provider);
            }

            // Link the provider account to the existing user
            $user->{$column} = $providerId;
            if ($provider === 'google' && ! $user->profile_image_url && $socialUser->getAvatar()) {
                $user->profile_image_url = $socialUser->getAvatar();
            }
            if (! $user->hasVerifiedEmail()) {
                $user->email_verified_at = now();
            }
            $user->save();

            return $this->completeLogin($user, $provider);
        }

        // New user - create account.
        // Social sign-in is an account-creation path like the sign-up form, so it obeys the same
        // policy: a plain selfhost is single-user unless ALLOW_REGISTRATION is set. Both
        // existing-user branches above have already returned, so this only blocks brand-new
        // accounts - current users (and invited stubs being linked) keep signing in.
        if (! public_registration_enabled() && ! config('app.is_testing') && User::exists()) {
            return redirect()->route('login')
                ->withErrors(['email' => __('messages.account_creation_disabled')]);
        }

        $utmParams = session('utm_params', []);

        // Fall back to cookie if session has no UTM data
        if (empty($utmParams) && request()->cookie('utm_params')) {
            $utmParams = json_decode(request()->cookie('utm_params'), true) ?? [];
        }

        $browserTimezone = request()->cookie('browser_timezone');
        $browserLanguage = request()->cookie('browser_language');
        // Google sends a locale claim; Facebook does not without an extra permission.
        $providerLocale = $provider === 'google' ? ($socialUser->user['locale'] ?? null) : null;
        $languageCode = null;

        if ($providerLocale && is_valid_language_code(substr($providerLocale, 0, 2))) {
            $languageCode = substr($providerLocale, 0, 2);
        } elseif (session()->has('guest_language') && is_valid_language_code(session('guest_language'))) {
            $languageCode = session('guest_language');
        } elseif ($browserLanguage && is_valid_language_code($browserLanguage)) {
            $languageCode = $browserLanguage;
        }

        $timezone = $browserTimezone ?: 'America/New_York';

        $user = User::create([
            'name' => $socialUser->getName(),
            'email' => $email,
            $column => $providerId,
            // Both providers only hand over an address they have confirmed.
            'email_verified_at' => now(),
            'password' => null, // No password for social-only users
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
            'signup_intent' => signup_intent_from_session(),
        ]);

        // Facebook's picture URL is signed and expires within weeks, so it is not kept.
        if ($provider === 'google') {
            $user->profile_image_url = $socialUser->getAvatar();
        }

        // Consent, on the path that never asked for it. RegisteredUserController::store()
        // validates `terms => accepted` on hosted; this one created an account with no consent
        // step at all, and it is roughly half of them.
        //
        // No checkbox here, deliberately: a tick in front of the button that converts best is
        // friction on the wrong path. Both auth pages state the terms beside the button instead,
        // and pressing it is the act being recorded. Only on the NEW-account branch - linking
        // a provider to an account that already exists is not a moment of consent.
        if (config('app.hosted')) {
            $user->terms_accepted_at = now();
        }

        if (session()->pull('pending_follow_consent_dismissed')) {
            $user->follow_consent_dismissed = true;
        }
        $user->save();

        // Link referral if referral code exists in session
        if (config('app.hosted')) {
            $referralCode = session('referral_code');
            if ($referralCode) {
                $referrer = User::where('referral_code', $referralCode)->first();
                if ($referrer && $referrer->id !== $user->id) {
                    $user->referred_by_user_id = $referrer->id;
                    $user->save();

                    Referral::create([
                        'referrer_user_id' => $referrer->id,
                        'referred_user_id' => $user->id,
                        'status' => 'pending',
                    ]);
                }
            }
        }

        session()->forget(['utm_params', 'utm_referrer_url', 'utm_landing_page', 'guest_language', 'referral_code']);

        // Half of all accounts are created here rather than by RegisteredUserController::store(),
        // and only that one fired this.
        //
        // Something DOES listen: Illuminate\Auth\Listeners\SendEmailVerificationNotification is
        // auto-registered by the framework's EventServiceProvider (reachable through
        // Application::configure()->withEvents(), so it is not in bootstrap/providers.php), and
        // User implements MustVerifyEmail. It is safe only because User::create() above sets
        // email_verified_at and the listener early-returns on hasVerifiedEmail(). If this path is
        // ever made conditional on the provider's own email_verified claim, this line starts
        // mailing every new social signup.
        //
        // Deliberately NOT also logging AuditService::AUTH_REGISTER: the provider's login entry
        // below already records this account's creation with a 'new_account' note, and adding a
        // second entry would double-count every social signup in the audit log.
        event(new Registered($user));

        Auth::login($user, true);
        $this->processPendingClaims($user);
        AuditService::log(self::PROVIDERS[$provider]['audit'], $user->id, 'User', $user->id, null, null, 'new_account');
        SocialLoginUtils::rememberMethod($provider);

        return redirect()->intended(post_signup_redirect_url($user));
    }

    /**
     * Sign an existing account in through a provider.
     */
    private function completeLogin(User $user, string $provider): RedirectResponse
    {
        if (session()->pull('pending_follow_consent_dismissed') && ! $user->follow_consent_dismissed) {
            $user->update(['follow_consent_dismissed' => true]);
        }
        Auth::login($user, true);
        $this->processPendingClaims($user);
        AuditService::log(self::PROVIDERS[$provider]['audit'], $user->id);
        SocialLoginUtils::rememberMethod($provider);

        // A Facebook identity may be waiting for this account (Google sign-in is one of the ways
        // its owner can prove they hold it).
        $linked = SocialLoginUtils::consumePendingLink($user);

        $redirect = redirect()->intended(route('home', absolute: false));

        return $linked ? $redirect->with('message', $linked) : $redirect;
    }

    /**
     * Hand over anything already waiting for this person.
     *
     * Reached from every social sign-in branch, which is why the email half belongs here rather
     * than only in RegisteredUserController: an OAuth signup issues no six-digit code, so without
     * this every act who accepted our invitation with "Continue with Google" got an account and
     * left their schedule behind. The provider's assertion is stronger evidence than the code path
     * anyway - the new-account branch already stamps email_verified_at on the strength of it.
     */
    private function processPendingClaims(User $user): void
    {
        $user->claimRolesByEmail();
        $user->claimSalesByEmail();

        $smsToken = session()->pull('sms_token');
        if ($smsToken) {
            $smsPhone = Cache::get('sms_signup_'.$smsToken);
            if ($smsPhone) {
                $user->claimRolesByPhone($smsPhone);
                Cache::forget('sms_signup_'.$smsToken);
            }
        }
    }

    /**
     * Redirect to Google OAuth for re-authentication to set password.
     * This is for users who signed up with Google and want to set a password.
     */
    public function redirectToGoogleForSetPassword(): RedirectResponse
    {
        return $this->redirectForReauth('google', 'auth.google.set_password.callback');
    }

    /**
     * Handle Google OAuth callback for setting password.
     * Verifies the user's identity and allows them to set a password.
     */
    public function handleGoogleCallbackForSetPassword(): RedirectResponse
    {
        return $this->handleSetPasswordCallback('google', 'auth.google.set_password.callback');
    }

    public function redirectToFacebookForSetPassword(): RedirectResponse
    {
        abort_unless(facebook_login_enabled(), 404);

        return $this->redirectForReauth('facebook', 'auth.facebook.set_password.callback');
    }

    public function handleFacebookCallbackForSetPassword(): RedirectResponse
    {
        abort_unless(facebook_login_enabled(), 404);

        return $this->handleSetPasswordCallback('facebook', 'auth.facebook.set_password.callback');
    }

    /**
     * Redirect to Google OAuth to connect account from settings.
     */
    public function redirectToGoogleConnect(): RedirectResponse
    {
        return $this->redirectForReauth('google', 'auth.google.connect.callback');
    }

    /**
     * Handle Google OAuth callback for connecting account from settings.
     */
    public function handleGoogleConnectCallback(): RedirectResponse
    {
        return $this->handleConnectCallback('google', 'auth.google.connect.callback');
    }

    public function redirectToFacebookConnect(): RedirectResponse
    {
        abort_unless(facebook_login_enabled(), 404);

        return $this->redirectForReauth('facebook', 'auth.facebook.connect.callback');
    }

    public function handleFacebookConnectCallback(): RedirectResponse
    {
        abort_unless(facebook_login_enabled(), 404);

        return $this->handleConnectCallback('facebook', 'auth.facebook.connect.callback');
    }

    /**
     * Disconnect Google OAuth account.
     */
    public function disconnectGoogle(): RedirectResponse
    {
        return $this->disconnect('google');
    }

    public function disconnectFacebook(): RedirectResponse
    {
        abort_unless(facebook_login_enabled(), 404);

        return $this->disconnect('facebook');
    }

    private function redirectForReauth(string $provider, string $callbackRoute): RedirectResponse
    {
        return Socialite::driver($provider)
            ->scopes(self::PROVIDERS[$provider]['scopes'])
            ->redirectUrl(route($callbackRoute))
            ->redirect();
    }

    private function handleSetPasswordCallback(string $provider, string $callbackRoute): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $back = route('profile.edit').'#section-password';

        if ($this->wasCancelled()) {
            return redirect()->to($back);
        }

        try {
            $socialUser = Socialite::driver($provider)
                ->redirectUrl(route($callbackRoute))
                ->user();
        } catch (\Exception $e) {
            return redirect()->to($back)
                ->withErrors(['password' => __('messages.'.$provider.'_auth_failed')], 'updatePassword');
        }

        // Verify this is the same provider account linked to the user
        $column = self::PROVIDERS[$provider]['column'];
        if (! $user->{$column} || $user->{$column} !== (string) $socialUser->getId()) {
            return redirect()->to($back)
                ->withErrors(['password' => __('messages.'.$provider.'_account_mismatch')], 'updatePassword');
        }

        // Set session flag allowing password to be set with timestamp (expires in 5 minutes)
        session(['can_set_password' => now()->timestamp]);

        return redirect()->to($back)
            ->with('status', $provider.'-verified');
    }

    private function handleConnectCallback(string $provider, string $callbackRoute): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $config = self::PROVIDERS[$provider];
        $back = route('profile.edit').$config['settings'];

        if ($this->wasCancelled()) {
            return redirect()->to($back);
        }

        try {
            $socialUser = Socialite::driver($provider)
                ->redirectUrl(route($callbackRoute))
                ->user();
        } catch (\Exception $e) {
            return redirect()->to($back)
                ->with('error', __('messages.'.$provider.'_auth_failed'));
        }

        $column = $config['column'];
        $providerId = (string) $socialUser->getId();
        $email = $socialUser->getEmail() ? strtolower($socialUser->getEmail()) : null;

        // Check if this provider account is already linked to another user
        $existingUser = User::where($column, $providerId)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingUser) {
            return redirect()->to($back)
                ->with('error', __($provider === 'facebook' ? 'messages.facebook_account_in_use' : 'messages.google_account_already_linked'));
        }

        // Link the provider account to this user
        $user->{$column} = $providerId;
        if (! $user->hasVerifiedEmail() && $email && $user->email === $email) {
            $user->email_verified_at = now();
        }
        $user->save();

        return redirect()->to($back)
            ->with('message', __('messages.'.$provider.'_account_connected'));
    }

    private function disconnect(string $provider): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $config = self::PROVIDERS[$provider];
        $back = route('profile.edit').$config['settings'];

        // Never remove the last way in: a password, or another linked provider.
        if (! $user->canDisconnectSocialLogin($provider)) {
            $message = $provider === 'google' ? 'cannot_disconnect_google_no_password' : 'cannot_disconnect_facebook_no_other_login';

            return redirect()->to($back)
                ->with('error', __('messages.'.$message));
        }

        $user->{$config['column']} = null;
        $user->save();

        return redirect()->to($back)
            ->with('message', __('messages.'.$provider.'_account_disconnected'));
    }
}
