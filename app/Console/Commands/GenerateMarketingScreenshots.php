<?php

namespace App\Console\Commands;

use App\Utils\ImageUtils;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Illuminate\Console\Command;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\ChromeProcess;

class GenerateMarketingScreenshots extends Command
{
    protected $signature = 'app:generate-marketing-screenshots {--schedule= : Generate screenshots for a single schedule subdomain} {--force : Overwrite existing files}';

    protected $description = 'Generate marketing carousel screenshots by visiting live demo schedule pages with ChromeDriver';

    private const SCHEDULES = [
        'battleofthebands' => 'Battle of the Bands',
        'nateswoodworkingshop' => "Nate's Woodworking Shop",
        'villageidiot' => 'Village Idiot',
        'karateclub' => 'Karate Club',
        'pagesbooknookshop' => 'Pages Book Nook Shop',
    ];

    public function handle(): int
    {
        $schedules = self::SCHEDULES;
        $singleSchedule = $this->option('schedule');
        if ($singleSchedule) {
            if (! isset($schedules[$singleSchedule])) {
                $this->error("Unknown schedule: {$singleSchedule}. Available: ".implode(', ', array_keys($schedules)));

                return 1;
            }
            $schedules = [$singleSchedule => $schedules[$singleSchedule]];
        }

        $force = $this->option('force');
        $outputDir = public_path('images/screenshots');

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        return $this->generate($schedules, $force, $outputDir);
    }

    private function generate(array $schedules, bool $force, string $outputDir): int
    {
        // Start ChromeDriver on a dynamic port
        $chromePort = $this->findAvailablePort();
        $chromeProcess = (new ChromeProcess)->toProcess(["--port={$chromePort}"]);
        $chromeProcess->start();

        if (! $this->waitForServer($chromePort)) {
            $this->error('ChromeDriver failed to start. Run: php artisan dusk:chrome-driver');
            $this->error($chromeProcess->getErrorOutput());

            return 1;
        }

        $this->info("ChromeDriver ready on port {$chromePort}.");

        // Create WebDriver with tall viewport to capture full schedule views
        $options = (new ChromeOptions)->addArguments([
            '--window-size=1280,1750',
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

        Browser::$storeScreenshotsAt = $outputDir;

        $browser = new Browser($driver);

        try {
            $generated = 0;
            $skipped = 0;

            foreach ($schedules as $subdomain => $name) {
                $listResult = $this->screenshotListPage($browser, $subdomain, $name, $outputDir, $force);
                if ($listResult === 'generated') {
                    $generated++;
                } elseif ($listResult === 'skipped') {
                    $skipped++;
                }

                $eventResult = $this->screenshotEventPage($browser, $subdomain, $name, $outputDir, $force);
                if ($eventResult === 'generated') {
                    $generated++;
                } elseif ($eventResult === 'skipped') {
                    $skipped++;
                }
            }

            $this->newLine();
            $this->info("Done! Generated: {$generated}, Skipped: {$skipped}");

            return 0;
        } finally {
            $browser->quit();
            $chromeProcess->stop();
        }
    }

    private function screenshotListPage(Browser $browser, string $subdomain, string $name, string $outputDir, bool $force): string
    {
        $baseName = "list_{$subdomain}";
        $jpgPath = "{$outputDir}/{$baseName}.jpg";

        if (! $force && file_exists($jpgPath)) {
            $this->line("  Skipping {$baseName} (already exists, use --force to overwrite)");

            return 'skipped';
        }

        $this->line("  Generating {$baseName} ({$name} - list view)...");

        $url = "https://{$subdomain}.eventschedule.com/";
        $browser->visit($url);
        $browser->pause(3000);

        // Switch to list view via the Vue app
        $browser->script("window.calendarVueApp.toggleView('list')");
        $browser->pause(3000);

        $pngPath = "{$outputDir}/{$baseName}.png";
        $browser->screenshot($baseName);

        if (! file_exists($pngPath)) {
            $this->warn("    Failed to capture {$baseName}");

            return 'failed';
        }

        $this->postProcess($pngPath, $jpgPath, $outputDir, $baseName);
        $this->line("    Generated {$baseName}");

        return 'generated';
    }

    private function screenshotEventPage(Browser $browser, string $subdomain, string $name, string $outputDir, bool $force): string
    {
        $baseName = "event_{$subdomain}";
        $jpgPath = "{$outputDir}/{$baseName}.jpg";

        if (! $force && file_exists($jpgPath)) {
            $this->line("  Skipping {$baseName} (already exists, use --force to overwrite)");

            return 'skipped';
        }

        $this->line("  Generating {$baseName} ({$name} - event detail)...");

        // Visit the graphic view to get all events via Vue app
        $url = "https://{$subdomain}.eventschedule.com/?graphic=1";
        $browser->visit($url);
        $browser->pause(3000);

        // Extract the first event URL from the Vue app's allEvents data
        $eventUrl = $browser->script("
            var events = window.calendarVueApp.allEvents;
            if (events && events.length > 0) {
                return events[0].url;
            }
            return null;
        ")[0] ?? null;

        if (! $eventUrl) {
            $this->warn("    No events found for {$subdomain}");

            return 'failed';
        }

        // Navigate to the event detail page
        $browser->visit($eventUrl);
        $browser->pause(3000);

        // Resize viewport to match content height (max 1750px)
        $contentHeight = $browser->script('return document.body.scrollHeight')[0] ?? 1750;
        $contentHeight = min((int) $contentHeight, 1750);
        $browser->driver->manage()->window()->setSize(new WebDriverDimension(1280, $contentHeight));
        $browser->pause(500);

        $pngPath = "{$outputDir}/{$baseName}.png";
        $browser->screenshot($baseName);

        // Reset viewport to default size
        $browser->driver->manage()->window()->setSize(new WebDriverDimension(1280, 1750));

        if (! file_exists($pngPath)) {
            $this->warn("    Failed to capture {$baseName}");

            return 'failed';
        }

        $this->postProcess($pngPath, $jpgPath, $outputDir, $baseName);
        $this->line("    Generated {$baseName}");

        return 'generated';
    }

    private function postProcess(string $pngPath, string $jpgPath, string $outputDir, string $baseName): void
    {
        // Convert PNG to JPG with white background
        $pngImage = imagecreatefrompng($pngPath);
        $width = imagesx($pngImage);
        $height = imagesy($pngImage);

        $jpgImage = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($jpgImage, 255, 255, 255);
        imagefill($jpgImage, 0, 0, $white);
        imagecopy($jpgImage, $pngImage, 0, 0, 0, 0, $width, $height);
        imagejpeg($jpgImage, $jpgPath, 85);
        imagedestroy($pngImage);
        imagedestroy($jpgImage);

        // Generate 585x800 thumbnail
        $thumbPath = "{$outputDir}/{$baseName}_800w.jpg";
        ImageUtils::generateThumbnail($jpgPath, $thumbPath, 585, 800);

        // Generate WebP variants
        ImageUtils::generateWebP($jpgPath, "{$outputDir}/{$baseName}.webp");
        ImageUtils::generateWebP($thumbPath, "{$outputDir}/{$baseName}_800w.webp");

        // Delete intermediate PNG
        unlink($pngPath);
    }

    private function findAvailablePort(): int
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($socket, '127.0.0.1', 0);
        socket_getsockname($socket, $addr, $port);
        socket_close($socket);

        return $port;
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
}
