<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\DemoService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DemoAutoLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only run in hosted or testing mode
        if (! config('app.hosted') && ! config('app.is_testing')) {
            return $next($request);
        }

        // Check if this is a demo subdomain request
        if (! $this->isDemoSubdomain($request)) {
            return $next($request);
        }

        // If already authenticated as demo user, redirect to demo schedule from landing page only
        if (Auth::check()) {
            if (DemoService::isDemoUser(Auth::user())) {
                // Only redirect from demo landing page, allow other navigation
                $path = trim($request->path(), '/');
                if ($path === '' || $path === DemoService::DEMO_SUBDOMAIN) {
                    return redirect()->route('role.view_admin', [
                        'subdomain' => DemoService::DEMO_ROLE_SUBDOMAIN,
                        'tab' => 'schedule',
                    ]);
                }
            }

            return $next($request);
        }

        // Find the demo user and log them in
        $demoUser = User::where('email', DemoService::DEMO_EMAIL)->first();

        if ($demoUser) {
            Auth::login($demoUser);
            $request->session()->regenerate();
            $request->session()->regenerateToken();

            // Detect and store browser language for demo users
            $this->detectAndStoreDemoLanguage($request);

            // Redirect to the schedule page after auto-login on demo subdomain
            return redirect()->route('role.view_admin', ['subdomain' => DemoService::DEMO_ROLE_SUBDOMAIN, 'tab' => 'schedule']);
        }

        return $next($request);
    }

    /**
     * Check if the request is for the demo subdomain
     */
    protected function isDemoSubdomain(Request $request): bool
    {
        // In testing mode, check the path for demo subdomain (takes priority)
        if (config('app.is_testing')) {
            // First try route parameter if available
            $subdomain = $request->route('subdomain');
            if ($subdomain === DemoService::DEMO_SUBDOMAIN) {
                return true;
            }

            // Fallback: check the URL path directly
            $path = trim($request->path(), '/');
            $segments = explode('/', $path);

            if (! empty($segments[0]) && $segments[0] === DemoService::DEMO_SUBDOMAIN) {
                return true;
            }
        }

        // In hosted mode, check the subdomain from the host
        if (config('app.hosted')) {
            $host = $request->getHost();
            $subdomain = $this->extractSubdomain($host);

            return $subdomain === DemoService::DEMO_SUBDOMAIN;
        }

        return false;
    }

    /**
     * Extract subdomain from host
     */
    protected function extractSubdomain(string $host): ?string
    {
        // Remove port if present
        $host = preg_replace('/:\d+$/', '', $host);

        // Get the base domain from config
        $baseDomain = config('app.domain', 'eventschedule.com');

        // If the host ends with the base domain, extract the subdomain
        if (str_ends_with($host, '.'.$baseDomain)) {
            $subdomain = substr($host, 0, -strlen('.'.$baseDomain));

            // Only return if it's a single subdomain (no dots)
            if (! str_contains($subdomain, '.')) {
                return $subdomain;
            }
        }

        // Handle case where host IS the subdomain.baseDomain
        $parts = explode('.', $host);
        if (count($parts) >= 3) {
            return $parts[0];
        }

        return null;
    }

    /**
     * Detect browser language and store in session for demo users
     */
    protected function detectAndStoreDemoLanguage(Request $request): void
    {
        if ($request->session()->has('demo_language')) {
            return; // Preserve existing selection
        }

        $supportedLanguages = config('app.supported_languages', ['en']);
        $browserLanguage = $request->getPreferredLanguage($supportedLanguages);
        $request->session()->put('demo_language', $browserLanguage ?? 'en');
    }
}
