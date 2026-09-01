<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The two secret-gated cron endpoints, /translate_data and /release_tickets.
 *
 * They are reachable by anyone, so the guard has to survive whatever the query string parses to -
 * not just a wrong string.
 */
class CronEndpointSecurityTest extends TestCase
{
    use RefreshDatabase;

    public static function endpoints(): array
    {
        return [['/translate_data'], ['/release_tickets']];
    }

    /**
     * request()->get() returns whatever the query string parsed to, so ?secret[]=x yields an
     * ARRAY. An array is truthy, so it passed the emptiness check and reached hash_equals(), which
     * type-errors on a non-string - an uncaught 500 for any anonymous caller, plus an error-tracker
     * event per probe. Exactly the shape of a bug this codebase has been bitten by before.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('endpoints')]
    public function test_an_array_secret_is_rejected_rather_than_fatal(string $path): void
    {
        config(['app.cron_secret' => 'the-real-secret']);

        $this->withoutExceptionHandling([])
            ->get($path.'?secret[]=x')
            ->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('endpoints')]
    public function test_a_wrong_secret_is_rejected(string $path): void
    {
        config(['app.cron_secret' => 'the-real-secret']);

        $this->get($path.'?secret=nope')->assertStatus(403);
    }

    /** An unset server secret fails closed on BOTH endpoints, so a misconfigured install is not wide open. */
    #[\PHPUnit\Framework\Attributes\DataProvider('endpoints')]
    public function test_an_unset_server_secret_rejects_everything(string $path): void
    {
        config(['app.cron_secret' => '']);

        $this->get($path.'?secret=anything')->assertStatus(403);
        $this->get($path)->assertStatus(403);
    }
}
