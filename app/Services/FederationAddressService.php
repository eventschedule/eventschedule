<?php

namespace App\Services;

use App\Jobs\SendQueuedEmail;
use App\Mail\FederationAddressConfirmed;
use App\Models\FederatedInstance;
use App\Utils\UrlUtils;
use Illuminate\Support\Facades\Cache;

/**
 * What the nexus does when an install reports an address other than the one on record.
 *
 * site_url is the authority every backlink check runs against (FederatedInstance::ownsUrl()),
 * so a new HOST is never taken on the install's word alone: a database restored onto another
 * server signs exactly like the original. But most moves are not that, and every hour one sits
 * in review, the install's new and changed events are refused. So:
 *
 *  - the same address with only its scheme or a leading "www." changed is adopted at once,
 *    because the host check cannot tell the two apart anyway (a path or port change is a claim,
 *    see sameAddress());
 *  - another host is recorded as a claim (reported_site_url + flagged_at) and the install stays
 *    where it is, approved or not. It is adopted when the old address redirects to the new one
 *    (checked hourly by federation:maintain, verifyByRedirect()), or by an admin;
 *  - a registration nobody approved yet follows its install, as it always has: the reviewer
 *    approves the address they see.
 *
 * Every response to an install says which address the network has (FederationService reads it),
 * and an install whose address was adopted sends everything again with its new links.
 */
class FederationAddressService
{
    /**
     * Claims checked for a redirect in one maintenance run. Small on purpose: on the HTTP cron
     * rail this runs inside one request, so the worst case (every hop timing out) is
     * limit x hops x timeout, 150 seconds. A claim is rare, and one left over waits an hour.
     */
    public const REDIRECT_CHECK_LIMIT = 10;

    /** Redirect hops followed on the OLD site before it is judged not to point anywhere new. */
    public const REDIRECT_HOPS = 3;

    /** Seconds allowed for each hop. */
    public const REDIRECT_TIMEOUT = 5;

    /** Where the redirect check's last run stopped, so the claims are checked in turns. */
    public const REDIRECT_CURSOR = 'federation.redirect_check_after';

    public const HOW_ADMIN = 'admin';

    public const HOW_REDIRECT = 'redirect';

    /**
     * The form an address is compared and stored in. Trailing slashes trimmed, and bounded to the
     * column: a value that is stored must be validated like one, or an over-long site_url fails the
     * save and 500s a signed request that is otherwise fine. Truncated rather than dropped, so the
     * stored value equals the compared one and the same junk stays "already seen".
     */
    public static function normalize(string $url): string
    {
        $url = rtrim($url, '/');

        return mb_strlen($url) > 255 ? mb_substr($url, 0, 255) : $url;
    }

    /** The host with any leading "www." removed, lowercased, or null. */
    public static function siteHost(?string $urlOrHost): ?string
    {
        $value = (string) $urlOrHost;
        $host = str_contains($value, '://') ? parse_url($value, PHP_URL_HOST) : $value;

        if (! is_string($host) || $host === '') {
            return null;
        }

        $host = strtolower($host);
        $host = str_starts_with($host, 'www.') ? substr($host, 4) : $host;

        return $host !== '' ? $host : null;
    }

    /**
     * The same install moved only its scheme or its "www.": same site, same port, same path. The
     * only change adopted without anybody looking. A path is deliberately NOT included: the record
     * is the address the redirect check fetches, and a path an install can choose (an event of its
     * own whose registration link goes elsewhere, any redirect on a shared host) would let a later
     * claim on another host be "confirmed" by a redirect the claimant wrote.
     */
    public static function sameAddress(?string $a, ?string $b): bool
    {
        if (! self::sameSite($a, $b)) {
            return false;
        }

        $pa = parse_url((string) $a) ?: [];
        $pb = parse_url((string) $b) ?: [];

        return ($pa['port'] ?? null) === ($pb['port'] ?? null)
            && rtrim($pa['path'] ?? '', '/') === rtrim($pb['path'] ?? '', '/');
    }

    /**
     * Two addresses on one site, as far as the backlink check is concerned: the same host, or one
     * is the other with "www." in front.
     */
    public static function sameSite(?string $a, ?string $b): bool
    {
        $a = self::siteHost($a);

        return $a !== null && $a === self::siteHost($b);
    }

    /**
     * Weigh the address an install reports against the record. Called from register() and from
     * every signed push, on the model the caller is about to save.
     *
     * @param  bool  $registering  register() also clears a claim that the install has stopped
     *                             making, and moves a registration nobody approved yet.
     */
    public function note(FederatedInstance $instance, string $reported, bool $registering = false): void
    {
        $reported = self::normalize($reported);
        $onRecord = rtrim((string) $instance->site_url, '/');

        if ($reported === $onRecord) {
            if ($instance->flagged_at !== null && $instance->isApproved()) {
                // The install reports the address on record again: the mismatch is over, so the
                // flag goes with it. isApproved() is the whole guard: a pending or suspended
                // install keeps its flag for a human, or a remote could push its own way out of
                // the review queue.
                $instance->reported_site_url = null;
                $instance->flagged_at = null;
            } elseif ($registering) {
                // A claim against an address the install no longer reports is stale. Left set,
                // the admin screen would offer to adopt an address nobody is claiming.
                $instance->reported_site_url = null;
            }

            return;
        }

        // A registration nobody has approved yet carries no standing to protect, so its record
        // follows the install. The flag tells the reviewer it moved host.
        if ($registering && $instance->status === FederatedInstance::STATUS_PENDING) {
            if (! self::sameSite($onRecord, $reported)) {
                $instance->flagged_at = now();
            }

            $instance->site_url = $reported;
            $instance->reported_site_url = null;

            return;
        }

        // Only the scheme or the "www." changed (sameAddress()): ownsUrl() checks hosts, so this
        // cannot let an install link anywhere it could not before. Not for a suspended install,
        // whose record is a human's to change.
        if ($instance->status !== FederatedInstance::STATUS_SUSPENDED
            && self::sameAddress($onRecord, $reported)
            && FederatedInstance::isAdoptableUrl($reported)) {
            $instance->site_url = $reported;
            $instance->reported_site_url = null;

            if ($instance->isApproved()) {
                $instance->flagged_at = null;
            }

            AuditService::log(
                AuditService::ADMIN_FEDERATION_ACCEPT_ADDRESS,
                null,
                'FederatedInstance',
                $instance->id,
                ['site_url' => $onRecord],
                ['site_url' => $reported],
                'Federated instance moved within its own site, adopted automatically',
            );

            return;
        }

        // Another host. Kept so the admin screen can show which address is claimed and offer to
        // adopt it, and stamped only for an address not already seen, so flagged_at reads as when
        // the mismatch STARTED and an admin who looked at this exact address is not re-alerted
        // every hour. The install keeps its status: an approved one stays listed at the address on
        // record while the claim is looked at.
        $isNewAddress = $instance->flagged_at === null
            || rtrim((string) $instance->reported_site_url, '/') !== $reported;

        $instance->reported_site_url = $reported;

        if ($isNewAddress) {
            $instance->flagged_at = now();
        }
    }

    /**
     * Make the claimed address the one on record.
     *
     * A conditional UPDATE matching the exact values read, not a read-then-save: installs push
     * hourly, and one landing in between would otherwise have its new claim overwritten or its
     * flag cleared with nothing on screen to say so.
     */
    public function adopt(FederatedInstance $instance, ?int $userId, string $how = self::HOW_ADMIN): bool
    {
        if (! $instance->flagged_at || ! $instance->hasAdoptableAddress()) {
            return false;
        }

        $previous = $instance->site_url;
        $reported = $instance->reported_site_url;

        $claimed = FederatedInstance::whereKey($instance->id)
            ->whereNotNull('flagged_at')
            ->where('reported_site_url', $reported)
            ->where('site_url', $previous)
            // The redirect check takes seconds per install, and an admin may suspend one meanwhile:
            // a check never adopts for an install that is no longer approved.
            ->when($how === self::HOW_REDIRECT, fn ($q) => $q->where('status', FederatedInstance::STATUS_APPROVED))
            ->update([
                'site_url' => $reported,
                'reported_site_url' => null,
                'flagged_at' => null,
            ]);

        if ($claimed !== 1) {
            return false;
        }

        $instance->forceFill([
            'site_url' => $reported,
            'reported_site_url' => null,
            'flagged_at' => null,
        ])->syncOriginal();

        AuditService::log(
            AuditService::ADMIN_FEDERATION_ACCEPT_ADDRESS,
            $userId,
            'FederatedInstance',
            $instance->id,
            ['site_url' => $previous],
            ['site_url' => $reported],
            $how === self::HOW_REDIRECT
                ? 'Accepted federated instance address change: the old address redirects to the new one'
                : 'Accepted federated instance address change',
        );

        $this->tellOperator($instance);

        return true;
    }

    /**
     * Adopt the claims whose old address now redirects to the new one.
     *
     * The push is signed, so the claim comes from something holding this install's secret; the
     * redirect says whoever runs the OLD address points it at the new one. A copy of the database
     * restored somewhere else has the first and not the second - the original still answers at
     * its own address - so it stays in review. Oldest claims first, a bounded number per run.
     */
    public function verifyByRedirect(int $limit = self::REDIRECT_CHECK_LIMIT): int
    {
        // In turns, by id, from where the last run stopped: ordered by age, ten claims whose old
        // address is dead or still answers would be the head of the queue for good, and no newer
        // move would ever be checked.
        $after = (int) Cache::get(self::REDIRECT_CURSOR, 0);
        $claims = fn () => FederatedInstance::approved()
            ->whereNotNull('flagged_at')
            ->whereNotNull('reported_site_url')
            ->orderBy('id');

        $instances = $claims()->where('id', '>', $after)->limit($limit)->get();

        if ($instances->count() < $limit && $after > 0) {
            $instances = $instances->concat(
                $claims()->where('id', '<=', $after)->limit($limit - $instances->count())->get()
            );
        }

        if ($instances->isNotEmpty()) {
            Cache::put(self::REDIRECT_CURSOR, $instances->last()->id, now()->addDays(7));
        }

        $adopted = 0;

        foreach ($instances as $instance) {
            if (! $instance->hasAdoptableAddress()) {
                continue;
            }

            try {
                $target = $this->redirectedHost((string) $instance->site_url);
            } catch (\Throwable $e) {
                // Unreachable or slow: not a no, just not a yes. Asked again next run.
                continue;
            }

            if ($target !== null && self::sameSite($target, $instance->reported_site_url)
                && $this->adopt($instance, null, self::HOW_REDIRECT)) {
                $adopted++;
            }
        }

        return $adopted;
    }

    /**
     * The first host off the old site that the old address redirects to, or null when it answers
     * itself, redirects nowhere new, or cannot be fetched safely. Each hop goes through the SSRF
     * guard, and the new site is never fetched: its address is all this needs.
     */
    protected function redirectedHost(string $from): ?string
    {
        // The record's own scheme, host, port and path, never a query or fragment it may carry from
        // before addresses were held to that: what answers there is the install's own front page.
        $parts = parse_url($from) ?: [];
        if (empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        $url = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '').($parts['path'] ?? '');

        for ($hop = 0; $hop < self::REDIRECT_HOPS; $hop++) {
            $response = UrlUtils::safeHttpGet($url, [], self::REDIRECT_TIMEOUT, 0);

            if (! $response || ! $response->redirect()) {
                return null;
            }

            $location = trim((string) $response->header('Location'));
            if ($location === '') {
                return null;
            }

            $next = $this->absolute($location, $url);
            if ($next === null) {
                return null;
            }

            if (! self::sameSite($next, $from)) {
                return strtolower((string) parse_url($next, PHP_URL_HOST)) ?: null;
            }

            $url = $next;
        }

        return null;
    }

    /** A Location header made absolute against the address that sent it. */
    protected function absolute(string $location, string $base): ?string
    {
        if (preg_match('#^https?://#i', $location)) {
            return $location;
        }

        $parts = parse_url($base);
        if (empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        $origin = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');

        if (str_starts_with($location, '//')) {
            return $parts['scheme'].':'.$location;
        }

        if (str_starts_with($location, '/')) {
            return $origin.$location;
        }

        // A relative path off the root is all a site root can mean here.
        return $origin.'/'.$location;
    }

    /**
     * Tell the operator their new address is in, and that the network will take everything again.
     * Only for a listed install with somewhere to write: this is an admin decision or a verified
     * redirect, never an unauthenticated request, so it is not the open relay registration guards.
     */
    protected function tellOperator(FederatedInstance $instance): void
    {
        if (! $instance->contact_email || ! $instance->isApproved()) {
            return;
        }

        try {
            SendQueuedEmail::dispatch(
                new FederationAddressConfirmed($instance),
                $instance->contact_email,
                null,
                $instance->mailLocale()
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
