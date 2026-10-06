<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Services\DemoService;
use App\Services\GrowthExportService;
use App\Services\RecurringRevenue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The dashboard's ARR and the growth page's MRR are one figure.
 *
 * They used to be computed twice and disagreed on nearly every edge below: the dashboard dropped
 * past_due and unverified owners and counted deleted schedules, while the growth page booked an
 * unrecognized price and a plan with no subscription row at a by-tier estimate. The fixture has a
 * row for each of those, so reintroducing any one of the old rules moves a total here.
 */
class RecurringRevenueTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.hosted' => true,
            'services.stripe_platform.price_monthly' => 'price_pro_m',
            'services.stripe_platform.price_yearly' => 'price_pro_y',
            'services.stripe_platform.enterprise_price_monthly' => 'price_ent_m',
            'services.stripe_platform.enterprise_price_yearly' => 'price_ent_y',
            'services.stripe_platform.price_monthly_amount' => '5',
            'services.stripe_platform.price_yearly_amount' => '50',
            'services.stripe_platform.enterprise_price_monthly_amount' => '15',
            'services.stripe_platform.enterprise_price_yearly_amount' => '150',
        ]);
    }

    private function schedule(array $attrs = []): Role
    {
        // A Stripe subscriber's plan columns: every webhook success path nulls plan_expires and
        // plan_source, so the subscription row is the only thing saying what they bought.
        return $this->createRole($this->createOwner(), 'venue', array_merge([
            'plan_type' => 'free',
            'plan_expires' => null,
            'plan_source' => null,
        ], $attrs));
    }

    private function subscribe(Role $role, ?string $price, string $status = 'active', array $attrs = []): int
    {
        return $role->subscriptions()->create(array_merge([
            'type' => 'default',
            'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => $status,
            'stripe_price' => $price,
            'quantity' => 1,
        ], $attrs))->id;
    }

    /** Cashier's multi-price shape: NULL stripe_price on the subscription, the price on its item. */
    private function subscribeMultiPrice(Role $role, string $itemPrice): void
    {
        DB::table('subscription_items')->insert([
            'subscription_id' => $this->subscribe($role, null),
            'stripe_id' => 'si_'.Str::random(14),
            'stripe_product' => 'prod_x',
            'stripe_price' => $itemPrice,
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** Builds the fixture and returns the ARR it should add up to. */
    private function seedEveryCase(): float
    {
        // Counted.
        $this->subscribe($this->schedule(), 'price_pro_m');                       // 60
        $this->subscribe($this->schedule(), 'price_ent_y');                       // 150
        $this->subscribe($this->schedule(), 'price_pro_m', 'past_due');           // 60: Stripe is still retrying
        $this->subscribe($this->schedule(), 'price_pro_y', 'active', [            // 50: paid until ends_at
            'ends_at' => now()->addDays(10),
        ]);
        $this->subscribe($this->schedule(['email_verified_at' => null]), 'price_pro_m'); // 60: the card is the verification
        $this->subscribeMultiPrice($this->schedule(), 'price_ent_m');             // 180: priced from its item

        // Not counted.
        $this->subscribe($this->schedule(), 'price_pro_m', 'trialing', ['trial_ends_at' => now()->addDays(7)]);
        $this->subscribe($this->schedule(), 'price_pro_m', 'trialing', [         // a cancelled trial: not pipeline either
            'trial_ends_at' => now()->addDays(7), 'ends_at' => now()->addDays(7),
        ]);
        $this->subscribe($this->schedule(), 'price_retired_2024');                // unrecognized: zero, not an estimate
        $this->subscribeMultiPrice($this->schedule(), 'price_retired_2024');      // unrecognized on its item too
        $this->schedule([                                                          // no subscription row at all
            'plan_type' => 'pro', 'plan_expires' => now()->addYear()->format('Y-m-d'), 'plan_term' => 'month',
        ]);
        $this->schedule([                                                          // admin comp
            'plan_type' => 'enterprise', 'plan_expires' => now()->addYear()->format('Y-m-d'), 'plan_source' => 'admin',
        ]);
        $this->subscribe($this->schedule(['is_deleted' => true]), 'price_ent_m'); // orphaned billing
        // Demo CONTENT (the schedule's contact address is the demo's), not the `demo-` subdomain
        // shape: a real schedule that holds that prefix is a real customer - see
        // test_a_real_schedule_on_a_demo_prefixed_subdomain_is_revenue.
        $this->subscribe($this->schedule(['email' => DemoService::DEMO_EMAIL]), 'price_ent_m');
        $this->subscribe($this->schedule(), 'price_pro_m', 'canceled', ['ends_at' => now()->subDay()]);
        $this->subscribe($this->schedule(), 'price_pro_m', 'active', ['ends_at' => now()->subDay()]); // webhook not in yet
        $this->subscribe($this->schedule(), 'price_pro_m', 'incomplete');
        $this->subscribe($this->schedule(), 'price_pro_m', 'unpaid');
        $this->subscribe($this->schedule(), 'price_ent_m', 'active', ['type' => 'other']);

        return 60 + 150 + 60 + 50 + 60 + 180;
    }

    public function test_it_counts_only_what_stripe_is_collecting_on(): void
    {
        $arr = $this->seedEveryCase();

        $this->assertSame([
            'mrr' => round($arr / 12, 2),
            'arr' => $arr,
            'billing_count' => 8,
            'trialing_count' => 1,
            'unrecognized_count' => 2,
        ], RecurringRevenue::summary());
    }

    public function test_the_dashboard_and_the_growth_page_report_the_same_figure(): void
    {
        $arr = $this->seedEveryCase();

        $admin = $this->createOwner(true);
        $response = $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('arr', $arr)
            ->assertViewHas('arrTrialingCount', 1);
        $response->assertSee(__('messages.recurring_revenue_excludes_trials', ['count' => 1]));

        $money = app(GrowthExportService::class)
            ->build(now()->subDays(30), now(), now()->subDays(60), now()->subDays(31))['monetization'];

        $this->assertSame($arr, $money['arr']);
        $this->assertSame(round($arr / 12, 2), $money['mrr']);
        $this->assertSame(1, $money['trialing_subscriptions']);
        $this->assertSame(2, $money['unrecognized_price_subscriptions']);
        // Over the six priced subscriptions, not the unrecognized ones booked at zero and not the
        // Pro + Enterprise plan counts, which include the comp and the legacy plan.
        $this->assertSame(round($arr / 12 / 6, 2), $money['arpu']);
    }

    /**
     * The subdomain shape used to be the demo test here, and it hid a paying customer: real
     * schedules named before cleanSubdomain() reserved the prefix still hold one ("Demo Night" got
     * `demo-night`). Demo content is keyed on the demo's contact address and owner instead.
     */
    public function test_a_real_schedule_on_a_demo_prefixed_subdomain_is_revenue(): void
    {
        $this->subscribe($this->schedule(['subdomain' => 'demo-night-'.Str::lower(Str::random(4))]), 'price_pro_m');

        $this->assertSame(60.0, RecurringRevenue::summary()['arr']);
    }

    /**
     * billingRoleIds() is what the growth payload calls a paying schedule, so it has to agree with
     * summary() row for row: a trial, a comp and a legacy plan are not paying, an unrecognized
     * price still is (it is being charged; it only books at zero).
     */
    public function test_billing_role_ids_are_the_schedules_summary_counts_as_billing(): void
    {
        $paying = $this->schedule();
        $this->subscribe($paying, 'price_pro_m');
        $pastDue = $this->schedule();
        $this->subscribe($pastDue, 'price_pro_m', 'past_due');
        $retired = $this->schedule();
        $this->subscribe($retired, 'price_retired_2024');
        $trial = $this->schedule();
        $this->subscribe($trial, 'price_pro_m', 'trialing', ['trial_ends_at' => now()->addDays(7)]);
        $comp = $this->schedule(['plan_type' => 'pro', 'plan_expires' => now()->addYear()->format('Y-m-d'), 'plan_source' => 'admin']);
        $lapsed = $this->schedule();
        $this->subscribe($lapsed, 'price_pro_m', 'canceled', ['ends_at' => now()->subDay()]);

        $ids = RecurringRevenue::billingRoleIds();

        $this->assertEqualsCanonicalizing([$paying->id, $pastDue->id, $retired->id], array_keys($ids));
        $this->assertSame(RecurringRevenue::summary()['billing_count'], count($ids));
        $this->assertArrayNotHasKey($trial->id, $ids);
        $this->assertArrayNotHasKey($comp->id, $ids);
        $this->assertArrayNotHasKey($lapsed->id, $ids);
    }

    public function test_an_install_with_no_subscriptions_reports_zero_and_no_arpu(): void
    {
        $this->schedule(['plan_type' => 'pro', 'plan_expires' => now()->addYear()->format('Y-m-d')]);

        $this->assertSame(0.0, RecurringRevenue::summary()['arr']);

        $money = app(GrowthExportService::class)
            ->build(now()->subDays(30), now(), now()->subDays(60), now()->subDays(31))['monetization'];

        $this->assertSame(0.0, $money['mrr']);
        $this->assertNull($money['arpu']);
    }

    /**
     * The dashboard's plan table and its total come from one loop, so the rows are the total.
     * Mutation: give the plan rows a query of their own, or count a trial as billing.
     */
    public function test_the_plan_rows_are_the_total_split_four_ways(): void
    {
        $arr = $this->seedEveryCase();

        $breakdown = RecurringRevenue::breakdown();
        $plans = $breakdown['plans'];

        $this->assertSame(RecurringRevenue::PLANS, array_keys($plans));
        // Three on Pro monthly (one past due, one whose owner never confirmed), and one each on
        // Pro yearly, Enterprise monthly (priced from its item) and Enterprise yearly.
        $this->assertSame([3, 1, 1, 1], array_column($plans, 'billing_count'));
        $this->assertSame([180.0, 50.0, 180.0, 150.0], array_column($plans, 'arr'));
        // 50 a year is 4.1666 a month. The odd cent goes to that row, and the four add up.
        $this->assertSame([15.0, 4.17, 15.0, 12.5], array_column($plans, 'mrr'));

        $this->assertSame($arr, array_sum(array_column($plans, 'arr')));
        $this->assertSame(46.67, $breakdown['totals']['mrr']);
        $this->assertSame(4667, (int) round(array_sum(array_column($plans, 'mrr')) * 100));

        // The two on a price config no longer names are customers, at zero, on a row of their own.
        $this->assertSame(['billing_count' => 2, 'trialing_count' => 0], $breakdown['unrecognized']);
        $this->assertSame(8, $breakdown['totals']['billing_count']);
        $this->assertSame(RecurringRevenue::summary(), array_intersect_key($breakdown['totals'], RecurringRevenue::summary()));
    }

    /**
     * A trial is priced like a subscription and shown beside the revenue, never in it. A trial
     * that was cancelled cannot convert, so it is not pipeline either. Mutation: add trial_cents
     * to cents, or stop skipping a trialing row that has ends_at.
     */
    public function test_a_trial_is_priced_beside_the_revenue_and_never_in_it(): void
    {
        $this->seedEveryCase();

        $breakdown = RecurringRevenue::breakdown();

        $this->assertSame([1, 0, 0, 0], array_column($breakdown['plans'], 'trialing_count'));
        $this->assertSame([5.0, 0.0, 0.0, 0.0], array_column($breakdown['plans'], 'trial_mrr'));
        $this->assertSame(1, $breakdown['totals']['trialing_count']);
        $this->assertSame(5.0, $breakdown['totals']['trial_mrr']);
        // And the revenue is what it was without them.
        $this->assertSame(46.67, $breakdown['totals']['mrr']);
    }

    /**
     * Past due and cancelling sit inside the billing figures, and a subscription that is both is
     * at risk once. Mutation: add the two counts together for at_risk_count.
     */
    public function test_revenue_at_risk_is_counted_once_and_inside_the_total(): void
    {
        $this->seedEveryCase();

        $totals = RecurringRevenue::breakdown()['totals'];

        $this->assertSame(1, $totals['past_due_count']);
        $this->assertSame(1, $totals['cancelling_count']);
        $this->assertSame(2, $totals['at_risk_count']);
        // Pro monthly past due (60 a year) and Pro yearly cancelling (50): 110 a year.
        $this->assertSame(9.17, $totals['at_risk_mrr']);

        // Past due AND cancelling: both counts move, the subscription is at risk once.
        $this->subscribe($this->schedule(), 'price_ent_m', 'past_due', ['ends_at' => now()->addDays(5)]);

        $totals = RecurringRevenue::breakdown()['totals'];

        $this->assertSame(2, $totals['past_due_count']);
        $this->assertSame(2, $totals['cancelling_count']);
        $this->assertSame(3, $totals['at_risk_count']);
        $this->assertSame(24.17, $totals['at_risk_mrr']);
    }

    /**
     * Two yearly prices that do not divide by twelve: rounded one by one the rows come to 18.34
     * against a total of 18.33. Mutation: round each row on its own.
     */
    public function test_rows_never_differ_from_the_total_by_a_cent(): void
    {
        config(['services.stripe_platform.enterprise_price_yearly_amount' => '170']);

        $this->subscribe($this->schedule(), 'price_pro_y');
        $this->subscribe($this->schedule(), 'price_ent_y');

        $breakdown = RecurringRevenue::breakdown();

        $this->assertSame(18.33, $breakdown['totals']['mrr']);
        $this->assertSame(1833, (int) round(array_sum(array_column($breakdown['plans'], 'mrr')) * 100));
    }

    /**
     * Cashier keeps a subscription with several prices as one row with its prices on items. It is
     * one customer, on the row of its highest plan, worth all of them. Mutation: split it by
     * price, which makes a Pro row with money and nobody on it.
     */
    public function test_a_subscription_with_two_prices_is_one_customer_on_its_highest_plan(): void
    {
        $id = $this->subscribe($this->schedule(), null);

        foreach (['price_pro_m', 'price_ent_m'] as $price) {
            DB::table('subscription_items')->insert([
                'subscription_id' => $id,
                'stripe_id' => 'si_'.Str::random(14),
                'stripe_product' => 'prod_x',
                'stripe_price' => $price,
                'quantity' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $breakdown = RecurringRevenue::breakdown();

        $this->assertSame([0, 0, 1, 0], array_column($breakdown['plans'], 'billing_count'));
        $this->assertSame(20.0, $breakdown['plans']['enterprise_month']['mrr']);
        $this->assertSame(0.0, $breakdown['plans']['pro_month']['mrr']);
        $this->assertSame(1, $breakdown['totals']['billing_count']);
        $this->assertSame(240.0, $breakdown['totals']['arr']);
    }
}
