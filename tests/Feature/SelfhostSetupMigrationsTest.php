<?php

namespace Tests\Feature;

use App\Models\LegalDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Issue #133: the setup wizard ran every migration inside the one sign-up request, which a
 * shared host's request timeout cut off with a bare 500 and no .env. The wizard now builds the
 * database a slice per request through sign_up.migrate before it posts the form.
 */
class SelfhostSetupMigrationsTest extends TestCase
{
    use RefreshDatabase;

    private ?string $probeDir = null;

    protected function tearDown(): void
    {
        if ($this->probeDir) {
            array_map('unlink', glob($this->probeDir.'/*.php'));
            rmdir($this->probeDir);
        }

        LegalDocument::flush();

        parent::tearDown();
    }

    /**
     * A fresh selfhost install: a blank APP_URL is what selfhost_needs_setup() reads as one,
     * without asking the database (its memo is only for an install that has an APP_URL).
     */
    private function freshSelfhost(): void
    {
        config([
            'app.hosted' => false,
            'app.is_nexus' => false,
            'app.is_testing' => false,
            'app.url' => '',
        ]);
    }

    /**
     * The connection the test already has open, so the endpoint does not reconnect (which
     * would end RefreshDatabase's transaction).
     */
    private function databaseFields(): array
    {
        $mysql = config('database.connections.mysql');

        return [
            'database_host' => (string) $mysql['host'],
            'database_port' => (string) $mysql['port'],
            'database_name' => (string) $mysql['database'],
            'database_username' => (string) $mysql['username'],
            'database_password' => (string) $mysql['password'],
        ];
    }

    public function test_it_answers_only_while_the_install_needs_setup(): void
    {
        $this->postJson(route('sign_up.migrate'), $this->databaseFields())->assertNotFound();
    }

    public function test_it_asks_for_the_database_it_is_to_build(): void
    {
        $this->freshSelfhost();

        $this->postJson(route('sign_up.migrate'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['database_host', 'database_name', 'database_username', 'database_password']);
    }

    public function test_a_database_with_nothing_pending_is_done_at_once(): void
    {
        $this->freshSelfhost();

        $response = $this->postJson(route('sign_up.migrate'), $this->databaseFields())->assertOk();

        $this->assertTrue($response->json('done'));
        $this->assertSame($response->json('total'), $response->json('migrated'));
        $this->assertGreaterThan(300, $response->json('total'));
    }

    public function test_a_pending_migration_is_run_and_recorded(): void
    {
        $this->freshSelfhost();

        // Writes to the cache, not the schema: DDL would commit RefreshDatabase's transaction.
        $this->probeDir = sys_get_temp_dir().'/setup-slice-'.bin2hex(random_bytes(4));
        mkdir($this->probeDir);
        $name = '2099_01_01_000000_setup_slice_probe';
        file_put_contents($this->probeDir."/{$name}.php", <<<'PHP'
            <?php

            use Illuminate\Database\Migrations\Migration;

            return new class extends Migration
            {
                public function up(): void
                {
                    cache()->put('setup_slice_probe', true, 60);
                }
            };
            PHP);
        app('migrator')->path($this->probeDir);

        $response = $this->postJson(route('sign_up.migrate'), $this->databaseFields())->assertOk();

        $this->assertTrue($response->json('done'));
        $this->assertSame($response->json('total'), $response->json('migrated'));
        $this->assertTrue(Cache::get('setup_slice_probe'));
        $this->assertTrue(DB::table('migrations')->where('migration', $name)->exists());
    }

    public function test_the_wizard_page_does_not_ask_for_legal_documents_before_setup(): void
    {
        $this->freshSelfhost();
        LegalDocument::flush();

        $queries = 0;
        DB::listen(function ($query) use (&$queries) {
            if (str_contains($query->sql, 'legal_documents')) {
                $queries++;
            }
        });

        $this->assertSame([], LegalDocument::index());
        $this->assertSame(0, $queries);
    }
}
