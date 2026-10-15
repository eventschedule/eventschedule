<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\AuditService;
use App\Services\Blocklist;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class ApiAuthentication
{
    /** Requests with no key or a wrong one, per address, in a one-minute window. */
    public const UNPROVEN_PREFIX = 'api_rate_limit_unproven:';

    public const UNPROVEN_LIMIT = 30;

    /** The account a key was last proven to belong to, by the key's SHA-256. */
    public const KEY_OWNER_PREFIX = 'api_key_owner:';

    /** The SHA-256 of the key an account last proved, so forgetProvenKey() can find the entry. */
    public const OWNER_KEY_PREFIX = 'api_key_owner_of:';

    /** How long a proven key is remembered after its last use. */
    public const PROVEN_TTL_HOURS = 24;

    /** One account's read or write bucket. */
    public static function bucket(int $userId, bool $write): string
    {
        return ($write ? 'api_rate_limit_write:user:' : 'api_rate_limit_read:user:').$userId;
    }

    /**
     * The address requests that prove no key are counted against: the visitor's own, read the way
     * the blocklist reads it (Blocklist::address()), never the socket's peer, which behind
     * Cloudflare is an edge address shared by everybody that edge serves.
     */
    public static function unprovenBucket(Request $request): string
    {
        return self::UNPROVEN_PREFIX.(Blocklist::address($request) ?? (string) $request->ip());
    }

    /**
     * Forget which account a key was proven for. Called when the key is disabled or replaced
     * (ApiSettingsController), so the old key no longer gets past the per-address bucket.
     */
    public static function forgetProvenKey(User $user): void
    {
        $keyHash = Cache::pull(self::OWNER_KEY_PREFIX.$user->id);

        if (is_string($keyHash) && $keyHash !== '') {
            Cache::forget(self::KEY_OWNER_PREFIX.$keyHash);
        }
    }

    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');
        $clientIp = $request->ip();

        // The account this exact key was last proven to belong to (only ever written after a
        // proof). It decides two things below, and it only ever refuses: it never lets a request in.
        $keyHash = $apiKey ? hash('sha256', $apiKey) : null;
        $knownUserId = $keyHash ? Cache::get(self::KEY_OWNER_PREFIX.$keyHash) : null;

        // Requests that prove no key (none sent, or a wrong one) are counted per address, and an
        // address that keeps sending them is refused before any key is checked: each check costs
        // a bcrypt comparison. A key already proven is let past this bucket, so a caller who
        // shares an address with somebody else's broken client (a voice-agent platform, a
        // company's NAT) is not refused for that client's mistakes.
        $unprovenKey = self::unprovenBucket($request);
        if (! $knownUserId && RateLimiter::tooManyAttempts($unprovenKey, self::UNPROVEN_LIMIT)) {
            return $this->rateLimitExceeded($clientIp, $unprovenKey);
        }

        if (! $apiKey) {
            RateLimiter::hit($unprovenKey, 60);
            $this->logFailedAttempt($clientIp, 'missing_api_key');

            return response()->json(['error' => 'API key is required'], 401);
        }

        // Tiered rate limiting per ACCOUNT, based on HTTP method. It was per address until
        // 2026-10, which put every caller behind one shared address on a single budget, so one
        // busy client refused all the others.
        //
        // Each bucket is a fixed one-minute window opened by its first counted request:
        // RateLimiter::hit() sets the expiry once, with Cache::add(), and increments inside it.
        // This used to be a Cache::put() of count + 1 with a fresh one-minute expiry on every
        // request, which pushed the expiry out each time. A bucket then emptied only after a full
        // idle minute, so a client reading every 30 seconds was refused on its 300th request, two
        // and a half hours in.
        $isWriteOperation = in_array($request->method(), ['POST', 'PUT', 'DELETE']);
        $rateLimit = $isWriteOperation ? 30 : 300;

        // A key holder over the limit is refused here, before the bcrypt check rather than after it.
        if ($knownUserId && RateLimiter::tooManyAttempts(self::bucket((int) $knownUserId, $isWriteOperation), $rateLimit)) {
            return $this->rateLimitExceeded($clientIp, self::bucket((int) $knownUserId, $isWriteOperation));
        }

        // Brute force protection per API key
        $bruteForceKey = 'api_brute_force:'.$keyHash;
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
            RateLimiter::hit($unprovenKey, 60);
            $this->logFailedAttempt($clientIp, 'invalid_api_key', $apiKey);

            // Add small delay to slow down brute force attacks
            usleep(250000); // 250ms delay

            return response()->json(['error' => 'Invalid API key'], 401);
        }

        // An account an operator has shut out (/admin/blocked). After the key is proven, so the
        // answer tells nobody anything about a key they do not hold.
        if ($user->isBlocked()) {
            $this->logFailedAttempt($clientIp, 'account_blocked');

            return response()->json(['error' => 'Account blocked'], 403);
        }

        // Check if API key has expired
        if ($user->api_key_expires_at && now()->greaterThan($user->api_key_expires_at)) {
            $this->logFailedAttempt($clientIp, 'api_key_expired');

            return response()->json(['error' => 'API key expired'], 401);
        }

        // Reset failed attempts on successful authentication
        Cache::forget($bruteForceKey);
        // Remembered for a day after its last use, so a caller that calls every few hours is still
        // known (and let past the per-address bucket) when somebody else on its address misbehaves.
        // The pointer back from the account is what lets a disabled or replaced key be forgotten.
        Cache::put(self::KEY_OWNER_PREFIX.$keyHash, $user->id, now()->addHours(self::PROVEN_TTL_HOURS));
        Cache::put(self::OWNER_KEY_PREFIX.$user->id, $keyHash, now()->addHours(self::PROVEN_TTL_HOURS));

        // Count the request only now, against the account the key belongs to. The increment is
        // atomic on the array, database and redis stores, so this also refuses requests that
        // passed the check above together, in a burst; the file store can undercount such a burst.
        // Asked first, so a refused request is not counted: the pre-check above only sees a key
        // already proven in the last day.
        $rateLimitKey = self::bucket((int) $user->id, $isWriteOperation);
        if (RateLimiter::tooManyAttempts($rateLimitKey, $rateLimit)
            || RateLimiter::hit($rateLimitKey, 60) > $rateLimit) {
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
