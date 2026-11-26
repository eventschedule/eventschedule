<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Cloudflare's own addresses: the edge a request passes through, never the person who sent it.
 *
 * On hosted, App Platform sits behind Cloudflare, and CF-Connecting-IP and the socket's peer both
 * read as one of these (all 146 sign-ups of a month were). A per-visitor counter keyed on one
 * merges thousands of people, and a blocklist entry for one refuses all of them, so an address
 * here is never taken for a visitor and never stored as a block.
 *
 * The ranges are the ones Cloudflare publishes (https://www.cloudflare.com/ips-v4 and ips-v6).
 */
class EdgeAddress
{
    public const CLOUDFLARE = [
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    /** Whether this is one of Cloudflare's addresses. */
    public static function isCloudflare(?string $ip): bool
    {
        if (! is_string($ip) || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        return IpUtils::checkIp($ip, self::CLOUDFLARE);
    }

    /**
     * Whether a range overlaps Cloudflare's: a block that would refuse an edge address with
     * everyone behind it. Two CIDR ranges overlap exactly when one holds the other's base
     * address, so that is what is asked, both ways.
     */
    public static function rangeTouchesCloudflare(string $cidr): bool
    {
        if (! str_contains($cidr, '/')) {
            return self::isCloudflare($cidr);
        }

        [$base, $bits] = explode('/', $cidr, 2);

        if (filter_var($base, FILTER_VALIDATE_IP) === false || ! ctype_digit($bits)) {
            return false;
        }

        foreach (self::CLOUDFLARE as $range) {
            [$edgeBase, $edgeBits] = explode('/', $range, 2);

            // Same family only, then: either one holds the other's base address.
            if (str_contains($edgeBase, ':') !== str_contains($base, ':')) {
                continue;
            }

            if (IpUtils::checkIp($base, $range) || IpUtils::checkIp($edgeBase, $cidr)) {
                return true;
            }
        }

        return false;
    }
}
