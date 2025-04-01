<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;

class ApiAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return response()->json(['error' => 'API key is required'], 401);
        }

        $user = User::where('api_key', $apiKey)
                    ->where('api_enabled', true)
                    ->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        auth()->login($user);
        return $next($request);
    }
} 