<?php

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;

/**
 * Finds N+1 queries in whatever the test suite runs, one request, job or command at a time.
 *
 * The suite already requests most pages of the app thousands of times, so it is the cheapest place
 * to notice a page that asks the database once per row. Two rules, both counted inside one SCOPE
 * (a request, a queued job, an artisan command, or the test body around them):
 *
 *  - lazy: an Eloquent relation lazy loaded on two or more models of one result, from the same
 *    line. Laravel marks a model for this only when its query returned more than one row
 *    (Eloquent\Builder::hydrate), so a fixture of two rows is enough to see it. One access, as in
 *    `$events->first()->venue`, is not an N+1 and does not count.
 *  - repeat: the same SELECT more than REPEAT_LIMIT times in one request, job or command. That is
 *    the N+1 lazy loading cannot see: a `->count()` per row, a setting looked up per row, a
 *    `->load()` per row. Literal integer IN lists are folded first, because eager loads on integer
 *    keys write their ids into the SQL (Relation::whereInMethod) and every row would otherwise be
 *    a different string.
 *
 * A finding is keyed by rule, the file that asked, what it asked for and the controller, job or
 * command it ran under (`rule|origin|what|entry`), never by route: one N+1 in layouts/navigation
 * would otherwise be one entry per page, and keyed by the origin alone a listed shared helper
 * would hide every new page that loops over it. A key not listed in
 * tests/query-baseline.php fails the test that ran it. The total per request is only reported,
 * because it depends on what each test seeds (PageQueryScalingTest holds per-page numbers).
 *
 * Environment:
 *  - QUERY_GUARD_REPORT=1  record, never fail (the report is written at the end of the run)
 *  - QUERY_GUARD_WRITE=1   record, never fail, and ADD every key hit to the baseline (full run only)
 *  - QUERY_GUARD_STRICT=1  CI: a listed key a complete run never hit is reported (QueryGuardExtension)
 */
final class QueryGuard
{
    /** More executions than this of one SELECT in one scope is a repeat. */
    public const REPEAT_LIMIT = 10;

    /** Lazy loads of one relation, from one line, in one scope, that make an N+1. */
    public const LAZY_LIMIT = 2;

    private const BACKTRACE_DEPTH = 60;

    /** @var list<array<string, mixed>> */
    private static array $stack = [];

    private static int $nextId = 0;

    /** Unlisted findings of the running test. @var list<array<string, mixed>> */
    private static array $pending = [];

    /** Every key found this run: key => ['count' => max seen, 'seen' => [label => true], 'finding' => first]. */
    private static array $hits = [];

    /** label => [requests, max queries, total queries] */
    private static array $totals = [];

    /** Route names a test requested. @var array<string, true> */
    private static array $routesHit = [];

    /** Named GET routes of the app, read once. @var array<string, string>|null */
    private static ?array $getRoutes = null;

    private static ?array $baseline = null;

    /** compiled view path => blade path */
    private static array $views = [];

    private static ?string $basePath = null;

    /** QueryGuardTest's switch: true or false wins over the environment, null leaves it to it. */
    public static ?bool $enforce = null;

    public static function basePath(): string
    {
        return self::$basePath ??= dirname(__DIR__, 2);
    }

    public static function baselinePath(): string
    {
        return self::basePath().'/tests/query-baseline.php';
    }

    public static function enforcing(): bool
    {
        if (self::$enforce !== null) {
            return self::$enforce;
        }

        return ! self::flag('QUERY_GUARD_REPORT') && ! self::flag('QUERY_GUARD_WRITE');
    }

    public static function flag(string $name): bool
    {
        $value = $_SERVER[$name] ?? $_ENV[$name] ?? getenv($name);

        return in_array((string) $value, ['1', 'true'], true);
    }

    /** @return array<string, string> key => reason */
    public static function baseline(): array
    {
        if (self::$baseline === null) {
            $path = self::baselinePath();
            self::$baseline = is_file($path) ? (array) require $path : [];
        }

        return self::$baseline;
    }

    public static function setBaseline(?array $baseline): void
    {
        self::$baseline = $baseline;
    }

    // ---------------------------------------------------------------- scopes

    public static function beginTest(string $label): void
    {
        self::$stack = [];
        self::$pending = [];
        self::push('test', $label);
    }

    /**
     * Close every scope still open and hand back what the test found that is not listed.
     *
     * @return list<array<string, mixed>>
     */
    public static function endTest(): array
    {
        while (self::$stack) {
            self::close(array_pop(self::$stack));
        }

        $pending = self::$pending;
        self::$pending = [];

        return $pending;
    }

    public static function push(string $kind, string $label): int
    {
        $id = ++self::$nextId;
        self::$stack[] = [
            'id' => $id,
            'kind' => $kind,
            'label' => $label,
            'queries' => 0,
            'select' => [],
            'lazy' => [],
        ];

        return $id;
    }

    public static function relabel(int $id, string $label): void
    {
        foreach (self::$stack as $i => $scope) {
            if ($scope['id'] === $id) {
                self::$stack[$i]['label'] = $label;
            }
        }
    }

    /** Close the scope with this id and any opened inside it that never closed (a job that threw). */
    public static function pop(int $id): void
    {
        $at = null;
        foreach (self::$stack as $i => $scope) {
            if ($scope['id'] === $id) {
                $at = $i;
            }
        }

        if ($at === null) {
            return;
        }

        while (count(self::$stack) > $at) {
            self::close(array_pop(self::$stack));
        }
    }

    /** Close the innermost scope of a kind (jobs and commands are told apart by events, not ids). */
    public static function popKind(string $kind): void
    {
        for ($i = count(self::$stack) - 1; $i >= 0; $i--) {
            if (self::$stack[$i]['kind'] === $kind) {
                self::pop(self::$stack[$i]['id']);

                return;
            }
        }
    }

    /** How many scopes are open (the test's own is one). */
    public static function depth(): int
    {
        return count(self::$stack);
    }

    /** @return list<array<string, mixed>> unlisted findings recorded so far in this test */
    public static function pending(): array
    {
        return self::$pending;
    }

    /** @return list<array<string, mixed>> take the unlisted findings recorded since $from */
    public static function takePendingSince(int $from): array
    {
        $taken = array_slice(self::$pending, $from);
        self::$pending = array_slice(self::$pending, 0, $from);

        return $taken;
    }

    // ---------------------------------------------------------------- what is counted

    public static function query(QueryExecuted $event): void
    {
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return;
        }

        $scope = &self::$stack[$top];
        $scope['queries']++;

        // The test body seeds in loops; only what the app runs on its own is held to the rule.
        if ($scope['kind'] === 'test' || ! preg_match('/^\s*\(?\s*select\b/i', $event->sql)) {
            return;
        }

        $sql = self::normalize($event->sql);
        $entry = &$scope['select'][$sql];
        $entry ??= ['count' => 0, 'finding' => null, 'lazy' => false];
        $entry['count']++;

        if ($entry['count'] !== self::REPEAT_LIMIT + 1) {
            return;
        }

        $frames = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, self::BACKTRACE_DEPTH);

        // A lazy load is the lazy rule's to report, with the relation's name in it.
        foreach ($frames as $frame) {
            if (($frame['function'] ?? '') === 'getRelationshipFromMethod') {
                $entry['lazy'] = true;

                return;
            }
        }

        $origin = self::origin($frames);
        if ($origin === null) {
            return;
        }

        $entry['finding'] = [
            'rule' => 'repeat',
            'key' => 'repeat|'.$origin['file'].'|'.self::table($sql).'|'.self::entry($frames),
            'origin' => $origin['file'].($origin['line'] ? ':'.$origin['line'] : ''),
            'context' => self::context($frames),
            'what' => $sql,
        ];
    }

    public static function lazyLoad(Model $model, string $relation): void
    {
        // What Laravel's own handler skips: a model that is not stored, or was made in this request.
        if (! $model->exists || $model->wasRecentlyCreated) {
            return;
        }

        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return;
        }

        $frames = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, self::BACKTRACE_DEPTH);
        $origin = self::origin($frames);

        // Test code reading a relation of its own fixtures is not the app's N+1.
        if ($origin === null || (self::$stack[$top]['kind'] === 'test' && str_starts_with($origin['file'], 'tests/'))) {
            return;
        }

        $what = get_class($model).'::'.$relation;
        $count = $origin['file'].':'.$origin['line'].'|'.$what;
        $entry = &self::$stack[$top]['lazy'][$count];

        if ($entry === null) {
            $entry = [
                'count' => 0,
                'finding' => [
                    'rule' => 'lazy',
                    'key' => 'lazy|'.$origin['file'].'|'.$what.'|'.self::entry($frames),
                    'origin' => $origin['file'].($origin['line'] ? ':'.$origin['line'] : ''),
                    'context' => self::context($frames),
                    'what' => $what,
                ],
            ];
        }

        $entry['count']++;
    }

    public static function requested(?string $routeName): void
    {
        if ($routeName !== null && $routeName !== '') {
            self::$routesHit[$routeName] = true;
        }
    }

    /** @param  iterable<\Illuminate\Routing\Route>  $routes */
    public static function rememberRoutes(iterable $routes): void
    {
        if (self::$getRoutes !== null) {
            return;
        }

        self::$getRoutes = [];
        foreach ($routes as $route) {
            $name = $route->getName();
            if ($name && in_array('GET', $route->methods(), true)) {
                self::$getRoutes[$name] = $route->uri();
            }
        }
    }

    // ---------------------------------------------------------------- run-wide state for the extension

    public static function hits(): array
    {
        return self::$hits;
    }

    public static function totals(): array
    {
        return self::$totals;
    }

    /** @return array<string, string> named GET routes no test requested */
    public static function untestedRoutes(): array
    {
        return array_diff_key(self::$getRoutes ?? [], self::$routesHit);
    }

    // ---------------------------------------------------------------- helpers

    public static function normalize(string $sql): string
    {
        // `in (5, 6)`, `in (?, ?)` and `in (?)` are the same question.
        $sql = preg_replace('/\bin\s*\(\s*(?:\?|-?\d+)(?:\s*,\s*(?:\?|-?\d+))*\s*\)/i', 'in (?)', $sql) ?? $sql;

        return preg_replace('/\s+/', ' ', trim($sql)) ?? $sql;
    }

    private static function table(string $sql): string
    {
        return preg_match('/\bfrom\s+[`"]?([A-Za-z0-9_]+)/i', $sql, $m) ? $m[1] : 'unknown';
    }

    /**
     * The innermost frame that is ours: not vendor/, not this file. A compiled view is named by
     * the Blade file it came from.
     *
     * @return array{file: string, line: int}|null
     */
    private static function origin(array $frames): ?array
    {
        foreach ($frames as $frame) {
            $file = $frame['file'] ?? null;
            if ($file === null || $file === __FILE__ || self::isVendor($file) || $file === self::basePath().'/tests/TestCase.php') {
                continue;
            }

            $view = self::viewSource($file);

            return ['file' => $view ?? self::relative($file), 'line' => $view ? 0 : (int) ($frame['line'] ?? 0)];
        }

        return null;
    }

    /**
     * Where the request was when it asked: the outermost controller, command, job, mail or
     * listener, and the outermost view. The origin is often a shared helper; this is the page.
     */
    private static function context(array $frames): string
    {
        $entry = null;
        $view = null;

        foreach ($frames as $frame) {
            $file = $frame['file'] ?? null;
            if ($file === null || self::isVendor($file)) {
                continue;
            }

            $relative = self::relative($file);
            if (preg_match('#^app/(Http/Controllers|Console|Jobs|Mail|Notifications|Listeners)/#', $relative)) {
                $entry = $relative;
            } elseif ($blade = self::viewSource($file)) {
                $view = $blade;
            }
        }

        return implode(' > ', array_filter([$entry, $view]));
    }

    /**
     * The controller, command, job, mail, notification or listener the finding ran under, or '-'.
     * Part of the key because the origin is often a shared helper (Role::isPro() reads
     * subscriptions for every page that asks it): keyed by the helper alone, one listed page
     * would hide every new page that loops over it.
     */
    private static function entry(array $frames): string
    {
        $entry = '-';
        foreach ($frames as $frame) {
            $file = $frame['file'] ?? null;
            if ($file !== null && ! self::isVendor($file)
                && preg_match('#^app/(Http/Controllers|Console|Jobs|Mail|Notifications|Listeners)/#', $relative = self::relative($file))) {
                $entry = $relative;
            }
        }

        return $entry;
    }

    private static function isVendor(string $file): bool
    {
        return str_contains($file, '/vendor/');
    }

    private static function relative(string $file): string
    {
        $base = self::basePath().'/';

        return str_starts_with($file, $base) ? substr($file, strlen($base)) : $file;
    }

    /** The Blade file a compiled view came from (BladeCompiler writes its path at the end), or null. */
    public static function viewSource(string $file): ?string
    {
        if (! str_contains($file, '/storage/framework/views/')) {
            return null;
        }

        if (! array_key_exists($file, self::$views)) {
            $tail = '';
            if (is_file($file) && ($handle = @fopen($file, 'r'))) {
                fseek($handle, max(0, filesize($file) - 1024));
                $tail = (string) stream_get_contents($handle);
                fclose($handle);
            }

            self::$views[$file] = preg_match('#/\*\*PATH (.+?) ENDPATH\*\*/#', $tail, $m) ? self::relative($m[1]) : self::relative($file);
        }

        return self::$views[$file];
    }

    /** Turn a closed scope's counts into findings. */
    private static function close(array $scope): void
    {
        $label = $scope['label'];

        if ($scope['kind'] !== 'test') {
            $total = &self::$totals[$label];
            $total ??= [0, 0, 0];
            $total[0]++;
            $total[1] = max($total[1], $scope['queries']);
            $total[2] += $scope['queries'];
        }

        $findings = [];

        foreach ($scope['lazy'] as $entry) {
            if ($entry['count'] >= self::LAZY_LIMIT) {
                $findings[] = $entry['finding'] + ['count' => $entry['count']];
            }
        }

        foreach ($scope['select'] as $entry) {
            if ($entry['finding'] !== null) {
                $findings[] = $entry['finding'] + ['count' => $entry['count']];
            }
        }

        foreach ($findings as $finding) {
            $finding['scope'] = $label;
            $key = $finding['key'];

            $hit = &self::$hits[$key];
            $hit ??= ['count' => 0, 'seen' => [], 'finding' => $finding];
            $hit['count'] = max($hit['count'], $finding['count']);
            if (count($hit['seen']) < 20) {
                $hit['seen'][$label] = true;
            }
            unset($hit);

            if (! array_key_exists($key, self::baseline())) {
                self::$pending[] = $finding;
            }
        }
    }

    /** The message a failing test prints. */
    public static function describe(array $findings): string
    {
        $lines = ['N+1 queries found (tests/Support/QueryGuard.php):', ''];
        $seen = [];

        foreach ($findings as $finding) {
            if (isset($seen[$finding['key']])) {
                continue;
            }
            $seen[$finding['key']] = true;

            if ($finding['rule'] === 'lazy') {
                $lines[] = "- {$finding['what']} lazy loaded on {$finding['count']} models of one result";
            } else {
                $lines[] = "- the same query ran {$finding['count']} times: ".mb_strimwidth($finding['what'], 0, 220, '...');
            }
            $lines[] = "    at   {$finding['origin']}";
            if ($finding['context'] !== '') {
                $lines[] = "    via  {$finding['context']}";
            }
            $lines[] = "    in   {$finding['scope']}";
            $lines[] = '    fix: '.($finding['rule'] === 'lazy'
                ? 'eager load it where the list is queried (->with() / ->load() on the collection)'
                : 'ask once for every row (a whereIn, a withCount, or a value computed before the loop)');
            $lines[] = '    or, only if it is deliberate, list it in tests/query-baseline.php with the reason:';
            $lines[] = '      '.var_export($finding['key'], true)." => 'why this cannot be one query',";
            $lines[] = '';
        }

        return implode("\n", $lines);
    }
}
