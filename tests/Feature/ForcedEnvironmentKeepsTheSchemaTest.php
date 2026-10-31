<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Depends;
use Tests\Feature\Concerns\ForcesEnvironment;
use Tests\TestCase;

/**
 * A test that rebuilds the app as another kind of install (ForcesEnvironment) leaves the schema as
 * it found it, so the test after it does not rebuild the schema. RefreshDatabase rebuilt it after
 * every such test because the test had rolled its transaction back by hand: about 36 seconds each
 * on CI, 7 of the suite's 45 minutes. leaveTestTransaction() steps back in before RefreshDatabase
 * asks. The two tests run in order (Depends). Mutation: roll the transaction back by hand again.
 */
class ForcedEnvironmentKeepsTheSchemaTest extends TestCase
{
    use ForcesEnvironment;
    use RefreshDatabase;

    private const EMAIL = 'forced-environment@example.test';

    private static int $buildsAfterTheRebuild = -1;

    public function test_a_test_rebuilds_the_app_as_hosted(): void
    {
        $this->leaveTestTransaction();
        $this->forceEnv('IS_HOSTED', 'true');
        $this->refreshApplication();
        $this->app['db']->connection()->beginTransaction();

        try {
            $this->assertTrue(config('app.hosted'));
            User::factory()->create(['email' => self::EMAIL]);
        } finally {
            $connection = $this->app['db']->connection();
            if ($connection->getPdo()->inTransaction()) {
                $connection->rollBack();
            }
            $connection->disconnect();
        }

        self::$buildsAfterTheRebuild = TestCase::$schemaBuilds;
    }

    #[Depends('test_a_test_rebuilds_the_app_as_hosted')]
    public function test_the_next_test_keeps_the_schema_and_finds_nothing_left_behind(): void
    {
        $this->assertSame(self::$buildsAfterTheRebuild, TestCase::$schemaBuilds, 'The test before this one made RefreshDatabase rebuild the schema.');
        $this->assertFalse(User::where('email', self::EMAIL)->exists(), 'The rebuilt app wrote past its own transaction.');
    }
}
