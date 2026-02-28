<?php

namespace App\Console\Commands;

use App\Utils\ImageUtils;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Console\Command;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\ChromeProcess;

class GenerateSocialImages extends Command
{
    protected $signature = 'app:generate-social-images {--page= : Generate a single page image} {--force : Overwrite existing files}';

    protected $description = 'Generate social/OG images by screenshotting marketing page heroes with ChromeDriver';

    private array $serverPipes = [];

    private const PAGES = [
        // Core pages
        'home' => '/',
        'features' => '/features',
        'pricing' => '/pricing',
        'about' => '/about',
        'examples' => '/examples',
        'faq' => '/faq',
        'why-create-account' => '/why-create-account',

        // Feature pages
        'features-ticketing' => '/features/ticketing',
        'features-ai' => '/features/ai',
        'features-calendar-sync' => '/features/calendar-sync',
        'features-analytics' => '/features/analytics',
        'features-integrations' => '/features/integrations',
        'features-custom-fields' => '/features/custom-fields',
        'features-team-scheduling' => '/features/team-scheduling',
        'features-sub-schedules' => '/features/sub-schedules',
        'features-online-events' => '/features/online-events',
        'features-newsletters' => '/features/newsletters',
        'features-recurring-events' => '/features/recurring-events',
        'features-embed-calendar' => '/features/embed-calendar',
        'features-fan-videos' => '/features/fan-videos',
        'features-boost' => '/features/boost',
        'features-private-events' => '/features/private-events',
        'features-event-graphics' => '/features/event-graphics',
        'features-polls' => '/features/polls',
        'features-white-label' => '/features/white-label',
        'features-custom-css' => '/features/custom-css',
        'features-custom-domain' => '/features/custom-domain',

        // Integration pages
        'google-calendar' => '/google-calendar',
        'caldav' => '/caldav',
        'stripe' => '/stripe',
        'invoiceninja' => '/invoiceninja',

        // For-X persona pages
        'for-talent' => '/for-talent',
        'for-venues' => '/for-venues',
        'for-curators' => '/for-curators',
        'for-musicians' => '/for-musicians',
        'for-djs' => '/for-djs',
        'for-comedians' => '/for-comedians',
        'for-circus-acrobatics' => '/for-circus-acrobatics',
        'for-magicians' => '/for-magicians',
        'for-spoken-word' => '/for-spoken-word',
        'for-bars' => '/for-bars',
        'for-nightclubs' => '/for-nightclubs',
        'for-music-venues' => '/for-music-venues',
        'for-theaters' => '/for-theaters',
        'for-dance-groups' => '/for-dance-groups',
        'for-theater-performers' => '/for-theater-performers',
        'for-food-trucks-and-vendors' => '/for-food-trucks-and-vendors',
        'for-comedy-clubs' => '/for-comedy-clubs',
        'for-restaurants' => '/for-restaurants',
        'for-breweries-and-wineries' => '/for-breweries-and-wineries',
        'for-art-galleries' => '/for-art-galleries',
        'for-community-centers' => '/for-community-centers',
        'for-fitness-and-yoga' => '/for-fitness-and-yoga',
        'for-workshop-instructors' => '/for-workshop-instructors',
        'for-visual-artists' => '/for-visual-artists',
        'for-farmers-markets' => '/for-farmers-markets',
        'for-hotels-and-resorts' => '/for-hotels-and-resorts',
        'for-libraries' => '/for-libraries',
        'for-webinars' => '/for-webinars',
        'for-live-concerts' => '/for-live-concerts',
        'for-online-classes' => '/for-online-classes',
        'for-virtual-conferences' => '/for-virtual-conferences',
        'for-live-qa-sessions' => '/for-live-qa-sessions',
        'for-watch-parties' => '/for-watch-parties',
        'for-ai-agents' => '/for-ai-agents',

        // Comparison pages
        'use-cases' => '/use-cases',
        'compare' => '/compare',
        'eventbrite-alternative' => '/eventbrite-alternative',
        'luma-alternative' => '/luma-alternative',
        'ticket-tailor-alternative' => '/ticket-tailor-alternative',
        'google-calendar-alternative' => '/google-calendar-alternative',
        'meetup-alternative' => '/meetup-alternative',
        'dice-alternative' => '/dice-alternative',
        'brown-paper-tickets-alternative' => '/brown-paper-tickets-alternative',
        'splash-alternative' => '/splash-alternative',

        // Deployment options
        'selfhost' => '/selfhost',
        'saas' => '/saas',
        'open-source' => '/open-source',

        // Other
        'contact' => '/contact',
        'docs' => '/docs',
        'search' => '/search',
        'privacy' => '/privacy',
        'terms-of-service' => '/terms-of-service',
        'self-hosting-terms-of-service' => '/self-hosting-terms-of-service',
        'blog' => '/blog',
    ];

    public function handle(): int
    {
        $pages = self::PAGES;
        $singlePage = $this->option('page');
        if ($singlePage) {
            if (! isset($pages[$singlePage])) {
                $this->error("Unknown page: {$singlePage}. Available pages: ".implode(', ', array_keys($pages)));

                return 1;
            }
            $pages = [$singlePage => $pages[$singlePage]];
        }

        $force = $this->option('force');
        $outputDir = public_path('images/social');

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

        try {
            return $this->generate($pages, $force, $outputDir);
        } finally {
            if ($hotFileBackup && file_exists($hotFileBackup)) {
                rename($hotFileBackup, $hotFile);
            }
        }
    }

    private function generate(array $pages, bool $force, string $outputDir): int
    {
        // Start temporary server with IS_NEXUS=true so marketing routes work at root paths
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

        if (! $this->waitForServer($chromePort)) {
            $this->error('ChromeDriver failed to start. Run: php artisan dusk:chrome-driver');
            $this->error($chromeProcess->getErrorOutput());
            $this->stopServer($serverProcess);

            return 1;
        }

        $this->info("ChromeDriver ready on port {$chromePort}.");

        $baseUrl = "http://127.0.0.1:{$port}";

        // Create WebDriver with OG image dimensions
        $options = (new ChromeOptions)->addArguments([
            '--window-size=1200,630',
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
            $generated = 0;
            $skipped = 0;

            foreach ($pages as $slug => $urlPath) {
                $pngPath = "{$outputDir}/{$slug}.png";
                $webpPath = "{$outputDir}/{$slug}.webp";

                if (! $force && file_exists($pngPath) && file_exists($webpPath)) {
                    $this->line("  Skipping {$slug} (already exists, use --force to overwrite)");
                    $skipped++;

                    continue;
                }

                $this->line("  Generating {$slug}...");

                $browser->visit($urlPath);

                // Force dark mode
                $browser->script("document.documentElement.classList.add('dark')");

                // Inject all screenshot overrides: disable animations, hide header/footer, center hero content
                $browser->script("
                    var style = document.createElement('style');
                    style.textContent = '*, *::before, *::after { animation: none !important; transition: none !important; } .animate-reveal { opacity: 1 !important; } header { display: none !important; } footer { display: none !important; } main > section:first-of-type { height: 630px !important; min-height: unset !important; padding: 0 !important; overflow: hidden !important; } main > section:first-of-type .flex.justify-center.gap-4 { display: none !important; } main > section:first-of-type .relative.z-10 > .mb-6:first-child { display: none !important; } main > section:first-of-type nav[aria-label=\"Breadcrumb\"] { display: none !important; } main > section:first-of-type > .relative.z-10 { position: absolute !important; top: 40% !important; left: 0 !important; right: 0 !important; transform: translateY(-50%) !important; padding: 0 !important; margin: 0 auto !important; } main > section:first-of-type .relative.z-10.text-center { display: flex !important; flex-direction: column !important; align-items: center !important; gap: 2rem !important; } main > section:first-of-type .relative.z-10.text-center > * { margin-top: 0 !important; margin-bottom: 0 !important; } .fixed.bottom-4.right-4.z-50 { display: none !important; }';
                    document.head.appendChild(style);
                ");

                $browser->pause(1500);

                $browser->screenshot($slug);

                if (file_exists($pngPath)) {
                    ImageUtils::generateWebP($pngPath, $webpPath);
                    $generated++;
                    $this->line("    Generated {$slug}");
                } else {
                    $this->warn("    Failed to generate {$slug}");
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
            'IS_NEXUS=true IS_HOSTED=false APP_TESTING=true DEBUGBAR_ENABLED=false PHP_CLI_SERVER_WORKERS=4 php %s serve --port=%d --host=127.0.0.1 --no-reload 2>/dev/null',
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
