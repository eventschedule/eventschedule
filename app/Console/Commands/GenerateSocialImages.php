<?php

namespace App\Console\Commands;

use App\Utils\ImageUtils;
use Illuminate\Console\Command;

class GenerateSocialImages extends Command
{
    protected $signature = 'app:generate-social-images {--page= : Generate a single page image} {--force : Overwrite existing files}';

    protected $description = 'Generate social/OG images by screenshotting dedicated blade views with Chrome headless';

    private const PAGES = [
        'home',
        'features',
        'pricing',
        'about',
        'selfhost',
        'integrations',
        'saas',
        'for-talent',
        'for-venues',
        'for-online',
        'docs',
        'for-community',
    ];

    public function handle(): int
    {
        $chromePath = $this->findChrome();
        if (! $chromePath) {
            $this->error('Chrome not found. Please install Google Chrome.');

            return 1;
        }

        $this->info("Using Chrome: {$chromePath}");

        $pages = self::PAGES;
        $singlePage = $this->option('page');
        if ($singlePage) {
            if (! in_array($singlePage, self::PAGES)) {
                $this->error("Unknown page: {$singlePage}. Available pages: ".implode(', ', self::PAGES));

                return 1;
            }
            $pages = [$singlePage];
        }

        $force = $this->option('force');
        $outputDir = public_path('images/social');

        // Temporarily remove Vite hot file so @vite uses built assets
        $hotFile = public_path('hot');
        $hotFileBackup = null;
        if (file_exists($hotFile)) {
            $hotFileBackup = $hotFile.'.bak';
            rename($hotFile, $hotFileBackup);
            $this->line('Temporarily moved Vite hot file to use built assets.');
        }

        try {
            return $this->generate($chromePath, $pages, $force, $outputDir);
        } finally {
            // Restore hot file
            if ($hotFileBackup && file_exists($hotFileBackup)) {
                rename($hotFileBackup, $hotFile);
            }
        }
    }

    private function generate(string $chromePath, array $pages, bool $force, string $outputDir): int
    {
        // Start a temporary server with debugbar disabled
        $port = $this->findAvailablePort();
        $this->info("Starting temporary server on port {$port}...");

        $serverProcess = $this->startServer($port);
        if (! $serverProcess) {
            $this->error('Failed to start temporary server.');

            return 1;
        }

        // Wait for server to be ready
        if (! $this->waitForServer($port)) {
            $this->error('Server failed to start within timeout.');
            $this->stopServer($serverProcess);

            return 1;
        }

        $this->info('Server ready.');

        $generated = 0;
        $skipped = 0;

        foreach ($pages as $page) {
            $pngPath = "{$outputDir}/{$page}.png";
            $webpPath = "{$outputDir}/{$page}.webp";

            if (! $force && file_exists($pngPath) && file_exists($webpPath)) {
                $this->line("  Skipping {$page} (already exists, use --force to overwrite)");
                $skipped++;

                continue;
            }

            $this->line("  Generating {$page}...");

            $url = "http://127.0.0.1:{$port}/social-preview/{$page}";

            if ($this->screenshot($chromePath, $url, $pngPath)) {
                ImageUtils::generateWebP($pngPath, $webpPath);
                $generated++;
            } else {
                $this->warn("  Failed to generate {$page}");
            }
        }

        $this->stopServer($serverProcess);

        $this->newLine();
        $this->info("Done! Generated: {$generated}, Skipped: {$skipped}");

        return 0;
    }

    private function findChrome(): ?string
    {
        $paths = [
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
            '/usr/bin/chromium-browser',
            '/usr/bin/chromium',
            '/snap/bin/chromium',
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Try `which` as fallback
        $which = trim((string) shell_exec('which google-chrome 2>/dev/null'));
        if ($which && file_exists($which)) {
            return $which;
        }

        $which = trim((string) shell_exec('which chromium 2>/dev/null'));
        if ($which && file_exists($which)) {
            return $which;
        }

        return null;
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
            'DEBUGBAR_ENABLED=false PHP_CLI_SERVER_WORKERS=4 php %s serve --port=%d --host=127.0.0.1 --no-reload 2>/dev/null',
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
            // Make stdout non-blocking
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);

            // Store pipes on the process for cleanup
            $this->serverPipes = $pipes;

            return $process;
        }

        return false;
    }

    private array $serverPipes = [];

    private function waitForServer(int $port, int $timeout = 10): bool
    {
        $start = time();
        while (time() - $start < $timeout) {
            $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
            if ($connection) {
                fclose($connection);

                return true;
            }
            usleep(200000); // 200ms
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
            // Kill the process group
            $pid = $status['pid'];
            if (PHP_OS_FAMILY === 'Windows') {
                exec("taskkill /F /T /PID {$pid} 2>/dev/null");
            } else {
                exec("kill {$pid} 2>/dev/null");
            }
        }

        proc_close($process);
    }

    private function screenshot(string $chromePath, string $url, string $outputPath): bool
    {
        $tempDir = sys_get_temp_dir().'/chrome-social-'.getmypid();

        // Remove existing file so we can detect when Chrome writes a new one
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }

        $cmd = sprintf(
            '%s --headless=new --disable-gpu --no-sandbox --hide-scrollbars --force-device-scale-factor=1 --window-size=1200,630 --screenshot=%s --user-data-dir=%s %s 2>/dev/null',
            escapeshellarg($chromePath),
            escapeshellarg($outputPath),
            escapeshellarg($tempDir),
            escapeshellarg($url)
        );

        // Run Chrome in background since it may not exit cleanly on macOS
        $descriptors = [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']];
        $process = proc_open($cmd, $descriptors, $pipes);

        if (! is_resource($process)) {
            return false;
        }

        // Wait for the screenshot file to appear (up to 30 seconds)
        $timeout = 30;
        $start = time();
        $success = false;

        while (time() - $start < $timeout) {
            if (file_exists($outputPath) && filesize($outputPath) > 0) {
                // Give Chrome a moment to finish writing
                usleep(500000);
                $success = true;

                break;
            }
            usleep(200000);
        }

        // Kill Chrome process
        foreach ($pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }
        $status = proc_get_status($process);
        if ($status['running']) {
            $pid = $status['pid'];
            exec("kill {$pid} 2>/dev/null");
            usleep(100000);
            exec("kill -9 {$pid} 2>/dev/null");
        }
        proc_close($process);

        // Clean up temp dir
        if (is_dir($tempDir)) {
            exec(sprintf('rm -rf %s 2>/dev/null', escapeshellarg($tempDir)));
        }

        return $success;
    }
}
