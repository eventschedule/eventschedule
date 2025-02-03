<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleBotTraffic
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (str_contains(strtolower($request->userAgent()), 'gptbot')) {
            $path = $request->path();
            if ($request->getQueryString()) {
                return redirect($path, 301);
            }
        }

        return $next($request);
    }
}
