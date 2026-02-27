<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Services\DemoService;
use App\Utils\ImageUtils;
use App\Utils\UrlUtils;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\ChromeProcess;

class GenerateDocScreenshots extends Command
{
    protected $signature = 'app:generate-doc-screenshots {--page= : Generate screenshots for a single docs page} {--force : Overwrite existing files}';

    protected $description = 'Generate AP screenshots for user guide docs using browser automation';

    private const TEMP_PASSWORD = 'doc-screenshots-temp-pw-2024';

    private const TEMP_EMAIL = 'screenshots@temp.local';

    private const OUTPUT_DIR = 'public/images/docs';

    private array $serverPipes = [];

    public function handle(): int
    {
        // Find demo user
        $user = User::where('email', DemoService::DEMO_EMAIL)->first();
        if (! $user) {
            $this->error('Demo user not found. Run php artisan app:setup-demo first.');

            return 1;
        }

        $role = Role::where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)->first();
        if (! $role) {
            $this->error('Demo schedule not found. Run php artisan app:setup-demo first.');

            return 1;
        }

        $venueRole = Role::where('subdomain', 'demo-moestavern')->first();

        $demoEvent = $role ? \App\Models\Event::whereHas('roles', fn ($q) => $q->where('roles.id', $role->id))
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->first() : null;

        // Build screenshot definitions
        $pages = $this->getPages($role, $venueRole, $demoEvent);

        // Filter to single page if requested
        $singlePage = $this->option('page');
        if ($singlePage) {
            if (! isset($pages[$singlePage])) {
                $this->error("Unknown page: {$singlePage}. Available pages: ".implode(', ', array_keys($pages)));

                return 1;
            }
            $pages = [$singlePage => $pages[$singlePage]];
        }

        $force = $this->option('force');
        $outputDir = public_path('images/docs');

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Temporarily remove Vite hot file so @vite uses built assets
        $hotFile = public_path('hot');
        $hotFileBackup = null;
        if (file_exists($hotFile)) {
            $hotFileBackup = $hotFile.'.bak';
            rename($hotFile, $hotFileBackup);
            $this->line('Temporarily moved Vite hot file to use built assets.');
        }

        // Save original credentials and set temp ones (temp email avoids demo mode warnings)
        $originalPasswordHash = $user->password;
        $originalEmail = $user->email;
        $user->password = Hash::make(self::TEMP_PASSWORD);
        $user->email = self::TEMP_EMAIL;
        $user->save();

        // Re-verify email (the User model clears email_verified_at on email change)
        $user->email_verified_at = now();
        $user->saveQuietly();

        try {
            return $this->generate($user, $pages, $force, $outputDir);
        } finally {
            // Restore original credentials
            $user->password = $originalPasswordHash;
            $user->email = $originalEmail;
            $user->save();

            // Restore hot file
            if ($hotFileBackup && file_exists($hotFileBackup)) {
                rename($hotFileBackup, $hotFile);
            }
        }
    }

    private function getPages(?Role $role, ?Role $venueRole, ?\App\Models\Event $demoEvent = null): array
    {
        $encodedRoleId = $role ? UrlUtils::encodeId($role->id) : null;

        $pages = [
            'getting-started' => [
                ['id' => 'getting-started--dashboard', 'route' => '/events'],
            ],
            'schedule-styling' => [
                ['id' => 'schedule-styling--section-style', 'route' => '/simpsons/edit', 'section' => 'section-style'],
            ],
            'creating-schedules' => [
                ['id' => 'creating-schedules--section-details', 'route' => '/simpsons/edit', 'section' => 'section-details'],
                ['id' => 'creating-schedules--section-address', 'route' => $venueRole ? '/demo-moestavern/edit' : null, 'section' => 'section-address'],
                ['id' => 'creating-schedules--section-contact-info', 'route' => '/simpsons/edit', 'section' => 'section-contact-info'],
                ['id' => 'creating-schedules--section-subschedules', 'route' => '/simpsons/edit', 'section' => 'section-subschedules'],
                ['id' => 'creating-schedules--section-settings', 'route' => '/simpsons/edit', 'section' => 'section-settings'],
                ['id' => 'creating-schedules--section-auto-import', 'route' => '/simpsons/edit', 'section' => 'section-auto-import'],
                ['id' => 'creating-schedules--section-integrations', 'route' => '/simpsons/edit', 'section' => 'section-integrations'],
            ],
            'creating-events' => [
                ['id' => 'creating-events--schedule-tab', 'route' => '/simpsons/schedule'],
                ['id' => 'creating-events--add-event', 'route' => '/simpsons/add-event'],
                ['id' => 'creating-events--import', 'route' => '/simpsons/import'],
            ],
            'sharing' => [
                ['id' => 'sharing--guest-portal', 'route' => '/simpsons', 'public' => true],
            ],
            'event-graphics' => [
                ['id' => 'event-graphics--graphic-page', 'route' => '/simpsons/events-graphic', 'pause' => 3000],
                ['id' => 'event-graphics--settings', 'route' => '/simpsons/events-graphic', 'pause' => 3000, 'script' => "document.querySelectorAll('nav[aria-label=\"Settings Tabs\"] button')[1].click()"],
            ],
            'newsletters' => [
                ['id' => 'newsletters--list', 'route' => '/newsletters?role_id='.$encodedRoleId],
                ['id' => 'newsletters--create', 'route' => '/newsletters/create?role_id='.$encodedRoleId],
            ],
            'tickets' => [
                ['id' => 'tickets--sales', 'route' => '/sales'],
            ],
            'analytics' => [
                ['id' => 'analytics--dashboard', 'route' => '/analytics', 'pause' => 3000],
            ],
            'account-settings' => [
                ['id' => 'account-settings--settings', 'route' => '/settings'],
            ],
            'availability' => [
                ['id' => 'availability--calendar', 'route' => '/simpsons/availability', 'pause' => 2000],
            ],
            'scan-agenda' => [
                ['id' => 'scan-agenda--page', 'route' => '/simpsons/scan-agenda'],
            ],
            'boost' => [
                ['id' => 'boost--page', 'route' => $demoEvent
                    ? '/boost/create?event_id='.UrlUtils::encodeId($demoEvent->id).'&role_id='.$encodedRoleId
                    : '/boost'],
            ],
            'fan-content' => [
                ['id' => 'fan-content--videos-tab', 'route' => '/simpsons/videos'],
            ],
        ];

        // Remove address screenshot if no venue role
        if (! $venueRole) {
            $pages['creating-schedules'] = array_values(array_filter(
                $pages['creating-schedules'],
                fn ($s) => $s['id'] !== 'creating-schedules--section-address'
            ));
        }

        return $pages;
    }

    private function generate(User $user, array $pages, bool $force, string $outputDir): int
    {
        // Start temporary server with IS_HOSTED=false and DEBUGBAR_ENABLED=false
        $port = $this->findAvailablePort();
        $this->info("Starting temporary server on port {$port}...");

        $serverProcess = $this->startServer($port);
        if (! $serverProcess) {
            $this->error('Failed to start temporary server.');

            return 1;
        }

        if (! $this->waitForServer($port)) {
            $this->error('Server failed to start within timeout.');
            $this->stopServer($serverProcess);

            return 1;
        }

        $this->info('Server ready.');

        // Start ChromeDriver on a dynamic port
        $chromePort = $this->findAvailablePort();
        $chromeProcess = (new ChromeProcess)->toProcess(["--port={$chromePort}"]);
        $chromeProcess->start();

        // Wait for ChromeDriver to be ready
        if (! $this->waitForServer($chromePort)) {
            $this->error('ChromeDriver failed to start. Run: php artisan dusk:chrome-driver');
            $this->error($chromeProcess->getErrorOutput());
            $this->stopServer($serverProcess);

            return 1;
        }

        $this->info("ChromeDriver ready on port {$chromePort}.");

        $baseUrl = "http://127.0.0.1:{$port}";

        // Create WebDriver
        $options = (new ChromeOptions)->addArguments([
            '--window-size=1280,900',
            '--disable-gpu',
            '--headless=new',
            '--disable-search-engine-choice-screen',
            '--force-device-scale-factor=1',
            '--hide-scrollbars',
        ]);

        $driver = RemoteWebDriver::create(
            "http://localhost:{$chromePort}",
            DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $options)
        );

        Browser::$baseUrl = $baseUrl;
        Browser::$storeScreenshotsAt = $outputDir;

        $browser = new Browser($driver);

        try {
            // Login
            $this->info('Logging in...');
            $browser->visit('/login')
                ->waitFor('#email', 10)
                ->pause(500)
                ->type('email', $user->email)
                ->type('password', self::TEMP_PASSWORD);

            $browser->script("document.querySelector('form[method=\"POST\"]').requestSubmit()");
            $browser->waitForLocation('/events', 15);
            $this->info('Logged in.');

            // Force light mode for consistent screenshots
            $browser->script("localStorage.setItem('theme', 'light')");
            $browser->script("document.documentElement.classList.remove('dark')");
            $browser->pause(300);

            $generated = 0;
            $skipped = 0;

            foreach ($pages as $pageName => $screenshots) {
                $this->newLine();
                $this->info("Page: {$pageName}");

                foreach ($screenshots as $screenshot) {
                    $id = $screenshot['id'];
                    $route = $screenshot['route'] ?? null;
                    $section = $screenshot['section'] ?? null;
                    $isPublic = $screenshot['public'] ?? false;
                    $pause = $screenshot['pause'] ?? 1500;

                    if (! $route) {
                        $this->warn("  Skipping {$id} (no route)");
                        $skipped++;

                        continue;
                    }

                    $pngPath = "{$outputDir}/{$id}.png";
                    $webpPath = "{$outputDir}/{$id}.webp";
                    $darkPngPath = "{$outputDir}/{$id}-dark.png";
                    $darkWebpPath = "{$outputDir}/{$id}-dark.webp";

                    if (! $force && file_exists($pngPath) && file_exists($webpPath) && file_exists($darkPngPath) && file_exists($darkWebpPath)) {
                        $this->line("  Skipping {$id} (already exists, use --force to overwrite)");
                        $skipped++;

                        continue;
                    }

                    $this->line("  Generating {$id}...");

                    $browser->visit($route);
                    $browser->pause($pause);

                    // Execute custom script if needed (e.g. click a tab)
                    $script = $screenshot['script'] ?? null;
                    if ($script) {
                        $browser->script($script);
                        $browser->pause(800);
                    }

                    // Click section nav if needed
                    if ($section) {
                        $browser->script("document.querySelector('a[data-section=\"{$section}\"]').click()");
                        $browser->pause(800);
                    }

                    // Take light screenshot (Browser stores as PNG in the storeScreenshotsAt dir)
                    $browser->screenshot($id);

                    if (file_exists($pngPath)) {
                        ImageUtils::generateWebP($pngPath, $webpPath);
                        $generated++;
                        $this->line("  Generated {$id}");
                    } else {
                        $this->warn("  Failed to generate {$id}");
                    }

                    // Take dark screenshot
                    $browser->script("localStorage.setItem('theme', 'dark')");
                    $browser->script("document.documentElement.classList.add('dark')");
                    $browser->pause(800);
                    $browser->screenshot($id.'-dark');

                    if (file_exists($darkPngPath)) {
                        ImageUtils::generateWebP($darkPngPath, $darkWebpPath);
                        $generated++;
                        $this->line("  Generated {$id}-dark");
                    } else {
                        $this->warn("  Failed to generate {$id}-dark");
                    }

                    // Restore light mode for next screenshot
                    $browser->script("localStorage.setItem('theme', 'light')");
                    $browser->script("document.documentElement.classList.remove('dark')");
                    $browser->pause(300);
                }
            }

            $this->newLine();
            $this->info("Done! Generated: {$generated}, Skipped: {$skipped}");

            return 0;
        } finally {
            $browser->quit();
            $chromeProcess->stop();
            $this->stopServer($serverProcess);
        }
    }

    private function findAvailablePort(): int
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($socket, '127.0.0.1', 0);
        socket_getsockname($socket, $addr, $port);
        socket_close($socket);

        return $port;
    }

    /**
     * @return resource|false
     */
    private function startServer(int $port)
    {
        $artisan = base_path('artisan');
        $cmd = sprintf(
            'IS_HOSTED=false DEBUGBAR_ENABLED=false PHP_CLI_SERVER_WORKERS=4 php %s serve --port=%d --host=127.0.0.1 --no-reload 2>/dev/null',
            escapeshellarg($artisan),
            $port
        );

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptors, $pipes);

        if (is_resource($process)) {
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);
            $this->serverPipes = $pipes;

            return $process;
        }

        return false;
    }

    private function waitForServer(int $port, int $timeout = 10): bool
    {
        $start = time();
        while (time() - $start < $timeout) {
            $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
            if ($connection) {
                fclose($connection);

                return true;
            }
            usleep(200000);
        }

        return false;
    }

    /**
     * @param  resource  $process
     */
    private function stopServer($process): void
    {
        foreach ($this->serverPipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }

        $status = proc_get_status($process);
        if ($status['running']) {
            $pid = $status['pid'];
            if (PHP_OS_FAMILY === 'Windows') {
                exec("taskkill /F /T /PID {$pid} 2>/dev/null");
            } else {
                exec("kill {$pid} 2>/dev/null");
            }
        }

        proc_close($process);
    }
}
