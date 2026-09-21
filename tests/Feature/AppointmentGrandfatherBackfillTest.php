<?php

namespace Tests\Feature;

use App\Models\AppointmentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The one-time amnesty behind appointment_types.paid_grandfathered_at.
 *
 * Like the ticket backfill this runs exactly once, on deploy, against a production database nobody
 * can reach from a dev machine, and it cannot be re-run: a type it fails to stamp silently stops
 * taking bookings, and one it stamps wrongly charges for ever on a free plan. So the predicate gets
 * its own test.
 *
 * Deliberately WIDER than the ticket rule, which requires evidence of a real paid sale. The free
 * plan allows exactly one appointment type, so the affected set is at most one row per free
 * schedule, and the marketing site had been promising that paid bookings worked on any plan. Any
 * ACTIVE priced type on a non-Pro schedule is stamped, booked or not.
 *
 * Calls the migration's backfill() directly rather than restating the query, for the same reason
 * the ticket test does: a test that re-implemented the filter would pass whatever the migration
 * actually does.
 */
class AppointmentGrandfatherBackfillTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function runBackfill(): void
    {
        // Globbed, not hardcoded: CLAUDE.md's "use today's date" rule makes a rename likely while
        // this sits unshipped, and a stale `require` is a FATAL that kills the whole PHPUnit run.
        $file = collect(File::glob(database_path('migrations/*_add_paid_grandfathered_at_to_appointment_types.php')))->first();

        $this->assertNotNull($file, 'the grandfather migration is missing from database/migrations');

        $migration = require $file;

        // RefreshDatabase has already run up(), so the column exists; only the stamping is re-run.
        AppointmentType::query()->update(['paid_grandfathered_at' => null]);
        $migration->backfill();
    }

    private function freeRole()
    {
        return $this->createRole($this->createOwner(), 'talent', [
            'timezone' => 'America/New_York',
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'trial_ends_at' => null,
        ]);
    }

    public function test_an_active_priced_type_on_a_free_schedule_is_stamped(): void
    {
        config(['app.hosted' => true]);

        $role = $this->freeRole();
        $type = $this->createAppointmentType($role, ['price' => 50, 'currency_code' => 'USD']);

        $this->runBackfill();

        $this->assertNotNull($type->fresh()->paid_grandfathered_at);
        $this->assertTrue($type->fresh()->canTakePayment(), 'so it keeps charging');
    }

    public function test_a_free_type_is_never_stamped(): void
    {
        config(['app.hosted' => true]);

        $role = $this->freeRole();
        $type = $this->createAppointmentType($role, ['price' => 0]);

        $this->runBackfill();

        $this->assertNull($type->fresh()->paid_grandfathered_at, 'a free type has nothing to grandfather');
    }

    /**
     * An inactive type takes no bookings, so it has no promise to keep. Stamping it would hand out
     * the amnesty for free to anyone who had a paused draft lying around.
     */
    public function test_an_inactive_priced_type_is_not_stamped(): void
    {
        config(['app.hosted' => true]);

        $role = $this->freeRole();
        $type = $this->createAppointmentType($role, ['price' => 50, 'currency_code' => 'USD', 'is_active' => false]);

        $this->runBackfill();

        $this->assertNull($type->fresh()->paid_grandfathered_at);
    }

    /**
     * A Pro schedule needs no stamp, and giving it one would survive a later downgrade it never
     * paid through - a permanent free pass earned by being Pro on one particular day.
     */
    public function test_a_pro_schedules_priced_type_is_not_stamped(): void
    {
        config(['app.hosted' => true]);

        // createRole() defaults to enterprise, which isPro() answers true for.
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['price' => 50, 'currency_code' => 'USD']);

        $this->assertTrue($role->fresh()->isPro(), 'sanity check: the fixture is Pro');

        $this->runBackfill();

        $this->assertNull($type->fresh()->paid_grandfathered_at);
        $this->assertTrue($type->fresh()->canTakePayment(), 'it charges because it is Pro, not because of a stamp');
    }

    public function test_the_backfill_is_idempotent(): void
    {
        config(['app.hosted' => true]);

        $role = $this->freeRole();
        $type = $this->createAppointmentType($role, ['price' => 50, 'currency_code' => 'USD']);

        $this->runBackfill();
        $first = $type->fresh()->paid_grandfathered_at;

        // Re-running must not move an existing stamp: whereNull skips a row already amnestied.
        $migration = require collect(File::glob(database_path('migrations/*_add_paid_grandfathered_at_to_appointment_types.php')))->first();
        $migration->backfill();

        $this->assertEquals($first, $type->fresh()->paid_grandfathered_at);
    }
}
