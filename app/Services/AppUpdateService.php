<?php

namespace App\Services;

use Codedge\Updater\Models\Release;
use Codedge\Updater\UpdaterManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

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
     * The PHP version each process that runs this install was last seen on, beyond the one
     * applying an update: the cron scheduler (usually the command-line PHP), the HTTP cron and
     * the website (the web server's). A server's two PHPs are often not the same version, and a
     * release that needs a newer one than either stops that side on boot.
     */
    public const PHP_SEEN_KEY = 'app_update.php_seen.';

    /** The installed state finishUpdate() last saw through, and one it failed on (see there). */
    public const FINISHED_KEY = 'app_update.finished_version';

    public const FAILED_KEY = 'app_update.finish_failed';

    /** Held by whatever copies a release in or finishes one, so two never migrate at once. */
    public const LOCK_KEY = 'app_update.finished_version.lock';

    /** Set when an in-place update could not clear its caches: the next finish clears them. */
    public const CLEAR_KEY = 'app_update.caches_stale';

    /**
     * Seconds each runner's PHP is kept: the two crons tick every minute, so two days, and a rail
     * that was retired cannot hold an update back for good; the website is seen when somebody opens
     * the update pages or updates from the browser, so a month.
     */
    public const PHP_RUNNERS = ['scheduler' => 172800, 'http_cron' => 172800, 'web' => 2592000];

    /** How long an update waits for a finish in progress (finishUpdate()) before giving up. */
    protected int $lockWaitSeconds = 120;

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
     * Returns ['status' => 'updated'|'up_to_date'|'error', 'message' => string], and on an
     * error a 'detail' for the console. Only the console asks for the file a refused update
     * stopped at: finding it walks the app again, in a request the walk that refused it has
     * already made long, and the web pages never show it.
     */
    public function performUpdate(UpdaterManager $updater, bool $forConsole = false): array
    {
        try {
            if (! $updater->source()->isNewVersionAvailable()) {
                return ['status' => 'up_to_date', 'message' => __('messages.no_new_version_available')];
            }

            $versionAvailable = $updater->source()->getVersionAvailable();

            if (! app()->runningInConsole()) {
                self::recordPhp('web');
            }

            $release = $updater->source()->fetch($versionAvailable);

            // fetch() has extracted the release; nothing of the install is replaced yet. A
            // release built for a newer PHP than this server runs would be copied in and then
            // stop the app on its next boot (Composer's platform check), so it is refused here.
            $required = $this->phpRequiredBy(createFolderFromFile((string) $release->getStoragePath()));
            $runtimes = $this->phpRuntimes();
            $oldest = $this->oldestOf($runtimes);

            if ($required !== null && version_compare($oldest, $required, '<')) {
                try {
                    $this->forgetFetchedRelease($release);
                } catch (\Exception $e) {
                    report($e);
                }

                $seen = collect($runtimes)->map(fn ($version, $where) => str_replace('_', ' ', $where).' '.$version)->implode(', ');

                return [
                    'status' => 'error',
                    'message' => __('messages.app_update_needs_newer_php', ['version' => $versionAvailable, 'required' => $required, 'current' => $oldest]),
                    'detail' => $versionAvailable.' needs PHP '.$required.' or newer, and this install runs on PHP '.$seen
                        .'. Nothing was changed. Upgrade PHP for both the web server and the cron job, then run the update again.'
                        .(isset($runtimes['web_assumed'])
                            ? ' The web server\'s PHP has not been seen in the last month, so it is taken to be the oldest this version allows: open Settings > App Update in a browser first if it is newer.'
                            : ''),
                ];
            }

            // From the first file copied to the last migration, a scheduler tick must not finish
            // this update at the same time (finishUpdate() takes the same lock): it would run the
            // migrations beside these, or see the new version beside half its migration files.
            $lock = Cache::lock(self::LOCK_KEY, 1200);

            try {
                $lock->block($this->lockWaitSeconds);
            } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
                try {
                    $this->forgetFetchedRelease($release);
                } catch (\Exception $e) {
                    report($e);
                }

                return ['status' => 'error', 'message' => __('messages.error'), 'detail' => 'An earlier update is still being finished. Nothing was changed; try again in a few minutes.'];
            }

            // The updater copies nothing unless every file of the app is writable, and says so
            // only by returning false. Unchecked, an install that gave the web user just
            // storage, bootstrap and public was told it had updated, and was offered the same
            // update again on its next visit. The cached version is kept: it is still waiting.
            if (! $updater->source()->update($release)) {
                // A folder that will not go must not turn the message below into a generic one.
                try {
                    $this->forgetFetchedRelease($release);
                } catch (\Exception $e) {
                    report($e);
                }

                // The path goes to the console only: on a plain selfhost install with no admin
                // account any signed-in user can open Settings > App Update (can_self_update()).
                $blocked = $forConsole ? $this->firstUnwritableFile(base_path()) : null;

                return [
                    'status' => 'error',
                    'message' => __('messages.app_update_files_not_writable'),
                    'detail' => 'The updater needs write access to every file under '.base_path()
                        .($blocked ? ', and cannot write to '.$blocked : '')
                        .'. Run this command as root, then run the chown from the installation guide\'s file permissions step again.',
                ];
            }

            // The files are in, but this process still holds the classes it had loaded from the old
            // vendor/, and the new ones it loads from here on expect their own neighbours. Moving
            // from Laravel 11 to 12 that broke the first migration (12's Blueprint asked 11's
            // MySqlBuilder for a property it never had), after every file had been replaced. So a
            // failure here is caught, not reported (reporting loads new classes too, and Sentry
            // failed the same way), and finishUpdate() completes it from the next process: the
            // scheduler's next tick, or the App Update page.
            $unfinished = null;

            try {
                Artisan::call('migrate', ['--force' => true]);

                $this->clearStaleCaches();

                // Done here, so the next tick does not clear the caches again (and with them an
                // `optimize` the admin runs straight after the update).
                Cache::forever(self::FINISHED_KEY, self::marker($versionAvailable));
            } catch (\Throwable $e) {
                // Left to finishUpdate(), which runs in a process that loaded one framework. Told
                // to PHP's own log, which loads nothing, and to whoever pressed Update.
                $unfinished = $e;
                @error_log('Event Schedule '.$versionAvailable.': the update was installed but did not finish ('.get_class($e).': '.$e->getMessage().'). The scheduler finishes it.');

                // The marker this process may hold is the old version's, which a stale cached config
                // would read again in the next process: forget it, and say the caches are dirty.
                // The cache store is safe to use here (its classes are loaded; see CACHE_KEY below).
                try {
                    Cache::forget(self::FINISHED_KEY);
                    Cache::forever(self::CLEAR_KEY, 1);
                } catch (\Throwable $e) {
                }
            }

            // The installed version this process is holding is the pre-update one, so the
            // comparison would keep reporting an update until the next request re-reads the
            // config the release just replaced. Drop the key instead of leaving it to lie.
            cache()->forget(self::CACHE_KEY);
            cache()->forget(self::CHECKED_AT_KEY);

            if ($unfinished !== null) {
                return [
                    'status' => 'updated',
                    'message' => __('messages.app_updated_unfinished', ['version' => $versionAvailable]),
                    'detail' => 'The files of '.$versionAvailable.' are in, but its migrations and cache clears failed here ('
                        .get_class($unfinished).': '.substr($unfinished->getMessage(), 0, 500).'). '
                        .(self::finishes() ? 'The next scheduler run finishes them, as does opening App Update; to finish now, run' : 'This install does not finish its own updates (a git checkout), so run')
                        .' php artisan migrate --force, then php artisan config:clear.',
                ];
            }

            return ['status' => 'updated', 'message' => __('messages.app_updated').' '.$versionAvailable];
        } catch (\Exception $e) {
            report($e);

            // 'message' is user-facing and stays generic per the repo's rule on never
            // surfacing raw exception text; 'detail' is for the console only.
            return ['status' => 'error', 'message' => __('messages.error'), 'detail' => $e->getMessage()];
        } finally {
            if (isset($lock)) {
                $lock->release();
            }
        }
    }

    /**
     * Recorded by each cron rail on every tick, and for the website by the update pages and an
     * update run from the browser (see PHP_SEEN_KEY).
     */
    public static function recordPhp(string $runner): void
    {
        // The nexus is deployed as a container and never updates itself. A selfhosted SaaS
        // (hosted, not the nexus) does, as can_self_update() and app:update say.
        if (config('app.is_nexus') || ! isset(self::PHP_RUNNERS[$runner])) {
            return;
        }

        // A stamp must never fail what it rides on (a scheduler tick, the HTTP cron, a page).
        try {
            Cache::put(self::PHP_SEEN_KEY.$runner, self::phpVersion(), self::PHP_RUNNERS[$runner]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Every PHP this install was seen running on: this process, and each runner seen within its
     * window.
     *
     * Run from the command line with the website not seen in that time, the website is taken to
     * run the oldest PHP the INSTALLED version allows (it is up, so it runs at least that): a
     * `php artisan app:update` on a newer command-line PHP would otherwise vouch for a web server
     * it has never met, and install a release that stops the site.
     *
     * @return array<string, string>
     */
    public function phpRuntimes(): array
    {
        $runtimes = ['this_process' => self::phpVersion()];

        foreach (array_keys(self::PHP_RUNNERS) as $runner) {
            $version = Cache::get(self::PHP_SEEN_KEY.$runner);

            if (is_string($version) && preg_match('/^\d+\.\d+\.\d+$/', $version)) {
                $runtimes[$runner] = $version;
            }
        }

        if (app()->runningInConsole() && ! isset($runtimes['web']) && ($installed = $this->phpRequiredBy(base_path())) !== null) {
            $runtimes['web_assumed'] = $installed;
        }

        return $runtimes;
    }

    /**
     * The oldest PHP the release extracted in $folder runs on, or null when it does not say.
     *
     * Read from vendor/composer/platform_check.php, which Composer writes from the root
     * composer.json and every package and which is what stops the app on boot under an older
     * PHP; from composer.json's own requirement when that file is missing.
     */
    public function phpRequiredBy(string $folder): ?string
    {
        if ($folder === '') {
            return null;
        }

        $check = $folder.'/vendor/composer/platform_check.php';

        if (is_file($check) && preg_match_all('/PHP_VERSION_ID\s*>=\s*(\d{5,6})\b/', (string) file_get_contents($check), $matches)) {
            $id = max(array_map('intval', $matches[1]));

            return intdiv($id, 10000).'.'.intdiv($id % 10000, 100).'.'.($id % 100);
        }

        $composer = $folder.'/composer.json';

        if (is_file($composer)) {
            $constraint = json_decode((string) file_get_contents($composer), true)['require']['php'] ?? null;

            return is_string($constraint) ? self::lowestPhpIn($constraint) : null;
        }

        return null;
    }

    /**
     * The lowest version a Composer constraint such as "^8.3", ">=8.2.0" or "^8.2 || ^8.3"
     * allows, or null when a branch is not one of those plain shapes.
     */
    public static function lowestPhpIn(string $constraint): ?string
    {
        $lowest = null;

        foreach (preg_split('/\s*\|\|?\s*/', trim($constraint)) as $branch) {
            if (! preg_match('/^(?:\^|~|>=|==?)?\s*v?(\d+)(?:\.(\d+|\*))?(?:\.(\d+|\*))?/', $branch, $m)) {
                return null;
            }

            $version = $m[1].'.'.(int) ($m[2] ?? 0).'.'.(int) ($m[3] ?? 0);

            if ($lowest === null || version_compare($version, $lowest, '<')) {
                $lowest = $version;
            }
        }

        return $lowest;
    }

    /** @param  array<string, string>  $runtimes */
    protected function oldestOf(array $runtimes): string
    {
        return collect($runtimes)->reduce(
            fn (?string $oldest, string $version) => $oldest === null || version_compare($version, $oldest, '<') ? $version : $oldest
        );
    }

    /** This PHP as major.minor.release: PHP_VERSION can carry a distribution's suffix. */
    protected static function phpVersion(): string
    {
        return PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION;
    }

    /**
     * Runs what an update left undone: the migrations and the cache clears that performUpdate()
     * runs in the same process right after copying the files, where the old framework's classes
     * are still loaded. An update from before that was caught (v1.0.139 and earlier) does not
     * survive it moving from Laravel 11 to 12: the files are replaced, the migrations never run,
     * and the admin sees a blank 500. This runs in a fresh process, which loaded one framework:
     * at the START of each scheduler tick (both rails), so the tick's own jobs meet the new schema,
     * and on the App Update pages, so an install whose cron does not run is finished when an admin
     * opens one ($fromPage: a failure is retried there once a minute, by the scheduler hourly).
     *
     * Once per installed state (marker(): the version AND the migration files on disk), so a tick
     * costs a directory listing and one cache read. The version alone was not enough: a tick that
     * started while an update was still copying folders saw the new version beside half its
     * migrations, and would have marked it done. The caches are cleared only when something says
     * they are stale (migrations pending, a cached config holding another version than the one on
     * disk, or an in-place update that left them: CLEAR_KEY), never merely because the marker is
     * missing: `cache:clear` empties it, and clearing then threw away an `optimize`.
     *
     * Never raises: whatever fails here stays out of the tick or the page that asked.
     */
    public static function finishUpdate(bool $fromPage = false): void
    {
        try {
            self::finish($fromPage);
        } catch (\Throwable $e) {
            @error_log('Event Schedule: finishing an update failed: '.get_class($e).': '.$e->getMessage());
        }
    }

    private static function finish(bool $fromPage): void
    {
        if (config('app.is_nexus') || config('app.is_testing') || ! self::finishes()) {
            return;
        }

        // The setup wizard migrates a fresh install itself, a slice at a time.
        if (selfhost_needs_setup()) {
            return;
        }

        $marker = self::marker((string) config('self-update.version_installed'));

        if ($marker === null || Cache::get(self::FINISHED_KEY) === $marker) {
            return;
        }

        // A finish that failed is not tried again on every tick: a migration that fails once fails
        // the same way a minute later, and each attempt is a report.
        if (Cache::get(self::FAILED_KEY) === $marker && (! $fromPage || ! Cache::add(self::FAILED_KEY.'.page', 1, 60))) {
            return;
        }

        // Held by performUpdate() too, from the copy to its own migrations, so the two never run
        // migrations at once. Longer than the work may take.
        $lock = Cache::lock(self::LOCK_KEY, 1200);

        if (! $lock->get()) {
            return;
        }

        try {
            // Finished by another process while this one was on its way here.
            if (Cache::get(self::FINISHED_KEY) === $marker) {
                return;
            }

            // A page request is given the time a migration may take; the command line has no limit,
            // and must not be given one for the rest of its tick.
            if (! app()->runningInConsole()) {
                @set_time_limit(900);
            }

            $migrator = app('migrator');

            if (! $migrator->repositoryExists()) {
                return;
            }

            $files = $migrator->getMigrationFiles(array_merge([database_path('migrations')], $migrator->paths()));
            $pending = array_diff(array_keys($files), $migrator->getRepository()->getRan());

            if ($pending !== [] || Cache::get(self::CLEAR_KEY) || self::configCacheIsStale()) {
                app(self::class)->clearStaleCaches();
            }

            if ($pending !== []) {
                Artisan::call('migrate', ['--force' => true]);
            }

            Cache::forever(self::FINISHED_KEY, $marker);
            Cache::forget(self::FAILED_KEY);
            Cache::forget(self::CLEAR_KEY);
        } catch (\Throwable $e) {
            Cache::put(self::FAILED_KEY, $marker, 3600);

            report($e);
        } finally {
            $lock->release();
        }
    }

    /**
     * A config cached before the files changed holds another version than the one the config file
     * on disk now says. That is what keeps an install reporting its old version and offering the
     * same update again.
     */
    private static function configCacheIsStale(): bool
    {
        if (! app()->configurationIsCached()) {
            return false;
        }

        $onDisk = (require config_path('self-update.php'))['version_installed'] ?? null;

        return is_string($onDisk) && $onDisk !== config('self-update.version_installed');
    }

    /**
     * Whether this install finishes its own updates. self-update.finish_updates when it is set to
     * a yes or a no; otherwise (unset, blank, or not a yes or a no) whether this is a release-zip
     * install: no .git, which is a folder in a clone and a file in a worktree or a submodule. Read
     * here and not only in the config file: an install that cached its config under v1.0.139 has
     * no such key, and the update this exists for is the one that never cleared that cache.
     */
    public static function finishes(): bool
    {
        $setting = config('self-update.finish_updates');

        if (is_bool($setting)) {
            return $setting;
        }

        $parsed = is_string($setting) ? filter_var($setting, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) : null;

        return $parsed ?? ! file_exists(base_path('.git'));
    }

    /** The installed version and the migration files beside it, or null with no version. */
    public static function marker(string $version): ?string
    {
        if ($version === '') {
            return null;
        }

        $files = [];

        foreach (array_merge([database_path('migrations')], app('migrator')->paths()) as $path) {
            $files = array_merge($files, glob($path.'/*.php') ?: []);
        }

        sort($files);

        return $version.'|'.md5(implode(',', array_map('basename', $files)));
    }

    /**
     * fetch() downloads and extracts the release to a folder named for the package, not the
     * version, and the next fetch() reuses any such folder without downloading again
     * (Release::isSourceAlreadyFetched()). Left behind by a refused update, it was installed
     * by the next attempt, under whatever version GitHub then called the newest.
     */
    protected function forgetFetchedRelease(Release $release): void
    {
        $archive = $release->getStoragePath();
        $name = $release->getRelease();

        // Only the archive fetch() named and the folder beside it. createFolderFromFile('/tmp')
        // is '/tmp', so a path that is not that archive must never reach deleteDirectory().
        if (! $archive || ! $name || basename($archive) !== $name) {
            return;
        }

        $folder = createFolderFromFile($archive);

        if ($folder === '' || $folder === $archive || dirname($folder) !== dirname($archive)) {
            return;
        }

        // A folder fetch() would reuse is not always one it made. A download path that holds the
        // install names the install itself (SELF_UPDATER_DOWNLOAD_PATH=/var/www beside
        // /var/www/eventschedule), and deleteDirectory() follows a link, so one planted at
        // /tmp/eventschedule would have it empty whatever it leads to.
        if (is_link($folder) || $this->holdsTheApp($folder)) {
            return;
        }

        File::delete($archive);
        File::deleteDirectory($folder);
    }

    /** Whether `$folder` is the app's own folder or its storage, or a folder above either. */
    protected function holdsTheApp(string $folder): bool
    {
        $real = realpath($folder);

        if ($real === false) {
            return false;
        }

        // rtrim first: realpath('/') is '/', and '//' starts no path.
        $prefix = rtrim($real, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        foreach ([base_path(), storage_path()] as $path) {
            $path = realpath($path);

            if ($path !== false && str_starts_with($path.DIRECTORY_SEPARATOR, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The file checkPermissions() stopped at, walked the same way: every file, no excludes.
     */
    protected function firstUnwritableFile(string $directory): ?string
    {
        try {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));

            foreach ($files as $file) {
                if ($file->isFile() && ! $file->isWritable()) {
                    return $file->getPathname();
                }
            }
        } catch (\UnexpectedValueException $e) {
            // A folder that cannot be read is no reason to hide the message itself.
        }

        return null;
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
