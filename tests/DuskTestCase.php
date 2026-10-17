<?php

namespace Tests;

use Closure;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\NoAlertOpenException;
use Facebook\WebDriver\Exception\NoSuchAlertException;
use Facebook\WebDriver\Exception\UnexpectedAlertOpenException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
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
     * Keep /admin/realtime off in the served app. Both Dusk configs set IS_NEXUS=true, where it
     * defaults ON, and turning it on shows the cookie banner - fixed to the bottom corner, where
     * WebDriver scrolls the element it is about to click, so it can intercept clicks no journey was
     * written to expect (GeneralTest and TicketTest happen to pass either way; this keeps every
     * journey on the page it was written against). After parent::setUp(), because
     * DatabaseTruncation empties `settings` there (and its afterTruncatingDatabase() hook is skipped
     * on the first test, which runs migrate:fresh instead). Setting::set() also clears the cached
     * map the served app reads.
     */
    protected function setUp(): void
    {
        parent::setUp();

        \App\Models\Setting::set('realtime_enabled', '0');
    }

    /**
     * A page with none of the app on it: a box to type into and a button that marks itself pressed.
     */
    private const INPUT_PROBE_PAGE = 'data:text/html;charset=utf-8,'
        .'%3Cinput%20id%3D%22p%22%3E%3Cbutton%20id%3D%22b%22%20onclick%3D%22this.dataset.hit%3D1%22%3Eb%3C%2Fbutton%3E';

    /** PHPUnit makes a new instance per test method, so this is "probed in this test yet". */
    private bool $inputProbed = false;

    /**
     * Check that WebDriver input reaches the page before a test drives it, and replace a Chrome
     * session where it does not.
     *
     * On CI a Chrome session sometimes starts with its input dead: pages load, script() runs and
     * Vue mounts, but click(), type() and the Actions API go nowhere, with no error. It is the same
     * symptom as the one-major driver gap below, with the versions agreeing. SeatingTest lost every
     * real-input journey to it twice (6 of 10 on 2026-10-15, 8 of 10 on 2026-10-17, run
     * 39049248104), and each read as an app bug: a typed plan name that "never saved", seats that
     * were "never held". The failure screenshots showed the name box focused and unchanged and
     * nothing over the seats, and Chrome itself logged that the designer "never had a user gesture
     * since its load" after a real WebDriver drag on it. Every other file in those runs passed, each
     * in its own Chrome. setupTestAccount()'s repair of the sign-up fields hides it at the start of
     * every journey, so nothing failed until the first type() with no repair behind it.
     *
     * The probe types and clicks on a data: page, so a failure there cannot be the app's doing and
     * replacing the browser cannot hide an app bug. It runs on the first browse() of each test
     * only: VenueMapJourneyTest and the event form journeys call browse() again mid-test and carry
     * on from the page they are on. A page the last test left unsaved may ask before it lets go,
     * which the probe answers, as the next test's own visit() would have to.
     */
    protected function createBrowsersFor(Closure $callback)
    {
        $browsers = parent::createBrowsersFor($callback);

        if ($this->inputProbed) {
            return $browsers;
        }

        $this->inputProbed = true;

        if (($dead = static::deadInput($browsers)) === null) {
            return $browsers;
        }

        fwrite(STDERR, "\nWebDriver input reached nothing on a blank page ({$dead}); replacing this Chrome session.\n");

        static::closeAll();
        $browsers = parent::createBrowsersFor($callback);

        if (($dead = static::deadInput($browsers)) !== null) {
            $this->fail(
                "WebDriver input reached nothing on a blank page in two Chrome sessions running ({$dead}). "
                .'This is the browser, not the app: see guardAgainstChromeDriverDrift() and createBrowsersFor() in tests/DuskTestCase.php.'
            );
        }

        return $browsers;
    }

    /** Why the first browser of `$browsers` whose input is dead is dead, or null if none is. */
    private static function deadInput(Collection $browsers): ?string
    {
        foreach ($browsers as $browser) {
            if (($dead = static::probeInput($browser->driver)) !== null) {
                return $dead;
            }
        }

        return null;
    }

    private static function probeInput(RemoteWebDriver $driver): ?string
    {
        try {
            for ($attempt = 0; $attempt < 2; $attempt++) {
                try {
                    $driver->get(self::INPUT_PROBE_PAGE);

                    if (str_starts_with($driver->getCurrentURL(), 'data:')) {
                        break;
                    }
                } catch (UnexpectedAlertOpenException) {
                    // The beforeunload prompt of a page left with unsaved changes.
                }

                try {
                    $driver->switchTo()->alert()->accept();
                } catch (NoSuchAlertException|NoAlertOpenException) {
                }
            }

            $driver->findElement(WebDriverBy::id('p'))->sendKeys('probe');
            $driver->findElement(WebDriverBy::id('b'))->click();

            $seen = $driver->executeScript('return { typed: document.getElementById("p").value, clicked: document.getElementById("b").dataset.hit === "1" };');
        } catch (Throwable $e) {
            return get_class($e).': '.strtok($e->getMessage(), "\n");
        }

        if (($seen['typed'] ?? null) === 'probe' && ($seen['clicked'] ?? false) === true) {
            return null;
        }

        return 'typed '.json_encode($seen['typed'] ?? null).', click '.(($seen['clicked'] ?? false) ? 'arrived' : 'did not arrive');
    }

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
