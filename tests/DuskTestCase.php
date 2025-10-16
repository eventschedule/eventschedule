<?php

namespace Tests;

use App\Models\Setting;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare the base application configuration for browser tests.
     */
    protected function setUp(): void
    {
        $this->applyBrowserEnvironmentOverrides();

        parent::setUp();

        // Ensure feature gates relying on the "testing" flag stay open even when
        // the Dusk environment reuses the base .env without APP_TESTING enabled.
        $this->app['config']->set('app.is_testing', true);
        $this->app['config']->set('app.debug', true);
        $this->app['config']->set('app.load_vite_assets', false);

        $this->app['config']->set('mail.default', 'log');
        $this->app['config']->set('mail.mailers.smtp.transport', 'log');
        $this->app['config']->set(
            'mail.mailers.smtp.channel',
            $this->app['config']->get('mail.mailers.log.channel')
        );

        if (Schema::hasTable('settings')) {
            Setting::setGroup('mail', [
                'mailer' => 'log',
                'disable_delivery' => true,
            ]);
        }
    }

    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        static::synchronizeDuskEnvironmentOverrides();

        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Ensure the browser tests consistently run with the expected environment overrides.
     */
    private function applyBrowserEnvironmentOverrides(): void
    {
        foreach ([
            'APP_TESTING' => 'true',
            'APP_DEBUG' => 'true',
            'LOAD_VITE_ASSETS' => 'false',
        ] as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    /**
     * Persist the environment overrides to the Dusk environment file so that the
     * HTTP server process that services browser requests sees the same values.
     */
    private static function synchronizeDuskEnvironmentOverrides(): void
    {
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env.dusk.local';

        if (! is_file($path)) {
            file_put_contents($path, '');
        }

        $contents = file_get_contents($path);
        $contents = is_string($contents) ? $contents : '';

        foreach ([
            'APP_TESTING' => 'true',
            'APP_DEBUG' => 'true',
            'LOAD_VITE_ASSETS' => 'false',
        ] as $key => $value) {
            $pattern = "/^{$key}=.*$/m";

            if (preg_match($pattern, $contents)) {
                $contents = (string) preg_replace($pattern, "{$key}={$value}", $contents);
            } else {
                $contents = rtrim($contents, "\r\n");

                if ($contents !== '') {
                    $contents .= PHP_EOL;
                }

                $contents .= "{$key}={$value}" . PHP_EOL;
            }
        }

        file_put_contents($path, $contents);
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
