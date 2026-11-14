<?php

namespace App\Support;

/**
 * Whether this install is the nexus: eventschedule.com itself, the one install that serves the
 * marketing site, the docs, the blog, the federation directory and the rest of what
 * `config('app.is_nexus')` gates.
 *
 * IS_NEXUS alone used to decide it, so an operator running the app as their own SaaS could set
 * it and serve Event Schedule's homepage, pricing and comparison pages on their own domain, with
 * our name, our prices and our claims about our product. Those pages are not part of what the
 * licence lets a derived product promote itself with (its clause 3). So the flag is honoured only
 * where the install's own address (APP_URL) is eventschedule.com, or a development host no
 * public site can be reached on: localhost, a loopback address, or a name under the reserved
 * `.test` and `.localhost` domains (RFC 2606, RFC 6761). Anywhere else the install is not the
 * nexus whatever its environment says, and its marketing addresses send a visitor to the app
 * (the non-nexus branch of routes/web.php).
 *
 * An operator could edit this file, but the self-updater replaces it on every update, and doing
 * so is a deliberate step around the licence rather than a setting.
 */
class Nexus
{
    public const DOMAIN = 'eventschedule.com';

    /** @param  mixed  $flag  IS_NEXUS as env() returns it */
    public static function claimed(mixed $flag, ?string $appUrl): bool
    {
        if (! filter_var($flag, FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        return self::hostAllowed($appUrl);
    }

    public static function hostAllowed(?string $appUrl): bool
    {
        $host = strtolower(rtrim((string) parse_url(trim((string) $appUrl), PHP_URL_HOST), '.'));

        if ($host === '') {
            return false;
        }

        if ($host === self::DOMAIN || str_ends_with($host, '.'.self::DOMAIN)) {
            return true;
        }

        if (in_array($host, ['localhost', '127.0.0.1', '[::1]'], true)) {
            return true;
        }

        return str_ends_with($host, '.test') || str_ends_with($host, '.localhost');
    }
}
