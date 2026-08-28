<?php

namespace Tests\Feature;

use App\Http\Controllers\SubscriptionWebhookController;
use App\Models\Role;
use App\Utils\PlanPriceUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * How a stored Stripe price ID resolves to a plan tier and term.
 *
 * Nothing in this app asks Stripe what a subscription costs - every tier and term decision is a
 * string match against the four configured price IDs. Only those four resolve; anything else
 * returns null, and callers must decline to write rather than guess.
 *
 * That null is the whole point. Before PlanPriceUtils, an unrecognized price fell through to
 * pro/month, so the subscription.updated webhook persisted a downgrade onto a customer whose card
 * was still being charged the Enterprise rate. These tests pin that it no longer can.
 *
 * (This file was LegacyPlanPriceTest. The STRIPE_LEGACY_* mechanism it was written for is gone;
 * the unknown-price behaviour it also covered outlives it and is what remains here.)
 */
class PlanPriceResolutionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const ENT_MONTHLY = 'price_ent_monthly_v2';

    private const ENT_YEARLY = 'price_ent_yearly_v2';

    private const PRO_MONTHLY = 'price_pro_monthly_v2';

    private const PRO_YEARLY = 'price_pro_yearly_v2';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.hosted' => true,
            'services.stripe_platform.enterprise_price_monthly' => self::ENT_MONTHLY,
            'services.stripe_platform.enterprise_price_yearly' => self::ENT_YEARLY,
            'services.stripe_platform.price_monthly' => self::PRO_MONTHLY,
            'services.stripe_platform.price_yearly' => self::PRO_YEARLY,
        ]);
    }

    /**
     * A role in the state a real Stripe subscriber is in: the legacy plan columns are nulled by
     * every webhook success path, so the price ID is the ONLY thing left saying what they bought.
     */
    private function subscriberOn(string $price, string $planType = 'enterprise'): Role
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => $planType,
            'plan_expires' => null,
            'stripe_id' => 'cus_'.uniqid(),
        ]);

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

        return $role->fresh();
    }

    public function test_a_configured_enterprise_price_grants_enterprise(): void
    {
        $role = $this->subscriberOn(self::ENT_MONTHLY);

        $this->assertTrue($role->hasActiveEnterpriseSubscription());
        $this->assertTrue($role->isEnterprise());
    }

    /** The match must stay tight: an unlisted price is not a free pass to Enterprise. */
    public function test_unknown_price_does_not_grant_enterprise(): void
    {
        $role = $this->subscriberOn('price_never_configured');

        $this->assertFalse($role->hasActiveEnterpriseSubscription());
        $this->assertNull(PlanPriceUtils::tierFor('price_never_configured'));
        $this->assertNull(PlanPriceUtils::termFor('price_never_configured'));
    }

    public function test_tier_and_term_resolve_for_every_configured_price(): void
    {
        $this->assertSame('enterprise', PlanPriceUtils::tierFor(self::ENT_YEARLY));
        $this->assertSame('enterprise', PlanPriceUtils::tierFor(self::ENT_MONTHLY));
        $this->assertSame('pro', PlanPriceUtils::tierFor(self::PRO_YEARLY));
        $this->assertSame('pro', PlanPriceUtils::tierFor(self::PRO_MONTHLY));

        $this->assertSame('year', PlanPriceUtils::termFor(self::ENT_YEARLY));
        $this->assertSame('year', PlanPriceUtils::termFor(self::PRO_YEARLY));
        $this->assertSame('month', PlanPriceUtils::termFor(self::ENT_MONTHLY));
        $this->assertSame('month', PlanPriceUtils::termFor(self::PRO_MONTHLY));
    }

    public function test_current_returns_the_configured_price(): void
    {
        $this->assertSame(self::ENT_MONTHLY, PlanPriceUtils::current('enterprise', 'monthly'));
        $this->assertSame(self::PRO_YEARLY, PlanPriceUtils::current('pro', 'yearly'));
    }

    public function test_amounts_come_from_the_configured_display_amounts(): void
    {
        config(['services.stripe_platform.enterprise_price_monthly_amount' => '29']);

        $this->assertSame(29.0, PlanPriceUtils::amountFor(self::ENT_MONTHLY));
        $this->assertNull(PlanPriceUtils::amountFor('price_never_configured'));
    }

    /**
     * A null price must not resolve to an amount.
     *
     * The guard at the top of amountFor() looks redundant now that the loop only ever matches a
     * configured ID, but it is also the only thing rejecting null: `null === (config(...) ?: null)`
     * is TRUE for any tier the install does not sell, so a Pro-only install would answer a null
     * price with the enterprise yearly amount.
     */
    public function test_a_null_price_has_no_amount(): void
    {
        config([
            'services.stripe_platform.enterprise_price_monthly' => null,
            'services.stripe_platform.enterprise_price_yearly' => null,
            'services.stripe_platform.enterprise_price_yearly_amount' => '150',
        ]);

        $this->assertNull(PlanPriceUtils::amountFor(null));
        $this->assertNull(PlanPriceUtils::tierFor(null));
        $this->assertNull(PlanPriceUtils::termFor(null));
    }

    private function fireSubscriptionUpdated(Role $role, string $priceId): void
    {
        $controller = new SubscriptionWebhookController;
        $method = new \ReflectionMethod($controller, 'handleCustomerSubscriptionUpdated');
        $method->setAccessible(true);
        $method->invoke($controller, [
            'data' => [
                'object' => [
                    'id' => 'sub_'.$role->id,
                    'customer' => $role->stripe_id,
                    'status' => 'active',
                    'items' => ['data' => [[
                        'id' => 'si_'.$role->id,
                        'price' => ['id' => $priceId, 'product' => 'prod_test'],
                        'quantity' => 1,
                        'current_period_end' => now()->addMonth()->timestamp,
                    ]]],
                ],
            ],
        ]);
    }

    public function test_webhook_writes_the_plan_for_a_configured_price(): void
    {
        $role = $this->subscriberOn(self::ENT_YEARLY);

        $this->fireSubscriptionUpdated($role, self::ENT_YEARLY);

        $role->refresh();
        $this->assertSame('enterprise', $role->plan_type);
        $this->assertSame('year', $role->plan_term);
    }

    /**
     * The destructive case. An ID we cannot place means config is incomplete, and the old code
     * fell through to pro/month - persisting a downgrade onto a paying Enterprise customer.
     */
    public function test_webhook_leaves_the_plan_alone_for_an_unknown_price(): void
    {
        $role = $this->subscriberOn(self::ENT_MONTHLY);
        $role->plan_term = 'year';
        $role->save();

        $this->fireSubscriptionUpdated($role, 'price_never_configured');

        $role->refresh();
        $this->assertSame('enterprise', $role->plan_type);
        $this->assertSame('year', $role->plan_term);
    }

    private function fireInvoicePaid(Role $role, string $priceId): void
    {
        $controller = new SubscriptionWebhookController;
        $method = new \ReflectionMethod($controller, 'handleInvoicePaymentSucceeded');
        $method->setAccessible(true);
        $method->invoke($controller, [
            'data' => [
                'object' => [
                    'customer' => $role->stripe_id,
                    'lines' => ['data' => [[
                        'price' => ['id' => $priceId],
                    ]]],
                ],
            ],
        ]);
    }

    public function test_paid_invoice_writes_the_plan_for_a_configured_price(): void
    {
        $role = $this->subscriberOn(self::ENT_YEARLY);

        $this->fireInvoicePaid($role, self::ENT_YEARLY);

        $role->refresh();
        $this->assertSame('enterprise', $role->plan_type);
        $this->assertSame('year', $role->plan_term);
    }

    /**
     * The handler that renews every billing cycle, so the likeliest one to meet a price ID that
     * config no longer names - which is now the ONLY outcome for any price left behind by a
     * repointed STRIPE_PRICE_*, since there is no legacy list to catch it.
     *
     * The subscription itself must be on the unrecognized price, not just the invoice line: it is
     * the SUBSCRIPTION's stored price that hasActiveEnterpriseSubscription() reads, and that
     * false is what used to stamp 'pro' onto a customer the same invoice had just charged the
     * Enterprise rate. An earlier version of this test put a known price on the subscription and
     * only an unknown one on the invoice line, and passed against the bug.
     */
    public function test_paid_invoice_leaves_the_plan_alone_for_an_unknown_price(): void
    {
        $role = $this->subscriberOn('price_never_configured');
        $role->plan_term = 'year';
        $role->save();

        $this->fireInvoicePaid($role, 'price_never_configured');

        $role->refresh();
        $this->assertSame('enterprise', $role->plan_type);
        $this->assertSame('year', $role->plan_term);
    }
}
