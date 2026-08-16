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
 * Stripe Prices are immutable, so changing what a plan costs means creating a NEW Price and
 * pointing config at it. Existing subscriptions keep billing on the old one indefinitely
 * (archiving a Price only blocks new use of it), so both generations of ID are live at once.
 *
 * Nothing in this app asks Stripe what a subscription costs - every tier and term decision is a
 * string match against config. Before PlanPriceUtils, pointing the four price ID vars at new
 * values stripped Enterprise from every grandfathered customer on their next page load, while
 * their card kept being charged the Enterprise rate, and the subscription.updated webhook then
 * persisted the downgrade to roles.plan_type.
 *
 * These tests pin that a retired price ID still resolves to the tier and term it was sold as.
 */
class LegacyPlanPriceTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** What we sell today. */
    private const CURRENT_ENT_MONTHLY = 'price_ent_monthly_v2';

    private const CURRENT_ENT_YEARLY = 'price_ent_yearly_v2';

    private const CURRENT_PRO_MONTHLY = 'price_pro_monthly_v2';

    private const CURRENT_PRO_YEARLY = 'price_pro_yearly_v2';

    /** Retired at the price change, still billing grandfathered subscribers. */
    private const LEGACY_ENT_MONTHLY = 'price_ent_monthly_v1';

    private const LEGACY_ENT_YEARLY = 'price_ent_yearly_v1';

    private const LEGACY_PRO_YEARLY = 'price_pro_yearly_v1';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.hosted' => true,
            'services.stripe_platform.enterprise_price_monthly' => self::CURRENT_ENT_MONTHLY,
            'services.stripe_platform.enterprise_price_yearly' => self::CURRENT_ENT_YEARLY,
            'services.stripe_platform.price_monthly' => self::CURRENT_PRO_MONTHLY,
            'services.stripe_platform.price_yearly' => self::CURRENT_PRO_YEARLY,
            'services.stripe_platform.legacy_enterprise_price_monthly' => self::LEGACY_ENT_MONTHLY,
            'services.stripe_platform.legacy_enterprise_price_yearly' => self::LEGACY_ENT_YEARLY,
            'services.stripe_platform.legacy_price_yearly' => self::LEGACY_PRO_YEARLY,
            'services.stripe_platform.legacy_price_amounts' => self::LEGACY_ENT_MONTHLY.':19,'.self::LEGACY_PRO_YEARLY.':50',
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

    public function test_legacy_enterprise_price_still_grants_enterprise(): void
    {
        $role = $this->subscriberOn(self::LEGACY_ENT_MONTHLY);

        $this->assertTrue($role->hasActiveEnterpriseSubscription());
        $this->assertTrue($role->isEnterprise());
    }

    public function test_current_enterprise_price_still_grants_enterprise(): void
    {
        $role = $this->subscriberOn(self::CURRENT_ENT_MONTHLY);

        $this->assertTrue($role->isEnterprise());
    }

    public function test_legacy_yearly_price_still_reports_a_yearly_term(): void
    {
        $entYearly = $this->subscriberOn(self::LEGACY_ENT_YEARLY);
        $proYearly = $this->subscriberOn(self::LEGACY_PRO_YEARLY, 'pro');

        $this->assertSame('yearly', $entYearly->currentPlanTerm());
        $this->assertSame('yearly', $proYearly->currentPlanTerm());
    }

    /** The match must stay tight: an unlisted price is not a free pass to Enterprise. */
    public function test_unknown_price_does_not_grant_enterprise(): void
    {
        $role = $this->subscriberOn('price_never_configured');

        $this->assertFalse($role->hasActiveEnterpriseSubscription());
        $this->assertNull(PlanPriceUtils::tierFor('price_never_configured'));
        $this->assertNull(PlanPriceUtils::termFor('price_never_configured'));
    }

    public function test_tier_and_term_resolve_for_both_generations(): void
    {
        $this->assertSame('enterprise', PlanPriceUtils::tierFor(self::LEGACY_ENT_YEARLY));
        $this->assertSame('enterprise', PlanPriceUtils::tierFor(self::CURRENT_ENT_YEARLY));
        $this->assertSame('pro', PlanPriceUtils::tierFor(self::LEGACY_PRO_YEARLY));

        $this->assertSame('year', PlanPriceUtils::termFor(self::LEGACY_ENT_YEARLY));
        $this->assertSame('month', PlanPriceUtils::termFor(self::LEGACY_ENT_MONTHLY));
    }

    /** Checkout and swap must never offer a retired price. */
    public function test_current_returns_only_the_live_price(): void
    {
        $this->assertSame(self::CURRENT_ENT_MONTHLY, PlanPriceUtils::current('enterprise', 'monthly'));
        $this->assertSame(self::CURRENT_PRO_YEARLY, PlanPriceUtils::current('pro', 'yearly'));
    }

    public function test_amounts_come_from_the_legacy_map_for_retired_prices(): void
    {
        config(['services.stripe_platform.enterprise_price_monthly_amount' => '29']);

        $this->assertSame(29.0, PlanPriceUtils::amountFor(self::CURRENT_ENT_MONTHLY));
        $this->assertSame(19.0, PlanPriceUtils::amountFor(self::LEGACY_ENT_MONTHLY));
        $this->assertSame(50.0, PlanPriceUtils::amountFor(self::LEGACY_PRO_YEARLY));
        $this->assertNull(PlanPriceUtils::amountFor('price_never_configured'));
    }

    /**
     * The amount map is a free-form env var, so it can name an ID the tier/term lists do not.
     * Honouring it there would hand callers an amount with no term, and the revenue rollup
     * annualizes anything it cannot prove is yearly - turning a half-configured YEARLY price
     * into a silent 12x overcount. Refusing the amount makes the mistake an undercount.
     */
    public function test_an_amount_without_a_configured_id_is_refused(): void
    {
        config(['services.stripe_platform.legacy_price_amounts' => 'price_orphan_yearly:200']);

        $this->assertNull(PlanPriceUtils::termFor('price_orphan_yearly'));
        $this->assertNull(PlanPriceUtils::amountFor('price_orphan_yearly'));
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

    public function test_webhook_keeps_enterprise_for_a_legacy_price(): void
    {
        $role = $this->subscriberOn(self::LEGACY_ENT_YEARLY);

        $this->fireSubscriptionUpdated($role, self::LEGACY_ENT_YEARLY);

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
        $role = $this->subscriberOn(self::LEGACY_ENT_MONTHLY);
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

    public function test_paid_invoice_keeps_enterprise_for_a_legacy_price(): void
    {
        $role = $this->subscriberOn(self::LEGACY_ENT_YEARLY);

        $this->fireInvoicePaid($role, self::LEGACY_ENT_YEARLY);

        $role->refresh();
        $this->assertSame('enterprise', $role->plan_type);
        $this->assertSame('year', $role->plan_term);
    }

    /**
     * The handler that renews every billing cycle, so the likeliest one to hit an ID that was
     * retired without being listed in STRIPE_LEGACY_*.
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
