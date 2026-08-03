<?php

namespace App\Services;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DigitalOceanService
{
    /**
     * Longest error we will hand back for storage in roles.custom_domain_error (a varchar).
     */
    protected const ERROR_LENGTH = 240;

    protected string $apiToken;

    protected string $appId;

    protected string $baseUrl = 'https://api.digitalocean.com/v2';

    /**
     * Why the last syncDomains() call failed, short enough to store on the role.
     */
    protected ?string $lastError = null;

    /**
     * True when the last syncDomains() call found the spec already correct and skipped the write.
     */
    protected bool $lastWasNoop = false;

    public function __construct()
    {
        $this->apiToken = config('services.digitalocean.api_token') ?? '';
        $this->appId = config('services.digitalocean.app_id') ?? '';
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiToken) && ! empty($this->appId);
    }

    /**
     * A short, already-truncated reason the last sync failed, or null if it succeeded.
     *
     * Safe to persist and to show in the admin panel. It is not safe to show to schedule owners:
     * it carries DigitalOcean's own wording, which is exactly the kind of raw upstream message the
     * owner-facing surfaces must not leak.
     */
    public function lastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * True when the last sync succeeded without needing to change anything.
     */
    public function lastWasNoop(): bool
    {
        return $this->lastWasNoop;
    }

    /**
     * Ensure a domain is present in the DigitalOcean App Platform app spec.
     */
    public function addDomain(string $hostname): bool
    {
        return $this->syncDomains([$hostname]);
    }

    /**
     * Ensure a domain is absent from the DigitalOcean App Platform app spec.
     */
    public function removeDomain(string $hostname): bool
    {
        return $this->syncDomains([], [$hostname]);
    }

    /**
     * Apply a batch of domain additions and removals to the app spec in a single write.
     *
     * DigitalOcean has no per-domain endpoint: the only way to change an app's domains is to PUT
     * the whole spec back, and every spec PUT creates a new App Platform deployment. Running a
     * remove and an add as two separate calls therefore redeploys the app twice, and the second
     * call executes while the container serving the request is already being replaced - which is
     * how the admin panel used to answer its own re-provision with a 503. So both sides are batched
     * into one read-modify-write, and the PUT is skipped entirely when nothing would change.
     *
     * @param  string[]  $add  hostnames to ensure are present
     * @param  string[]  $remove  hostnames to ensure are absent
     */
    public function syncDomains(array $add = [], array $remove = []): bool
    {
        $this->lastError = null;
        $this->lastWasNoop = false;

        if (! $this->isConfigured()) {
            Log::warning('DigitalOcean service not configured, skipping domain sync', [
                'add' => $add,
                'remove' => $remove,
            ]);
            $this->lastError = 'DigitalOcean is not configured.';

            return false;
        }

        try {
            // Serialize the read-modify-write. Two concurrent syncs would each build their new spec
            // from the same snapshot, so whichever PUT landed second would silently drop the other
            // one's domain. Note this only serializes across App Platform instances when
            // CACHE_STORE is a shared driver (database/redis), not on the file default.
            return Cache::lock('do_app_spec', 120)->block(60, function () use ($add, $remove) {
                return $this->applyDomainChanges($add, $remove);
            });
        } catch (LockTimeoutException $e) {
            Log::warning('Timed out waiting for the DO app spec lock', [
                'add' => $add,
                'remove' => $remove,
            ]);
            $this->lastError = 'Another domain update is already in progress.';

            return false;
        } catch (\Exception $e) {
            Log::error('Exception syncing domains in DO app spec', [
                'add' => $add,
                'remove' => $remove,
                'error' => $e->getMessage(),
            ]);
            report($e);
            $this->lastError = Str::limit($e->getMessage(), self::ERROR_LENGTH);

            return false;
        }
    }

    /**
     * Build the new domain list and write it back, if it differs from what DigitalOcean has.
     *
     * @param  string[]  $add
     * @param  string[]  $remove
     */
    protected function applyDomainChanges(array $add, array $remove): bool
    {
        $app = $this->getApp();
        $spec = $app['spec'] ?? [];
        $current = $spec['domains'] ?? [];

        // A hostname on both sides means "make sure this is registered" (the re-provision case, and
        // an owner saving the same domain twice). Removing and re-adding it would rewrite the spec
        // for no reason and cost a deployment, so the add wins.
        $addLower = array_map('strtolower', $add);
        $removeLower = array_diff(array_map('strtolower', $remove), $addLower);

        $changed = false;
        $domains = [];

        foreach ($current as $domain) {
            if (in_array(strtolower($domain['domain'] ?? ''), $removeLower, true)) {
                $changed = true;

                continue;
            }

            $domains[] = $domain;
        }

        $present = array_map(fn ($domain) => strtolower($domain['domain'] ?? ''), $domains);

        foreach ($add as $hostname) {
            if (in_array(strtolower($hostname), $present, true)) {
                // Already registered. Leave DigitalOcean's own entry exactly as it is: rebuilding
                // it would produce a spec that differs only in fields DO added itself, and the
                // resulting no-op PUT would still trigger a deployment.
                continue;
            }

            $domains[] = [
                'domain' => $hostname,
                'type' => 'PRIMARY',
                'zone' => '',
            ];
            $present[] = strtolower($hostname);
            $changed = true;
        }

        if (! $changed) {
            Log::info('DO app spec domains already up to date, skipping write', [
                'add' => $add,
                'remove' => $remove,
            ]);
            $this->lastWasNoop = true;

            return true;
        }

        $spec['domains'] = $domains;

        $response = Http::withToken($this->apiToken)
            ->connectTimeout(10)
            ->timeout(60)
            ->put("{$this->baseUrl}/apps/{$this->appId}", [
                'spec' => $spec,
            ]);

        if ($response->successful()) {
            Log::info('DO app spec domains updated', [
                'add' => $add,
                'remove' => $remove,
            ]);

            return true;
        }

        $this->lastError = $this->summarize($response);

        Log::error('Failed to update domains in DO app spec', [
            'add' => $add,
            'remove' => $remove,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return false;
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
            report($e);

            return null;
        }
    }

    /**
     * Get all domains and their status from the DO app.
     *
     * Note this reads the GET app response, where each domain is wrapped as
     * {spec: {domain: ...}, phase: ...}. The app spec itself (what syncDomains writes) uses the
     * flat shape, $domain['domain'].
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
            report($e);

            return [];
        }
    }

    /**
     * Get the full app object from DO API.
     */
    protected function getApp(): array
    {
        $response = Http::withToken($this->apiToken)
            ->connectTimeout(10)
            ->timeout(30)
            ->get("{$this->baseUrl}/apps/{$this->appId}");

        if (! $response->successful()) {
            throw new \RuntimeException('Failed to read the DigitalOcean app: '.$this->summarize($response));
        }

        return $response->json('app') ?? [];
    }

    /**
     * Reduce a failed DO response to something short enough to store on the role.
     *
     * DigitalOcean returns {"id": "...", "message": "..."} on rejection, which is the useful part;
     * fall back to the raw body when it does not.
     */
    protected function summarize(Response $response): string
    {
        $message = $response->json('message');

        if (! is_string($message) || trim($message) === '') {
            $message = $response->body();
        }

        return Str::limit(trim("HTTP {$response->status()}: ".trim($message)), self::ERROR_LENGTH);
    }
}
