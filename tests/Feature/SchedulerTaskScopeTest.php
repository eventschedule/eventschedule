<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SchedulerHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The Scheduler card on /admin/queue leaves out tasks that do nothing on this kind of install, and
 * SchedulerHealth::ONLY_ON is the list it decides by.
 *
 * That list is hand-maintained beside the gates it describes, which live in two places: an `if`
 * around the Artisan::call in routes/console.php, or an early return at the top of the command.
 * These tests tie it to both, the same source-text way CronRailSyncTest ties the two cron rails
 * together. A task gated in either place but missing from the list would show a healthy "ran X ago"
 * on installs where it never does anything; a task listed under the wrong kind would vanish from
 * the very installs it runs on.
 *
 * WHAT THIS CANNOT SEE: a gate that is not the closure's first statement, or not a plain
 * `if ([!] config('app.hosted'|'app.is_nexus'))`; and for the command-side check, a gate anywhere
 * but literally in the command's own source.
 */
class SchedulerTaskScopeTest extends TestCase
{
    use RefreshDatabase;

    /** What an early return in a command looks like for each kind: it bails on the other kinds. */
    private const COMMAND_GATES = [
        'hosted' => "if (! config('app.hosted'))",
        'not_hosted' => "if (config('app.hosted'))",
        'nexus' => "if (! config('app.is_nexus'))",
        'not_nexus' => "if (config('app.is_nexus'))",
    ];

    public function test_every_listed_task_is_scheduled(): void
    {
        $names = array_map(fn ($event) => $event->description, SchedulerHealth::events());

        foreach (array_keys(SchedulerHealth::ONLY_ON) as $name) {
            $this->assertContains($name, $names, "{$name} is in SchedulerHealth::ONLY_ON but not in routes/console.php");
        }
    }

    public function test_every_install_gated_closure_is_listed_under_its_kind(): void
    {
        $gated = $this->consoleGates();

        $this->assertNotEmpty($gated, 'fixture: routes/console.php has hosted-gated closures, so the parser found none');

        foreach ($gated as $name => $kind) {
            $this->assertSame(
                $kind,
                SchedulerHealth::ONLY_ON[$name] ?? null,
                "{$name} is gated in routes/console.php, so SchedulerHealth::ONLY_ON must list it as '{$kind}'"
            );
        }
    }

    public function test_every_task_listed_without_a_console_gate_is_gated_in_its_command(): void
    {
        $gated = $this->consoleGates();

        foreach (SchedulerHealth::ONLY_ON as $name => $kind) {
            if (isset($gated[$name])) {
                continue;
            }

            $source = $this->commandSource($this->commandFor($name));

            $this->assertStringContainsString(
                self::COMMAND_GATES[$kind],
                $source,
                "{$name} is listed as '{$kind}' in SchedulerHealth::ONLY_ON, but neither routes/console.php nor its command gates it that way"
            );
        }
    }

    public function test_the_queue_page_hides_tasks_that_do_nothing_here(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->forceFill(['is_admin' => true])->save();
        $session = ['admin_password_confirmed_at' => now()->timestamp];

        // A live cron rail: while the scheduler looks stalled the card lists no tasks at all.
        Cache::put('scheduler.last_run_at', now()->timestamp, now()->addDay());
        Cache::put('scheduler.last_run_at.cron', now()->timestamp, now()->addDays(7));

        config(['app.hosted' => false, 'app.is_nexus' => false]);
        $this->withSession($session)->actingAs($admin)->get('/admin/queue')
            ->assertOk()
            ->assertSee('>process-queue<', false)
            ->assertSee('>app-check-version<', false)
            ->assertDontSee('>app-setup-demo<', false)
            ->assertDontSee('>federation-maintain<', false);

        config(['app.hosted' => true, 'app.is_nexus' => true]);
        $this->withSession($session)->actingAs($admin)->get('/admin/queue')
            ->assertOk()
            ->assertSee('>app-setup-demo<', false)
            ->assertSee('>federation-maintain<', false)
            ->assertDontSee('>app-check-version<', false);
    }

    /**
     * Schedule name => kind, for every routes/console.php closure whose first statement is an
     * install-kind `if`.
     *
     * @return array<string, string>
     */
    private function consoleGates(): array
    {
        $gates = [];

        foreach (explode('Schedule::call(', file_get_contents(base_path('routes/console.php'))) as $chunk) {
            // The first ->name() in a chunk is this entry's; the chunk then runs on into the next
            // entry's comment block, which never names anything.
            if (! preg_match("/->name\\('([^']+)'\\)/", $chunk, $name)) {
                continue;
            }

            if (preg_match("/^function \\(\\) \\{\\s*if \\((!\\s*)?config\\('app\\.(hosted|is_nexus)'\\)\\) \\{/", $chunk, $gate)) {
                $kind = $gate[2] === 'hosted' ? 'hosted' : 'nexus';
                $gates[$name[1]] = trim($gate[1]) === '!' ? 'not_'.$kind : $kind;
            }
        }

        return $gates;
    }

    /** The Artisan command a scheduled task runs, read from its routes/console.php closure. */
    private function commandFor(string $name): string
    {
        foreach (explode('Schedule::call(', file_get_contents(base_path('routes/console.php'))) as $chunk) {
            if (preg_match("/->name\\('([^']+)'\\)/", $chunk, $match) && $match[1] === $name
                && preg_match("/Artisan::call\\('([^']+)'/", $chunk, $command)) {
                return $command[1];
            }
        }

        $this->fail("{$name} has no Artisan::call in routes/console.php");
    }

    private function commandSource(string $command): string
    {
        foreach (glob(app_path('Console/Commands/*.php')) as $file) {
            $source = file_get_contents($file);

            if (preg_match("/\\\$signature = '".preg_quote($command, '/')."[\\s']/", $source)) {
                return $source;
            }
        }

        $this->fail("No command class has the signature {$command}");
    }
}
