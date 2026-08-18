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
     * ADVISORY ONLY - the caller logs the result and carries on. $ip is the real client address only
     * when Laravel trusts the upstream proxy, and config/trustedproxy.php trusts none unless
     * IS_NEXUS is set, so on a selfhost install behind Cloudflare or Docker this is the proxy's
     * address and never matches. That is exactly why this must not gate settlement: confirmsPayment()
     * does.
     */
    public function isValidSourceIp(?string $ip): bool
    {
        if (! $ip) {
            return false;
        }

        $hosts = (array) config('payments.payfast.itn_hosts', []);

        // Cached: this check is advisory (its result only feeds a log line), so it must not cost up
        // to four blocking DNS resolutions on the settlement path for every ITN. Literal addresses
        // in the config skip resolution entirely, which is also what keeps the tests off the network.
        //
        // The key includes the configured hosts, so an operator adding one - the realistic urgent
        // case, since Payfast has added sending addresses before - takes effect at once instead of
        // waiting out a TTL that was reasoned about for DNS changes, not config edits.
        $key = 'payfast:itn_ips:'.md5(implode(',', $hosts));
        $allowed = Cache::get($key);

        if ($allowed === null) {
            $allowed = [];

            foreach ($hosts as $host) {
                // A literal address in the config is taken as-is; anything else is resolved.
                if (filter_var($host, FILTER_VALIDATE_IP)) {
                    $allowed[] = $host;

                    continue;
                }

                $addresses = gethostbynamel($host);

                if ($addresses !== false) {
                    $allowed = array_merge($allowed, $addresses);
                }
            }

            // Only a NON-empty result is cached. Storing a total resolution failure would keep the
            // check failing - and logging false "unrecognised source" warnings on every genuine ITN -
            // for the whole TTL after DNS had already recovered.
            if ($allowed) {
                Cache::put($key, $allowed, 300);
            }
        }

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
