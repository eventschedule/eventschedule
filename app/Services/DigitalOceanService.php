<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DigitalOceanService
{
    protected string $apiToken;

    protected string $appId;

    protected string $baseUrl = 'https://api.digitalocean.com/v2';

    public function __construct()
    {
        $this->apiToken = config('services.digitalocean.api_token', '');
        $this->appId = config('services.digitalocean.app_id', '');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiToken) && ! empty($this->appId);
    }

    /**
     * Add a domain to the DigitalOcean App Platform app spec.
     *
     * Note: addDomain/removeDomain manipulate the app spec directly, where domains
     * use a flat structure (e.g., $domain['domain']). In contrast, getDomainStatus/
     * getAppDomains read from the GET app response, where each domain is wrapped as
     * {spec: {domain: ...}, phase: ...}, so they access $domain['spec']['domain'].
     */
    public function addDomain(string $hostname): bool
    {
        if (! $this->isConfigured()) {
            Log::warning('DigitalOcean service not configured, skipping addDomain', ['hostname' => $hostname]);

            return false;
        }

        try {
            $app = $this->getApp();
            $spec = $app['spec'] ?? [];

            // Check if domain already exists
            $domains = $spec['domains'] ?? [];
            foreach ($domains as $domain) {
                if (($domain['domain'] ?? '') === $hostname) {
                    Log::info('Domain already exists in DO app spec', ['hostname' => $hostname]);

                    return true;
                }
            }

            // Add the new domain
            $domains[] = [
                'domain' => $hostname,
                'type' => 'PRIMARY',
                'zone' => '',
            ];
            $spec['domains'] = $domains;

            // Update the app spec
            $response = Http::withToken($this->apiToken)
                ->put("{$this->baseUrl}/apps/{$this->appId}", [
                    'spec' => $spec,
                ]);

            if ($response->successful()) {
                Log::info('Domain added to DO app spec', ['hostname' => $hostname]);

                return true;
            }

            Log::error('Failed to add domain to DO app spec', [
                'hostname' => $hostname,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception adding domain to DO app spec', [
                'hostname' => $hostname,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Remove a domain from the DigitalOcean App Platform app spec.
     */
    public function removeDomain(string $hostname): bool
    {
        if (! $this->isConfigured()) {
            Log::warning('DigitalOcean service not configured, skipping removeDomain', ['hostname' => $hostname]);

            return false;
        }

        try {
            $app = $this->getApp();
            $spec = $app['spec'] ?? [];

            $domains = $spec['domains'] ?? [];
            $spec['domains'] = array_values(array_filter($domains, function ($domain) use ($hostname) {
                return ($domain['domain'] ?? '') !== $hostname;
            }));

            $response = Http::withToken($this->apiToken)
                ->put("{$this->baseUrl}/apps/{$this->appId}", [
                    'spec' => $spec,
                ]);

            if ($response->successful()) {
                Log::info('Domain removed from DO app spec', ['hostname' => $hostname]);

                return true;
            }

            Log::error('Failed to remove domain from DO app spec', [
                'hostname' => $hostname,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception removing domain from DO app spec', [
                'hostname' => $hostname,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get the status/phase of a specific domain from the DO app.
     */
    public function getDomainStatus(string $hostname): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $app = $this->getApp();
            $domains = $app['domains'] ?? [];

            foreach ($domains as $domain) {
                if (($domain['spec']['domain'] ?? '') === $hostname) {
                    return $domain['phase'] ?? 'UNKNOWN';
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Exception getting domain status from DO', [
                'hostname' => $hostname,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get all domains and their status from the DO app.
     */
    public function getAppDomains(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        try {
            $app = $this->getApp();
            $domains = $app['domains'] ?? [];

            $result = [];
            foreach ($domains as $domain) {
                $hostname = $domain['spec']['domain'] ?? ($domain['domain'] ?? '');
                $result[$hostname] = [
                    'phase' => $domain['phase'] ?? 'UNKNOWN',
                    'certificate_expiration' => $domain['certificate_expiration'] ?? null,
                ];
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Exception getting app domains from DO', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get the full app object from DO API.
     */
    protected function getApp(): array
    {
        $response = Http::withToken($this->apiToken)
            ->get("{$this->baseUrl}/apps/{$this->appId}");

        if (! $response->successful()) {
            throw new \RuntimeException("Failed to get DO app: {$response->status()} {$response->body()}");
        }

        return $response->json('app') ?? [];
    }
}
