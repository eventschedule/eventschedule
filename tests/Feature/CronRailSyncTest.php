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
 *
 * WHAT THESE GUARDS CANNOT SEE - read this before trusting a green run:
 *
 * - Cadence on the HTTP rail is read from the TIER'S KEY NAME, never its TTL. Changing
 *   Cache::put('td_hourly', true, now()->addHour()) to addMinutes(5) keeps the label 'hourly' and
 *   keeps this file green while the whole hourly block starts running twelve times as often.
 *   self::TIERS is a hand-maintained map with nothing tying it to the source it describes.
 * - The condition walk understands `if` and nothing else. `elseif`, `else`, a brace-less body,
 *   alternative syntax, and the scheduler's own ->when() / ->skip() / ->environments() all read as
 *   'ungated'. Moving an existing `if (config('app.hosted'))` into ->when() - a tidy-up a reviewer
 *   would wave through - deletes the gate from this file's view entirely.
 * - hostedGate() reads negation from the text immediately before config('app.hosted'). It is right
 *   for `!`, `!config(...)` and `$x && ! config(...)`, and WRONG for `! (config('app.hosted'))`,
 *   `config('app.hosted') === false` and `$x !== config('app.hosted')`, all of which report
 *   'hosted'. Hoisting a negation over a compound condition on one rail only passes green.
 * - consoleCadences() splits on Schedule::call(, so each chunk carries the FOLLOWING entry's
 *   comment block. preg_match takes the first hit, which is safe only while every entry uses a
 *   frequency method from self::CADENCES. ->cron(), ->twiceDaily(), ->weekly(),
 *   ->everyThirtyMinutes() and ->everyTenMinutes() are all absent from it, and ->at('03:00') vs
 *   ->at('12:00') compare equal to a bare ->daily().
 * - Schedule::call(new SomeJob) has no Artisan::call, so the newsletter entry is invisible on both
 *   rails, as are the MetaAdsService::isBoostConfigured() and AdsService::isEnabled() gates.
 *
 * Every one of those is a real way for the rails to diverge with this file green. Widen the guard
 * rather than assuming it already covers a shape you are about to introduce.
 */
class CronRailSyncTest extends TestCase
{
    /**
     * A raw DB::beginTransaction() must be recovered on \Throwable, not \Exception.
     *
     * Both cron rails continue to the next task after a failure - ScheduleRunCommand catches
     * Throwable per event, and translateData catches it per command - so an \Error that escapes a
     * raw transaction's recovery leaves that transaction OPEN, and every write the rest of the tick
     * makes is rolled back at teardown while the cache-backed tier keys survive to mark the work
     * done. Silent, and repeats every tick.
     *
     * DB::transaction(closure) is safe (it catches Throwable itself), so only hand-rolled
     * transactions need this. There is exactly one such file reachable from either rail today.
     */
    public function test_raw_transactions_recover_on_throwable(): void
    {
        foreach (['app/Services/NewsletterService.php'] as $path) {
            $source = file_get_contents(base_path($path));

            foreach (['DB::beginTransaction()', '->beginTransaction()'] as $needle) {
                $offset = 0;

                while (($at = strpos($source, $needle, $offset)) !== false) {
                    $offset = $at + 1;
                    $recovery = substr($source, $at, 4000);

                    $this->assertStringContainsString('catch (\Throwable', $recovery,
                        "{$path}: the recovery for a raw {$needle} must catch \Throwable - an ".
                        '\Error would otherwise leave the transaction open for the rest of the cron tick'
                    );
                }
            }
        }
    }

    /**
     * Commands deliberately absent from BOTH rails. Each is hand-run until the hazards in its own
     * docblock are closed; see the notes in routes/console.php. Listing them here means adding one
     * back to a schedule is a deliberate edit to this test, not an accident.
     */
    private const UNSCHEDULED = [
        // app:send-event-announcements came OFF this list once the seven hazards in its docblock
        // were closed; it is now on both rails, hourly, and the parity check above covers it like
        // any other entry.
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
            'Register the command on both rails (CLAUDE.md). Frequency and the hosted gate are '.
            'compared by the two tests below.'
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
     * A command gated on config('app.hosted') on one rail must be gated the SAME WAY on the other.
     *
     * This is the exact shape of the drift that shipped: routes/console.php wrapped
     * app:send-onboarding-nudges in a hosted check and translateData() did not.
     *
     * Polarity is part of the comparison, not just presence. An earlier version of this test asked
     * only "is config('app.hosted') mentioned in an enclosing condition", which is equally true of
     * `if (config('app.hosted'))` and `if (! config('app.hosted'))` - so flipping one rail's gate,
     * which ships a hosted-only command to every selfhost or suppresses app:import-curator-events
     * on the only installs meant to run it, passed green. That is strictly worse than the drift the
     * test was written to catch.
     */
    public function test_the_hosted_gate_matches_on_both_rails(): void
    {
        $console = $this->console();
        $http = $this->translateData();

        foreach ($this->commandsIn($console) as $command) {
            $this->assertSame(
                $this->hostedGate($console, $command),
                $this->hostedGate($http, $command),
                "{$command} is gated on config('app.hosted') differently on the two cron rails. ".
                'An install driving cron through the other rail will run it when this one would not.'
            );
        }
    }

    /**
     * Same command, same ARGUMENTS.
     *
     * Name, cadence and gate can all agree while the two rails still behave differently, and they
     * did: app:charge-installments was called with ['--max-seconds' => 120] on the HTTP rail and
     * bare on the scheduler rail. Benign only because the signature defaults to the same 120 - but
     * it is exactly the shape that stops being benign the moment either number moves, and
     * routes/console.php sizes its mutex TTL by reasoning about "the command's own 120s budget".
     */
    public function test_the_arguments_match_on_both_rails(): void
    {
        $console = $this->argumentsIn($this->console());
        $http = $this->argumentsIn($this->translateData());
        $drift = [];

        foreach (array_keys($console) as $command) {
            if (($http[$command] ?? null) !== $console[$command]) {
                $drift[$command] = 'console='.json_encode($console[$command]).
                    ' http='.json_encode($http[$command] ?? null);
            }
        }

        $this->assertSame([], $drift,
            'these commands are invoked with different arguments on the two cron rails, so installs '.
            'on the two rails get different behaviour even though the name, cadence and gate agree.'
        );
    }

    /**
     * Artisan arguments per command, whitespace-normalised.
     *
     * @return array<string, string>
     */
    private function argumentsIn(string $source): array
    {
        preg_match_all(
            "/Artisan::call\\(\\s*'([^']+)'\\s*(,\\s*\\[[^\\]]*\\])?/",
            $source,
            $matches,
            PREG_SET_ORDER
        );

        $arguments = [];

        foreach ($matches as $match) {
            // queue:* IS compared here, unlike in commandsIn(): its presence is shared plumbing,
            // but its arguments are exactly what drifts silently - --sleep, --max-time and --tries
            // all change how much work one tick does.
            $arguments[$match[1]] = preg_replace('/\\s+/', ' ', trim($match[2] ?? ''));
        }

        return $arguments;
    }

    /**
     * CLAUDE.md requires matching FREQUENCY too, and until this existed nothing checked it: moving
     * a command from the hourly tier to the daily one on a single rail passed cleanly, even though
     * the failure message told you it would not.
     *
     * The two rails express cadence completely differently - cron expressions on one, cache-key
     * tiers on the other - so they are both normalised to a plain label before comparing.
     */
    public function test_the_frequency_matches_on_both_rails(): void
    {
        $console = $this->consoleCadences();
        $http = $this->httpCadences();
        $drift = [];

        foreach ($this->commandsIn($this->console()) as $command) {
            $this->assertArrayHasKey($command, $console, "no cadence parsed for {$command} in routes/console.php");
            $this->assertArrayHasKey($command, $http, "no tier parsed for {$command} in translateData()");

            if (isset(self::CADENCE_EXCEPTIONS[$command])) {
                $this->assertSame(
                    self::CADENCE_EXCEPTIONS[$command],
                    [$console[$command], $http[$command]],
                    "{$command} is listed as an allowed cadence exception, but the rails no longer ".
                    'match the pair recorded there. Note this also fires when the rails are brought '.
                    'into AGREEMENT - which is the good outcome: delete the CADENCE_EXCEPTIONS entry.'
                );

                continue;
            }

            if ($console[$command] !== $http[$command]) {
                $drift[$command] = "console={$console[$command]} http={$http[$command]}";
            }
        }

        $this->assertSame([], $drift,
            'these commands run at different frequencies on the two cron rails, so installs on the '.
            'two rails get different behaviour (CLAUDE.md). Add a documented entry to '.
            'CADENCE_EXCEPTIONS only if the difference is deliberate AND provably harmless.'
        );
    }

    /**
     * Commands whose cadence deliberately differs, as [scheduler rail, HTTP rail].
     *
     * app:retry-failed-jobs: the per-job cap, the cooldown and the per-job error handling all live
     * INSIDE the command, so the two cadences cannot produce two different retry BUDGETS - which is
     * why both rails' comments say to keep the callers in sync without saying to keep the frequency
     * in sync. Not identical behaviour, though: RetryFailedJobs exempts the first retry from the
     * cooldown, so the one-minute rail reaches a newly-failed job sooner and rescans its batch five
     * times as often. Listing it here means changing it is a deliberate edit, not an accident.
     */
    private const CADENCE_EXCEPTIONS = [
        'app:retry-failed-jobs' => ['5min', 'minute'],
    ];

    /**
     * Cadence per command on the scheduler rail, read from the chained frequency method.
     *
     * @return array<string, string>
     */
    private function consoleCadences(): array
    {
        $cadences = [];

        // One chunk per Schedule::call(...) statement; the chained ->hourly() etc. sits at its end.
        foreach (array_slice(explode('Schedule::call(', $this->console()), 1) as $chunk) {
            preg_match_all("/Artisan::call\(\s*'([^']+)'/", $chunk, $commands);
            preg_match('/->(everyMinute|everyFiveMinutes|everyFifteenMinutes|hourly|daily|monthly)\(/', $chunk, $frequency);

            if ($frequency === []) {
                continue;
            }

            foreach ($commands[1] as $command) {
                $cadences[$command] = self::CADENCES[$frequency[1]];
            }
        }

        return $cadences;
    }

    /**
     * Cadence per command on the HTTP rail, read from the cache key of the tier enclosing the call.
     *
     * A command in no tier at all is in the "EVERY CALL" block at the top of the method, which the
     * documented crontab hits once a minute.
     *
     * @return array<string, string>
     */
    private function httpCadences(): array
    {
        $source = $this->translateData();
        $cadences = [];

        foreach ($this->commandsIn($source) as $command) {
            $cadences[$command] = 'minute';

            foreach ($this->enclosingConditions($source, $command) as $condition) {
                foreach (self::TIERS as $key => $cadence) {
                    if (str_contains($condition, "'{$key}'")) {
                        $cadences[$command] = $cadence;

                        continue 3;
                    }
                }
            }
        }

        return $cadences;
    }

    /** Scheduler-rail frequency methods, normalised. */
    private const CADENCES = [
        'everyMinute' => 'minute',
        'everyFiveMinutes' => '5min',
        'everyFifteenMinutes' => '15min',
        'hourly' => 'hourly',
        'daily' => 'daily',
        'monthly' => 'monthly',
    ];

    /** HTTP-rail tier cache keys, normalised to the same labels. */
    private const TIERS = [
        'td_5min' => '5min',
        'td_15min' => '15min',
        'td_translate' => '15min',
        'td_hourly' => 'hourly',
        'td_daily' => 'daily',
        'notified_pending_today' => 'daily',
        'td_monthly' => 'monthly',
    ];

    /**
     * 'hosted' | 'not-hosted' | 'ungated' for this command's call site.
     *
     * Negation is read from the text immediately before config('app.hosted') rather than from
     * anywhere in the condition, so `$x && ! config('app.hosted')` is read as negated and
     * `! $x && config('app.hosted')` is not.
     */
    private function hostedGate(string $source, string $command): string
    {
        foreach ($this->enclosingConditions($source, $command) as $condition) {
            $at = strpos($condition, self::HOSTED);

            if ($at === false) {
                continue;
            }

            return str_ends_with(rtrim(substr($condition, 0, $at)), '!') ? 'not-hosted' : 'hosted';
        }

        return 'ungated';
    }

    private const HOSTED = "config('app.hosted')";

    /**
     * The conditions of every `if` block enclosing this command's call site, outermost first.
     *
     * Walks tokens tracking brace depth rather than looking backwards over raw text, because the
     * two rails write their conditions differently: routes/console.php puts an `if` immediately
     * around a single call, while translateData() wraps a whole GROUP of commands in one `if` with
     * try/catch blocks nested inside. A lookbehind finds the `try {` and misses the condition.
     *
     * @return string[]
     */
    private function enclosingConditions(string $source, string $command): array
    {
        $needle = "Artisan::call('{$command}'";
        $this->assertStringContainsString($needle, $source, "{$command} not found in this rail");

        // preg_replace, not ltrim: ltrim's second argument is a CHARACTER SET, so on a substring
        // beginning "public function translateData" it eats the leading "p" and the tokenizer then
        // lexes text that is not the source.
        $tokens = token_get_all("<?php\n".preg_replace('/^<\?php/', '', $source));

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
                return array_values($openIfs);
            }
        }

        $this->fail("could not locate the call site for {$command}");
    }
}
