<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\AssertionFailedError;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\Support\QueryGuard;
use Tests\Support\QueryGuardExtension;
use Tests\TestCase;

/**
 * The guard that finds N+1 queries in every request the suite makes (tests/Support/QueryGuard.php).
 *
 * Each case drives a route of its own through the real HTTP kernel, so what is held is the whole
 * path: the listener, the request's scope, the rule and the key a finding is listed under.
 */
class QueryGuardTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const PREFIX = '/__query-guard/a/b/c';

    protected function setUp(): void
    {
        parent::setUp();

        // Findings are read back from the guard instead of failing the test that made them.
        QueryGuard::$enforce = false;
        $this->routes();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        QueryGuard::$enforce = null;
        QueryGuard::setBaseline(null);
    }

    private function routes(): void
    {
        $ids = fn () => Event::query()->orderBy('id')->pluck('id')->all();

        Route::get(self::PREFIX.'/lazy', fn () => Event::query()->whereIn('id', $ids())->get()->map(fn ($e) => $e->creatorRole?->name)->implode(','));
        Route::get(self::PREFIX.'/eager', fn () => Event::query()->whereIn('id', $ids())->with('creatorRole')->get()->map(fn ($e) => $e->creatorRole?->name)->implode(','));
        Route::get(self::PREFIX.'/once', fn () => (string) Event::query()->whereIn('id', $ids())->get()->first()->creatorRole?->name);
        Route::get(self::PREFIX.'/repeat', function () {
            foreach (range(1, QueryGuard::REPEAT_LIMIT + 2) as $n) {
                DB::table('events')->whereIntegerInRaw('id', [$n])->get();
            }

            return 'ok';
        });
        Route::get(self::PREFIX.'/under', function () {
            foreach (range(1, QueryGuard::REPEAT_LIMIT) as $n) {
                DB::table('events')->whereIntegerInRaw('id', [$n])->get();
            }

            return 'ok';
        });
        Route::get(self::PREFIX.'/redirect', fn () => redirect(self::PREFIX.'/lazy'));
        Route::get(self::PREFIX.'/throws', function () {
            DB::select('select 1');

            throw new \RuntimeException('thrown on purpose');
        });
        Route::get(self::PREFIX.'/view', function () {
            View::addLocation(base_path('tests/fixtures/views'));

            return view('query-guard-list', ['events' => Event::query()->get()]);
        });
    }

    private function threeEvents(): void
    {
        $owner = $this->createOwner();
        foreach (range(1, 3) as $n) {
            $this->createEvent($this->createRole($owner, 'venue', ['name' => 'Room '.$n]));
        }
    }

    /** @return array<string, array<string, mixed>> the unlisted findings so far, by key */
    private function found(): array
    {
        return collect(QueryGuard::pending())->keyBy('key')->all();
    }

    private const LAZY_KEY = 'lazy|tests/Feature/QueryGuardTest.php|App\Models\Event::creatorRole|-';

    /**
     * A relation read on each row of a list is found, named by the file and the relation, and
     * eager loading it clears it.
     * Mutation: return early from QueryGuard::lazyLoad(), or drop Model::preventLazyLoading() from TestCase::setUp().
     */
    public function test_a_relation_read_per_row_is_found_and_eager_loading_clears_it(): void
    {
        $this->threeEvents();

        $this->get(self::PREFIX.'/eager')->assertOk();
        $this->assertSame([], $this->found(), 'eager loaded: nothing to report');

        $this->get(self::PREFIX.'/lazy')->assertOk();
        $found = $this->found();
        $this->assertArrayHasKey(self::LAZY_KEY, $found);
        $this->assertSame(3, $found[self::LAZY_KEY]['count']);
        $this->assertStringStartsWith('GET /__query-guard', $found[self::LAZY_KEY]['scope']);
    }

    /**
     * Reading one row's relation is not an N+1, even though the row came from a list.
     * Mutation: set QueryGuard::LAZY_LIMIT to 1.
     */
    public function test_one_read_from_a_list_is_not_an_n_plus_one(): void
    {
        $this->threeEvents();

        $this->get(self::PREFIX.'/once')->assertOk();

        $this->assertSame([], $this->found());
    }

    /**
     * The same SELECT asked once per row is found, though each row's id is written into the SQL,
     * and up to the limit it is not.
     * Mutation: drop the IN-list folding from QueryGuard::normalize().
     */
    public function test_one_select_per_row_is_found_even_with_its_ids_inlined(): void
    {
        $this->get(self::PREFIX.'/under')->assertOk();
        $this->assertSame([], $this->found(), 'at the limit: fine');

        $this->get(self::PREFIX.'/repeat')->assertOk();
        $found = $this->found();
        $this->assertArrayHasKey('repeat|tests/Feature/QueryGuardTest.php|events|-', $found);
        $this->assertSame(QueryGuard::REPEAT_LIMIT + 2, $found['repeat|tests/Feature/QueryGuardTest.php|events|-']['count']);

        $this->assertSame(
            QueryGuard::normalize('select * from `events` where `id` in (5, 6)'),
            QueryGuard::normalize('select * from `events` where `id` in (?)'),
        );
    }

    /**
     * An unlisted finding fails the test that made the request, with the line that would list it,
     * and a listed one does not.
     * Mutation: drop the takePendingSince() check from TestCase::call().
     */
    public function test_an_unlisted_finding_fails_the_request_and_a_listed_one_does_not(): void
    {
        $this->threeEvents();
        QueryGuard::$enforce = true;
        QueryGuard::setBaseline([]);

        $message = null;
        try {
            $this->get(self::PREFIX.'/lazy');
        } catch (AssertionFailedError $e) {
            $message = $e->getMessage();
        }

        $this->assertNotNull($message, 'an unlisted N+1 fails the request');
        $this->assertStringContainsString('App\Models\Event::creatorRole lazy loaded on 3 models', $message);
        $this->assertStringContainsString(var_export(self::LAZY_KEY, true)." => 'why this cannot be one query',", $message);

        QueryGuard::setBaseline([self::LAZY_KEY => 'held by QueryGuardTest']);
        $this->get(self::PREFIX.'/lazy')->assertOk();
        QueryGuard::$enforce = false;
    }

    /**
     * A redirect followed inside a request, and a request that throws, each leave the scopes as
     * they found them, so nothing the test does next is counted as a request's.
     * Mutation: move QueryGuard::pop() out of the `finally` in TestCase::call().
     */
    public function test_redirects_and_exceptions_close_their_scopes(): void
    {
        $this->threeEvents();
        $depth = QueryGuard::depth();

        $this->followingRedirects()->get(self::PREFIX.'/redirect')->assertOk();
        $this->assertSame($depth, QueryGuard::depth());
        $this->assertArrayHasKey(self::LAZY_KEY, $this->found(), 'the redirected request was measured');

        $this->followRedirects = false;
        $this->withoutExceptionHandling();
        try {
            $this->get(self::PREFIX.'/throws');
            $this->fail('the route throws');
        } catch (\RuntimeException) {
        }
        $this->assertSame($depth, QueryGuard::depth());
    }

    /**
     * A test that rebuilds the app (ForcesEnvironment) is still measured after the rebuild.
     * Mutation: register the listener in setUp() instead of refreshApplication().
     */
    public function test_the_guard_still_listens_after_the_app_is_rebuilt(): void
    {
        $this->refreshApplication();
        $this->routes();

        $this->get(self::PREFIX.'/repeat')->assertOk();

        $this->assertArrayHasKey('repeat|tests/Feature/QueryGuardTest.php|events|-', $this->found());
    }

    /**
     * A finding in a view is named by its Blade file, not by the compiled copy.
     * Mutation: return null from QueryGuard::viewSource().
     */
    public function test_a_finding_in_a_view_names_the_blade_file(): void
    {
        $this->threeEvents();

        $this->get(self::PREFIX.'/view')->assertOk();

        $this->assertArrayHasKey('lazy|tests/fixtures/views/query-guard-list.blade.php|App\Models\Event::creatorRole|-', $this->found());
    }

    /**
     * What a run says about the listed keys: only a complete run on CI judges; a key nobody hit
     * fails when its file changed in the push and is a warning otherwise, or when the change is
     * unknown.
     * Mutation: return every unhit key as `fail` from QueryGuardExtension::judge().
     */
    public function test_the_ratchet_fails_only_a_fixed_key_whose_file_changed(): void
    {
        $listed = ['lazy|app/A.php|X::y|-', 'lazy|app/B.php|X::z|app/Http/Controllers/D.php', 'repeat|app/C.php|events|-'];
        $hit = ['repeat|app/C.php|events|-'];

        $this->assertSame(['fail' => [], 'warn' => []], QueryGuardExtension::judge($listed, $hit, false, true, ['app/A.php']), 'a partial run judges nothing');
        $this->assertSame(['fail' => [], 'warn' => []], QueryGuardExtension::judge($listed, $hit, true, false, ['app/A.php']), 'off CI it judges nothing');
        $this->assertSame(
            ['fail' => ['lazy|app/A.php|X::y|-'], 'warn' => ['lazy|app/B.php|X::z|app/Http/Controllers/D.php']],
            QueryGuardExtension::judge($listed, $hit, true, true, ['app/A.php', 'app/C.php']),
        );
        $this->assertSame(
            ['fail' => ['lazy|app/B.php|X::z|app/Http/Controllers/D.php'], 'warn' => ['lazy|app/A.php|X::y|-']],
            QueryGuardExtension::judge($listed, $hit, true, true, ['app/Http/Controllers/D.php']),
            'a change to the controller it ran under counts too',
        );
        $this->assertSame(
            ['fail' => [], 'warn' => ['lazy|app/A.php|X::y|-', 'lazy|app/B.php|X::z|app/Http/Controllers/D.php']],
            QueryGuardExtension::judge($listed, $hit, true, true, null),
            'without a base to compare with, nothing fails',
        );
    }

    /**
     * Every key in the baseline is one the guard can write: a rule, a file that exists, and what
     * was asked for. A key that names a deleted file can never be hit again.
     * Mutation: add 'lazy|app/Nowhere.php|X::y|-' to tests/query-baseline.php.
     */
    public function test_every_listed_key_names_a_file_that_exists(): void
    {
        QueryGuard::setBaseline(null);
        $this->assertIsArray(require QueryGuard::baselinePath());

        foreach (QueryGuard::baseline() as $key => $reason) {
            $parts = explode('|', $key);
            $this->assertCount(4, $parts, $key);
            $this->assertContains($parts[0], ['lazy', 'repeat'], $key);
            $this->assertFileExists(base_path($parts[1]), $key);
            $this->assertTrue($parts[3] === '-' || is_file(base_path($parts[3])), $key.': the entry is a file or -');
            $this->assertStringStartsNotWith('tests/', $parts[1], 'a test\'s own fixture is never listed: '.$key);
            $this->assertIsString($reason);
            $this->assertNotSame('', trim($reason), $key.' needs a reason');
        }
    }

    /**
     * CI's suite runs strict, so a fixed N+1 cannot stay on the list unnoticed. Read from the
     * environment the run actually has, not from the workflow's text.
     * Mutation: drop QUERY_GUARD_STRICT from the test step in .github/workflows/test.yml.
     */
    public function test_ci_runs_the_guard_strict(): void
    {
        if (getenv('GITHUB_ACTIONS') !== 'true') {
            $this->markTestSkipped('Asserted on CI only.');
        }

        $this->assertTrue(QueryGuard::flag('QUERY_GUARD_STRICT'), 'QUERY_GUARD_STRICT=1 is missing from the test step in .github/workflows/test.yml');
        $this->assertTrue(QueryGuard::enforcing(), 'CI must not run the guard in report or write mode');
    }
}
