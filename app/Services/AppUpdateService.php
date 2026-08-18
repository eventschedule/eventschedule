<?php

namespace App\Services;

use Codedge\Updater\UpdaterManager;
use Illuminate\Support\Facades\Artisan;

/**
 * Single owner of everything the selfhost self-updater needs to know: which version is
 * installed, which one GitHub has, whether that means an update is available, and how to
 * apply one.
 *
 * It exists because those four questions used to be answered in four places
 * (ProfileController, AppController, the app:update command and a Blade partial), each with
 * its own cache TTL and its own idea of what a failed lookup means.
 */
class AppUpdateService
{
    /**
     * Where the latest released version is cached. Deliberately the same key the older code
     * used, so an install that upgrades mid-TTL keeps its warm value.
     */
    public const CACHE_KEY = 'version_available';

    /**
     * When the cached value was last refreshed from GitHub.
     */
    public const CHECKED_AT_KEY = 'version_available_checked_at';

    /**
     * Longer than the daily app:check-version cadence on purpose. At the old one hour the
     * admin badge would have been absent for twenty-three hours out of every twenty-four,
     * because nothing but a page view ever warmed the key.
     */
    public const CACHE_SECONDS = 90000; // 25 hours

    /**
     * How long a failed lookup is remembered. Unauthenticated GitHub allows 60 calls an
     * hour, so once it starts rejecting, not caching the failure means every render pays a
     * blocking request that is guaranteed to fail. Same reasoning as GitHubUtils::getStars().
     */
    public const FAILURE_CACHE_SECONDS = 300;

    /**
     * The version this install is running, per the config file the release zip ships.
     */
    public function versionInstalled(): string
    {
        return (string) config('self-update.version_installed');
    }

    /**
     * The latest released version, read from cache only. Never makes an outbound request, so
     * it is safe on paths that run for every page render (the admin nav badge composer).
     *
     * Returns null for "unknown", which covers both a cache miss and a cached failure.
     */
    public function cachedVersionAvailable(): ?string
    {
        $cached = cache()->get(self::CACHE_KEY);

        // false is the failure marker written below. Legacy installs may also hold the
        // 'Error: failed to check version' string the old ProfileController wrote straight
        // into the view, which must never be treated as a version number.
        if (! is_string($cached) || $cached === '' || str_starts_with($cached, 'Error')) {
            return null;
        }

        return $cached;
    }

    /**
     * The latest released version, hitting GitHub when the cache is cold or $refresh is set.
     * Returns null when the lookup fails.
     */
    public function versionAvailable(UpdaterManager $updater, bool $refresh = false): ?string
    {
        if (! $refresh) {
            if ($cached = $this->cachedVersionAvailable()) {
                return $cached;
            }

            // A cached failure is honoured for its full window, so a rate-limited GitHub is
            // asked once per FAILURE_CACHE_SECONDS rather than once per page load.
            if (cache()->get(self::CACHE_KEY) === false) {
                return null;
            }
        }

        try {
            $version = (string) $updater->source()->getVersionAvailable();
        } catch (\Exception $e) {
            report($e);
            cache()->put(self::CACHE_KEY, false, self::FAILURE_CACHE_SECONDS);

            return null;
        }

        if ($version === '') {
            cache()->put(self::CACHE_KEY, false, self::FAILURE_CACHE_SECONDS);

            return null;
        }

        cache()->put(self::CACHE_KEY, $version, self::CACHE_SECONDS);
        cache()->put(self::CHECKED_AT_KEY, now()->timestamp, self::CACHE_SECONDS);

        return $version;
    }

    /**
     * When the cached version was last refreshed, or null if it never has been.
     */
    public function lastCheckedAt(): ?\Illuminate\Support\Carbon
    {
        $timestamp = cache()->get(self::CHECKED_AT_KEY);

        return is_int($timestamp) ? \Illuminate\Support\Carbon::createFromTimestamp($timestamp) : null;
    }

    /**
     * Whether a newer release exists, from cache alone.
     *
     * An unknown available version is NOT an update. The old code compared the two strings
     * with !=, so a failed lookup (which wrote the literal 'Error: failed to check version'
     * into the value) rendered an Update button during any GitHub outage.
     *
     * version_compare rather than !=, so an install running ahead of the latest release - a
     * developer on main - is not told to downgrade.
     */
    public function isUpdateAvailable(): bool
    {
        $available = $this->cachedVersionAvailable();

        if ($available === null) {
            return false;
        }

        return version_compare(
            $this->normalize($this->versionInstalled()),
            $this->normalize($available),
            '<'
        );
    }

    /**
     * Download and apply the latest release, run migrations, then clear the caches the
     * updater cannot reach.
     *
     * Returns ['status' => 'updated'|'up_to_date'|'error', 'message' => string].
     */
    public function performUpdate(UpdaterManager $updater): array
    {
        try {
            if (! $updater->source()->isNewVersionAvailable()) {
                return ['status' => 'up_to_date', 'message' => __('messages.no_new_version_available')];
            }

            $versionAvailable = $updater->source()->getVersionAvailable();

            $release = $updater->source()->fetch($versionAvailable);

            $updater->source()->update($release);

            Artisan::call('migrate', ['--force' => true]);

            $this->clearStaleCaches();

            // The installed version this process is holding is the pre-update one, so the
            // comparison would keep reporting an update until the next request re-reads the
            // config the release just replaced. Drop the key instead of leaving it to lie.
            cache()->forget(self::CACHE_KEY);
            cache()->forget(self::CHECKED_AT_KEY);

            return ['status' => 'updated', 'message' => __('messages.app_updated').' '.$versionAvailable];
        } catch (\Exception $e) {
            report($e);

            // 'message' is user-facing and stays generic per the repo's rule on never
            // surfacing raw exception text; 'detail' is for the console only.
            return ['status' => 'error', 'message' => __('messages.error'), 'detail' => $e->getMessage()];
        }
    }

    /**
     * bootstrap/cache is in config/self-update.php's exclude_folders, so a release never
     * replaces it. On an install that followed docs/SECURITY_CONFIG.md and ran
     * `php artisan optimize`, that leaves config.php holding the pre-update
     * self-update.version_installed - the app then reports the old version forever and
     * re-offers the same update on every visit - and routes-v7.php freezing the route table.
     *
     * Deliberately not cache:clear, and not optimize:clear (which contains cache:clear). The
     * application cache holds Cache::lock mutexes that serialise Stripe installment charges,
     * 30-day sms_signup tokens, in-flight phone and email verification codes, and every
     * brute-force counter. Wiping those to refresh one version string would trade a cosmetic
     * bug for a real one, and would hand a throttled attacker a fresh budget.
     *
     * Note this runs in the OLD code's process, so it repairs the update that just landed
     * for every subsequent request, and it is the next update that benefits from this method
     * existing at all.
     */
    protected function clearStaleCaches(): void
    {
        // config:clear  - the bug this exists for: bootstrap/cache/config.php keeps the
        //                 pre-update self-update.version_installed, so the app reports the old
        //                 version forever and re-offers the same update on every visit.
        // route:clear    - routes-v7.php freezes the route table, and releases add routes.
        // event:clear    - `php artisan optimize` writes bootstrap/cache/events.php too.
        // view:clear     - copied Blade files get a fresh mtime so Blade normally recompiles on
        //                  its own; this is the backstop for an archive that kept timestamps.
        // clear-compiled - services.php and packages.php. vendor/ IS replaced by a release, but
        //                  bootstrap/cache is not, so a release that adds a Composer package
        //                  ships the code and never registers its service provider.
        foreach (['config:clear', 'route:clear', 'event:clear', 'view:clear', 'clear-compiled'] as $command) {
            try {
                Artisan::call($command);
            } catch (\Exception $e) {
                // A cache that will not clear is not a reason to report a successful update
                // as a failure. The operator can run the command by hand.
                report($e);
            }
        }
    }

    /**
     * Tags are published as v1.0.124 but nothing guarantees the two sides agree on the
     * prefix, and version_compare treats a leading v as a separate part.
     */
    protected function normalize(string $version): string
    {
        return ltrim(trim($version), 'vV');
    }
}
