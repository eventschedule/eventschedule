<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Read at runtime by Laravel's TrustProxies middleware. This MUST live in a
    | config file (not bootstrap/app.php) so it survives `php artisan config:cache`
    | - env() returns null outside config files once the config is cached, which
    | would otherwise drop proxy trust and cause infinite redirect loops behind a
    | reverse proxy / Cloudflare.
    |
    | Set TRUSTED_PROXIES to '*' to trust all proxies, or a comma-separated list of
    | proxy IPs/CIDRs (e.g. "10.0.0.0/8,192.168.1.1"). When unset, the nexus
    | (IS_NEXUS=true) trusts all proxies; other installs trust none.
    |
    */

    'proxies' => env('TRUSTED_PROXIES') ?: (env('IS_NEXUS') ? '*' : null),

];
