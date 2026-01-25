<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * Ensures the authenticated user has admin privileges.
     * This provides defense-in-depth for admin routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            return redirect()->route('home')->with('error', __('messages.not_authorized'));
        }

        return $next($request);
    }
}
