<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A trial never gets less AI import than Free. Role::aiParseDailyLimit() used to answer a trial
 * first, with a cap of its own (10), so starting a trial of a paid plan cut a schedule's daily
 * AI import from the 50 it had on Free. A trial now gets the allowance of the plan it is a trial of.
 */
class AiParseTrialLimitTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    public function test_a_pro_trial_gets_the_pro_allowance(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'pro',
            'plan_expires' => now()->addDays(7)->format('Y-m-d'),
            'trial_ends_at' => now()->addDays(7),
        ]);

        $this->assertTrue($role->isOnTrial(), 'sanity check: the schedule is on a trial');
        $this->assertSame(50, $role->aiParseDailyLimit());
    }

    public function test_an_enterprise_trial_gets_the_enterprise_allowance(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'enterprise',
            'plan_expires' => now()->addDays(7)->format('Y-m-d'),
            'trial_ends_at' => now()->addDays(7),
        ]);

        $this->assertTrue($role->isOnTrial(), 'sanity check: the schedule is on a trial');
        $this->assertSame(100, $role->aiParseDailyLimit());
    }

    public function test_a_trial_never_gets_less_than_free(): void
    {
        $free = $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'trial_ends_at' => null,
        ]);
        $trial = $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'pro',
            'plan_expires' => now()->addDays(7)->format('Y-m-d'),
            'trial_ends_at' => now()->addDays(7),
        ]);

        $this->assertFalse($free->isOnTrial());
        $this->assertGreaterThanOrEqual($free->aiParseDailyLimit(), $trial->aiParseDailyLimit());
    }
}
