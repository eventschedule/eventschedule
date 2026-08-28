<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * app:extend-plans pushes yearly plans out another year. It has no plan_source filter and no
 * subscription check, and on this install nearly every yearly plan is an admin grant - so a
 * single run was enough to silently hand back everything app:wind-down-comped-plans had just
 * done. The owner gets told their plan is ending, and then it is not.
 */
class ExtendPlansTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.hosted' => true]);
    }

    private function yearly(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', array_merge([
            'plan_type' => 'pro',
            'plan_term' => 'year',
            'plan_expires' => now()->addDays(30)->format('Y-m-d'),
            'trial_ends_at' => null,
        ], $attrs));
    }

    private function extend(): void
    {
        $this->artisan('app:extend-plans')->assertExitCode(0);
    }

    /** The ordinary case is unchanged: a genuine yearly plan still gets its year. */
    public function test_it_still_extends_an_ordinary_yearly_plan(): void
    {
        $role = $this->yearly(['plan_source' => null]);
        $expected = now()->addDays(30)->addYear()->format('Y-m-d');

        $this->extend();

        $this->assertSame($expected, $role->fresh()->plan_expires);
    }

    /**
     * The guard. A comped role that the wind-down has put on a dated trial must keep that date:
     * its owner has already been emailed that the plan ends.
     */
    public function test_it_refuses_to_undo_a_wind_down(): void
    {
        $role = $this->yearly([
            'plan_source' => 'admin',
            'trial_ends_at' => now()->addDays(30),
        ]);
        $original = $role->plan_expires;

        $this->extend();

        $this->assertSame($original, $role->fresh()->plan_expires,
            'extending a wound-down plan contradicts the email its owner already received');
    }

    /**
     * The residual gap, pinned so it is a known limit rather than a surprise: a DORMANT
     * wind-down moves plan_expires but sets no trial_ends_at, so nothing distinguishes it from
     * any other admin grant with a near expiry. This command still extends it. The command warns
     * about the count; the operational rule is to not run it after a wind-down.
     */
    public function test_a_dormant_wind_down_is_still_extended_and_warned_about(): void
    {
        $role = $this->yearly(['plan_source' => 'admin', 'trial_ends_at' => null]);
        $expected = now()->addDays(30)->addYear()->format('Y-m-d');

        $this->artisan('app:extend-plans')
            ->expectsOutputToContain('admin-granted comps')
            ->assertExitCode(0);

        $this->assertSame($expected, $role->fresh()->plan_expires);
    }
}
