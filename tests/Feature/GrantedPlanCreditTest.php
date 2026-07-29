<?php

namespace Tests\Feature;

use App\Models\Referral;
use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule whose Enterprise plan an admin handed out carries a small "Event Schedule" credit in
 * the guest footer. Customers paying through Stripe do not - they buy white-label, and the
 * marketing pages promise it without qualification.
 *
 * roles.plan_type cannot make this call on its own: every Stripe path writes it too. The
 * distinguishing column is plan_source, and the layout re-checks Stripe on top of it.
 */
class GrantedPlanCreditTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The credit's tagged URL, which no other footer emits. */
    private const CREDIT = 'utm_source=granted-plan';

    /** Unique to the free-tier black bar's nexus variant. */
    private const BLACK_BAR = 'Invoice Ninja';

    private const ENTERPRISE_PRICE = 'price_enterprise_monthly_test';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.hosted' => true,
            'app.is_nexus' => true,
            'services.stripe_platform.enterprise_price_monthly' => self::ENTERPRISE_PRICE,
        ]);
    }

    private function createEnterpriseRole(array $attrs = []): Role
    {
        // createRole() already defaults to enterprise with a future expiry.
        return $this->createRole($this->createOwner(), 'venue', array_merge([
            'name' => 'Credit Venue',
            'plan_source' => 'admin',
        ], $attrs));
    }

    /** EnsureUserIsAdmin gates every /admin route on a confirmed password this session. */
    private function actingAsAdmin(): self
    {
        // The /admin routes are registered at boot from IS_HOSTED, which differs by environment.
        // Overriding the config in setUp() cannot register a route, so skip rather than fail with
        // a confusing RouteNotFoundException (same guard as AdminAlertsTest).
        if (! Route::has('admin.schedules.update')) {
            $this->markTestSkipped('Hosted-only admin routes are not registered in this environment.');
        }

        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->createOwner(true));
    }

    /** Cashier reads subscriptions directly; no Stripe call is involved. */
    private function giveStripeSubscription(Role $role, string $price = self::ENTERPRISE_PRICE): void
    {
        DB::table('subscriptions')->insert([
            'role_id' => $role->id,
            'type' => 'default',
            'stripe_id' => 'sub_'.$role->id,
            'stripe_status' => 'active',
            'stripe_price' => $price,
            'quantity' => 1,
            'trial_ends_at' => null,
            'ends_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_admin_granted_enterprise_carries_the_credit(): void
    {
        $role = $this->createEnterpriseRole();

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringContainsString(
            'href="https://eventschedule.com?utm_source=granted-plan&amp;utm_medium=footer"',
            $content
        );
        // The credit stands in for the black bar rather than joining it.
        $this->assertStringNotContainsString(self::BLACK_BAR, $content);
    }

    public function test_paying_enterprise_never_carries_the_credit(): void
    {
        // plan_source is deliberately left stale at 'admin' - a schedule that was granted a plan
        // and later subscribed must stop showing the credit even if some Stripe path forgot to
        // clear the column. This is the regression the second half of the predicate exists for.
        $role = $this->createEnterpriseRole();
        $this->giveStripeSubscription($role);

        $this->assertTrue($role->fresh()->hasActiveEnterpriseSubscription());

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringNotContainsString(self::CREDIT, $content);
        $this->assertStringNotContainsString(self::BLACK_BAR, $content);
    }

    public function test_referral_earned_enterprise_carries_no_credit(): void
    {
        // Earned, not given. Same row shape as an admin grant, which is why the column exists.
        $role = $this->createEnterpriseRole(['plan_source' => 'referral']);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringNotContainsString(self::CREDIT, $content);
    }

    public function test_untagged_enterprise_carries_no_credit(): void
    {
        // Rows the migration backfill could not classify stay null and fail closed.
        $role = $this->createEnterpriseRole(['plan_source' => null]);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringNotContainsString(self::CREDIT, $content);
    }

    public function test_free_tier_keeps_the_black_bar_and_no_credit(): void
    {
        $role = $this->createEnterpriseRole([
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'plan_source' => null,
        ]);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringContainsString(self::BLACK_BAR, $content);
        $this->assertStringNotContainsString(self::CREDIT, $content);
    }

    public function test_credit_is_absent_from_embeds(): void
    {
        $role = $this->createEnterpriseRole();

        $content = $this->get('/'.$role->subdomain.'?embed=1')->assertOk()->getContent();

        $this->assertStringNotContainsString(self::CREDIT, $content);
    }

    public function test_admin_plan_update_stamps_and_clears_the_source(): void
    {
        $role = $this->createEnterpriseRole(['plan_source' => null]);
        $admin = $this->actingAsAdmin();

        $admin->put(route('admin.schedules.update', ['role' => UrlUtils::encodeId($role->id)]), [
            'plan_type' => 'enterprise',
            'plan_term' => 'year',
            'plan_expires' => now()->addYear()->format('Y-m-d'),
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.schedules'));

        $this->assertSame('admin', $role->fresh()->plan_source);

        $admin->put(route('admin.schedules.update', ['role' => UrlUtils::encodeId($role->id)]), [
            'plan_type' => 'free',
            'plan_term' => 'year',
            'plan_expires' => null,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.schedules'));

        $this->assertNull($role->fresh()->plan_source);
    }

    public function test_admin_re_save_does_not_convert_a_referral_reward(): void
    {
        // Re-saving the form without moving the plan must leave provenance alone. Otherwise an
        // admin touching plan_term would put the credit on a plan the owner earned.
        $expires = now()->addMonth()->format('Y-m-d');
        $role = $this->createEnterpriseRole([
            'plan_source' => 'referral',
            'plan_expires' => $expires,
        ]);

        $this->actingAsAdmin()
            ->put(route('admin.schedules.update', ['role' => UrlUtils::encodeId($role->id)]), [
                'plan_type' => 'enterprise',
                'plan_term' => 'month',
                'plan_expires' => $expires,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.schedules'));

        $this->assertSame('referral', $role->fresh()->plan_source);
    }

    public function test_referral_credit_does_not_strip_an_admin_grant(): void
    {
        // ReferralController stacks its reward onto whatever plan the schedule already holds, so
        // claiming provenance there would let one redeemed referral revoke an admin grant's
        // credit for good.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'name' => 'Granted Then Referred',
            'plan_source' => 'admin',
        ]);

        $referral = Referral::create([
            'referrer_user_id' => $owner->id,
            'referred_user_id' => $this->createOwner()->id,
            'plan_type' => 'enterprise',
            'status' => 'qualified',
            'subscribed_at' => now()->subDays(45),
            'qualified_at' => now()->subDays(2),
        ]);

        $this->actingAs($owner)
            ->post(route('referrals.apply_credit'), [
                'referral_id' => UrlUtils::encodeId($referral->id),
                'role_id' => UrlUtils::encodeId($role->id),
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('credited', $referral->fresh()->status);
        $this->assertSame('admin', $role->fresh()->plan_source);
    }

    public function test_referral_credit_tags_an_untagged_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'name' => 'Referred Only',
            'plan_type' => 'free',
            'plan_expires' => null,
            'plan_source' => null,
        ]);

        $referral = Referral::create([
            'referrer_user_id' => $owner->id,
            'referred_user_id' => $this->createOwner()->id,
            'plan_type' => 'enterprise',
            'status' => 'qualified',
            'subscribed_at' => now()->subDays(45),
            'qualified_at' => now()->subDays(2),
        ]);

        $this->actingAs($owner)
            ->post(route('referrals.apply_credit'), [
                'referral_id' => UrlUtils::encodeId($referral->id),
                'role_id' => UrlUtils::encodeId($role->id),
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('referral', $role->fresh()->plan_source);
    }

    public function test_admin_plan_update_does_not_label_a_paying_customer(): void
    {
        $role = $this->createEnterpriseRole(['plan_source' => null]);
        $this->giveStripeSubscription($role);

        // Nudging a subscriber's plan_term must not record them as having been given the plan.
        $this->actingAsAdmin()
            ->put(route('admin.schedules.update', ['role' => UrlUtils::encodeId($role->id)]), [
                'plan_type' => 'enterprise',
                'plan_term' => 'month',
                'plan_expires' => null,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.schedules'));

        $this->assertNull($role->fresh()->plan_source);
    }
}
