<?php

/*
 * CI's test shards (.github/workflows/test.yml, Tests\Support\TestShards).
 *
 *   php tests/shard.php phpunit <i> <N>      writes phpunit.shard.xml: phpunit.xml running shard i of N
 *                                            of tests/Unit and tests/Feature
 *   php tests/shard.php browser <i> <N>      prints shard i of N of tests/Browser, one file a line
 *   php tests/shard.php query-guard <dir> <N> judges the N shards' QueryGuard hits as one run
 *   php tests/shard.php weights <run id>      rewrites tests/shard-weights.php from a green CI run
 */

require __DIR__.'/../vendor/autoload.php';

use Tests\Support\QueryGuardExtension;
use Tests\Support\TestShards;

[$self, $mode, $first, $count] = array_pad($argv, 4, null);
$count = (int) $count;

if ($mode === 'weights' && preg_match('/^\d+$/', (string) $first)) {
    $log = (string) shell_exec('gh run view '.escapeshellarg((string) $first).' --log');
    $weights = TestShards::weightsFromLog($log);
    if (count($weights) < 100) {
        fwrite(STDERR, 'Only '.count($weights)." files found in that run's log: is it a complete, green run?\n");
        exit(1);
    }
    $path = dirname(__DIR__).'/tests/shard-weights.php';
    $header = preg_replace('/return \[.*$/s', '', (string) file_get_contents($path));
    $body = '';
    foreach ($weights as $file => $seconds) {
        $body .= '    '.var_export($file, true).' => '.$seconds.",\n";
    }
    file_put_contents($path, $header."return [\n".$body."];\n");
    echo count($weights)." files weighed\n";
    exit(0);
}

if ($count < 1) {
    fwrite(STDERR, "usage: php tests/shard.php phpunit|browser <i> <N> | query-guard <dir> <N>\n");
    exit(2);
}

switch ($mode) {
    case 'phpunit':
        $files = TestShards::shard(['tests/Unit', 'tests/Feature'], (int) $first, $count);
        $root = dirname(__DIR__);
        file_put_contents($root.'/phpunit.shard.xml', TestShards::phpunitConfig((string) file_get_contents($root.'/phpunit.xml'), $files));
        echo 'Shard '.$first.' of '.$count.': '.count($files)." files\n";
        exit(0);

    case 'browser':
        $files = array_filter(TestShards::shard(['tests/Browser'], (int) $first, $count), fn ($file) => basename($file) !== 'ExampleTest.php');
        echo implode("\n", $files)."\n";
        exit(0);

    case 'query-guard':
        exit(QueryGuardExtension::mergeShards((string) $first, $count));
}

fwrite(STDERR, "unknown mode: {$mode}\n");
exit(2);
