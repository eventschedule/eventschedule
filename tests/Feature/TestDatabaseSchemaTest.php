<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\TestDatabase;

/**
 * Pins the two string rules that decide whether the test harness is safe.
 *
 * tests/TestDatabase.php hands each run its own schema and reclaims dead ones with DROP DATABASE,
 * and Tests\TestCase refuses to run against anything else. Both decisions are made by pattern
 * matching, and a pattern that is subtly wrong looks exactly like a pattern that is right - the
 * first version of schemaPattern() omitted its '%' at one call site and printed a GRANT that
 * applied cleanly and did nothing. So these assert against real MySQL LIKE semantics rather than
 * against a literal string.
 *
 * No RefreshDatabase on purpose: these evaluate SELECT expressions and must not migrate.
 */
class TestDatabaseSchemaTest extends TestCase
{
    private function likeMatches(string $name, string $pattern): bool
    {
        return (bool) DB::selectOne('SELECT ? LIKE ? AS matched', [$name, $pattern])->matched;
    }

    public function test_the_schema_pattern_matches_per_session_databases(): void
    {
        $pattern = TestDatabase::schemaPattern('eventschedule_test');

        $this->assertTrue(
            $this->likeMatches('eventschedule_test_1b36b820', $pattern),
            'A per-session schema must match, or the prune can never reclaim one.'
        );
        $this->assertTrue($this->likeMatches('eventschedule_test_ci1', $pattern));
    }

    public function test_the_schema_pattern_cannot_match_the_dev_or_shared_database(): void
    {
        $pattern = TestDatabase::schemaPattern('eventschedule_test');

        $this->assertFalse(
            $this->likeMatches('eventschedule', $pattern),
            'The dev database must be unreachable by DROP DATABASE.'
        );
        $this->assertFalse(
            $this->likeMatches('eventschedule_test', $pattern),
            'The shared schema is what CI and token-less runs use; the prune must not touch it.'
        );
    }

    public function test_the_schema_pattern_escapes_its_underscores(): void
    {
        $pattern = TestDatabase::schemaPattern('eventschedule_test');

        // Unescaped, '_' is a single-character wildcard in both LIKE and GRANT, which would widen
        // the pattern to any database whose name merely has the right shape.
        $this->assertFalse($this->likeMatches('eventscheduleXtestXabc', $pattern));
        $this->assertFalse($this->likeMatches('eventschedule-test-abc', $pattern));
    }

    public function test_the_guard_accepts_test_schemas_and_rejects_everything_else(): void
    {
        $this->assertTrue(TestDatabase::isDedicatedTestSchema('eventschedule_test'));
        $this->assertTrue(TestDatabase::isDedicatedTestSchema('eventschedule_test_1b36b820'));

        $this->assertFalse(TestDatabase::isDedicatedTestSchema('eventschedule'));
        $this->assertFalse(TestDatabase::isDedicatedTestSchema('eventschedule_production'));
        // Without the /D modifier, '$' also matches just before a trailing newline, so a value
        // carrying stray whitespace would be waved through as if it were clean.
        $this->assertFalse(
            TestDatabase::isDedicatedTestSchema("eventschedule_test\n"),
            'A trailing newline must not slip past the end anchor.'
        );
    }

    public function test_the_running_suite_is_actually_on_a_dedicated_schema(): void
    {
        // getDatabaseName() is the resolved connection, not the env var the guard reads, so this
        // is what catches the two diverging (DB_URL would do exactly that).
        $connected = DB::connection()->getDatabaseName();

        $this->assertSame(getenv('DB_DATABASE'), $connected);
        $this->assertTrue(TestDatabase::isDedicatedTestSchema($connected));
    }
}
