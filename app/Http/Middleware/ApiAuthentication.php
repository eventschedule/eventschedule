<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');
        $clientIp = $request->ip();

        if (!$apiKey) {
            $this->logFailedAttempt($clientIp, 'missing_api_key');
            return response()->json(['error' => 'API key is required'], 401);
        }

        // Rate limiting per IP
        $rateLimitKey = 'api_rate_limit:' . $clientIp;
        $attempts = Cache::get($rateLimitKey, 0);
        
        if ($attempts >= 60) { // 60 requests per minute
            $this->logFailedAttempt($clientIp, 'rate_limit_exceeded');
            return response()->json(['error' => 'Rate limit exceeded'], 429);
        }

        // Brute force protection per API key
        $bruteForceKey = 'api_brute_force:' . hash('sha256', $apiKey);
        $failedAttempts = Cache::get($bruteForceKey, 0);
        
        if ($failedAttempts >= 10) { // 10 failed attempts
            $this->logFailedAttempt($clientIp, 'brute_force_protection', $apiKey);
            return response()->json(['error' => 'API key temporarily blocked'], 423);
        }

        $user = User::where('api_key', $apiKey)->first();

        if (!$user) {
            // Increment failed attempts
            Cache::put($bruteForceKey, $failedAttempts + 1, now()->addMinutes(15));
            $this->logFailedAttempt($clientIp, 'invalid_api_key', $apiKey);
            
            // Add small delay to slow down brute force attacks
            usleep(250000); // 250ms delay
            
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        // Reset failed attempts on successful authentication
        Cache::forget($bruteForceKey);
        
        // Increment rate limit counter
        Cache::put($rateLimitKey, $attempts + 1, now()->addMinute());

        // Update last used timestamp
        $user->update(['api_last_used_at' => now()]);
        
        auth()->login($user);
        
        return $next($request);
    }
    
    private function logFailedAttempt($ip, $reason, $apiKey = null)
    {
        Log::warning('API authentication failed', [
            'ip' => $ip,
            'reason' => $reason,
            'api_key_hash' => $apiKey ? hash('sha256', $apiKey) : null,
            'timestamp' => now()->toISOString()
        ]);
    }
} 