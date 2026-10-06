<?php

namespace App\Utils;

use App\Services\RealtimeDashboard;
use Illuminate\Support\Str;

/**
 * Where an account came from, from the first touch its sign-up stored (users.utm_*, referrer_url,
 * landing_page, referred_by_user_id).
 *
 * One classifier for every page that says so - the admin dashboard's breakdown and its list of
 * latest sign-ups, and /admin/realtime's sign-ups - so a person cannot be "Direct" in one place and
 * something else in another. The channels and their names are RealtimeTracker's and
 * RealtimeDashboard's, the ones a live visit gets, plus two that only a stored account has.
 *
 * In order:
 *  1. referral  - a referral link was used. The only source backed by a row of ours.
 *  2. the campaign tags, read as RealtimeTracker::utmChannel() reads them.
 *  3. the referring site, unless it is one a visitor only passes through: a sign-in provider
 *     handing them back (accounts.google.com is what a Google sign-up arrives from), or one of our
 *     own hosts.
 *  4. direct    - nothing but a real landing page.
 *  5. unrecorded - nothing at all, or only the sign-up or sign-in page.
 *
 * "Unrecorded" is not "Direct", and the difference is most of the point. A first visit to an
 * edge-cached marketing page is stored only with marketing consent (see CaptureUtmParameters and
 * docs/GROWTH_DATA.md), so a visitor who declined arrives at /sign_up with no tags and no referrer.
 * Calling that Direct would be a guess that grows with every declined banner.
 */
class SignupSource
{
    /** In display order, most specific first; the two with no source last. */
    public const CHANNELS = ['search', 'ai', 'social', 'referral', 'email', 'paid', 'campaign', 'other', 'direct', 'unrecorded'];

    /** Pages every account passes through. Landing on one says nothing about where it came from. */
    private const AUTH_PATHS = ['/sign_up', '/login', '/register'];

    /**
     * @return array{channel: string, name: ?string, campaign: ?string, landing: ?string}
     */
    public static function classify(?string $utmSource, ?string $utmMedium, ?string $utmCampaign, ?string $referrerUrl, ?string $landingPage, bool $referred): array
    {
        $landing = self::landingPath($landingPage);
        $host = self::referrerHost($referrerUrl);

        if ($referred) {
            return ['channel' => 'referral', 'name' => null, 'campaign' => null, 'landing' => $landing];
        }

        if ($tagged = RealtimeTracker::utmChannel($utmSource, $utmMedium, $utmCampaign, $host)) {
            return $tagged + ['landing' => $landing];
        }

        if ($host !== null && ! RealtimeTracker::isPassThroughHost($host)) {
            return ['channel' => RealtimeTracker::hostChannel($host) ?? 'other', 'name' => $host, 'campaign' => null, 'landing' => $landing];
        }

        return ['channel' => $landing !== null ? 'direct' : 'unrecorded', 'name' => null, 'campaign' => null, 'landing' => $landing];
    }

    /**
     * classify(), for a User or a plain row carrying the same columns.
     *
     * @return array{channel: string, name: ?string, campaign: ?string, landing: ?string}
     */
    public static function forUser(object $user): array
    {
        return self::classify(
            $user->utm_source ?? null,
            $user->utm_medium ?? null,
            $user->utm_campaign ?? null,
            $user->referrer_url ?? null,
            $user->landing_page ?? null,
            ! empty($user->referred_by_user_id),
        );
    }

    /**
     * The two lines a list shows for one account, or null when nothing was recorded. $referrerName
     * is the person whose referral link was used, when the caller has it.
     *
     * @return ?array{primary: string, secondary: ?string, channel: string}
     */
    public static function display(object $user, ?string $referrerName = null): ?array
    {
        $source = self::forUser($user);

        if ($source['channel'] === 'unrecorded') {
            return null;
        }

        $tagged = filled($user->utm_source ?? null) && filled($user->utm_medium ?? null);

        $primary = match (true) {
            $source['channel'] === 'referral' => filled($referrerName)
                ? __('messages.realtime_referred_by', ['name' => $referrerName])
                : self::label('referral'),
            filled($source['name']) => $source['name'].($tagged ? ' / '.$user->utm_medium : ''),
            default => self::label($source['channel']),
        };

        return [
            'primary' => Str::limit($primary, 60),
            // The home page is where a visit with no other story starts; it is not worth a line.
            'secondary' => $source['landing'] !== null && $source['landing'] !== '/' ? Str::limit($source['landing'], 60) : null,
            'channel' => $source['channel'],
        ];
    }

    public static function label(string $channel): string
    {
        return match ($channel) {
            'referral' => __('messages.referral_program'),
            'unrecorded' => __('messages.realtime_source_unknown'),
            default => RealtimeDashboard::channelLabel($channel),
        };
    }

    /**
     * The stored landing page as a path, or null when there is none worth showing. Both capture
     * paths store a bare path with no leading slash; older rows hold a full URL, and either may
     * carry a query string.
     */
    public static function landingPath(?string $landingPage): ?string
    {
        $value = trim((string) $landingPage);

        if ($value === '') {
            return null;
        }

        $path = (string) preg_replace('#^https?://[^/]+#i', '', $value);
        $path = (string) preg_split('/[?#]/', $path, 2)[0];
        $path = '/'.trim($path, '/');

        if (in_array(strtolower($path), self::AUTH_PATHS, true)) {
            return null;
        }

        return mb_substr($path, 0, 120);
    }

    private static function referrerHost(?string $referrerUrl): ?string
    {
        $value = trim((string) $referrerUrl);

        if ($value === '') {
            return null;
        }

        // A value with no scheme parses as a path, so a bare host would be lost.
        $host = parse_url(preg_match('#^[a-z][a-z0-9+.-]*://#i', $value) ? $value : 'https://'.$value, PHP_URL_HOST);

        return RealtimeTracker::normalizeHost(is_string($host) ? $host : null);
    }
}
