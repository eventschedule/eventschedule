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
        parent::setUp();

        // Ensure feature gates relying on the "testing" flag stay open even when
        // the Dusk environment reuses the base .env without APP_TESTING enabled.
        $this->app['config']->set('app.is_testing', true);

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
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
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
