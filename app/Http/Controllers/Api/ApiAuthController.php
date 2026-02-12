<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SignupVerificationCode;
use App\Rules\NoFakeEmail;
use App\Services\AuditService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class ApiAuthController extends Controller
{
    public function sendCode(Request $request)
    {
        if (! config('app.hosted')) {
            return response()->json(['error' => 'Registration codes are only required in hosted mode'], 400);
        }

        // Honeypot check
        if ($request->filled('website')) {
            return response()->json(['error' => 'Invalid request'], 422);
        }

        try {
            $request->validate([
                'email' => ['required', 'string', 'email', 'max:255', new NoFakeEmail],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $email = strtolower($request->email);

        // Check if email is already registered (allow stub users through)
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $isStub = is_null($existingUser->password) && is_null($existingUser->google_id) && is_null($existingUser->facebook_id);
            if (! $isStub) {
                return response()->json(['error' => 'Unable to send verification code'], 422);
            }
        }

        // Rate limiting: max 5 codes per hour per email
        $attemptsKey = 'signup_code_attempts_'.$email;
        $attempts = Cache::get($attemptsKey, 0);

        if ($attempts >= 5) {
            return response()->json(['error' => 'Too many verification code requests. Please try again later.'], 429);
        }

        // Generate 6-digit code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store code keyed by email for validation
        Cache::put('api_signup_code_for_'.$email, $code, now()->addMinutes(10));

        // Increment attempts counter (expires in 1 hour)
        Cache::put($attemptsKey, $attempts + 1, now()->addHour());

        // Send notification
        Notification::route('mail', $email)->notify(new SignupVerificationCode($code));

        return response()->json([
            'data' => [
                'message' => 'Verification code sent to '.$email,
            ],
        ]);
    }

    public function register(Request $request)
    {
        // Honeypot check
        if ($request->filled('website')) {
            return response()->json(['error' => 'Invalid request'], 422);
        }

        // Rate limit: 3 registrations per IP per hour
        $registerRateKey = 'api_register_rate:'.$request->ip();
        $registerAttempts = Cache::get($registerRateKey, 0);
        if ($registerAttempts >= 3) {
            return response()->json(['error' => 'Too many registration attempts. Please try again later.'], 429);
        }

        // Block selfhosted registration if users already exist
        if (! config('app.hosted') && ! config('app.is_testing') && User::count() > 0) {
            return response()->json(['error' => 'Registration is closed'], 403);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => array_merge(
                ['required', 'string', 'email', 'max:255'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            'password' => ['required', 'string', 'min:8'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'language_code' => ['nullable', 'string', 'in:'.implode(',', config('app.supported_languages', ['en']))],
        ];

        // Require verification code in hosted mode
        if (config('app.hosted') && ! config('app.is_testing')) {
            $rules['verification_code'] = ['required', 'string', 'size:6'];
        }

        try {
            $request->validate($rules);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Validate verification code for hosted mode
        if (config('app.hosted') && ! config('app.is_testing')) {
            $email = strtolower($request->email);
            $storedCode = Cache::pull('api_signup_code_for_'.$email);
            if (! $storedCode || ! hash_equals($storedCode, $request->verification_code)) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => ['verification_code' => ['Invalid or expired verification code']],
                ], 422);
            }
        }

        $email = strtolower($request->email);
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            $isStub = is_null($existingUser->password) && is_null($existingUser->google_id) && is_null($existingUser->facebook_id);
            if (! $isStub) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => ['email' => ['Email is already registered']],
                ], 422);
            }

            // Upgrade stub user
            $existingUser->update([
                'name' => $request->name,
                'password' => Hash::make($request->password),
                'timezone' => $request->timezone ?? 'America/New_York',
                'language_code' => $request->language_code ?? 'en',
                'use_24_hour_time' => detect_24_hour_time($request->timezone, $request->language_code),
            ]);
            $user = $existingUser;
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $email,
                'password' => Hash::make($request->password),
                'timezone' => $request->timezone ?? 'America/New_York',
                'language_code' => $request->language_code ?? 'en',
                'use_24_hour_time' => detect_24_hour_time($request->timezone, $request->language_code),
            ]);
        }

        // Mark email verified
        $user->email_verified_at = now();

        // Generate API key
        $plaintextKey = bin2hex(random_bytes(16));
        $user->api_key = substr(hash('sha256', $plaintextKey), 0, 8);
        $user->api_key_hash = Hash::make($plaintextKey);
        $user->api_key_expires_at = now()->addYear();
        $user->save();

        // Increment registration rate limiter
        Cache::put($registerRateKey, $registerAttempts + 1, now()->addHour());

        AuditService::log(AuditService::API_REGISTER, $user->id, 'User', $user->id);

        return response()->json([
            'data' => [
                'api_key' => $plaintextKey,
                'api_key_expires_at' => $user->api_key_expires_at->toIso8601String(),
                'user' => [
                    'id' => UrlUtils::encodeId($user->id),
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ], 201, [], JSON_PRETTY_PRINT);
    }

    public function login(Request $request)
    {
        // Rate limit: 5 attempts per IP per 15 min
        $loginRateKey = 'api_login_rate:'.$request->ip();
        $loginAttempts = Cache::get($loginRateKey, 0);
        if ($loginAttempts >= 5) {
            return response()->json(['error' => 'Too many login attempts. Please try again later.'], 429);
        }

        try {
            $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = User::where('email', strtolower($request->email))->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            Cache::put($loginRateKey, $loginAttempts + 1, now()->addMinutes(15));
            usleep(250000); // 250ms delay

            return response()->json(['error' => 'Invalid email or password'], 401);
        }

        // Check for 2FA
        if ($user->hasTwoFactorEnabled()) {
            return response()->json(['error' => 'This account has two-factor authentication enabled. Please generate an API key from the web UI.'], 403);
        }

        // Generate new API key (replaces any existing key)
        $plaintextKey = bin2hex(random_bytes(16));
        $user->api_key = substr(hash('sha256', $plaintextKey), 0, 8);
        $user->api_key_hash = Hash::make($plaintextKey);
        $user->api_key_expires_at = now()->addYear();
        $user->save();

        // Reset login rate limiter on success
        Cache::forget($loginRateKey);

        AuditService::log(AuditService::API_LOGIN, $user->id);

        return response()->json([
            'data' => [
                'api_key' => $plaintextKey,
                'api_key_expires_at' => $user->api_key_expires_at->toIso8601String(),
                'user' => [
                    'id' => UrlUtils::encodeId($user->id),
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }
}
