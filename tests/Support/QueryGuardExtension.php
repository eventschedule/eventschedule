<?php

namespace Tests\Support;

use PHPUnit\Event\Test\Finished as TestFinished;
use PHPUnit\Event\Test\FinishedSubscriber as TestFinishedSubscriber;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\TestRunner\ExecutionStartedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * The end of a run, for QueryGuard: the report, the baseline's ratchet and its writer.
 *
 * Only a COMPLETE run may judge the baseline: one with no filter, path, group or suite chosen,
 * not stopped early, and where every test that was started finished. A run that died (CI's OOM
 * of 2026-10 died at about test 5,150) never reaches ExecutionFinished, so it judges nothing.
 *
 *  - The report (storage/logs/query-guard.txt) is written after any run: what was found that is
 *    not listed, listed keys nobody hit, the heaviest requests and the named GET routes no test
 *    requested. It is information only.
 *  - QUERY_GUARD_STRICT=1 (CI): a listed key that a complete run never hit FAILS the run when the
 *    file it names changed in this push (QUERY_GUARD_BASE is the commit it is compared with):
 *    that edit fixed it, so it comes off the list. A key whose file did not change is a warning,
 *    because a test or fixture that stopped reaching it says nothing about the code.
 *  - QUERY_GUARD_WRITE=1: adds every key hit to tests/query-baseline.php. Add-only, and only on a
 *    complete run, so a filtered run can never drop what it did not reach.
 *
 * Failing the run: PHPUnit has already chosen its exit code when this runs, so a failure is a
 * shutdown function that exits 1 after it. tests/bootstrap.php registered its own (the opcache
 * note) earlier, so that one still runs first.
 */
final class QueryGuardExtension implements Extension
{
    public static bool $complete = false;

    public static int $started = 0;

    public static int $finished = 0;

    public static bool $partial = false;

    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        self::$partial = $configuration->hasFilter()
            || $configuration->hasCliArguments()
            || $configuration->hasGroups()
            || $configuration->includeTestSuite() !== ''
            || $configuration->stopOnFailure()
            || $configuration->stopOnError()
            || $configuration->stopOnDefect();

        $facade->registerSubscriber(new class implements ExecutionStartedSubscriber
        {
            public function notify(ExecutionStarted $event): void
            {
                QueryGuardExtension::$started = $event->testSuite()->count();
            }
        });

        $facade->registerSubscriber(new class implements TestFinishedSubscriber
        {
            public function notify(TestFinished $event): void
            {
                QueryGuardExtension::$finished++;
            }
        });

        $facade->registerSubscriber(new class implements ExecutionFinishedSubscriber
        {
            public function notify(ExecutionFinished $event): void
            {
                QueryGuardExtension::finish();
            }
        });
    }

    public static function finish(): void
    {
        $complete = ! self::$partial && self::$started > 0 && self::$finished >= self::$started;
        $baseline = QueryGuard::baseline();
        $hits = QueryGuard::hits();
        $base = (string) (getenv('QUERY_GUARD_BASE') ?: '');

        $verdict = self::judge(
            array_keys($baseline),
            array_keys($hits),
            $complete,
            QueryGuard::flag('QUERY_GUARD_STRICT'),
            $complete && $base !== '' ? self::changedFiles($base) : null,
        );

        foreach ($verdict['warn'] as $key) {
            self::say("::warning file=tests/query-baseline.php::QueryGuard: listed but not hit by this run, so possibly fixed: {$key}");
        }
        foreach ($verdict['fail'] as $key) {
            self::say("::error file=tests/query-baseline.php::QueryGuard: {$key} is listed, its file changed in this push and no test hit it any more. It is fixed: take it off tests/query-baseline.php.");
        }

        $added = 0;
        if (QueryGuard::flag('QUERY_GUARD_WRITE')) {
            if (! $complete) {
                self::say('QueryGuard: QUERY_GUARD_WRITE needs a complete run (no filter, path, group or suite), so nothing was written.');
            } else {
                $added = self::write($baseline, $hits);
                self::say("QueryGuard: added {$added} key(s) to tests/query-baseline.php.");
            }
        }

        $unlisted = array_diff_key($hits, $baseline);
        if ($unlisted && ! QueryGuard::enforcing()) {
            self::say('QueryGuard: '.count($unlisted).' unlisted N+1 key(s) found (report mode). See storage/logs/query-guard.txt.');
        }

        self::report($unlisted, $complete ? array_values(array_diff(array_keys($baseline), array_keys($hits))) : [], $complete);

        if ($verdict['fail']) {
            register_shutdown_function(static function (): void {
                exit(1);
            });
        }
    }

    /**
     * What a run says about the listed keys. Pure, so QueryGuardTest can hold every branch.
     *
     * @param  list<string>  $listed
     * @param  list<string>  $hit
     * @param  list<string>|null  $changed  files changed in this push, or null when unknown
     * @return array{fail: list<string>, warn: list<string>}
     */
    public static function judge(array $listed, array $hit, bool $complete, bool $strict, ?array $changed): array
    {
        if (! $complete || ! $strict) {
            return ['fail' => [], 'warn' => []];
        }

        $fail = [];
        $warn = [];
        $hitSet = array_flip($hit);
        $changedSet = array_flip($changed ?? []);

        foreach ($listed as $key) {
            if (isset($hitSet[$key])) {
                continue;
            }

            // The file that asked, or the controller, job or command it ran under.
            $parts = explode('|', $key);
            if ($changed !== null && (isset($changedSet[$parts[1] ?? '']) || isset($changedSet[$parts[3] ?? '']))) {
                $fail[] = $key;
            } else {
                $warn[] = $key;
            }
        }

        return ['fail' => $fail, 'warn' => $warn];
    }

    /** @return list<string>|null */
    private static function changedFiles(string $base): ?array
    {
        if (! preg_match('/^[0-9a-f]{7,40}$/', $base) || preg_match('/^0+$/', $base)) {
            return null;
        }

        $out = [];
        $status = 1;
        exec('git -C '.escapeshellarg(QueryGuard::basePath()).' diff --name-only '.escapeshellarg($base).' HEAD 2>/dev/null', $out, $status);

        return $status === 0 ? $out : null;
    }

    /** Add the keys hit and not listed; never remove one. Returns how many were added. */
    public static function write(array $baseline, array $hits): int
    {
        // A finding asked from test code is a test's own fixture (QueryGuardTest's routes N+1 on
        // purpose), never something to list.
        $new = array_filter(array_diff_key($hits, $baseline), fn ($key) => ! str_contains($key, '|tests/'), ARRAY_FILTER_USE_KEY);
        if (! $new) {
            return 0;
        }

        ksort($new);
        $today = date('Y-m-d');
        $path = QueryGuard::baselinePath();
        $existing = is_file($path) ? (string) file_get_contents($path) : '';

        $lines = '';
        foreach ($new as $key => $hit) {
            $seen = implode(', ', array_slice(array_keys($hit['seen']), 0, 3));
            $lines .= '    '.var_export($key, true).' => '.var_export("baseline {$today}: {$seen}", true).",\n";
        }

        if (! preg_match('/\n\];\s*$/', $existing)) {
            $existing = "<?php\n\nreturn [\n];\n";
        }

        file_put_contents($path, preg_replace('/\n\];\s*$/', "\n".rtrim($lines, "\n")."\n];\n", $existing, 1));
        QueryGuard::setBaseline(null);

        return count($new);
    }

    private static function report(array $unlisted, array $unhit, bool $complete): void
    {
        $out = ['QueryGuard report, '.date('Y-m-d H:i:s').($complete ? ' (complete run)' : ' (partial run)'), ''];

        $out[] = '== Unlisted N+1 findings ('.count($unlisted).')';
        foreach ($unlisted as $key => $hit) {
            $f = $hit['finding'];
            $out[] = "{$key}  x{$hit['count']}";
            $out[] = "    at {$f['origin']}".($f['context'] !== '' ? "  via {$f['context']}" : '');
            $out[] = '    in '.implode(', ', array_slice(array_keys($hit['seen']), 0, 5));
        }

        $out[] = '';
        $out[] = '== Listed but not hit'.($complete ? ' ('.count($unhit).')' : ' (only a complete run can say)');
        foreach ($unhit as $key) {
            $out[] = $key;
        }

        $totals = QueryGuard::totals();
        uasort($totals, fn ($a, $b) => $b[1] <=> $a[1]);
        $out[] = '';
        $out[] = '== Heaviest requests, jobs and commands (most queries in one run of it)';
        foreach (array_slice($totals, 0, 40, true) as $label => [$runs, $max, $sum]) {
            $out[] = sprintf('%6d max  %8.1f avg  %6d runs  %s', $max, $sum / max(1, $runs), $runs, $label);
        }

        $untested = QueryGuard::untestedRoutes();
        ksort($untested);
        $out[] = '';
        $out[] = '== Named GET routes no test requested ('.count($untested).')';
        foreach ($untested as $name => $uri) {
            $out[] = "{$name}  /{$uri}";
        }

        $dir = QueryGuard::basePath().'/storage/logs';
        if (is_dir($dir)) {
            file_put_contents($dir.'/query-guard.txt', implode("\n", $out)."\n");
        }
    }

    private static function say(string $line): void
    {
        fwrite(STDERR, $line."\n");
    }
}
