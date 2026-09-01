<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CLAUDE.md's "keep translateData and console.php in sync" rule, made enforceable.
 *
 * Every scheduled command is registered twice: in routes/console.php for installs driven by a
 * crontab schedule:run (and, on hosted, by the DigitalOcean worker running schedule:work), and in
 * AppController::translateData() for installs driven by an HTTP cron hitting /translate_data. The
 * rule has been a comment since the second rail was added, and it had already drifted: the hosted
 * gate on app:send-onboarding-nudges was present on one rail and absent on the other, so a plain
 * selfhost using the HTTP endpoint mailed nudges the crontab rail deliberately suppresses.
 *
 * A drift here is not a tidiness problem. The two rails disagreeing means a command silently never
 * runs on one kind of install, or runs somewhere it was deliberately kept away from - and in this
 * codebase most of these commands send mail or move money.
 *
 * These are source-text assertions rather than behavioural ones, following the idiom already used
 * by TranslateCommandTest and QueueFailureHandlingTest. The rails are two hand-maintained lists;
 * comparing the lists is the point.
 */
class CronRailSyncTest extends TestCase
{
    /**
     * Commands deliberately absent from BOTH rails. Each is hand-run until the hazards in its own
     * docblock are closed; see the notes in routes/console.php. Listing them here means adding one
     * back to a schedule is a deliberate edit to this test, not an accident.
     */
    private const UNSCHEDULED = [
        'app:send-event-announcements',
        'app:send-activation-nudges',
    ];

    private function console(): string
    {
        return file_get_contents(base_path('routes/console.php'));
    }

    private function translateData(): string
    {
        $controller = file_get_contents(app_path('Http/Controllers/AppController.php'));
        $start = strpos($controller, 'public function translateData');
        $this->assertNotFalse($start, 'AppController::translateData() has moved or been renamed');

        // The method ends at the next method declaration at class-body indentation.
        $end = strpos($controller, "\n    public function ", $start + 1);

        return substr($controller, $start, $end === false ? null : $end - $start);
    }

    /** @return string[] sorted, unique artisan command names invoked in $source */
    private function commandsIn(string $source): array
    {
        preg_match_all("/Artisan::call\(\s*'([^']+)'/", $source, $matches);

        $commands = array_unique($matches[1]);

        // queue:work and the queue:* maintenance verbs are plumbing both rails share; they are not
        // scheduled application commands and are covered by QueueFailureHandlingTest.
        $commands = array_filter($commands, fn ($c) => ! str_starts_with($c, 'queue:'));

        sort($commands);

        return array_values($commands);
    }

    public function test_every_scheduled_command_is_registered_on_both_rails(): void
    {
        $console = $this->commandsIn($this->console());
        $http = $this->commandsIn($this->translateData());

        $this->assertNotEmpty($console, 'no commands parsed out of routes/console.php - has the idiom changed?');

        $this->assertSame(
            $console,
            $http,
            "The two cron rails have drifted.\n".
            'Only on routes/console.php: '.implode(', ', array_diff($console, $http))."\n".
            'Only on translateData(): '.implode(', ', array_diff($http, $console))."\n".
            'Register the command on both rails with matching frequency (CLAUDE.md).'
        );
    }

    public function test_the_deliberately_unscheduled_commands_are_on_neither_rail(): void
    {
        $console = $this->console();
        $http = $this->translateData();

        foreach (self::UNSCHEDULED as $command) {
            $this->assertStringNotContainsString(
                "Artisan::call('{$command}'", $console,
                "{$command} is hand-run on purpose; see its docblock before scheduling it"
            );
            $this->assertStringNotContainsString("Artisan::call('{$command}'", $http, "{$command} is hand-run on purpose");
        }
    }

    /**
     * A command gated on config('app.hosted') on one rail must be gated on the other. This is the
     * exact shape of the drift that shipped: routes/console.php wrapped app:send-onboarding-nudges
     * in a hosted check and translateData() did not.
     */
    public function test_the_hosted_gate_matches_on_both_rails(): void
    {
        $console = $this->console();
        $http = $this->translateData();

        foreach ($this->commandsIn($console) as $command) {
            $this->assertSame(
                $this->isHostedGated($console, $command),
                $this->isHostedGated($http, $command),
                "{$command} is gated on config('app.hosted') on one cron rail but not the other. ".
                'An install driving cron through the ungated rail will run it when the other rail would not.'
            );
        }
    }

    /**
     * Whether any conditional block enclosing this command's call site tests config('app.hosted').
     *
     * Walks tokens tracking brace depth rather than looking backwards over raw text, because the
     * two rails write the gate differently: routes/console.php puts an `if` immediately around a
     * single call, while translateData() wraps a whole GROUP of hosted-only commands in one `if`
     * with try/catch blocks nested inside. A lookbehind finds the `try {` and misses the gate.
     */
    private function isHostedGated(string $source, string $command): bool
    {
        $needle = "Artisan::call('{$command}'";
        $this->assertStringContainsString($needle, $source, "{$command} not found in this rail");

        $tokens = token_get_all('<?php '.ltrim($source, "<?php \n"));

        // Conditions of the `if` blocks currently open, keyed by the brace depth they opened at.
        $openIfs = [];
        $depth = 0;
        $pendingIf = null;   // condition text being collected between `if (` and its `)`
        $parens = 0;
        $armed = null;       // a finished condition waiting for its `{`

        foreach ($tokens as $token) {
            $text = is_array($token) ? $token[1] : $token;

            if (is_array($token) && $token[0] === T_IF) {
                $pendingIf = '';
                $parens = 0;

                continue;
            }

            if ($pendingIf !== null) {
                if ($text === '(') {
                    $parens++;
                } elseif ($text === ')') {
                    $parens--;
                    if ($parens === 0) {
                        [$armed, $pendingIf] = [$pendingIf, null];

                        continue;
                    }
                }
                $pendingIf .= $text;

                continue;
            }

            if ($text === '{') {
                $depth++;
                if ($armed !== null) {
                    $openIfs[$depth] = $armed;
                    $armed = null;
                }

                continue;
            }

            if ($text === '}') {
                unset($openIfs[$depth]);
                $depth--;

                continue;
            }

            // The call site: T_STRING 'Artisan' starts it, but matching on the raw text of any
            // token that carries the command name is enough and survives the leading backslash.
            if (is_array($token) && $token[0] === T_CONSTANT_ENCAPSED_STRING && trim($text, "'\"") === $command) {
                foreach ($openIfs as $condition) {
                    if (str_contains($condition, "config('app.hosted')")) {
                        return true;
                    }
                }

                return false;
            }
        }

        $this->fail("could not locate the call site for {$command}");
    }
}
