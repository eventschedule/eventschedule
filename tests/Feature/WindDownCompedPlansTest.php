<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The wind-down rewrites live plan dates on real customers, so every guard rail gets a test.
 */
class WindDownCompedPlansTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.hosted' => true]);
    }

    /** A comped Pro schedule sitting on a far-future expiry, the shape this command targets. */
    private function comped(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', array_merge([
            'plan_type' => 'pro',
            'plan_source' => 'admin',
            'plan_expires' => now()->addYears(3)->format('Y-m-d'),
            'trial_ends_at' => null,
        ], $attrs));
    }

    private function windDown(array $options = []): void
    {
        $this->artisan('app:wind-down-comped-plans', $options)->assertExitCode(0);
    }

    public function test_dry_run_writes_nothing(): void
    {
        $role = $this->comped();
        $original = $role->plan_expires;

        $this->windDown();

        $role->refresh();
        $this->assertSame($original, $role->plan_expires);
        $this->assertNull($role->trial_ends_at);
    }

    public function test_a_schedule_with_an_audience_gets_a_dated_trial(): void
    {
        $role = $this->comped();
        DB::table('role_user')->insert([
            'role_id' => $role->id,
            'user_id' => $this->createOwner()->id,
            'level' => 'follower',
        ]);
        // Five followers is the default threshold; this one has plenty.
        for ($i = 0; $i < 6; $i++) {
            DB::table('role_user')->insert([
                'role_id' => $role->id,
                'user_id' => $this->createOwner()->id,
                'level' => 'follower',
            ]);
        }

        $this->windDown(['--apply' => true, '--trial-days' => 90]);

        $role->refresh();
        $this->assertNotNull($role->trial_ends_at, 'an addressable schedule gets a runway');
        $this->assertSame(now()->addDays(90)->format('Y-m-d'), $role->plan_expires);
        $this->assertSame(now()->addDays(90)->format('Y-m-d'), $role->trial_ends_at->format('Y-m-d'),
            'plan_expires must move too - isPro() falls back to it, so a trial alone changes nothing');
    }

    public function test_a_dormant_schedule_lapses_without_a_trial(): void
    {
        $role = $this->comped();

        $this->windDown(['--apply' => true, '--lapse-days' => 30]);

        $role->refresh();
        $this->assertNull($role->trial_ends_at, 'no runway and no emails for a dead schedule');
        $this->assertSame(now()->addDays(30)->format('Y-m-d'), $role->plan_expires);
    }

    public function test_a_paid_sale_makes_a_schedule_addressable(): void
    {
        $role = $this->comped();
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 50]);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 20], $ticket, 1);

        $this->windDown(['--apply' => true]);

        $this->assertNotNull($role->refresh()->trial_ends_at);
    }

    public function test_it_never_extends_a_plan(): void
    {
        // Already ending in a week - the wind-down must not push it out to 90 days.
        $soon = now()->addDays(7)->format('Y-m-d');
        $role = $this->comped(['plan_expires' => $soon]);

        $this->windDown(['--apply' => true, '--trial-days' => 90, '--lapse-days' => 30]);

        $role->refresh();
        $this->assertSame($soon, $role->plan_expires, 'a sooner expiry is left alone');
        $this->assertNull($role->trial_ends_at);
    }

    /**
     * A redeemed referral stacks 30 days onto whatever plan the schedule already had, and
     * ReferralController sets plan_source with ??= - on purpose, because overwriting 'admin'
     * would strip the comped credit chip from an admin grant. So a role that earned a month
     * still reads plan_source = 'admin', and the wind-down happily pulled plan_expires back
     * past the month somebody had actually earned. The docblock promises otherwise.
     */
    public function test_a_month_earned_by_referral_is_not_taken_back(): void
    {
        $role = $this->comped();

        DB::table('referrals')->insert([
            'referrer_user_id' => $role->user_id,
            'referred_user_id' => $role->user_id,
            'credited_role_id' => $role->id,
            'plan_type' => 'pro',
            'status' => 'credited',
            'credited_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->windDown(['--apply' => true]);

        $role->refresh();
        $this->assertNull($role->trial_ends_at);
        $this->assertSame(now()->addYears(3)->format('Y-m-d'), $role->plan_expires);
    }

    public function test_stripe_customers_and_referral_rewards_are_untouched(): void
    {
        $referral = $this->comped(['plan_source' => 'referral']);
        $stripe = $this->comped(['plan_source' => null]);

        $subscribed = $this->comped();
        DB::table('subscriptions')->insert([
            'role_id' => $subscribed->id,
            'type' => 'default',
            'stripe_id' => 'sub_'.uniqid(),
            'stripe_status' => 'active',
            'stripe_price' => 'price_test',
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->windDown(['--apply' => true]);

        foreach ([$referral, $stripe, $subscribed] as $role) {
            $role->refresh();
            $this->assertNull($role->trial_ends_at);
            $this->assertSame(now()->addYears(3)->format('Y-m-d'), $role->plan_expires);
        }
    }

    public function test_it_is_idempotent(): void
    {
        $role = $this->comped();

        $this->windDown(['--apply' => true, '--lapse-days' => 30]);
        $firstPass = $role->refresh()->plan_expires;

        // A second run a day later must not shorten it again.
        $this->travel(1)->days();
        $this->windDown(['--apply' => true, '--lapse-days' => 30]);

        $this->assertSame($firstPass, $role->refresh()->plan_expires);
    }

    public function test_it_does_nothing_on_a_selfhosted_install(): void
    {
        config(['app.hosted' => false]);
        $role = $this->comped();

        $this->windDown(['--apply' => true]);

        $this->assertSame(now()->addYears(3)->format('Y-m-d'), $role->refresh()->plan_expires);
    }
}
