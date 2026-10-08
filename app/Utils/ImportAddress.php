<?php

namespace App\Utils;

/**
 * What can be said about an address somebody gives us to read events from, before and after
 * it is fetched: how it is spelled, what it is really the address of, and what came back.
 *
 * Pure: no fetch happens here. Shared by the link import, which reads an address once, and by
 * feeds, which keep reading it.
 */
class ImportAddress
{
    /**
     * Sites that answer a server with a sign-in wall. Reading them finds a login form, so they
     * are turned away before any fetch, with what does work instead.
     */
    private const SIGN_IN_WALLS = [
        'facebook.com' => 'Facebook',
        'fb.com' => 'Facebook',
        'fb.me' => 'Facebook',
        'instagram.com' => 'Instagram',
    ];

    /** The address as it will be fetched, or null when it is not an address this app reads. */
    public static function normalise(string $link): ?string
    {
        $url = trim($link);
        // What a calendar app calls a subscription address is the same feed over https.
        $url = preg_replace('#^webcals?://#i', 'https://', $url);

        if (! preg_match('#^https?://[^\s/]+\.[^\s/]+#i', $url) || strlen($url) > 2048) {
            return null;
        }

        // A name with letters outside ASCII (münchen.example) is fetched by its ASCII form. The
        // fetch guard refuses the other: the name it vets has to be the name that is looked
        // up, letter for letter. Only the name is converted (not a user name before it or a
        // port after it), and by the rules browsers use now: under the older, "transitional"
        // ones straße.de is strasse.de, which is somebody else's address.
        if (preg_match('#^(https?://(?:[^/?\#\s@]*@)?)([^/?\#\s@:\[\]]+)((?::\d*)?(?:[/?\#].*)?)$#is', $url, $parts)
            && preg_match('/[^\x00-\x7F]/', $parts[2])) {
            $ascii = function_exists('idn_to_ascii')
                ? idn_to_ascii($parts[2], IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46)
                : false;
            // A conversion that brings in an "@" or a slash (their full-width forms map to
            // them) has made another address of it, not another spelling of this one.
            if (! is_string($ascii) || ! preg_match('/^[a-z0-9._\-]+$/i', $ascii)) {
                return null;
            }
            $url = $parts[1].$ascii.$parts[3];
        }

        return $url;
    }

    /** The name of the platform when the address is behind a sign-in wall, else null. */
    public static function signInWall(string $url): ?string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        foreach (self::SIGN_IN_WALLS as $domain => $platform) {
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return $platform;
            }
        }

        return null;
    }

    /**
     * Links people have that are not themselves readable, but point at a feed that is.
     *
     * @return array{0: string, 1: ?string} the address to fetch, and what it was rewritten from
     */
    public static function knownFeedFor(string $url): array
    {
        $parts = parse_url($url) ?: [];
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        // A Google Calendar share or embed link is a script page with nothing in it. The
        // calendar it names has a public feed at a fixed address.
        if ($host === 'calendar.google.com' && ! str_contains($path, '/ical/')) {
            $calendarId = null;

            if (! empty($query['src']) && is_string($query['src'])) {
                $calendarId = $query['src'];
            } elseif (! empty($query['cid']) && is_string($query['cid'])) {
                // "cid" is the calendar id, either as written or base64-encoded.
                $decoded = base64_decode(strtr($query['cid'], '-_', '+/'), true);
                $calendarId = $decoded !== false && str_contains($decoded, '@') ? $decoded : $query['cid'];
            }

            if ($calendarId && preg_match('/^[^\s\/]+@[^\s\/]+$/', $calendarId)) {
                return ['https://calendar.google.com/calendar/ical/'.rawurlencode($calendarId).'/public/basic.ics', 'google'];
            }
        }

        // A published Outlook calendar has a page and a feed side by side.
        if (preg_match('/(^|\.)outlook\.(live|office365|office)\.com$/', $host) && str_ends_with($path, '/calendar.html')) {
            return [preg_replace('/calendar\.html(\?.*)?$/', 'calendar.ics', $url), 'outlook'];
        }

        return [$url, null];
    }

    /** A calendar by what it is, not by what the address ends in. */
    public static function isCalendar(string $contentType, string $body): bool
    {
        return str_contains(strtolower($contentType), 'text/calendar')
            || str_starts_with(ltrim($body, "\xEF\xBB\xBF \t\r\n"), 'BEGIN:VCALENDAR');
    }

    public static function isHtml(string $contentType, string $body): bool
    {
        return str_contains(strtolower($contentType), 'html')
            || (bool) preg_match('/<(!doctype html|html)\b/i', substr($body, 0, 2048));
    }
}
