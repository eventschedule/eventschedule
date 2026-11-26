<?php

namespace App\Services;

use App\Http\Middleware\CaptureUtmParameters;
use App\Models\Role;
use App\Models\User;
use App\Notifications\SignupVerificationCode;
use App\Rules\NoFakeEmail;
use App\Rules\NotBlocklisted;
use App\Utils\StubAccountUtils;
use App\Utils\TimezoneUtils;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The accounts a public form makes or signs in beside what it is really for: the "Submit your
 * event" page and the booking form (intent `request`), and the vendor link of a vendor map
 * (intent `vendor`).
 *
 * The first three methods were EventController's (guestImportWithAccount()'s password branch,
 * createAccountWithCode() and createTalentSchedule()) and moved here unchanged, with the intent
 * as a parameter, so the vendor link could stand on the same door rather than a copy of it.
 *
 * The last two are the vendor link's own: a sign-in with an emailed code and NO password, because
 * a farmer who joins a market from a WhatsApp link on a phone will not remember one. It is a new
 * door, so it carries what every sign-in door must (CLAUDE.md): an account with two-factor is
 * sent to the sign-in page, wrong codes count against the ACCOUNT, a blocked account is refused,
 * the operator's list is asked where an account is MADE (never at sign-in), and off hosted an
 * account waiting for its owner is not handed to whoever types its address.
 */
class GuestAccountService
{
    /** How long an emailed code is good for, and how many may be sent an hour per address. */
    public const CODE_MINUTES = 10;

    public const CODES_PER_HOUR = 5;

    public const CODES_PER_DAY = 12;

    /** Wrong codes allowed per account (or per address with no account yet), and the wait. */
    public const WRONG_CODES = 5;

    public const WRONG_CODE_LOCK_SECONDS = 300;

    /**
     * Sign in with a password from a guest form (`account_email`, `account_password`).
     *
     * Mirrors the canonical login form's protections: a shared per-email|ip lockout (same throttle
     * key as LoginRequest, so guesses here and on /login draw from one 5-try budget) plus
     * failed-login audit logging. The route's per-IP throttle alone never locks the targeted
     * account. Throws ValidationException (422 JSON) on any failure.
     */
    public function loginWithPassword(Request $request): User
    {
        $request->validate([
            'account_email' => ['required', 'string', 'email'],
            'account_password' => ['required', 'string'],
        ]);

        $email = strtolower((string) $request->input('account_email'));
        $throttleKey = Str::transliterate(Str::lower((string) $request->input('account_email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            event(new Lockout($request));
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'account_password' => [trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ]);
        }

        if (! Auth::attempt([
            'email' => $email,
            'password' => (string) $request->input('account_password'),
        ])) {
            RateLimiter::hit($throttleKey);
            AuditService::log(AuditService::AUTH_LOGIN_FAILED, null, null, null, null, null, $email);
            throw ValidationException::withMessages([
                'account_password' => [__('messages.incorrect_password')],
            ]);
        }

        // An account an operator has shut out submits nothing: said here, before the
        // event is made. EnsureAccountNotBlocked would only sign it out afterwards.
        if (auth()->user()->isBlocked()) {
            Auth::guard('web')->logout();
            throw ValidationException::withMessages([
                'account_password' => [__('messages.account_blocked')],
            ]);
        }

        // An account with two-factor signs in on the sign-in page, which asks for its code.
        // Signed in here, the password alone was enough for a full session.
        if (auth()->user()->hasTwoFactorEnabled()) {
            Auth::guard('web')->logout();
            throw ValidationException::withMessages([
                'account_password' => [__('messages.two_factor_sign_in_first')],
            ]);
        }

        RateLimiter::clear($throttleKey);

        $user = auth()->user();
        AuditService::log(AuditService::AUTH_LOGIN, $user->id);

        return $user;
    }

    /**
     * Validate the 6-digit code (hosted), then create or update (stub) the submitter and log them
     * in. Throws ValidationException (422 JSON) on any failure.
     */
    public function createWithCode(Request $request, Role $role, string $intent = 'request'): User
    {
        // Turnstile is enforced earlier, at the code-send step (sendVerificationCode), so it is
        // not re-checked here (the emailed code is the proof, and the token is single-use).

        // Selfhost does not allow public self-registration after the first user unless the
        // operator sets ALLOW_REGISTRATION (mirrors RegisteredUserController::store). Block
        // account creation/claim here too so this guest flow can't be used to mint accounts
        // or take over an invited stub without verification.
        if (! self::registrationOpen()) {
            throw ValidationException::withMessages([
                'account_email' => [__('messages.account_creation_disabled')],
            ]);
        }

        $email = strtolower((string) $request->input('account_email'));
        $existingUser = User::where('email', $email)->first();
        $isStub = $existingUser ? $existingUser->isStub() : false;

        $request->validate([
            'account_name' => ['required', 'string', 'max:255'],
            'account_email' => array_merge(
                ['required', 'string', 'email', 'max:255', new NotBlocklisted],
                $isStub ? [] : ['unique:users,email'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            'account_password' => ['required', 'string', 'min:8'],
        ] + (config('app.hosted') ? ['terms' => ['accepted']] : []), [], [
            // As RegisteredUserController::store() requires it: hosted only, where the box is
            // shown with the privacy policy. The page ticked it and never sent it.
            'terms' => __('messages.terms_of_service'),
        ]);

        // Off hosted there is no code step, so nothing here has shown that this person reads the
        // address. An account that is waiting for its owner (an invited team member, somebody who
        // bought a ticket) is not handed to whoever types its address: the address is mailed its
        // set-password link, as sign-up and the login page do for it.
        if ($isStub && ! config('app.hosted')) {
            throw ValidationException::withMessages([
                'account_email' => [self::stubRefusal($existingUser)],
            ]);
        }

        // Verify the emailed 6-digit code (hosted only, matching registration).
        if (config('app.hosted') && ! config('app.is_testing')) {
            $request->validate(['verification_code' => ['required', 'string', 'size:6']]);

            $originalEmail = Cache::pull('signup_code_email_'.$request->verification_code);
            if (! $originalEmail || strtolower($originalEmail) !== $email) {
                throw ValidationException::withMessages([
                    'verification_code' => [__('messages.code_invalid')],
                ]);
            }
        }

        $attributes = [
            'name' => $request->input('account_name'),
            'password' => Hash::make($request->input('account_password')),
            // The browser may report an alias (Asia/Calcutta) the schedule form's select lacks.
            'timezone' => TimezoneUtils::canonicalize(is_string($request->input('timezone')) ? $request->input('timezone') : null)
                ?: ($role->timezone ?: 'UTC'),
            'language_code' => self::guestLanguage(),
        ] + self::attribution($request);

        // The guest-submit flow is an event request on someone else's schedule;
        // stubs keep their original acquisition context (team invite, subscriber)
        if ($isStub) {
            $attributes['signup_intent'] = $existingUser->signup_intent ?? $intent;
            $existingUser->update($attributes);
            $user = $existingUser;
        } else {
            $attributes['signup_intent'] = $intent;
            $user = User::create(array_merge($attributes, ['email' => $email]));
        }

        session()->forget(['utm_params', 'utm_referrer_url', 'utm_landing_page', 'guest_language']);

        $user->email_verified_at = now();
        $user->signup_ip = Blocklist::address($request);
        // The form's terms checkbox, recorded as RegisteredUserController does (hosted only, where
        // the box is shown with the privacy policy).
        if (config('app.hosted') && $request->boolean('terms')) {
            $user->terms_accepted_at = now();
        }
        $user->save();

        Auth::login($user);

        return $user;
    }

    /**
     * Create a minimal talent schedule owned by the user. Contact fields are intentionally left
     * null so the auto-created page stays noindex until the owner adds details.
     */
    public function createTalentSchedule(User $user, string $name, $timezone = null, $languageCode = null): Role
    {
        $talent = new Role;
        $talent->type = 'talent';
        $talent->user_id = $user->id;
        $talent->name = $name;

        // generateSubdomain already handles uniqueness, but retry on the off chance two
        // same-named talents are created at once (mirrors the venue auto-create in
        // ConvertsLocationToVenue) so a race can't surface as a raw UNIQUE-index 500.
        $subdomain = Role::generateSubdomain($name, shortest: false);
        $attempts = 0;
        while (Role::where('subdomain', $subdomain)->exists() && $attempts < 10) {
            $subdomain = Role::generateSubdomain($name.'-'.++$attempts, shortest: false);
        }
        $talent->subdomain = $subdomain;

        $talent->timezone = $timezone ?: 'UTC';
        $talent->language_code = is_valid_language_code($languageCode) ? $languageCode : 'en';

        // Mark verified (without an email/phone) so the page is "claimed" and public:
        // RoleController::viewGuest() answers an unpublished schedule as an unknown address,
        // and sends its own members into the app. Leaving email/phone null keeps the
        // auto-created page noindex and out of the sitemap.
        $talent->email_verified_at = now();

        if (config('app.is_testing')) {
            $talent->plan_type = 'enterprise';
            $talent->plan_expires = '2099-01-01';
        }

        $talent->save();

        $user->roles()->attach($talent->id, [
            'level' => 'owner',
            'created_at' => now(),
        ]);

        AuditService::log(AuditService::SCHEDULE_CREATE, $user->id, 'Role', $talent->id, null, null, $talent->name);

        return $talent;
    }

    /**
     * Whether a NEW account may be made here. Selfhost does not allow public self-registration
     * after the first user unless the operator sets ALLOW_REGISTRATION (RegisteredUserController).
     */
    public static function registrationOpen(): bool
    {
        return public_registration_enabled() || config('app.is_testing') || User::count() === 0;
    }

    /**
     * Mail a 6-digit sign-in code to an address, for the vendor link. Works for an address with
     * an account (it signs that account in) and one without (it makes one).
     *
     * The code is kept against the ADDRESS (`guest_code:` + a hash of it), not against the code
     * as the sign-up form's is: that store answers "whose code is this?", and so cannot count
     * wrong guesses against one account. The mail is the sign-up form's own
     * (SignupVerificationCode), so a person sees one kind of code mail whichever door they used.
     *
     * Refused, before anything is mailed: an account with two-factor (the sign-in page asks for
     * its code), a blocked account, an address or network the operator's list refuses where an
     * account would be MADE, a new account where registration is closed, and off hosted an
     * account that waits for its owner (its set-password link is mailed instead).
     *
     * @throws ValidationException under `email`
     */
    public function sendSignInCode(Request $request, string $email): void
    {
        $email = strtolower(trim($email));
        $existing = User::where('email', $email)->first();

        $this->refuseForSignIn($existing, $request, $email);

        // Counted before the send and atomically, so requests in parallel cannot slip past the
        // hourly cap (each code is a set of guesses at an account's sign-in).
        $sendsKey = 'guest_code_sends:'.self::addressKey($email);
        $dayKey = 'guest_code_sends_day:'.self::addressKey($email);
        Cache::add($sendsKey, 0, now()->addHour());
        Cache::add($dayKey, 0, now()->addDay());
        // An hour's cap, and a day's: every code is five guesses at the account.
        if ((int) Cache::increment($sendsKey) > self::CODES_PER_HOUR || (int) Cache::increment($dayKey) > self::CODES_PER_DAY) {
            throw ValidationException::withMessages(['email' => [__('messages.code_rate_limit')]]);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        try {
            Notification::route('mail', $email)->notifyNow(new SignupVerificationCode($code));
        } catch (\Throwable $e) {
            report($e);
            throw ValidationException::withMessages(['email' => [__('messages.error_sending_code')]]);
        }

        Cache::put('guest_code:'.self::addressKey($email), $code, now()->addMinutes(self::CODE_MINUTES));
    }

    /**
     * Check an emailed code and sign the address in, making the account if there is none. No
     * password: a new account has none (it can set one later). Remember-me is on: the vendor
     * comes back through the same WhatsApp link weeks later.
     *
     * @throws ValidationException under `email` or `code`
     */
    public function signInWithCode(Request $request, string $email, string $code, string $intent, string $name, Role $context): User
    {
        $email = strtolower(trim($email));
        $existing = User::where('email', $email)->first();

        // Asked again: an account can be blocked, or given two-factor, between the two steps.
        $this->refuseForSignIn($existing, $request, $email);

        // Per ACCOUNT when there is one, so a new code or another network does not renew the
        // allowance; per address otherwise.
        $throttleKey = 'guest-code:'.($existing ? 'u'.$existing->id : self::addressKey($email));

        if (RateLimiter::tooManyAttempts($throttleKey, self::WRONG_CODES)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages(['code' => [trans('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)])]]);
        }

        $stored = Cache::get('guest_code:'.self::addressKey($email));

        if (! is_string($stored) || ! preg_match('/^\d{6}$/', $code) || ! hash_equals($stored, $code)) {
            RateLimiter::hit($throttleKey, self::WRONG_CODE_LOCK_SECONDS);
            if ($existing) {
                AuditService::log(AuditService::AUTH_LOGIN_FAILED, null, null, null, null, null, $email);
            }

            // The last allowed guess also spends the code: a new one has to be asked for.
            if (RateLimiter::tooManyAttempts($throttleKey, self::WRONG_CODES)) {
                Cache::forget('guest_code:'.self::addressKey($email));
            }

            throw ValidationException::withMessages(['code' => [__('messages.code_invalid')]]);
        }

        Cache::forget('guest_code:'.self::addressKey($email));
        RateLimiter::clear($throttleKey);

        if ($existing && ! $existing->isStub()) {
            $user = $existing;
            if (! $user->hasVerifiedEmail()) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
            Auth::login($user, true);
            AuditService::log(AuditService::AUTH_LOGIN, $user->id);

            return $user;
        }

        $attributes = [
            'name' => mb_substr(trim($name), 0, 255) ?: Str::before($email, '@'),
            'timezone' => $context->timezone ?: 'UTC',
            'language_code' => is_valid_language_code($context->language_code) ? $context->language_code : self::guestLanguage(),
        ] + self::attribution($request);

        if ($existing) {
            // A stub on hosted (off hosted refuseForSignIn() has already turned it away): the
            // code proves the address, as sign-up's does. It keeps its own acquisition context.
            $attributes['signup_intent'] = $existing->signup_intent ?? $intent;
            $attributes['name'] = $existing->name ?: $attributes['name'];
            $existing->update($attributes);
            $user = $existing;
        } else {
            $attributes['signup_intent'] = $intent;
            $user = User::create($attributes + ['email' => $email]);
        }

        session()->forget(['utm_params', 'utm_referrer_url', 'utm_landing_page', 'guest_language']);

        $user->email_verified_at = now();
        $user->signup_ip = Blocklist::address($request);
        // The step says that continuing accepts the terms, beside links to them (hosted only, as
        // on the other guest forms).
        if (config('app.hosted')) {
            $user->terms_accepted_at = now();
        }
        $user->save();

        Auth::login($user, true);
        AuditService::log(AuditService::AUTH_REGISTER, $user->id);

        return $user;
    }

    /**
     * Everything that stops a code sign-in before a code is mailed or checked.
     *
     * @throws ValidationException under `email`; `reason` = `login` means the sign-in page
     */
    private function refuseForSignIn(?User $existing, Request $request, string $email): void
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
            throw ValidationException::withMessages(['email' => [__('validation.email', ['attribute' => __('messages.email')])]]);
        }

        if ($existing && $existing->isBlocked()) {
            throw ValidationException::withMessages(['email' => [__('messages.account_blocked')]]);
        }

        // An emailed code is a password-less way in: never for an account that runs the platform,
        // which signs in on the login page.
        if ($existing && $existing->hasTwoFactorEnabled()) {
            throw ValidationException::withMessages(['email' => [__('messages.two_factor_sign_in_first')]]);
        }
        if ($existing && $existing->is_admin) {
            throw ValidationException::withMessages(['email' => [__('messages.log_in_first')]]);
        }

        if ($existing && ! $existing->isStub()) {
            return;
        }

        // From here on an account is MADE (or a waiting one adopted), so the operator's list and
        // the registration switch apply, as on the sign-up form.
        if (! $existing && ! self::registrationOpen()) {
            throw ValidationException::withMessages(['email' => [__('messages.account_creation_disabled')]]);
        }

        validator(['email' => $email], ['email' => array_merge(
            [new NotBlocklisted],
            config('app.hosted') ? [new NoFakeEmail] : []
        )])->validate();

        if ($existing && ! config('app.hosted')) {
            throw ValidationException::withMessages(['email' => [self::stubRefusal($existing)]]);
        }
    }

    private static function stubRefusal(User $stub): string
    {
        return match (StubAccountUtils::send($stub)) {
            StubAccountUtils::THROTTLED => __('messages.login_password_link_already_sent'),
            default => __('messages.login_no_password_yet'),
        };
    }

    private static function addressKey(string $email): string
    {
        return hash('sha256', strtolower(trim($email)));
    }

    private static function guestLanguage(): string
    {
        return (session()->has('guest_language') && is_valid_language_code(session('guest_language')))
            ? session('guest_language')
            : 'en';
    }

    /** Where the visitor came from, as the sign-up form records it. */
    private static function attribution(Request $request): array
    {
        $utmParams = session('utm_params', []);
        if (empty($utmParams) && $request->cookie('utm_params')) {
            $utmParams = json_decode($request->cookie('utm_params'), true) ?? [];
        }

        // Last fallback: the browser-written es_attribution cookie, for a guest who arrived
        // through an edge-cached marketing page and so never got a server session.
        $clientAttribution = CaptureUtmParameters::clientAttribution($request);

        if (empty($utmParams)) {
            $utmParams = $clientAttribution['utm_params'];
        }

        return [
            'utm_source' => $utmParams['utm_source'] ?? null,
            'utm_medium' => $utmParams['utm_medium'] ?? null,
            'utm_campaign' => $utmParams['utm_campaign'] ?? null,
            'utm_content' => $utmParams['utm_content'] ?? null,
            'utm_term' => $utmParams['utm_term'] ?? null,
            'referrer_url' => session('utm_referrer_url') ?? $request->cookie('utm_referrer_url') ?? $clientAttribution['utm_referrer_url'],
            'landing_page' => session('utm_landing_page') ?? $request->cookie('utm_landing_page') ?? $clientAttribution['utm_landing_page'],
        ];
    }
}
