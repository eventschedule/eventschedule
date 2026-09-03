<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The three database checks in deploy:preflight, which are the ones that need production data
 * and so are the ones nobody can eyeball from a laptop.
 *
 * The stranded-subscription check is the reason this file exists. This release deleted
 * STRIPE_LEGACY_*, so PlanPriceUtils resolves a tier only by exact match against the four
 * current price IDs, and a live subscription on any other ID means that customer keeps being
 * charged while hasActiveEnterpriseSubscription() returns false and ARR counts them at zero -
 * announced by nothing but a Log::warning. On any database that happens to have no stranded
 * rows the check prints a pass without ever having compared anything, so the failing path has
 * to be exercised deliberately or it is not evidence of anything.
 */
class DeployPreflightDatabaseTest extends TestCase
{
    use RefreshDatabase;

    private function configurePrices(): void
    {
        config([
            'services.stripe_platform.price_monthly' => 'price_current_pro_monthly',
            'services.stripe_platform.price_yearly' => 'price_current_pro_yearly',
            'services.stripe_platform.enterprise_price_monthly' => 'price_current_ent_monthly',
            'services.stripe_platform.enterprise_price_yearly' => 'price_current_ent_yearly',
        ]);
    }

    private function subscription(string $priceId, string $status = 'active'): void
    {
        DB::table('subscriptions')->insert([
            'role_id' => 1,
            'type' => 'default',
            'stripe_id' => 'sub_'.bin2hex(random_bytes(6)),
            'stripe_status' => $status,
            'stripe_price' => $priceId,
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_it_passes_when_every_live_subscription_is_on_a_configured_price(): void
    {
        $this->configurePrices();
        $this->subscription('price_current_pro_monthly');
        $this->subscription('price_current_ent_yearly');

        $this->artisan('deploy:preflight --skip-remote')
            ->expectsOutputToContain('live price ID(s) are recognised');
    }

    /**
     * FAILS to be caught at all before this check existed: the runbook asked an operator to eyeball
     * a DISTINCT query against four config values by hand.
     */
    public function test_it_reports_a_subscription_on_a_retired_price_id(): void
    {
        $this->configurePrices();
        $this->subscription('price_current_pro_monthly');
        $this->subscription('price_retired_from_2024');

        $this->artisan('deploy:preflight --skip-remote')
            ->expectsOutputToContain('NOT recognised by config')
            ->expectsOutputToContain('price_retired_from_2024')
            ->assertExitCode(1);
    }

    /**
     * A cancelled subscription on a retired price is not a problem - nothing is being charged and
     * no tier is being withdrawn. Only active, trialing and past_due count.
     */
    public function test_it_ignores_a_cancelled_subscription_on_a_retired_price(): void
    {
        $this->configurePrices();
        $this->subscription('price_current_pro_monthly');
        $this->subscription('price_retired_from_2024', 'canceled');

        $this->artisan('deploy:preflight --skip-remote')
            ->doesntExpectOutputToContain('NOT recognised by config');
    }

    /**
     * past_due is deliberately included: Stripe is still retrying the charge and the role keeps
     * Enterprise access through the dunning window, so a stranded past_due subscriber has
     * exactly the problem this check is looking for.
     */
    public function test_it_counts_a_past_due_subscription_as_live(): void
    {
        $this->configurePrices();
        $this->subscription('price_retired_from_2024', 'past_due');

        $this->artisan('deploy:preflight --skip-remote')
            ->expectsOutputToContain('NOT recognised by config');
    }

    public function test_it_reports_the_row_counts_that_decide_migration_cost(): void
    {
        $this->artisan('deploy:preflight --skip-remote')
            ->expectsOutputToContain('coupon rows to reset')
            ->expectsOutputToContain('Migration tables are small enough');
    }

    /**
     * The command must never leave any doubt about which database answered: run from a clone it
     * is a dev database and the answers are meaningless, run on the container it is production.
     */
    public function test_it_names_the_database_it_connected_to(): void
    {
        $this->artisan('deploy:preflight --skip-remote')
            ->expectsOutputToContain('connected to '.config('database.connections.'.config('database.default').'.database'));
    }
}
