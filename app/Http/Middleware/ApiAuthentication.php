<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class ApiAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');
        $clientIp = $request->ip();

        if (! $apiKey) {
            $this->logFailedAttempt($clientIp, 'missing_api_key');

            return response()->json(['error' => 'API key is required'], 401);
        }

        // Tiered rate limiting per IP based on HTTP method.
        //
        // Each bucket is a fixed one-minute window opened by its first counted request:
        // RateLimiter::hit() sets the expiry once, with Cache::add(), and increments inside it.
        // This used to be a Cache::put() of count + 1 with a fresh one-minute expiry on every
        // request, which pushed the expiry out each time. A bucket then emptied only after a full
        // idle minute, so a client reading every 30 seconds was refused on its 300th request, two
        // and a half hours in.
        $isWriteOperation = in_array($request->method(), ['POST', 'PUT', 'DELETE']);
        $rateLimitKey = $isWriteOperation
            ? 'api_rate_limit_write:'.$clientIp
            : 'api_rate_limit_read:'.$clientIp;
        $rateLimit = $isWriteOperation ? 30 : 300;

        if (RateLimiter::tooManyAttempts($rateLimitKey, $rateLimit)) {
            return $this->rateLimitExceeded($clientIp, $rateLimitKey);
        }

        // Brute force protection per API key
        $bruteForceKey = 'api_brute_force:'.hash('sha256', $apiKey);
        $failedAttempts = Cache::get($bruteForceKey, 0);

        if ($failedAttempts >= 10) { // 10 failed attempts
            $this->logFailedAttempt($clientIp, 'brute_force_protection', $apiKey);

            return response()->json(['error' => 'API key temporarily blocked'], 423);
        }

        // Find user using prefix-based lookup for efficiency
        // api_key column stores first 8 chars of SHA256(raw_key) as an index
        $keyPrefix = substr(hash('sha256', $apiKey), 0, 8);
        $candidates = User::where('api_key', $keyPrefix)
            ->whereNotNull('api_key_hash')
            ->get();

        $user = null;
        foreach ($candidates as $candidate) {
            // Verify full API key against bcrypt hash
            if (Hash::check($apiKey, $candidate->api_key_hash)) {
                $user = $candidate;
                break;
            }
        }

        if (! $user) {
            // Increment failed attempts
            Cache::put($bruteForceKey, $failedAttempts + 1, now()->addMinutes(15));
            $this->logFailedAttempt($clientIp, 'invalid_api_key', $apiKey);

            // Add small delay to slow down brute force attacks
            usleep(250000); // 250ms delay

            return response()->json(['error' => 'Invalid API key'], 401);
        }

        // Check if API key has expired
        if ($user->api_key_expires_at && now()->greaterThan($user->api_key_expires_at)) {
            $this->logFailedAttempt($clientIp, 'api_key_expired');

            return response()->json(['error' => 'API key expired'], 401);
        }

        // Reset failed attempts on successful authentication
        Cache::forget($bruteForceKey);

        // Count the request only now, so a key that fails never spends the address's allowance.
        // The increment is atomic on the array, database and redis stores, so this also refuses
        // requests that passed the check above together, in a burst; the file store can
        // undercount such a burst.
        if (RateLimiter::hit($rateLimitKey, 60) > $rateLimit) {
            return $this->rateLimitExceeded($clientIp, $rateLimitKey);
        }

        auth()->login($user);

        return $next($request);
    }

    private function rateLimitExceeded($clientIp, string $rateLimitKey)
    {
        $this->logFailedAttempt($clientIp, 'rate_limit_exceeded');

        // Seconds until this bucket's window closes and its count starts again from zero.
        return response()->json(['error' => 'Rate limit exceeded'], 429)
            ->header('Retry-After', (string) max(1, RateLimiter::availableIn($rateLimitKey)));
    }

    private function logFailedAttempt($ip, $reason, $apiKey = null)
    {
        AuditService::log(AuditService::API_AUTH_FAILED, null, null, null, null, null, $reason);
    }
}
