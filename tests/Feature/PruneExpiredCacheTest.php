<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * app:prune-cache - the only thing that deletes an expired row from the database cache whose key
 * is never read again. See the command's docblock for why that grew the hosted MySQL.
 */
class PruneExpiredCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // phpunit.xml runs the suite on the array store; this is about the database one.
        config(['cache.default' => 'database']);
        $this->freezeTime();
    }

    private function row(string $key, int $expiration): array
    {
        return ['key' => $key, 'value' => serialize(1), 'expiration' => $expiration];
    }

    public function test_it_deletes_expired_rows_and_keeps_live_ones(): void
    {
        $now = now()->getTimestamp();

        DB::table('cache')->insert([
            $this->row('expired', $now - 10),
            // Expired by DatabaseStore's own test (expiration > now is live), so it must go too.
            $this->row('expiring_now', $now),
            $this->row('live', $now + 10),
            // What rememberForever() writes: ten years out.
            $this->row('forever', $now + 315360000),
        ]);
        DB::table('cache_locks')->insert([
            ['key' => 'lock_expired', 'owner' => 'a', 'expiration' => $now - 10],
            ['key' => 'lock_live', 'owner' => 'b', 'expiration' => $now + 10],
        ]);

        $this->artisan('app:prune-cache')
            ->expectsOutput('Pruned 2 expired cache rows and 1 expired locks.')
            ->assertSuccessful();

        $this->assertEqualsCanonicalizing(['live', 'forever'], DB::table('cache')->pluck('key')->all());
        $this->assertSame(['lock_live'], DB::table('cache_locks')->pluck('key')->all());
    }

    public function test_it_works_through_more_than_one_batch(): void
    {
        $now = now()->getTimestamp();

        DB::table('cache')->insert(array_map(fn ($i) => $this->row("expired_{$i}", $now - 60), range(1, 1500)));
        DB::table('cache')->insert($this->row('live', $now + 60));

        $this->artisan('app:prune-cache')->assertSuccessful();

        $this->assertSame(['live'], DB::table('cache')->pluck('key')->all());
    }

    /** Through the real store, so the test cannot drift from how Laravel writes the column. */
    public function test_it_prunes_what_the_database_store_wrote(): void
    {
        Cache::store('database')->put('short', 'x', 60);
        Cache::store('database')->forever('kept', 'y');

        $this->travel(61)->seconds();
        $this->artisan('app:prune-cache')->assertSuccessful();

        $this->assertSame(1, DB::table('cache')->count());
        $this->assertSame('y', Cache::store('database')->get('kept'));
    }

    public function test_it_does_nothing_when_the_cache_is_not_the_database(): void
    {
        config(['cache.default' => 'array']);
        DB::table('cache')->insert($this->row('expired', now()->getTimestamp() - 10));

        $this->artisan('app:prune-cache')
            ->expectsOutput('Skipping: the [array] cache store does not use the database.')
            ->assertSuccessful();

        $this->assertSame(1, DB::table('cache')->count());
    }

    public function test_the_columns_it_filters_on_are_indexed(): void
    {
        $this->assertTrue(Schema::hasIndex('cache', ['expiration']));
        $this->assertTrue(Schema::hasIndex('cache_locks', ['expiration']));
        $this->assertTrue(Schema::hasIndex('failed_jobs', ['failed_at']));
    }
}
