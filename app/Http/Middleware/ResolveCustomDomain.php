<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
            Log::info('Custom domain not found or not active', ['host' => $host]);
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
        $appUrl = "https://app.{$baseDomain}";
        $customDomainUrl = "https://{$host}";

        // Replace subdomain URLs with custom domain URLs in HTML responses
        if ($this->isHtmlResponse($response)) {
            $content = $response->getContent();
            if ($content) {
                $content = str_replace($subdomainUrl, $customDomainUrl, $content);
                $content = str_replace($appUrl, $customDomainUrl, $content);

                // Also replace protocol-relative and http variants
                $content = str_replace("http://{$role->subdomain}.{$baseDomain}", $customDomainUrl, $content);
                $content = str_replace("//{$role->subdomain}.{$baseDomain}", "//{$host}", $content);

                $response->setContent($content);
            }
        }

        // Rewrite Location header on redirect responses
        if ($response->isRedirection() && $response->headers->has('Location')) {
            $location = $response->headers->get('Location');
            $location = str_replace($subdomainUrl, $customDomainUrl, $location);
            $location = str_replace($appUrl, $customDomainUrl, $location);
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
}
