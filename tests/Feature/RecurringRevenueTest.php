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
}
