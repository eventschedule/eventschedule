<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ResolveCustomDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only applies in hosted mode
        if (! config('app.hosted')) {
            return $next($request);
        }

        $host = $request->getHost();
        $baseDomain = _base_domain();

        // Skip if this is a normal eventschedule.com request
        if ($host === $baseDomain || str_ends_with($host, '.'.$baseDomain)) {
            return $next($request);
        }

        // Look up the custom domain
        $role = Cache::remember("custom_domain:{$host}", 600, function () use ($host) {
            return Role::where('custom_domain_host', $host)
                ->where('custom_domain_mode', 'direct')
                ->where('custom_domain_status', 'active')
                ->first(['id', 'subdomain', 'custom_domain_host']);
        });

        if (! $role) {
            abort(404);
        }

        // Store the original custom domain host for URL replacement later
        $request->attributes->set('custom_domain_host', $host);
        $request->attributes->set('custom_domain_subdomain', $role->subdomain);

        // Rewrite the host headers so Laravel's subdomain routing matches
        $newHost = $role->subdomain.'.'.$baseDomain;
        $request->headers->set('HOST', $newHost);
        $request->server->set('HTTP_HOST', $newHost);
        $request->server->set('SERVER_NAME', $newHost);

        $response = $next($request);

        $subdomainUrl = "https://{$role->subdomain}.{$baseDomain}";
        $customDomainUrl = "https://{$host}";

        // Replace subdomain URLs with custom domain URLs in HTML responses
        // Note: only replace the schedule's subdomain URL, not app URLs (login, admin, follow)
        if ($this->isHtmlResponse($response)) {
            $content = $response->getContent();
            if ($content) {
                $content = str_replace($subdomainUrl, $customDomainUrl, $content);

                // Also replace protocol-relative and http variants
                $content = str_replace("http://{$role->subdomain}.{$baseDomain}", $customDomainUrl, $content);
                $content = str_replace("//{$role->subdomain}.{$baseDomain}", "//{$host}", $content);

                // Restore canonical, og:url, and hreflang to subdomain URL (SEO: canonical should always be the primary domain)
                $content = str_replace(
                    ['<link rel="canonical" href="' . $customDomainUrl, '<meta property="og:url" content="' . $customDomainUrl],
                    ['<link rel="canonical" href="' . $subdomainUrl, '<meta property="og:url" content="' . $subdomainUrl],
                    $content
                );
                $content = preg_replace(
                    '/<link rel="alternate" hreflang="([^"]*)" href="' . preg_quote($customDomainUrl, '/') . '/',
                    '<link rel="alternate" hreflang="$1" href="' . $subdomainUrl,
                    $content
                );

                $response->setContent($content);
            }
        }

        // Replace subdomain URLs in JSON responses (e.g. calendar data)
        if ($this->isJsonResponse($response)) {
            $content = $response->getContent();
            if ($content) {
                // JSON encodes forward slashes as \/, so replace the escaped versions
                $escapedSubdomainUrl = str_replace('/', '\\/', $subdomainUrl);
                $escapedCustomDomainUrl = str_replace('/', '\\/', $customDomainUrl);
                $content = str_replace($escapedSubdomainUrl, $escapedCustomDomainUrl, $content);

                $escapedHttpSubdomain = str_replace('/', '\\/', "http://{$role->subdomain}.{$baseDomain}");
                $escapedHttpCustom = str_replace('/', '\\/', $customDomainUrl);
                $content = str_replace($escapedHttpSubdomain, $escapedHttpCustom, $content);

                $response->setContent($content);
            }
        }

        // Rewrite Location header on redirect responses
        if ($response->isRedirection() && $response->headers->has('Location')) {
            $location = $response->headers->get('Location');
            $location = str_replace($subdomainUrl, $customDomainUrl, $location);
            $location = str_replace("http://{$role->subdomain}.{$baseDomain}", $customDomainUrl, $location);
            $response->headers->set('Location', $location);
        }

        return $response;
    }

    protected function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');

        return str_contains($contentType, 'text/html') && $response->getStatusCode() < 400;
    }

    protected function isJsonResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');

        return str_contains($contentType, 'application/json') && $response->getStatusCode() < 400;
    }
}
