<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MarketingDailyStat;
use App\Models\PageView;
use App\Models\Referral;
use App\Models\User;
use App\Notifications\SignupVerificationCode;
use App\Rules\NoFakeEmail;
use App\Rules\ValidTurnstile;
use App\Services\AuditService;
use App\Utils\TurnstileUtils;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create()
    {
        // Preserve SMS token in session before any redirects so the login flow can use it
        $smsToken = request('sms_token');
        if (is_string($smsToken) && strlen($smsToken) > 0 && strlen($smsToken) <= 60) {
            session(['sms_token' => $smsToken]);
        }

        // Preserve the marketing-page schedule-type choice across validation
        // failures and the Google OAuth round-trip (same mechanism as sms_token)
        $requestedType = request('type');
        if (in_array($requestedType, ['talent', 'venue', 'curator'], true)) {
            session(['signup_role_type' => $requestedType]);
        }

        if (! config('app.hosted') && config('app.url') && ! config('app.is_testing') && ! selfhost_needs_setup() && User::exists()) {
            return redirect()->route('login');
        }

        restore_pending_action();

        if (request()->has('lang') && is_valid_language_code(request('lang'))) {
            \App::setLocale(request('lang'));
        }

        $smsPhone = null;
        $smsToken = session('sms_token');
        if ($smsToken) {
            $smsPhone = Cache::get('sms_signup_'.$smsToken);
            if ($smsPhone) {
                // Store in session so it survives Google OAuth redirect and validation failures
                session(['sms_token' => $smsToken]);

                // Check if a non-stub user with this phone already exists
                $existingPhoneUser = User::where('phone', $smsPhone)
                    ->where(function ($q) {
                        $q->whereNotNull('password')
                            ->orWhereNotNull('google_id')
                            ->orWhereNotNull('google_oauth_id')
                            ->orWhereNotNull('facebook_id');
                    })->first();

                if ($existingPhoneUser) {
                    return redirect()->route('login')
                        ->with('status', __('messages.account_exists_please_login'));
                }
            } else {
                // Clear stale token from session
                session()->forget('sms_token');
            }
        }

        // Onboarding funnel stage 2 ("viewed sign-up page"). Count once per session per
        // UTC day, skipping bots. Counted on all deployments (the /sign_up page is served
        // on the app subdomain, not the nexus, so this is not gated on is_nexus).
        if (! PageView::isBot(request()->userAgent())) {
            $dayKey = 'signup_view_'.now()->format('Ymd');
            if (! session()->has($dayKey)) {
                MarketingDailyStat::record('signup_views');
                session()->put($dayKey, true);
            }
        }

        return view('auth.register', [
            'smsPhone' => $smsPhone,
        ]);
    }

    /**
     * Send verification code to email address.
     */
    public function sendVerificationCode(Request $request): JsonResponse
    {
        // Honeypot check
        if ($request->filled('website')) {
            return response()->json([
                'success' => false,
                'message' => __('messages.invalid_request'),
            ], 422);
        }

        // Turnstile verification
        if (TurnstileUtils::isEnabled() && ! config('app.is_testing')) {
            if (! TurnstileUtils::verify($request->input('cf-turnstile-response'), $request->ip())) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.turnstile_verification_failed'),
                    'errors' => ['cf-turnstile-response' => [__('messages.turnstile_verification_failed')]],
                ], 422);
            }
        }

        $emailValidationRules = ['required', 'string', 'email', 'max:255'];

        // Add fake email validation for hosted mode
        if (config('app.hosted')) {
            $emailValidationRules[] = new NoFakeEmail;
        }

        $request->validate([
            'email' => $emailValidationRules,
        ]);

        $email = strtolower($request->email);

        // Check if email is already registered (allow stub users through)
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $isStub = $existingUser->isStub();

            if (! $isStub) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.email_already_registered'),
                ], 422);
            }
        }

        // Rate limiting: max 5 codes per hour per email
        $attemptsKey = 'signup_code_attempts_'.$email;
        $attempts = Cache::get($attemptsKey, 0);

        if ($attempts >= 5) {
            return response()->json([
                'success' => false,
                'message' => __('messages.code_rate_limit'),
            ], 429);
        }

        // Generate 6-digit code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store reverse mapping (code -> email) for validation
        // Each code has its own independent 10-minute expiration
        Cache::put('signup_code_email_'.$code, $email, now()->addMinutes(10));

        // Increment attempts counter (expires in 1 hour)
        Cache::put($attemptsKey, $attempts + 1, now()->addHour());

        // Send notification to email (using a temporary user object for notification)
        $tempUser = new User;
        $tempUser->email = $email;
        Notification::route('mail', $email)->notifyNow(new SignupVerificationCode($code));

        $response = [
            'success' => true,
            'message' => __('messages.code_sent'),
        ];

        if (isset($existingUser) && $existingUser && $existingUser->name) {
            $response['name'] = $existingUser->name;
        }

        return response()->json($response);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            throw ValidationException::withMessages([
                'email' => __('messages.invalid_request'),
            ]);
        }

        if (selfhost_needs_setup()) {
            $request->validate([
                'database_host' => ['required', 'string', 'max:255'],
                'database_port' => ['required', 'string', 'max:255'],
                'database_name' => ['required', 'string', 'max:255'],
                'database_username' => ['required', 'string', 'max:255'],
                'database_password' => ['required', 'string', 'max:255'],
            ]);

            $baseUrl = $request->getSchemeAndHttpHost();
            $basePath = $request->getBaseUrl(); // Detects "/public" if present
            $url = rtrim($baseUrl.$basePath, '/');

            $envPath = base_path('.env');

            // Fail fast if we cannot persist configuration, before running migrations.
            if (! is_writable($envPath)) {
                return back()->withErrors(['database_host' => __('messages.env_not_writable')])->withInput();
            }

            // Apply the posted credentials to the live connection and run migrations
            // BEFORE persisting anything. If the database is not usable, nothing is
            // written to .env, so the setup wizard stays reachable on the next request
            // instead of locking the user out with a half-configured install.
            config(['database.connections.mysql.host' => $request->database_host]);
            config(['database.connections.mysql.port' => $request->database_port]);
            config(['database.connections.mysql.database' => $request->database_name]);
            config(['database.connections.mysql.username' => $request->database_username]);
            config(['database.connections.mysql.password' => $request->database_password]);

            DB::purge('mysql');

            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                report($e);

                return back()->withErrors(['database_host' => __('messages.setup_migration_failed')])->withInput();
            }

            // Migrations succeeded: now it is safe to persist the configuration to .env.
            // This preserves the APP_KEY already generated by AppServiceProvider (the
            // per-key preg_replace only touches the listed keys).
            try {
                $envContent = file_get_contents($envPath);

                // Sanitize input values to prevent .env injection
                // addslashes() is insufficient - we must also block newlines and special chars
                $sanitizeEnvValue = function ($value) {
                    // Remove any newlines, carriage returns, and null bytes which could inject new env vars
                    $value = str_replace(["\r", "\n", "\0"], '', $value);
                    // Escape backslashes and double quotes for .env format
                    $value = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);

                    return $value;
                };

                $sanitizedHost = $sanitizeEnvValue($request->database_host);
                $sanitizedPort = (int) $request->database_port;
                $sanitizedName = $sanitizeEnvValue($request->database_name);
                $sanitizedUsername = $sanitizeEnvValue($request->database_username);
                $sanitizedPassword = $sanitizeEnvValue($request->database_password);
                $sanitizedUrl = $sanitizeEnvValue($url);

                $envContent = preg_replace_callback('/APP_ENV=.*/', fn () => 'APP_ENV=production', $envContent);
                $envContent = preg_replace_callback('/APP_URL=.*/', fn () => 'APP_URL="'.$sanitizedUrl.'"', $envContent);

                $envContent = preg_replace_callback('/DB_HOST=.*/', fn () => 'DB_HOST="'.$sanitizedHost.'"', $envContent);
                $envContent = preg_replace_callback('/DB_PORT=.*/', fn () => 'DB_PORT='.$sanitizedPort, $envContent);
                $envContent = preg_replace_callback('/DB_DATABASE=.*/', fn () => 'DB_DATABASE="'.$sanitizedName.'"', $envContent);
                $envContent = preg_replace_callback('/DB_USERNAME=.*/', fn () => 'DB_USERNAME="'.$sanitizedUsername.'"', $envContent);
                $envContent = preg_replace_callback('/DB_PASSWORD=.*/', fn () => 'DB_PASSWORD="'.$sanitizedPassword.'"', $envContent);

                if ($request->report_errors) {
                    $envContent = preg_replace_callback('/REPORT_ERRORS=.*/', fn () => 'REPORT_ERRORS=true', $envContent);
                }

                // Write to temporary file first, then move to prevent corruption
                $tempFile = $envPath.'.tmp';
                if (file_put_contents($tempFile, $envContent) === false) {
                    throw new \Exception('Failed to write configuration file');
                }

                if (! rename($tempFile, $envPath)) {
                    // Fall back to direct write if rename fails (e.g. Docker bind mounts)
                    if (file_put_contents($envPath, $envContent) === false) {
                        @unlink($tempFile);
                        throw new \Exception('Failed to update configuration file');
                    }
                    @unlink($tempFile);
                }
            } catch (\Exception $e) {
                return back()->withErrors(['database_host' => __('messages.env_not_writable')])->withInput();
            }

            // Non-critical for first boot: a failed symlink must not block setup.
            // --force avoids the "link already exists" error on retries.
            try {
                Artisan::call('storage:link', ['--force' => true]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        if (! config('app.hosted') && ! config('app.is_testing') && User::count() > 0) {
            return redirect()->route('login');
        }

        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => array_merge(
                ['required', 'string', 'email', 'max:255'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            'password' => ['required', 'string', 'min:8'],
            'language_code' => ['nullable', 'string', 'max:5'],
            'cf-turnstile-response' => [new ValidTurnstile],
            'sms_token' => ['nullable', 'string', 'max:60'],
        ];

        // Add verification code validation for hosted mode only
        if (config('app.hosted') && ! config('app.is_testing')) {
            $validationRules['verification_code'] = ['required', 'string', 'size:6'];
        }

        $request->validate($validationRules);

        // Validate verification code for hosted mode only
        if (config('app.hosted') && ! config('app.is_testing')) {
            $email = strtolower($request->email);

            // Atomically get and remove the code to prevent race conditions
            $originalEmail = Cache::pull('signup_code_email_'.$request->verification_code);
            if (! $originalEmail || strtolower($originalEmail) !== $email) {
                throw ValidationException::withMessages([
                    'verification_code' => [__('messages.code_invalid')],
                ]);
            }
        }

        // Default to English if browser language is not supported
        $languageCode = $request->language_code;
        if (! $languageCode || ! array_key_exists($languageCode, config('app.supported_languages', ['en' => 'english']))) {
            $languageCode = 'en';
        }

        $utmParams = session('utm_params', []);

        // Fall back to cookie if session has no UTM data
        if (empty($utmParams) && $request->cookie('utm_params')) {
            $utmParams = json_decode($request->cookie('utm_params'), true) ?? [];
        }

        // Classify the signup before any session keys are consumed below; the
        // hidden sms_token input is a fallback for when the session copy is gone
        $signupIntent = signup_intent_from_session();
        if ($signupIntent === 'organizer' && $request->filled('sms_token')) {
            $signupIntent = 'claim';
        }

        $email = strtolower($request->email);
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            $isStub = $existingUser->isStub();

            if (! $isStub) {
                throw ValidationException::withMessages([
                    'email' => [__('messages.email_already_registered')],
                ]);
            }

            $existingUser->update([
                'name' => $request->name,
                'password' => Hash::make($request->password),
                'timezone' => $request->timezone ?? 'America/New_York',
                'language_code' => $languageCode,
                'use_24_hour_time' => detect_24_hour_time($request->timezone, $languageCode),
                'utm_source' => $utmParams['utm_source'] ?? null,
                'utm_medium' => $utmParams['utm_medium'] ?? null,
                'utm_campaign' => $utmParams['utm_campaign'] ?? null,
                'utm_content' => $utmParams['utm_content'] ?? null,
                'utm_term' => $utmParams['utm_term'] ?? null,
                'referrer_url' => session('utm_referrer_url') ?? $request->cookie('utm_referrer_url'),
                'landing_page' => session('utm_landing_page') ?? $request->cookie('utm_landing_page'),
                // Keep the stub's original acquisition context (team invite,
                // newsletter subscriber) rather than re-labeling it organizer
                'signup_intent' => $existingUser->signup_intent ?? $signupIntent,
            ]);
            $user = $existingUser;
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $email,
                'password' => Hash::make($request->password),
                'timezone' => $request->timezone ?? 'America/New_York',
                'language_code' => $languageCode,
                'use_24_hour_time' => detect_24_hour_time($request->timezone, $languageCode),
                'utm_source' => $utmParams['utm_source'] ?? null,
                'utm_medium' => $utmParams['utm_medium'] ?? null,
                'utm_campaign' => $utmParams['utm_campaign'] ?? null,
                'utm_content' => $utmParams['utm_content'] ?? null,
                'utm_term' => $utmParams['utm_term'] ?? null,
                'referrer_url' => session('utm_referrer_url') ?? $request->cookie('utm_referrer_url'),
                'landing_page' => session('utm_landing_page') ?? $request->cookie('utm_landing_page'),
                'signup_intent' => $signupIntent,
            ]);
        }

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

        // On any non-nexus install (selfhost or a self-hosted SaaS), make the first user the
        // instance admin so they can reach the admin portal and self-updater.
        if (! config('app.is_nexus') && User::count() === 1) {
            $user->is_admin = true;
        }

        // Mark email as verified if code was validated (hosted mode) or in non-hosted/testing mode
        if ((config('app.hosted') && ! config('app.is_testing')) || ! config('app.hosted') || config('app.is_testing')) {
            $user->email_verified_at = now();
            $user->save();
        }

        if (session()->pull('pending_follow_consent_dismissed')) {
            $user->follow_consent_dismissed = true;
            $user->save();
        }

        // Process SMS signup token - auto-verify phone and claim matching roles
        $smsToken = $request->sms_token ?? session('sms_token');
        if ($smsToken) {
            $smsPhone = Cache::get('sms_signup_'.$smsToken);
            if ($smsPhone) {
                $user->claimRolesByPhone($smsPhone);
                Cache::forget('sms_signup_'.$smsToken);
            }
            // Clean up session token (stored in create() for Google OAuth flow)
            $request->session()->forget('sms_token');
        }

        event(new Registered($user));

        Auth::login($user, true);

        AuditService::log(AuditService::AUTH_REGISTER, $user->id, 'User', $user->id);

        return redirect(post_signup_redirect_url($user));
    }
}
