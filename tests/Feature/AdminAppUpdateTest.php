<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AdminAlertService;
use App\Services\AppUpdateService;
use Codedge\Updater\Contracts\SourceRepositoryTypeContract;
use Codedge\Updater\Models\Release;
use Codedge\Updater\UpdaterManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mockery;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * System > App Update in the admin panel, the nav badge that advertises it, and the
 * app:make-admin escape hatch that gets a locked-out operator to it.
 *
 * All three exist because of issue #106: the self-updater lived only in Settings, the docs and
 * marketing site said it was "in your admin panel", and a selfhost user who lost the Settings
 * section had no second way in and no way to grant themselves admin.
 *
 * app.is_nexus, app.is_testing, app.hosted and app.url are pinned in every test. phpunit.xml
 * pins IS_NEXUS/APP_TESTING true and CI writes IS_HOSTED=true into .env, so nothing here may
 * rely on an ambient default - and CI leaves APP_URL blank, which is the fourth key's reason
 * for being (see selfhost()).
 */
class AdminAppUpdateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function selfhost(array $overrides = []): void
    {
        config(array_merge([
            'app.is_nexus' => false,
            'app.is_testing' => false,
            'app.hosted' => false,
            // Turning off BOTH hosted and is_testing re-arms selfhost_needs_setup(), which
            // reads a blank APP_URL as a fresh install and has EnsureSelfhostSetup redirect
            // every request to the setup wizard - so the controller under test never runs.
            // CI copies .env.example, where APP_URL is blank, so it cannot be left ambient.
            'app.url' => config('app.url') ?: 'http://localhost',
            'self-update.version_installed' => 'v1.0.100',
            // On for a release-zip install, which is what these tests stand for; the checkout
            // they run in has a .git, which turns it off.
            'self-update.finish_updates' => true,
        ], $overrides));

        // The update's cache clears are never run for real here: they would empty this checkout's
        // compiled views and package manifest under every other test run that shares it.
        $this->app->instance(AppUpdateService::class, new class extends AppUpdateService
        {
            protected function clearStaleCaches(): void {}
        });

        AdminAlertService::flush();
    }

    private function adminActing(User $admin)
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
    }

    public function test_an_admin_can_open_the_app_update_page(): void
    {
        $this->selfhost();
        Cache::put(AppUpdateService::CACHE_KEY, 'v1.0.101', 600);

        $response = $this->adminActing($this->createOwner(true))->get(route('admin.app_update'));

        $response->assertOk();
        $response->assertSee('v1.0.100', false);
        $response->assertSee('v1.0.101', false);
        $response->assertSee(route('admin.app_update.run'), false);
    }

    public function test_the_page_says_up_to_date_and_offers_no_update_button(): void
    {
        $this->selfhost();
        Cache::put(AppUpdateService::CACHE_KEY, 'v1.0.100', 600);

        $response = $this->adminActing($this->createOwner(true))->get(route('admin.app_update'));

        $response->assertOk();
        // false: the admin views use @lang, which echoes unescaped, so the apostrophe in
        // "You're up to date!" is not entity-encoded on the page.
        $response->assertSee(__('messages.up_to_date'), false);
        $response->assertDontSee(route('admin.app_update.run'), false);
    }

    public function test_the_page_404s_on_nexus(): void
    {
        $this->selfhost(['app.is_nexus' => true]);

        $this->adminActing($this->createOwner(true))->get(route('admin.app_update'))->assertNotFound();
    }

    public function test_a_non_admin_cannot_reach_the_page(): void
    {
        $this->selfhost();

        $this->actingAs($this->createOwner())->get(route('admin.app_update'))->assertRedirect();
    }

    public function test_the_nav_badge_appears_only_when_a_newer_version_is_cached(): void
    {
        $this->selfhost();

        Cache::put(AppUpdateService::CACHE_KEY, 'v1.0.101', 600);
        AdminAlertService::flush();
        $this->assertSame(1, AdminAlertService::badges()['tab']['app-update']['count'] ?? 0);

        Cache::put(AppUpdateService::CACHE_KEY, 'v1.0.100', 600);
        AdminAlertService::flush();
        $this->assertArrayNotHasKey('app-update', AdminAlertService::badges()['tab']);
    }

    public function test_the_nav_badge_never_calls_github(): void
    {
        // The badge composer runs on EVERY admin page render, and unauthenticated GitHub allows
        // 60 calls an hour for the whole install. A cold cache must read "no update", not block
        // the nav on a network round trip.
        $this->selfhost();
        Cache::forget(AppUpdateService::CACHE_KEY);
        Http::fake();

        AdminAlertService::flush();
        $badges = AdminAlertService::badges();

        Http::assertNothingSent();
        $this->assertArrayNotHasKey('app-update', $badges['tab']);
    }

    public function test_the_badge_is_silent_on_nexus(): void
    {
        $this->selfhost(['app.is_nexus' => true]);
        Cache::put(AppUpdateService::CACHE_KEY, 'v1.0.101', 600);
        AdminAlertService::flush();

        $this->assertArrayNotHasKey('app-update', AdminAlertService::badges()['tab']);
    }

    public function test_a_failed_version_check_is_not_an_available_update(): void
    {
        // The old code wrote the literal string 'Error: failed to check version' into the value
        // the view compared with !=, so every GitHub outage rendered an Update button.
        $this->selfhost();
        $service = app(AppUpdateService::class);

        Cache::put(AppUpdateService::CACHE_KEY, false, 600);
        $this->assertNull($service->cachedVersionAvailable());
        $this->assertFalse($service->isUpdateAvailable());

        Cache::put(AppUpdateService::CACHE_KEY, 'Error: failed to check version', 600);
        $this->assertNull($service->cachedVersionAvailable());
        $this->assertFalse($service->isUpdateAvailable());

        Cache::forget(AppUpdateService::CACHE_KEY);
        $this->assertFalse($service->isUpdateAvailable());
    }

    public function test_an_install_ahead_of_the_latest_release_is_not_offered_a_downgrade(): void
    {
        $this->selfhost(['self-update.version_installed' => 'v1.0.124']);
        Cache::put(AppUpdateService::CACHE_KEY, 'v1.0.123', 600);

        $this->assertFalse(app(AppUpdateService::class)->isUpdateAvailable());
    }

    public function test_the_version_prefix_is_normalized_before_comparing(): void
    {
        $this->selfhost(['self-update.version_installed' => 'v1.0.100']);
        Cache::put(AppUpdateService::CACHE_KEY, '1.0.100', 600);

        $this->assertFalse(app(AppUpdateService::class)->isUpdateAvailable());
    }

    public function test_the_post_update_clear_never_wipes_the_application_cache(): void
    {
        // cache:clear (and optimize:clear, which contains it) would release the Cache::lock
        // mutex that serialises Stripe installment charges, drop 30-day sms_signup tokens and
        // in-flight verification codes, and hand a throttled attacker a fresh budget.
        $source = file_get_contents(app_path('Services/AppUpdateService.php'));

        $this->assertStringNotContainsString("'cache:clear'", $source);
        $this->assertStringNotContainsString("'optimize:clear'", $source);
        $this->assertStringContainsString("'config:clear'", $source);
        $this->assertStringContainsString("'clear-compiled'", $source);
    }

    public function test_the_updater_package_post_update_hook_is_left_empty(): void
    {
        // UpdaterServiceProvider registers config('self-update.artisan_commands.post_update')
        // entries by ['class'], so an entry shaped like a command signature resolves null and
        // fatals the first Artisan::call() of any request. Nothing in the package calls
        // postUpdateArtisanCommands() anyway.
        $this->assertSame([], config('self-update.artisan_commands.post_update'));
    }

    public function test_an_update_that_could_not_replace_the_files_is_not_reported_as_installed(): void
    {
        // UpdateExecutor::run() copies nothing unless every file under base_path() is writable,
        // and says so only by returning false. An install that gave the web user storage,
        // bootstrap and public, as the docs say, was told "App updated successfully!", had its
        // migrations run against the old code, and was offered the same update again.
        $this->selfhost();
        Cache::put(AppUpdateService::CACHE_KEY, 'v9.9.9', 600);

        // fetch() has already extracted the release beside its archive. The next fetch() reuses
        // a folder it finds there without downloading, so a refused update must take it away.
        $downloads = $this->downloadFolder();
        mkdir($downloads.'/eventschedule/app', 0755, true);
        file_put_contents($downloads.'/eventschedule.zip', 'zip');

        $result = app(AppUpdateService::class)->performUpdate($this->updaterThatCannotCopy($this->fetched($downloads, 'eventschedule.zip')), forConsole: true);

        $this->assertSame('error', $result['status']);
        $this->assertSame(__('messages.app_update_files_not_writable'), $result['message']);
        $this->assertStringContainsString(base_path(), $result['detail']);
        $this->assertStringContainsString('as root', $result['detail']);
        $this->assertSame('v9.9.9', Cache::get(AppUpdateService::CACHE_KEY), 'the update is still waiting');
        $this->assertFileDoesNotExist($downloads.'/eventschedule.zip');
        $this->assertDirectoryDoesNotExist($downloads.'/eventschedule');
        $this->assertDirectoryExists($downloads);
    }

    /**
     * createFolderFromFile('/tmp/') is '/tmp/': a release whose path is not its own archive must
     * leave the download folder alone. Each case gets past every guard but the one it names.
     */
    public static function releasesThatAreNotTheirOwnArchive(): array
    {
        return [
            'no release name' => [fn (string $downloads) => (new Release)->setStoragePath($downloads)],
            'a name, and the path never given it' => [fn (string $downloads) => (new Release)->setStoragePath($downloads.'/')->setRelease('eventschedule.zip')],
            'a name with no extension' => [fn (string $downloads) => (new Release)->setStoragePath($downloads.'/')->setRelease('keep')->updateStoragePath()],
            'a name that is all extension' => [fn (string $downloads) => (new Release)->setStoragePath($downloads.'/')->setRelease('.zip')->updateStoragePath()],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('releasesThatAreNotTheirOwnArchive')]
    public function test_a_refused_update_never_deletes_the_download_folder_itself(\Closure $release): void
    {
        $this->selfhost();
        $downloads = $this->downloadFolder();
        mkdir($downloads.'/keep');
        file_put_contents($downloads.'/keep/file', 'x');

        app(AppUpdateService::class)->performUpdate($this->updaterThatCannotCopy($release($downloads)));

        $this->assertFileExists($downloads.'/keep/file');
    }

    /**
     * SELF_UPDATER_DOWNLOAD_PATH=/var/www beside an install at /var/www/eventschedule names the
     * install as the release's folder, and fetch() reuses it because it exists. Storage stands in
     * for the install here: a test must never be one guard away from deleting the repository.
     */
    public function test_a_refused_update_never_deletes_the_app_it_runs_in(): void
    {
        $this->selfhost();
        $downloads = $this->downloadFolder();

        foreach (['', '/storage'] as $below) {
            $this->app->useStoragePath($downloads.'/eventschedule'.$below);
            File::ensureDirectoryExists(storage_path());
            file_put_contents(storage_path('upload'), 'x');

            app(AppUpdateService::class)->performUpdate($this->updaterThatCannotCopy($this->fetched($downloads, 'eventschedule.zip')));

            $this->assertFileExists(storage_path('upload'), $below === '' ? 'the folder is the storage folder' : 'the folder holds the storage folder');
        }
    }

    public function test_a_refused_update_never_follows_a_link_where_its_folder_would_be(): void
    {
        // /tmp is shared, so anybody on the machine can put a link where the release would go.
        $this->selfhost();
        $downloads = $this->downloadFolder();
        mkdir($downloads.'/elsewhere');
        file_put_contents($downloads.'/elsewhere/upload', 'x');
        symlink($downloads.'/elsewhere', $downloads.'/eventschedule');

        app(AppUpdateService::class)->performUpdate($this->updaterThatCannotCopy($this->fetched($downloads, 'eventschedule.zip')));

        $this->assertFileExists($downloads.'/elsewhere/upload');
    }

    /** A release the way the GitHub source builds it: the download folder, then the archive's name. */
    private function fetched(string $downloads, string $name): Release
    {
        return (new Release)->setStoragePath($downloads.'/')->setRelease($name)->updateStoragePath();
    }

    private function downloadFolder(): string
    {
        $dir = sys_get_temp_dir().'/app-update-test-'.bin2hex(random_bytes(6));
        mkdir($dir);
        $this->beforeApplicationDestroyed(fn () => File::deleteDirectory($dir));

        return $dir;
    }

    /** $copies: how many times the test expects update() to be asked to copy the release in. */
    private function updaterThatCannotCopy(Release $release, int $copies = 1): UpdaterManager
    {
        $source = Mockery::mock(SourceRepositoryTypeContract::class);
        $source->shouldReceive('isNewVersionAvailable')->andReturn(true);
        $source->shouldReceive('getVersionAvailable')->andReturn('v9.9.9');
        $source->shouldReceive('fetch')->andReturn($release);
        $source->shouldReceive('update')->times($copies)->andReturn(false);

        // UpdaterManager is final, so the fake source goes where source() looks first.
        $updater = app(UpdaterManager::class);
        (new \ReflectionProperty(UpdaterManager::class, 'sources'))->setValue($updater, ['github' => $source]);

        Artisan::shouldReceive('call')->never();

        return $updater;
    }

    /** The release fetch() extracted, needing PHP $major.$minor as Composer's platform check says it. */
    private function releaseNeedingPhp(string $downloads, int $major, int $minor): Release
    {
        File::ensureDirectoryExists($downloads.'/eventschedule/vendor/composer');
        file_put_contents($downloads.'/eventschedule/vendor/composer/platform_check.php', "<?php\n\$issues = array();\n\nif (!(PHP_VERSION_ID >= ".($major * 10000 + $minor * 100).")) {\n    \$issues[] = 'x';\n}\n");
        file_put_contents($downloads.'/eventschedule.zip', 'zip');

        return $this->fetched($downloads, 'eventschedule.zip');
    }

    /**
     * A release that needs a newer PHP than the server runs used to be copied over the install,
     * and Composer's platform check then stopped the app on its next boot: the install was down
     * until somebody upgraded PHP by hand. It is refused before a file is replaced.
     */
    public function test_a_release_that_needs_a_newer_php_is_refused_before_anything_is_replaced(): void
    {
        $this->selfhost();
        $this->seen('web');
        Cache::put(AppUpdateService::CACHE_KEY, 'v9.9.9', 600);
        $downloads = $this->downloadFolder();
        $release = $this->releaseNeedingPhp($downloads, PHP_MAJOR_VERSION, PHP_MINOR_VERSION + 1);

        $result = app(AppUpdateService::class)->performUpdate($this->updaterThatCannotCopy($release, copies: 0), forConsole: true);

        $needed = PHP_MAJOR_VERSION.'.'.(PHP_MINOR_VERSION + 1).'.0';
        $this->assertSame('error', $result['status']);
        $this->assertSame(__('messages.app_update_needs_newer_php', [
            'version' => 'v9.9.9', 'required' => $needed, 'current' => PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION,
        ]), $result['message']);
        $this->assertStringContainsString('needs PHP '.$needed, $result['detail']);
        $this->assertSame('v9.9.9', Cache::get(AppUpdateService::CACHE_KEY), 'the update is still waiting');
        // The next fetch() would reuse the folder without downloading: it must not be left.
        $this->assertDirectoryDoesNotExist($downloads.'/eventschedule');
    }

    /**
     * The web server's PHP and the command line's are often not the same version. Updated from
     * the page on the newer one, the site came up and every scheduled task (the queue, mail,
     * imports) died on the older one, with nothing on screen.
     */
    public function test_the_php_the_scheduler_runs_on_counts_as_much_as_the_one_updating(): void
    {
        $this->selfhost();
        $downloads = $this->downloadFolder();
        $release = $this->releaseNeedingPhp($downloads, PHP_MAJOR_VERSION, PHP_MINOR_VERSION);
        $this->seen('web');
        Cache::put(AppUpdateService::PHP_SEEN_KEY.'scheduler', '7.4.0', 600);

        $result = app(AppUpdateService::class)->performUpdate($this->updaterThatCannotCopy($release, copies: 0), forConsole: true);

        $this->assertSame('error', $result['status']);
        $this->assertStringContainsString('scheduler 7.4.0', $result['detail']);
    }

    public function test_a_release_every_php_of_the_install_can_run_goes_on_to_be_copied(): void
    {
        $this->selfhost();
        $downloads = $this->downloadFolder();
        $release = $this->releaseNeedingPhp($downloads, PHP_MAJOR_VERSION, PHP_MINOR_VERSION);
        $this->seen('scheduler');
        $this->seen('web');
        Cache::put(AppUpdateService::PHP_SEEN_KEY.'http_cron', 'not a version', 600);

        // update() is asked once; it refuses for its own reason, which is the next message.
        $result = app(AppUpdateService::class)->performUpdate($this->updaterThatCannotCopy($release, copies: 1));

        $this->assertSame(__('messages.app_update_files_not_writable'), $result['message']);
    }

    /**
     * `sudo php artisan app:update` is what the refusal for unwritable files tells an admin to run,
     * and the command line's PHP is often newer than the web server's. Not having seen the website
     * lately, the command line takes it to run the oldest PHP the installed version allows (it is
     * up, so it runs at least that) rather than vouch for it: a release that raises the floor waits
     * until the website has been seen on a PHP that can run it.
     */
    public function test_from_the_command_line_a_website_not_seen_lately_is_not_vouched_for(): void
    {
        $this->selfhost();
        $installed = app(AppUpdateService::class)->phpRequiredBy(base_path());
        $this->assertNotNull($installed, 'this install ships its platform check');
        [$major, $minor] = array_map('intval', explode('.', $installed));
        $this->seen('scheduler');

        $downloads = $this->downloadFolder();
        $result = app(AppUpdateService::class)->performUpdate($this->updaterThatCannotCopy($this->releaseNeedingPhp($downloads, $major, $minor + 1), copies: 0), forConsole: true);

        $this->assertSame('error', $result['status']);
        $this->assertStringContainsString('web assumed '.$installed, $result['detail']);
        $this->assertStringContainsString('has not been seen', $result['detail']);

        // Seen on a PHP that can run it, the same release goes on (and update() refuses for its
        // own reason, which is the next message). Only where this PHP can run it too, as on CI.
        if (version_compare(PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION, $major.'.'.($minor + 1), '<')) {
            return;
        }

        Cache::put(AppUpdateService::PHP_SEEN_KEY.'web', $major.'.'.($minor + 1).'.0', 600);
        $result = app(AppUpdateService::class)->performUpdate($this->updaterThatCannotCopy($this->releaseNeedingPhp($this->downloadFolder(), $major, $minor + 1), copies: 1));

        $this->assertSame(__('messages.app_update_files_not_writable'), $result['message']);
    }

    public function test_the_php_a_release_needs_is_read_from_what_it_ships(): void
    {
        $service = app(AppUpdateService::class);
        $downloads = $this->downloadFolder();

        $this->assertNull($service->phpRequiredBy(''), 'no folder is never the root of the disk');
        $this->assertNull($service->phpRequiredBy($downloads), 'a release that says nothing is not held back');

        file_put_contents($downloads.'/composer.json', json_encode(['require' => ['php' => '^8.2 || ^8.3']]));
        $this->assertSame('8.2.0', $service->phpRequiredBy($downloads));

        // The platform check wins: it is what runs on boot, and it counts every package too.
        $this->releaseNeedingPhp($downloads.'/x', 8, 4);
        $this->assertSame('8.4.0', $service->phpRequiredBy($downloads.'/x/eventschedule'));

        // Several conditions: the highest is the floor.
        file_put_contents($downloads.'/x/eventschedule/vendor/composer/platform_check.php', "<?php\nif (!(PHP_VERSION_ID >= 80200)) {}\nif (!(PHP_VERSION_ID >= 80300)) {}\n");
        $this->assertSame('8.3.0', $service->phpRequiredBy($downloads.'/x/eventschedule'));

        foreach (['^8.3' => '8.3.0', '>=8.2.1' => '8.2.1', '~8.3.4' => '8.3.4', '8.3.*' => '8.3.0', '^8.4|^8.3' => '8.3.0', '>=8.2 <9.0' => '8.2.0', '>8.2' => null, '*' => null] as $constraint => $lowest) {
            $this->assertSame($lowest, AppUpdateService::lowestPhpIn($constraint), $constraint);
        }
    }

    /**
     * A release whose PHP floor rose ships as eventschedule-php8.3.zip. An updater from before the
     * check above looks for a name containing eventschedule.zip and finds nothing to download;
     * this one finds either, the way the package matches it (Str::match on 'regex:...').
     */
    public function test_the_updater_finds_a_release_named_for_its_php_and_an_older_one_does_not(): void
    {
        $pattern = Str::after(config('self-update.repository_types.github.package_file_name'), 'regex:');

        foreach (['eventschedule.zip' => true, 'eventschedule-php8.3.zip' => true, 'eventschedule-php8.10.zip' => true, 'eventschedule.zip.sha256' => false, 'other-eventschedule.zip' => false] as $name => $found) {
            $this->assertSame($found, (bool) Str::match('/'.$pattern.'/', $name), $name);
        }

        $this->assertFalse(Str::contains('eventschedule-php8.3.zip', 'eventschedule.zip'), 'what the older updater asks');
    }

    public function test_each_rail_records_the_php_it_runs_on(): void
    {
        $this->selfhost();
        $php = PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION;
        Cache::forget(AppUpdateService::PHP_SEEN_KEY.'scheduler');
        Cache::forget(AppUpdateService::PHP_SEEN_KEY.'web');

        event(new \Illuminate\Console\Events\CommandFinished('schedule:run', new \Symfony\Component\Console\Input\ArrayInput([]), new \Symfony\Component\Console\Output\NullOutput, 0));
        $this->assertSame($php, Cache::get(AppUpdateService::PHP_SEEN_KEY.'scheduler'));

        // The website: the update page an admin opens before updating. (The HTTP cron's is held
        // by CronEndpointSecurityTest.)
        Http::fake();
        $admin = $this->createOwner(true);
        $this->adminActing($admin)->get(route('admin.app_update'))->assertOk();
        $this->assertSame($php, Cache::get(AppUpdateService::PHP_SEEN_KEY.'web'));

        // And Settings, whose App Update section is the other place it is offered.
        Cache::forget(AppUpdateService::PHP_SEEN_KEY.'web');
        $this->adminActing($admin)->get(route('profile.edit'))->assertOk();
        $this->assertSame($php, Cache::get(AppUpdateService::PHP_SEEN_KEY.'web'));

        // A selfhosted SaaS (hosted, not the nexus) updates itself; the nexus never does.
        Cache::forget(AppUpdateService::PHP_SEEN_KEY.'web');
        config(['app.hosted' => true]);
        AppUpdateService::recordPhp('web');
        $this->assertSame($php, Cache::get(AppUpdateService::PHP_SEEN_KEY.'web'));

        Cache::forget(AppUpdateService::PHP_SEEN_KEY.'web');
        config(['app.is_nexus' => true]);
        AppUpdateService::recordPhp('web');
        $this->assertNull(Cache::get(AppUpdateService::PHP_SEEN_KEY.'web'));
    }

    /**
     * A folder holding one pending migration that only writes a row, so the test's transaction
     * still rolls it back (a table would be created outside it: MySQL commits DDL at once).
     */
    private function pendingMigration(bool $fails = false): string
    {
        $dir = $this->downloadFolder();
        file_put_contents($dir.'/2026_11_01_999999_finish_update_probe.php', "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Support\\Facades\\DB;\n\nreturn new class extends Migration\n{\n    public function up(): void\n    {\n        "
            .($fails ? "throw new \\RuntimeException('probe failed');" : "DB::table('settings')->insert(['key' => 'finish_update_probe', 'value' => '1']);")
            ."\n    }\n};\n");
        app('migrator')->path($dir);

        return $dir;
    }

    /**
     * An update from v1.0.139 replaced every file, then failed its first migration in the process
     * that copied them (Laravel 12's Blueprint beside Laravel 11's MySqlBuilder) and showed a 500.
     * The next process finishes it, once per installed version.
     */
    public function test_the_next_process_runs_the_migrations_an_update_left_behind(): void
    {
        $this->selfhost(['self-update.version_installed' => 'v1.0.140']);
        Cache::forget(AppUpdateService::FINISHED_KEY);
        $this->pendingMigration();
        // The cache clears are asked for, not run: they would empty this checkout's compiled
        // views and package manifest under every other test run that shares it.
        $service = new class extends AppUpdateService
        {
            public int $cleared = 0;

            protected function clearStaleCaches(): void
            {
                $this->cleared++;
            }
        };
        $this->app->instance(AppUpdateService::class, $service);

        AppUpdateService::finishUpdate();

        $this->assertSame(1, $service->cleared);
        $this->assertDatabaseHas('settings', ['key' => 'finish_update_probe']);
        $this->assertDatabaseHas('migrations', ['migration' => '2026_11_01_999999_finish_update_probe']);
        $this->assertSame(AppUpdateService::marker('v1.0.140'), Cache::get(AppUpdateService::FINISHED_KEY));

        // Once per version: a tick after that reads one cache key and nothing else.
        DB::table('migrations')->where('migration', '2026_11_01_999999_finish_update_probe')->delete();
        DB::table('settings')->where('key', 'finish_update_probe')->delete();
        AppUpdateService::finishUpdate();
        $this->assertDatabaseMissing('migrations', ['migration' => '2026_11_01_999999_finish_update_probe']);
    }

    /**
     * A tick that starts while an update is still copying folders sees the new version beside
     * half its migrations. Marked done by version alone, the rest would never run: the files
     * that land after it change the marker, and the next tick finishes them.
     */
    public function test_migrations_that_land_after_a_finish_are_finished_by_the_next_tick(): void
    {
        $this->selfhost(['self-update.version_installed' => 'v1.0.140']);
        Cache::forget(AppUpdateService::FINISHED_KEY);
        $dir = $this->pendingMigration();
        $this->app->instance(AppUpdateService::class, new class extends AppUpdateService
        {
            protected function clearStaleCaches(): void {}
        });
        AppUpdateService::finishUpdate();

        file_put_contents($dir.'/2026_11_01_999998_finish_update_late.php', "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Support\\Facades\\DB;\n\nreturn new class extends Migration\n{\n    public function up(): void\n    {\n        DB::table('settings')->insert(['key' => 'finish_update_late', 'value' => '1']);\n    }\n};\n");
        AppUpdateService::finishUpdate();

        $this->assertDatabaseHas('settings', ['key' => 'finish_update_late']);
    }

    /** An install whose cron does not run is finished when an admin opens App Update. */
    public function test_the_app_update_page_finishes_what_an_update_left_behind(): void
    {
        $this->selfhost(['self-update.version_installed' => 'v1.0.140']);
        Cache::forget(AppUpdateService::FINISHED_KEY);
        $this->pendingMigration();
        $this->app->instance(AppUpdateService::class, new class extends AppUpdateService
        {
            protected function clearStaleCaches(): void {}
        });
        Http::fake();

        $this->adminActing($this->createOwner(true))->get(route('admin.app_update'))->assertOk();

        $this->assertDatabaseHas('migrations', ['migration' => '2026_11_01_999999_finish_update_probe']);
    }

    /** Settings is the other page App Update is on, and finishes an update the same way. */
    public function test_the_settings_page_finishes_what_an_update_left_behind(): void
    {
        $this->selfhost(['self-update.version_installed' => 'v1.0.140']);
        Cache::forget(AppUpdateService::FINISHED_KEY);
        $this->pendingMigration();
        $this->countingClears();
        Http::fake();

        $this->adminActing($this->createOwner(true))->get(route('profile.edit'))->assertOk();

        $this->assertDatabaseHas('migrations', ['migration' => '2026_11_01_999999_finish_update_probe']);
    }

    public function test_a_migration_that_fails_is_reported_and_tried_again_later_not_every_tick(): void
    {
        $this->selfhost(['self-update.version_installed' => 'v1.0.140']);
        Cache::forget(AppUpdateService::FINISHED_KEY);
        $this->pendingMigration(fails: true);
        $reported = [];
        $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->reportable(function (\Throwable $e) use (&$reported) {
            $reported[] = $e->getMessage();

            return false;
        });

        AppUpdateService::finishUpdate();
        $this->assertCount(1, $reported);
        $this->assertNull(Cache::get(AppUpdateService::FINISHED_KEY), 'not marked done');

        // Not again on the next tick...
        AppUpdateService::finishUpdate();
        $this->assertCount(1, $reported);

        // ...but an admin opening App Update tries again at once, and then not on every view.
        AppUpdateService::finishUpdate(fromPage: true);
        $this->assertCount(2, $reported);
        AppUpdateService::finishUpdate(fromPage: true);
        $this->assertCount(2, $reported);
    }

    public function test_the_nexus_never_migrates_itself(): void
    {
        $this->selfhost(['app.is_nexus' => true, 'self-update.version_installed' => 'v1.0.140']);
        Cache::forget(AppUpdateService::FINISHED_KEY);
        $this->pendingMigration();

        AppUpdateService::finishUpdate();

        $this->assertDatabaseMissing('settings', ['key' => 'finish_update_probe']);
    }

    /**
     * The same failure in this version's own updater no longer turns a finished copy into a 500:
     * the files are in, and finishUpdate() completes the rest from the next process.
     */
    public function test_an_update_whose_migrations_fail_in_the_copying_process_still_reports_it_was_installed(): void
    {
        $this->selfhost();
        $this->seen('web');
        $downloads = $this->downloadFolder();
        $release = $this->releaseNeedingPhp($downloads, 7, 4);

        $source = Mockery::mock(SourceRepositoryTypeContract::class);
        $source->shouldReceive('isNewVersionAvailable')->andReturn(true);
        $source->shouldReceive('getVersionAvailable')->andReturn('v9.9.9');
        $source->shouldReceive('fetch')->andReturn($release);
        $source->shouldReceive('update')->once()->andReturn(true);
        $updater = app(UpdaterManager::class);
        (new \ReflectionProperty(UpdaterManager::class, 'sources'))->setValue($updater, ['github' => $source]);
        Artisan::shouldReceive('call')->once()->with('migrate', ['--force' => true])
            ->andThrow(new \Error('Access to undeclared static property Illuminate\\Database\\Schema\\MySqlBuilder::$defaultTimePrecision'));

        Cache::forever(AppUpdateService::FINISHED_KEY, AppUpdateService::marker('v1.0.100'));
        $errorLog = ini_set('error_log', '/dev/null');
        try {
            $result = app(AppUpdateService::class)->performUpdate($updater, forConsole: true);
        } finally {
            ini_set('error_log', (string) $errorLog);
        }

        // The old version's marker would let the next process skip it under a stale cached config.
        $this->assertNull(Cache::get(AppUpdateService::FINISHED_KEY));
        $this->assertSame(1, Cache::get(AppUpdateService::CLEAR_KEY));

        // Installed, and said to be unfinished, with what failed for whoever runs the command.
        $this->assertSame('updated', $result['status']);
        $this->assertSame(__('messages.app_updated_unfinished', ['version' => 'v9.9.9']), $result['message']);
        $this->assertStringContainsString('MySqlBuilder::$defaultTimePrecision', $result['detail']);
        $this->assertStringContainsString('php artisan migrate --force', $result['detail']);
    }

    /**
     * A git checkout is deployed and migrated by hand: finishing on the next tick would race the
     * deploy's own `composer install` and `migrate`, and the version moves at every release commit.
     */
    public function test_a_git_checkout_is_never_migrated_by_the_scheduler(): void
    {
        $this->selfhost(['self-update.version_installed' => 'v1.0.140', 'self-update.finish_updates' => false]);
        Cache::forget(AppUpdateService::FINISHED_KEY);
        $this->pendingMigration();

        AppUpdateService::finishUpdate();

        $this->assertDatabaseMissing('settings', ['key' => 'finish_update_probe']);
    }

    /**
     * Unset, a release-zip install (no .git) finishes and a checkout does not. Unset includes a
     * config cached under v1.0.139, which has no such key: that install is the one this exists for,
     * and reading the missing key as "off" left it unmigrated for good.
     */
    public function test_whether_an_install_finishes_its_updates(): void
    {
        $zipInstall = ! file_exists(base_path('.git'));

        config(['self-update.finish_updates' => null]);
        $this->assertSame($zipInstall, AppUpdateService::finishes());

        config(['self-update' => array_diff_key(config('self-update'), ['finish_updates' => true])]);
        $this->assertSame($zipInstall, AppUpdateService::finishes(), 'a config cached before the key existed');

        // A release-zip install has no .git: unset, or missing from a cached config, it finishes.
        $real = base_path();
        $this->app->setBasePath($this->downloadFolder());
        try {
            $this->assertTrue(AppUpdateService::finishes());
        } finally {
            $this->app->setBasePath($real);
        }

        config(['self-update.finish_updates' => true]);
        $this->assertTrue(AppUpdateService::finishes());
        config(['self-update.finish_updates' => false]);
        $this->assertFalse(AppUpdateService::finishes());

        // From the environment as words: a yes or a no is taken, anything else (a blank) is unset.
        config(['self-update.finish_updates' => 'no']);
        $this->assertFalse(AppUpdateService::finishes());
        config(['self-update.finish_updates' => 'on']);
        $this->assertTrue(AppUpdateService::finishes());
        config(['self-update.finish_updates' => '']);
        $this->assertSame($zipInstall, AppUpdateService::finishes());
    }

    /** A stub whose cache clears are counted, not run (they would empty this shared checkout's). */
    private function countingClears(): AppUpdateService
    {
        $service = new class extends AppUpdateService
        {
            public int $cleared = 0;

            protected function clearStaleCaches(): void
            {
                $this->cleared++;
            }
        };
        $this->app->instance(AppUpdateService::class, $service);

        return $service;
    }

    /**
     * With nothing pending and nothing stale, a missing marker is not a reason to clear: `cache:clear`
     * empties it, and clearing then threw away the `optimize` the guide says to run after it.
     */
    public function test_a_missing_marker_alone_clears_nothing(): void
    {
        $this->selfhost(['self-update.version_installed' => 'v1.0.140']);
        Cache::forget(AppUpdateService::FINISHED_KEY);
        $service = $this->countingClears();

        AppUpdateService::finishUpdate();

        $this->assertSame(0, $service->cleared);
        $this->assertSame(AppUpdateService::marker('v1.0.140'), Cache::get(AppUpdateService::FINISHED_KEY));
    }

    /** An in-place update that could not clear its caches leaves CLEAR_KEY, and the next finish clears them. */
    public function test_caches_an_update_left_dirty_are_cleared_by_the_next_finish(): void
    {
        $this->selfhost(['self-update.version_installed' => 'v1.0.140']);
        Cache::forget(AppUpdateService::FINISHED_KEY);
        Cache::forever(AppUpdateService::CLEAR_KEY, 1);
        $service = $this->countingClears();

        AppUpdateService::finishUpdate();

        $this->assertSame(1, $service->cleared);
        $this->assertNull(Cache::get(AppUpdateService::CLEAR_KEY));
    }

    /** A finish in progress (or an update copying) holds the lock: nothing else migrates beside it. */
    public function test_a_finish_waits_its_turn_behind_the_lock(): void
    {
        $this->selfhost(['self-update.version_installed' => 'v1.0.140']);
        Cache::forget(AppUpdateService::FINISHED_KEY);
        $this->pendingMigration();
        $this->countingClears();
        $this->assertTrue(Cache::lock(AppUpdateService::LOCK_KEY, 600)->get());

        AppUpdateService::finishUpdate();

        $this->assertDatabaseMissing('settings', ['key' => 'finish_update_probe']);
        $this->assertNull(Cache::get(AppUpdateService::FINISHED_KEY));
    }

    /** Finished as a scheduler tick STARTS, so the tick's own jobs meet the new schema. */
    public function test_a_scheduler_tick_finishes_the_update_before_its_tasks_run(): void
    {
        $this->selfhost(['self-update.version_installed' => 'v1.0.140']);
        Cache::forget(AppUpdateService::FINISHED_KEY);
        $this->pendingMigration();
        $this->app->instance(AppUpdateService::class, new class extends AppUpdateService
        {
            protected function clearStaleCaches(): void {}
        });

        event(new \Illuminate\Console\Events\CommandStarting('schedule:run', new \Symfony\Component\Console\Input\ArrayInput([]), new \Symfony\Component\Console\Output\NullOutput));

        $this->assertDatabaseHas('migrations', ['migration' => '2026_11_01_999999_finish_update_probe']);
    }

    /** A scheduler tick finishing an earlier update holds the lock: nothing is copied over it. */
    public function test_an_update_waits_for_a_finish_in_progress_and_copies_nothing_over_it(): void
    {
        $this->selfhost();
        $this->seen('web');
        $this->assertTrue(Cache::lock(AppUpdateService::LOCK_KEY, 600)->get(), 'the finish in progress');
        $service = new class extends AppUpdateService
        {
            protected int $lockWaitSeconds = 0;
        };

        $result = $service->performUpdate($this->updaterThatCannotCopy($this->releaseNeedingPhp($this->downloadFolder(), 7, 4), copies: 0), forConsole: true);

        $this->assertSame('error', $result['status']);
        $this->assertStringContainsString('still being finished', $result['detail']);
    }

    /** An update that finished in place is marked so: the next tick does not clear the caches again. */
    public function test_an_update_that_finished_in_place_is_not_finished_again(): void
    {
        $this->selfhost();
        $this->seen('web');
        $source = Mockery::mock(SourceRepositoryTypeContract::class);
        $source->shouldReceive('isNewVersionAvailable')->andReturn(true);
        $source->shouldReceive('getVersionAvailable')->andReturn('v9.9.9');
        $source->shouldReceive('fetch')->andReturn($this->releaseNeedingPhp($this->downloadFolder(), 7, 4));
        $source->shouldReceive('update')->once()->andReturn(true);
        $updater = app(UpdaterManager::class);
        (new \ReflectionProperty(UpdaterManager::class, 'sources'))->setValue($updater, ['github' => $source]);
        Artisan::shouldReceive('call')->with('migrate', ['--force' => true])->once()->andReturn(0);
        $service = new class extends AppUpdateService
        {
            protected function clearStaleCaches(): void {}
        };

        $this->assertSame('updated', $service->performUpdate($updater)['status']);
        $this->assertSame(AppUpdateService::marker('v9.9.9'), Cache::get(AppUpdateService::FINISHED_KEY));
        $this->assertTrue(Cache::lock(AppUpdateService::LOCK_KEY, 1)->get(), 'the update let go of the lock');
        Cache::lock(AppUpdateService::LOCK_KEY)->forceRelease();

        // The next process, on the new version, has nothing to do.
        config(['self-update.version_installed' => 'v9.9.9']);
        $next = $this->countingClears();
        AppUpdateService::finishUpdate();
        $this->assertSame(0, $next->cleared);
    }

    /** This PHP, as the given runner last saw it. */
    private function seen(string $runner): void
    {
        Cache::put(AppUpdateService::PHP_SEEN_KEY.$runner, PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION, 600);
    }

    public function test_make_admin_promotes_a_user_who_is_not_id_1(): void
    {
        $first = $this->createOwner(true);
        $second = $this->createOwner();
        $this->assertFalse($second->isAdmin());

        $this->artisan('app:make-admin', ['email' => $second->email])->assertExitCode(0);

        $this->assertTrue($second->fresh()->isAdmin());
        $this->assertTrue($first->fresh()->isAdmin(), 'the existing admin is untouched');
    }

    public function test_make_admin_works_when_nobody_is_an_admin(): void
    {
        $user = $this->createOwner();
        User::query()->update(['is_admin' => false]);

        $this->artisan('app:make-admin', ['email' => $user->email])->assertExitCode(0);

        $this->assertTrue($user->fresh()->isAdmin());
    }

    public function test_make_admin_bypasses_mass_assignment_without_weakening_it(): void
    {
        $user = $this->createOwner();

        $this->artisan('app:make-admin', ['email' => $user->email])->assertExitCode(0);

        $this->assertTrue($user->fresh()->is_admin);
        $this->assertContains('is_admin', (new User)->getGuarded(), 'is_admin must stay guarded');
    }

    public function test_make_admin_reports_an_unknown_email_without_promoting_anyone(): void
    {
        $this->createOwner();

        $this->artisan('app:make-admin', ['email' => 'nobody@example.test'])->assertExitCode(1);

        $this->assertSame(0, User::where('is_admin', true)->count());
    }

    public function test_make_admin_is_idempotent(): void
    {
        $user = $this->createOwner(true);

        $this->artisan('app:make-admin', ['email' => $user->email])->assertExitCode(0);

        $this->assertTrue($user->fresh()->isAdmin());
    }

    public function test_make_admin_lists_the_current_admins(): void
    {
        $admin = $this->createOwner(true);

        $this->artisan('app:make-admin')->expectsOutputToContain($admin->email)->assertExitCode(0);
    }

    public function test_make_admin_runs_on_nexus(): void
    {
        // Deliberately not gated: shell access already outranks the flag, and gating would lock
        // out the operator of an install where nobody is an admin - the case it exists for.
        config(['app.is_nexus' => true]);
        $user = $this->createOwner();

        $this->artisan('app:make-admin', ['email' => $user->email])->assertExitCode(0);

        $this->assertTrue($user->fresh()->isAdmin());
    }

    public function test_a_promoted_user_can_reach_the_admin_portal(): void
    {
        $this->selfhost();
        $user = $this->createOwner();
        $this->artisan('app:make-admin', ['email' => $user->email]);

        $this->adminActing($user->fresh())->get(route('admin.app_update'))->assertOk();
    }

    public function test_the_update_command_is_gated_on_nexus_not_hosted(): void
    {
        // It used to bail on config('app.hosted'), which left the self-hosted SaaS operator -
        // the one install type whose web route is admin-gated - with no command line either.
        $source = file_get_contents(app_path('Console/Commands/UpdateApp.php'));

        $this->assertStringContainsString("config('app.is_nexus')", $source);
        $this->assertStringNotContainsString("config('app.hosted')", $source);
        $this->assertStringNotContainsString('exit;', $source, 'exit; reports success to cron and kills phpunit');
    }

    public function test_the_update_command_refuses_on_nexus_with_a_failure_code(): void
    {
        config(['app.is_nexus' => true]);

        $this->artisan('app:update')->expectsOutputToContain('Not authorized')->assertExitCode(1);
    }

    public function test_check_version_no_ops_on_nexus(): void
    {
        config(['app.is_nexus' => true]);
        Http::fake();

        $this->artisan('app:check-version')->assertExitCode(0);

        Http::assertNothingSent();
    }
}
