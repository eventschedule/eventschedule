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

    /**
     * A dry run that reports only "347 addressable" gives you the size of the blast radius but
     * not who is in it, so the segmentation cannot be sanity checked before real customers' plans
     * are rewritten - and the audit rows that would tell you afterwards are pruned at 90 days,
     * right around when these trials end.
     */
    public function test_the_dry_run_names_the_schedules_it_would_touch(): void
    {
        $role = $this->comped();

        $this->artisan('app:wind-down-comped-plans')
            ->expectsOutputToContain($role->subdomain)
            ->expectsOutputToContain('dormant')
            ->assertExitCode(0);

        $this->assertNull($role->fresh()->trial_ends_at, 'still a dry run');
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

        // --spread-days=1 pins the offset to zero: this test is about the segmentation and
        // the plan_expires/trial_ends_at pairing, not about how dates are spread.
        $this->windDown(['--apply' => true, '--trial-days' => 90, '--spread-days' => 1]);

        $role->refresh();
        $this->assertNotNull($role->trial_ends_at, 'an addressable schedule gets a runway');
        $this->assertSame(now()->addDays(90)->format('Y-m-d'), $role->plan_expires);
        $this->assertSame(now()->addDays(90)->format('Y-m-d'), $role->trial_ends_at->format('Y-m-d'),
            'plan_expires must move too - isPro() falls back to it, so a trial alone changes nothing');
    }

    public function test_a_dormant_schedule_lapses_without_a_trial(): void
    {
        $role = $this->comped();

        $this->windDown(['--apply' => true, '--lapse-days' => 30, '--spread-days' => 1]);

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

    /**
     * Every addressable role used to get the SAME trial_ends_at, so the whole cohort entered
     * SendSubscriptionReminders' 14-day window on one day and was mailed in a single run -
     * synchronously, inside a web request on hosted. Spreading the dates is what defuses that.
     */
    public function test_end_dates_are_spread_across_the_cohort(): void
    {
        $roles = collect(range(1, 6))->map(fn () => $this->comped());

        $this->windDown(['--apply' => true, '--lapse-days' => 30, '--spread-days' => 14]);

        // Deterministic and keyed off the role id, so the offset survives a re-run and does not
        // depend on row order - that is what keeps the command idempotent.
        foreach ($roles as $role) {
            $this->assertSame(
                now()->addDays(30 + ($role->id % 14))->format('Y-m-d'),
                $role->fresh()->plan_expires
            );
        }

        $dates = $roles->map(fn ($r) => $r->fresh()->plan_expires);

        // The point of the exercise: not everyone lands on the same day.
        $this->assertGreaterThan(1, $dates->unique()->count(), 'the cohort must not end on one date');

        // And nothing lands before the floor the operator asked for.
        foreach ($dates as $date) {
            $this->assertGreaterThanOrEqual(now()->addDays(30)->format('Y-m-d'), $date);
        }
    }

    /**
     * The spread may move a date; it must not change WHO is wound down.
     *
     * The never-extend guard compares against the unoffset floor. Testing the offset target
     * instead made the guard trip for a band up to spread-days wide, selected by `id % spread` -
     * so two identical schedules got opposite outcomes on role id parity, and the skipped one
     * got no trial_ends_at, never entered SendSubscriptionReminders, and lapsed in silence.
     *
     * The existing fixtures cannot see this: comped() sets plan_expires three years out, so the
     * guard never fires in any other test.
     */
    public function test_the_spread_does_not_change_which_schedules_are_wound_down(): void
    {
        // Addressable, and expiring INSIDE the spread band: after the 30-day floor, before
        // floor + spread. This is the band where the offset used to decide the outcome.
        $roles = collect(range(1, 8))->map(function () {
            $role = $this->comped(['plan_expires' => now()->addDays(35)->format('Y-m-d')]);
            for ($i = 0; $i < 6; $i++) {
                DB::table('role_user')->insert([
                    'role_id' => $role->id, 'user_id' => $this->createOwner()->id, 'level' => 'follower',
                ]);
            }

            return $role;
        });

        $this->windDown(['--apply' => true, '--trial-days' => 30, '--spread-days' => 14]);

        foreach ($roles as $role) {
            $fresh = $role->fresh();

            // The property that matters. A skipped role gets no trial_ends_at, so it never
            // enters SendSubscriptionReminders and its plan lapses with no email at all -
            // and which roles were skipped used to depend on `id % spread`.
            $this->assertNotNull(
                $fresh->trial_ends_at,
                'id '.$role->id.' was skipped because of its offset, so it would lapse in silence'
            );

            // Never later than what it already had, and never earlier than the floor.
            $this->assertLessThanOrEqual(now()->addDays(35)->format('Y-m-d'), $fresh->plan_expires);
            $this->assertGreaterThanOrEqual(now()->addDays(30)->format('Y-m-d'), $fresh->plan_expires);
        }
    }

    /** The guard itself still holds: a plan already ending before the floor keeps its own date. */
    public function test_a_plan_ending_before_the_floor_is_still_left_alone(): void
    {
        $role = $this->comped(['plan_expires' => now()->addDays(5)->format('Y-m-d')]);

        $this->windDown(['--apply' => true, '--lapse-days' => 30, '--spread-days' => 14]);

        $this->assertSame(now()->addDays(5)->format('Y-m-d'), $role->fresh()->plan_expires);
    }

    /** A second run must not re-spread an already-wound-down role onto a new date. */
    public function test_the_spread_is_stable_across_runs(): void
    {
        $role = $this->comped();
        for ($i = 0; $i < 6; $i++) {
            DB::table('role_user')->insert([
                'role_id' => $role->id, 'user_id' => $this->createOwner()->id, 'level' => 'follower',
            ]);
        }

        $this->windDown(['--apply' => true, '--trial-days' => 90]);
        $first = $role->fresh()->trial_ends_at->format('Y-m-d');

        $this->windDown(['--apply' => true, '--trial-days' => 90]);

        $this->assertSame($first, $role->fresh()->trial_ends_at->format('Y-m-d'));
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
