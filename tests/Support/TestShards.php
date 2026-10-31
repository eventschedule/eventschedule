<?php

namespace Tests\Support;

/**
 * Splits the test files into N shards that CI runs side by side (.github/workflows/test.yml), so
 * the suite takes a quarter of the time it took as one process.
 *
 * Every file is in exactly one shard, and the split depends only on the files, so each job of the
 * matrix works out the same split on its own. A file is weighed by the seconds it took on CI
 * (weight()), and the heaviest go first, each to the lightest shard so far. Whole files, never single tests: a file's
 * tests can depend on each other (Depends) and share what setUpBeforeClass() builds.
 * TestShardsTest holds the split.
 */
final class TestShards
{
    /** @return list<string> every *Test.php under the directories, relative to the repository */
    public static function files(array $directories): array
    {
        $base = QueryGuard::basePath();
        $files = [];

        foreach ($directories as $directory) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base.'/'.$directory, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if (str_ends_with($file->getFilename(), 'Test.php')) {
                    $files[] = substr($file->getPathname(), strlen($base) + 1);
                }
            }
        }

        sort($files);

        return $files;
    }

    /**
     * Seconds the file is expected to take: what it took on CI (tests/shard-weights.php), or for a
     * file not there yet, its number of tests at what a test of its kind usually takes.
     */
    public static function weight(string $file): int
    {
        static $measured = null;
        $measured ??= (array) require QueryGuard::basePath().'/tests/shard-weights.php';

        if (isset($measured[$file])) {
            return max(1, (int) $measured[$file]);
        }

        $source = (string) file_get_contents(QueryGuard::basePath().'/'.$file);
        $tests = max(1, preg_match_all('/function\s+test\w*\s*\(|#\[Test\]/', $source));

        return str_starts_with($file, 'tests/Browser/') ? $tests * 15 : (int) ceil($tests * 0.3);
    }

    /**
     * Seconds per test file in a CI run's log (gh run view --log), for tests/shard-weights.php.
     *
     * @return array<string, int>
     */
    public static function weightsFromLog(string $log): array
    {
        $seconds = [];
        $class = null;

        foreach (preg_split('/\R/', $log) as $line) {
            if (preg_match('/(?:PASS|FAIL|WARN)\s+(Tests\\\\\S+)/', $line, $m)) {
                $class = $m[1];
            } elseif ($class !== null && preg_match('/[✓⨯-]\s+.*?\s+([\d.]+)s\s*$/u', $line, $m)) {
                $seconds[$class] = ($seconds[$class] ?? 0) + (float) $m[1];
            }
        }

        $weights = [];
        foreach ($seconds as $class => $total) {
            $file = 'tests/'.str_replace('\\', '/', substr($class, strlen('Tests\\'))).'.php';
            if (is_file(QueryGuard::basePath().'/'.$file)) {
                $weights[$file] = max(1, (int) round($total));
            }
        }
        ksort($weights);

        return $weights;
    }

    /**
     * @param  array<string, int>  $weights  file => weight
     * @return list<list<string>> the files of each shard, in file order
     */
    public static function split(array $weights, int $count): array
    {
        $files = array_keys($weights);
        usort($files, fn ($a, $b) => [$weights[$b], $a] <=> [$weights[$a], $b]);

        $shards = array_fill(0, $count, []);
        $totals = array_fill(0, $count, 0);

        foreach ($files as $file) {
            $lightest = array_keys($totals, min($totals), true)[0];
            $shards[$lightest][] = $file;
            $totals[$lightest] += $weights[$file];
        }

        foreach ($shards as &$shard) {
            sort($shard);
        }

        return $shards;
    }

    /** @return list<string> the files of shard $shard (1-based) of $count */
    public static function shard(array $directories, int $shard, int $count): array
    {
        $weights = [];
        foreach (self::files($directories) as $file) {
            $weights[$file] = self::weight($file);
        }

        return self::split($weights, $count)[$shard - 1];
    }

    /** phpunit.xml with its test suites replaced by one suite of the shard's files. */
    public static function phpunitConfig(string $phpunitXml, array $files): string
    {
        $suite = "<testsuites>\n        <testsuite name=\"Shard\">\n";
        foreach ($files as $file) {
            $suite .= '            <file>'.htmlspecialchars($file, ENT_XML1)."</file>\n";
        }
        $suite .= "        </testsuite>\n    </testsuites>";

        $config = preg_replace('#<testsuites>.*?</testsuites>#s', $suite, $phpunitXml, 1, $replaced);

        if ($replaced !== 1) {
            throw new \RuntimeException('phpunit.xml has no <testsuites> block to replace.');
        }

        return $config;
    }
}
