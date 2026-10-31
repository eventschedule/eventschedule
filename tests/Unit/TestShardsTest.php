<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Support\QueryGuard;
use Tests\Support\QueryGuardExtension;
use Tests\Support\TestShards;

/**
 * CI runs the suite as shards side by side (tests/shard.php, .github/workflows/test.yml). A file
 * left out of every shard would never run and nothing would say so, and the QueryGuard ratchet
 * needs every shard's hits before it can judge the baseline.
 */
class TestShardsTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = sys_get_temp_dir().'/test-shards-'.bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir.'/*') ?: []);
        rmdir($this->dir);
        QueryGuard::setBaseline(null);
        QueryGuardExtension::$sayUsing = null;
        unset($_SERVER['QUERY_GUARD_STRICT'], $_SERVER['QUERY_GUARD_SHARD']);
        parent::tearDown();
    }

    /** Mutation: drop a file in split(), or let a file land in two shards. */
    public function test_every_test_file_runs_in_exactly_one_shard(): void
    {
        foreach ([['tests/Unit', 'tests/Feature'], ['tests/Browser']] as $directories) {
            $all = TestShards::files($directories);
            $this->assertNotEmpty($all);
            $this->assertContains(str_replace(dirname(__DIR__, 2).'/', '', __FILE__), TestShards::files(['tests/Unit']));

            foreach ([1, 4, 5, 6] as $count) {
                $seen = [];
                for ($shard = 1; $shard <= $count; $shard++) {
                    $files = TestShards::shard($directories, $shard, $count);
                    $this->assertNotEmpty($files, "shard {$shard} of {$count} is empty");
                    $seen = array_merge($seen, $files);
                }
                sort($seen);
                $this->assertSame($all, $seen, implode(', ', $directories)." in {$count} shards");
            }
        }
    }

    /** The heaviest file goes first, each to the lightest shard so far. Mutation: assign in name order. */
    public function test_the_split_evens_out_the_time(): void
    {
        // In name order this would be [a, c, e] (16) and [b, d] (10).
        $shards = TestShards::split(['a' => 1, 'b' => 4, 'c' => 5, 'd' => 6, 'e' => 10], 2);

        $this->assertSame([['b', 'e'], ['a', 'c', 'd']], $shards);
    }

    /** Mutation: keep the original suites, or lose phpunit.xml's environment block. */
    public function test_the_shard_config_is_phpunit_xml_running_the_shards_files(): void
    {
        $phpunit = (string) file_get_contents(dirname(__DIR__, 2).'/phpunit.xml');
        $config = TestShards::phpunitConfig($phpunit, ['tests/Unit/ATest.php', 'tests/Feature/BTest.php']);

        $xml = simplexml_load_string($config);
        $this->assertSame(['tests/Unit/ATest.php', 'tests/Feature/BTest.php'], array_map('strval', $xml->xpath('//testsuites/testsuite/file')));
        $this->assertCount(1, $xml->xpath('//testsuites/testsuite'));
        $this->assertSame(substr($phpunit, strpos($phpunit, '</testsuites>')), substr($config, strpos($config, '</testsuites>')));
    }

    public function test_the_weights_are_read_from_a_ci_log(): void
    {
        $log = "feature-tests\tRun\t2026-10-27T21:10:00.0Z    PASS  Tests\\Unit\\TestShardsTest\n"
            ."feature-tests\tRun\t2026-10-27T21:10:00.1Z   ✓ every test file runs in exactly one shard    1.40s  \n"
            ."feature-tests\tRun\t2026-10-27T21:10:00.2Z   ✓ the split evens out the time                 0.20s  \n"
            ."feature-tests\tRun\t2026-10-27T21:10:00.3Z    PASS  Tests\\Feature\\NoSuchFileTest\n"
            ."feature-tests\tRun\t2026-10-27T21:10:00.4Z   ✓ something                                     9.00s  \n";

        $this->assertSame(['tests/Unit/TestShardsTest.php' => 2], TestShards::weightsFromLog($log));
    }

    public function test_the_shard_is_read_from_the_environment(): void
    {
        $_SERVER['QUERY_GUARD_SHARD'] = '2/5';
        $this->assertSame([2, 5], QueryGuardExtension::shard());

        foreach (['', '0/5', '6/5', 'two'] as $value) {
            $_SERVER['QUERY_GUARD_SHARD'] = $value;
            $this->assertNull(QueryGuardExtension::shard(), $value);
        }
    }

    /**
     * The shards' hits together are one complete run's, and a missing or cut-short shard judges
     * nothing. Mutation: judge with the shards that are there, or count a shard that did not finish.
     */
    public function test_the_ratchet_judges_every_shard_together(): void
    {
        $_SERVER['QUERY_GUARD_STRICT'] = '1';
        $said = [];
        QueryGuardExtension::$sayUsing = function (string $line) use (&$said): void {
            $said[] = $line;
        };
        QueryGuard::setBaseline(['repeat|a.php|x|-' => 'why', 'repeat|b.php|y|-' => 'why']);

        $write = fn (int $shard, bool $complete, array $hits) => file_put_contents(
            QueryGuardExtension::shardPath($this->dir, $shard),
            json_encode(['shard' => $shard, 'complete' => $complete, 'tests' => 3, 'hits' => $hits]),
        );

        $write(1, true, ['repeat|a.php|x|-']);
        $this->assertSame(1, QueryGuardExtension::mergeShards($this->dir, 2), 'shard 2 is missing');
        $this->assertStringContainsString('shard 2 of 2 is missing', end($said));

        $write(2, false, ['repeat|b.php|y|-']);
        $this->assertSame(1, QueryGuardExtension::mergeShards($this->dir, 2), 'shard 2 stopped short');

        $write(2, true, ['repeat|b.php|y|-']);
        $this->assertSame(0, QueryGuardExtension::mergeShards($this->dir, 2));
    }
}
