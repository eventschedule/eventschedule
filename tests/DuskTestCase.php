<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\OperatingSystem;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::guardAgainstChromeDriverDrift();

            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Refuse to run when ChromeDriver and Chrome are a major version apart.
     *
     * A TWO-major gap is loud: ChromeDriver refuses the session and every test dies on
     * SessionNotCreatedException. docs/TEST_COVERAGE.md records that happening at driver v147
     * against Chrome v149.
     *
     * A ONE-major gap is not. The session IS created, pages load, navigation works, script()
     * runs and Vue mounts - but ChromeDriver's synthesized input quietly goes nowhere, so
     * click(), type() and the Actions API all become silent no-ops while everything driven
     * through script() keeps working. SeatingTest lost 7 of its 10 journeys to that on Chrome
     * 153 against ChromeDriver 152, and every one of them read as an app bug: a plan name that
     * "never saved", a section that "did not move", seats that "were never held".
     *
     * Chrome auto-updates and the vendored binary does not, so a dev machine drifts into this
     * about every four weeks. CI never sees it - .github/workflows/test.yml runs
     * `dusk:chrome-driver --detect` on every run, which is also the fix here.
     *
     * Anything this cannot determine is skipped rather than failed: a remote driver, a Chrome
     * installed somewhere Dusk does not look, a binary that will not answer --version. The
     * guard must never itself be the reason a suite fails.
     */
    protected static function guardAgainstChromeDriverDrift(): void
    {
        // Read the raw layers rather than env(): this runs in a #[BeforeClass] hook, before
        // setUp() has booted the application that would have loaded .env.
        $remote = $_SERVER['DUSK_DRIVER_URL'] ?? $_ENV['DUSK_DRIVER_URL'] ?? getenv('DUSK_DRIVER_URL');

        if (! empty($remote)) {
            return;
        }

        $driver = static::chromeDriverMajorVersion();
        $chrome = static::chromeMajorVersion();

        if ($driver === null || $chrome === null || $driver === $chrome) {
            return;
        }

        throw new RuntimeException(
            "Chrome is {$chrome} but ChromeDriver is {$driver}. Across that gap WebDriver input "
            .'(click, type, drag) silently does nothing, so the browser tests fail as though the '
            ."app were broken.\nRun: php artisan dusk:chrome-driver --detect"
        );
    }

    /**
     * The major version of the ChromeDriver binary Dusk is about to start.
     *
     * buildChromeProcess() is Dusk's own resolution, so this asks the exact binary that will be
     * launched - including a useChromedriver() override - rather than guessing at a path.
     */
    protected static function chromeDriverMajorVersion(): ?int
    {
        try {
            $process = static::buildChromeProcess(['--version']);
            $process->run();

            return static::majorVersionIn($process->getOutput());
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * The major version of the installed Chrome, found the way dusk:chrome-driver --detect finds it.
     */
    protected static function chromeMajorVersion(): ?int
    {
        try {
            $commands = OperatingSystem::chromeVersionCommands(OperatingSystem::id());
        } catch (Throwable) {
            return null;
        }

        foreach ($commands as $command) {
            $process = Process::fromShellCommandline($command);
            $process->run();

            if ($major = static::majorVersionIn($process->getOutput())) {
                return $major;
            }
        }

        return null;
    }

    /**
     * Pull the leading number out of a "153.0.8010.48" anywhere in the output.
     *
     * The same pattern ChromeDriverCommand::detectChromeVersion() uses, which is what makes this
     * work on the Windows `reg query` output as well as on a --version line.
     */
    protected static function majorVersionIn(string $output): ?int
    {
        preg_match('/(\d+)(\.\d+){3}/', $output, $matches);

        return isset($matches[1]) ? (int) $matches[1] : null;
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
