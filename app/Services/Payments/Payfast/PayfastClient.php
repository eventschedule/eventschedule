<?php

namespace App\Services\Payments\Payfast;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Payfast's hosts, and the two checks that need to talk back to them.
 */
class PayfastClient
{
    public function __construct(private bool $sandbox) {}

    public function processUrl(): string
    {
        return $this->baseUrl().'/eng/process';
    }

    public function validateUrl(): string
    {
        return $this->baseUrl().'/eng/query/validate';
    }

    private function baseUrl(): string
    {
        return $this->sandbox
            ? 'https://sandbox.payfast.co.za'
            : 'https://www.payfast.co.za';
    }

    /**
     * Is this request really from Payfast?
     *
     * The host list is resolved rather than hardcoded as addresses because Payfast changes them; that
     * is their own guidance. It comes from config so tests can point it at the test client without
     * stubbing gethostbynamel().
     *
     * $ip must be the real client address. Behind Cloudflare or any reverse proxy that means
     * TrustProxies has to be configured, which it is - otherwise every ITN appears to come from the
     * proxy and none of them validate.
     */
    public function isValidSourceIp(?string $ip): bool
    {
        if (! $ip) {
            return false;
        }

        // Cached: this check is advisory (its result only feeds a log line), so it must not cost
        // up to four blocking DNS resolutions on the settlement path for every ITN. Five minutes is
        // far shorter than any DNS change Payfast would make. Literal addresses in the config skip
        // resolution entirely, which is also what keeps the tests off the network.
        $allowed = Cache::remember('payfast:itn_ips', 300, function () {
            $resolved = [];

            foreach ((array) config('payments.payfast.itn_hosts', []) as $host) {
                // A literal address in the config is taken as-is; anything else is resolved.
                if (filter_var($host, FILTER_VALIDATE_IP)) {
                    $resolved[] = $host;

                    continue;
                }

                $addresses = gethostbynamel($host);

                if ($addresses !== false) {
                    $resolved = array_merge($resolved, $addresses);
                }
            }

            return $resolved;
        });

        if (! $allowed) {
            // Resolution failed for every host. Reported as not-valid, but note the caller treats
            // this whole check as ADVISORY - a DNS blip only changes what gets logged, never whether
            // a genuine payment settles. confirmsPayment() is the gate.
            Log::warning('Payfast ITN source check could not resolve any valid host');

            return false;
        }

        return in_array($ip, array_unique($allowed), true);
    }

    /**
     * Ask Payfast to confirm the payload we just received is one they sent.
     *
     * The last of the four checks and the only one an attacker cannot influence at all: even with a
     * leaked passphrase and a spoofed source address, a forged payment has to be one Payfast agrees
     * happened.
     *
     * @param  array<string, mixed>  $payload  the ITN exactly as received, signature included
     */
    public function confirmsPayment(array $payload): bool
    {
        try {
            $response = Http::asForm()
                ->timeout(15)
                ->post($this->validateUrl(), $payload);

            if (! $response->successful()) {
                Log::warning('Payfast ITN validation request failed', [
                    'status' => $response->status(),
                ]);

                return false;
            }

            // The body is a bare word. Payfast documents VALID / INVALID, and has been known to pad
            // it with whitespace.
            return strtoupper(trim($response->body())) === 'VALID';
        } catch (\Throwable $e) {
            // Network trouble is not a confirmation. Payfast retries the ITN, so failing closed
            // delays settlement rather than losing it.
            Log::warning('Payfast ITN validation could not be completed: '.$e->getMessage());

            return false;
        }
    }
}
