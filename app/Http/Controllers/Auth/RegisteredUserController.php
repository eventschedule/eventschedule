<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SignupVerificationCode;
use App\Rules\NoFakeEmail;
use App\Rules\ValidTurnstile;
use App\Utils\TurnstileUtils;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        if (! config('app.hosted') && config('app.url') && ! config('app.is_testing')) {
            return redirect()->route('login');
        }

        if (request()->has('lang')) {
            \App::setLocale(request('lang'));
        }

        return view('auth.register');
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

        // Check if email is already registered
        if (User::where('email', $email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.email_already_registered'),
            ], 422);
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
        Notification::route('mail', $email)->notify(new SignupVerificationCode($code));

        return response()->json([
            'success' => true,
            'message' => __('messages.code_sent'),
        ]);
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

        if (! config('app.hosted') && ! config('app.url')) {
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

            // Update database settings in .env file
            $envPath = base_path('.env');
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
                unlink($tempFile);
                throw new \Exception('Failed to update configuration file');
            }

            config(['database.connections.mysql.host' => $request->database_host]);
            config(['database.connections.mysql.port' => $request->database_port]);
            config(['database.connections.mysql.database' => $request->database_name]);
            config(['database.connections.mysql.username' => $request->database_username]);
            config(['database.connections.mysql.password' => $request->database_password]);

            DB::purge('mysql');

            try {
                Artisan::call('migrate', ['--force' => true]);
                Artisan::call('storage:link');
            } catch (\Exception $e) {
                return back()->withErrors(['database_host' => 'Database setup failed: '.$e->getMessage()])->withInput();
            }
        }

        if (! config('app.hosted') && ! config('app.is_testing') && User::count() > 0) {
            return redirect()->route('login');
        }

        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => array_merge(
                ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            'password' => ['required', 'string', 'min:8'],
            'language_code' => ['nullable', 'string', 'in:'.implode(',', config('app.supported_languages', ['en']))],
            'cf-turnstile-response' => [new ValidTurnstile],
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

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'timezone' => $request->timezone ?? 'America/New_York',
            'language_code' => $request->language_code ?? 'en',
        ]);

        // Mark email as verified if code was validated (hosted mode) or in non-hosted/testing mode
        if ((config('app.hosted') && ! config('app.is_testing')) || ! config('app.hosted') || config('app.is_testing')) {
            $user->email_verified_at = now();
            $user->save();
        }

        event(new Registered($user));

        Auth::login($user, true);

        return redirect(route('home', absolute: false));
    }
}
