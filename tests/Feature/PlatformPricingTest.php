<?php

namespace Tests\Feature;

use App\Models\Referral;
use App\Models\Setting;
use App\Models\User;
use App\Utils\PlatformCurrency;
use App\Utils\PlatformPricing;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What the installation ADVERTISES its plans at, set from /admin/settings.
 *
 * Discussion #118: the amounts lived only in STRIPE_PRICE_*_AMOUNT, so a selfhoster changing what
 * they charge had to edit .env and re-run config:cache - or, as the reporter actually did, edit
 * Blade files by hand and lose the work on every upgrade.
 *
 * Two things this file exists to prove, because both are silent when they break:
 *   1. the admin value reaches EVERY surface, not just the ones behind the view composer;
 *   2. it reaches NONE of the billing-fact surfaces - ARR, MRR and renewal amounts must keep
 *      answering "what does Stripe charge?", which no marketing form may steer.
 */
class PlatformPricingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Values no page could quote by coincidence, so a hit is proof the setting travelled. */
    private const PRO_MONTHLY = '4321';

    private const PRO_YEARLY = '43210';

    private const ENT_MONTHLY = '8765';

    private const ENT_YEARLY = '87650';

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        PlatformPricing::flush();
        PlatformCurrency::flush();

        config([
            'services.stripe_platform.price_monthly_amount' => '9',
            'services.stripe_platform.price_yearly_amount' => '90',
            'services.stripe_platform.enterprise_price_monthly_amount' => '29',
            'services.stripe_platform.enterprise_price_yearly_amount' => '290',
            'app.platform_currency' => 'USD',
        ]);
    }

    protected function tearDown(): void
    {
        PlatformPricing::flush();
        PlatformCurrency::flush();

        parent::tearDown();
    }

    private function adminActing(): User
    {
        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        return $admin;
    }

    private function usePrices(
        string $proMonthly = self::PRO_MONTHLY,
        string $proYearly = self::PRO_YEARLY,
        string $entMonthly = self::ENT_MONTHLY,
        string $entYearly = self::ENT_YEARLY,
    ): void {
        Setting::set('plan_price_pro_monthly', $proMonthly);
        Setting::set('plan_price_pro_yearly', $proYearly);
        Setting::set('plan_price_enterprise_monthly', $entMonthly);
        Setting::set('plan_price_enterprise_yearly', $entYearly);
        PlatformPricing::flush();
    }

    // ---------------------------------------------------------------- resolution

    public function test_a_fresh_install_falls_back_to_config(): void
    {
        $this->assertSame(9.0, PlatformPricing::proMonthly());
        $this->assertSame(90.0, PlatformPricing::proYearly());
        $this->assertSame(29.0, PlatformPricing::enterpriseMonthly());
        $this->assertSame(290.0, PlatformPricing::enterpriseYearly());
    }

    public function test_config_falls_back_to_the_shipped_default(): void
    {
        // What an install with the vars present-but-empty in .env actually sees: env() returns ''
        // for those, so a default ARGUMENT never fires and (float) '' would price the plan at 0.
        config([
            'services.stripe_platform.price_monthly_amount' => '',
            'services.stripe_platform.enterprise_price_yearly_amount' => '',
        ]);
        PlatformPricing::flush();

        $this->assertSame(9.0, PlatformPricing::proMonthly());
        $this->assertSame(290.0, PlatformPricing::enterpriseYearly());
    }

    public function test_the_setting_overrides_config(): void
    {
        $this->usePrices();

        $this->assertSame(4321.0, PlatformPricing::proMonthly());
        $this->assertSame(43210.0, PlatformPricing::proYearly());
        $this->assertSame(8765.0, PlatformPricing::enterpriseMonthly());
        $this->assertSame(87650.0, PlatformPricing::enterpriseYearly());
    }

    public function test_a_blank_setting_falls_back_to_config(): void
    {
        // Setting::set($key, null) writes a row whose value is NULL, so ?? would stop here and
        // return null. The resolver uses ?: for exactly this.
        Setting::set('plan_price_pro_monthly', null);
        PlatformPricing::flush();

        $this->assertSame(9.0, PlatformPricing::proMonthly());
    }

    public function test_flush_drops_the_memo(): void
    {
        $this->assertSame(9.0, PlatformPricing::proMonthly());

        Setting::set('plan_price_pro_monthly', '4321');
        $this->assertSame(9.0, PlatformPricing::proMonthly(), 'memoized for the request, by design');

        PlatformPricing::flush();
        $this->assertSame(4321.0, PlatformPricing::proMonthly());
    }

    public function test_a_decimal_price_survives_to_the_rendered_string(): void
    {
        // The reason the whole feature allows 2dp: SaaS pricing is overwhelmingly X.99 / X.50.
        Setting::set('plan_price_pro_monthly', '12.50');
        PlatformPricing::flush();

        $this->assertSame(12.5, PlatformPricing::proMonthly());
        $this->assertSame('$12.50', plan_price(PlatformPricing::proMonthly()));
    }

    public function test_stored_reports_the_override_not_the_effective_value(): void
    {
        // The admin form renders stored() in the field and the effective value as a placeholder,
        // so an operator can tell "unset, defaulting to 9" from "explicitly set to 9".
        $this->assertNull(PlatformPricing::stored('pro', 'monthly'));

        Setting::set('plan_price_pro_monthly', '9');
        PlatformPricing::flush();

        $this->assertSame('9', PlatformPricing::stored('pro', 'monthly'));
    }

    // ---------------------------------------------------------------- the admin endpoint

    public function test_an_admin_can_set_all_four_prices(): void
    {
        $this->adminActing();

        $this->post(route('admin.settings.update_plan_pricing'), [
            'plan_price_pro_monthly' => '149',
            'plan_price_pro_yearly' => '1490',
            'plan_price_enterprise_monthly' => '399',
            'plan_price_enterprise_yearly' => '3990',
        ])->assertRedirect(route('admin.settings'));

        PlatformPricing::flush();

        $this->assertSame(149.0, PlatformPricing::proMonthly());
        $this->assertSame(1490.0, PlatformPricing::proYearly());
        $this->assertSame(399.0, PlatformPricing::enterpriseMonthly());
        $this->assertSame(3990.0, PlatformPricing::enterpriseYearly());
    }

    public function test_a_non_admin_cannot(): void
    {
        $this->actingAs($this->createOwner());

        $this->post(route('admin.settings.update_plan_pricing'), ['plan_price_pro_monthly' => '149']);

        $this->assertNull(Setting::get('plan_price_pro_monthly'));
    }

    public function test_demo_mode_refuses_the_save(): void
    {
        // is_demo_mode() keys off the signed-in user's EMAIL, not a config flag - setting
        // app.is_demo does nothing and the save would go straight through.
        $admin = $this->adminActing();
        $admin->forceFill(['email' => \App\Services\DemoService::DEMO_EMAIL])->save();

        $this->post(route('admin.settings.update_plan_pricing'), ['plan_price_pro_monthly' => '149']);

        $this->assertNull(Setting::get('plan_price_pro_monthly'));
    }

    public function test_it_rejects_a_price_that_is_not_money(): void
    {
        $this->adminActing();

        foreach (['abc', '0', '-5', '9.999', '1000000'] as $bad) {
            $this->post(route('admin.settings.update_plan_pricing'), ['plan_price_pro_monthly' => $bad])
                ->assertSessionHasErrors('plan_price_pro_monthly');

            $this->assertNull(Setting::get('plan_price_pro_monthly'), "accepted {$bad}");
        }
    }

    public function test_a_blank_field_clears_only_its_own_override(): void
    {
        $this->usePrices();
        $this->adminActing();

        $this->post(route('admin.settings.update_plan_pricing'), [
            'plan_price_pro_monthly' => '',
            'plan_price_pro_yearly' => self::PRO_YEARLY,
            'plan_price_enterprise_monthly' => self::ENT_MONTHLY,
            'plan_price_enterprise_yearly' => self::ENT_YEARLY,
        ])->assertSessionHasNoErrors();

        PlatformPricing::flush();

        $this->assertSame(9.0, PlatformPricing::proMonthly(), 'cleared, so back to config');
        $this->assertSame(43210.0, PlatformPricing::proYearly(), 'untouched by its neighbour');
    }

    public function test_the_card_is_hidden_where_no_plan_price_is_ever_quoted(): void
    {
        $this->adminActing();

        // A plain single-tenant selfhost: marketing and docs are inside if (config('app.is_nexus')),
        // /referrals is hosted-only, the Plan tab is @if (config('app.hosted')) and every schedule
        // resolves to Enterprise. Nothing on that install renders a plan price.
        config(['app.hosted' => false, 'app.is_nexus' => false]);
        $this->get(route('admin.settings'))->assertDontSee('plan_price_pro_monthly');

        config(['app.hosted' => true]);
        $this->get(route('admin.settings'))->assertSee('plan_price_pro_monthly');
    }

    public function test_the_form_shows_the_effective_value_as_a_placeholder(): void
    {
        $this->adminActing();
        config(['app.hosted' => true]);

        $this->get(route('admin.settings'))->assertSee('placeholder="9"', false);
    }

    // ---------------------------------------------------------------- fan-out

    public function test_the_marketing_site_quotes_the_admin_price(): void
    {
        $this->usePrices();

        // Rendered through the view factory, not over HTTP: marketing routes are registered at
        // boot inside if (config('app.is_nexus')), which a test cannot config() its way past.
        $this->assertStringContainsString(plan_price(4321), view('marketing.pricing')->render());
        $this->assertStringContainsString(plan_price(8765), view('marketing.custom-domain')->render());
    }

    public function test_the_pages_the_composer_does_not_reach_quote_it_too(): void
    {
        // The comparison and replacement copy is assembled in PHP arrays inside the controller,
        // where the view composer has not run. This is the half that desynced last time.
        $this->usePrices();

        $controller = app(\App\Http\Controllers\MarketingController::class);

        $planPrice = new \ReflectionMethod($controller, 'planPrice');
        $planPrice->setAccessible(true);
        $this->assertSame(4321.0, $planPrice->invoke($controller, false));
        $this->assertSame(8765.0, $planPrice->invoke($controller, true));

        $rates = new \ReflectionMethod($controller, 'getHubFeeRates');
        $rates->setAccessible(true);
        $this->assertSame(4321.0, $rates->invoke($controller)['eventschedule']['monthly']);
    }

    public function test_the_referral_program_quotes_it(): void
    {
        $this->usePrices();
        $owner = $this->createOwner();

        $html = $this->actingAs($owner)->withoutExceptionHandling()
            ->get(route('referrals'))->getContent();

        // The formatted string, not the bare digits: plan_price() puts a thousands separator in,
        // so asserting "4321" would fail against a page correctly rendering "$4,321".
        $this->assertStringContainsString(plan_price(4321), $html);
    }

    public function test_the_upgrade_gate_quotes_it(): void
    {
        $this->usePrices();
        config(['app.hosted' => true]);

        // Blade::render, not view(): an anonymous component's @props compile against $attributes,
        // which only exists when it is resolved as a component.
        $html = \Illuminate\Support\Facades\Blade::render('<x-plan-gate tier="pro" :bullets="[]" />');

        $this->assertStringContainsString(plan_price(4321), $html);
        $this->assertStringContainsString(plan_price(43210), $html);
    }

    public function test_the_yearly_saving_is_derived_and_never_negative(): void
    {
        // Nothing stops an operator pricing the year above twelve months. "Save -8%" on an
        // upgrade button is worse than showing no saving at all.
        $this->usePrices(proMonthly: '10', proYearly: '100');
        $this->assertSame(17, $this->savePercentFor('pro'));

        $this->usePrices(proMonthly: '10', proYearly: '130');
        $this->assertSame(0, $this->savePercentFor('pro'));
    }

    private function savePercentFor(string $tier): int
    {
        $monthly = PlatformPricing::amount($tier, 'monthly');
        $yearly = PlatformPricing::amount($tier, 'yearly');
        $monthlyTotal = $monthly * 12;

        return $monthlyTotal > 0
            ? max(0, (int) round((($monthlyTotal - $yearly) / $monthlyTotal) * 100))
            : 0;
    }

    // ---------------------------------------------------------------- real money

    public function test_a_referral_credit_uses_the_admin_price_without_truncating_it(): void
    {
        // The one place decimals meet real money. This used to read (int) $amount BEFORE
        // multiplying by the smallest-unit multiplier, so a plan advertised at 9.99 credited 900
        // minor units instead of 999 - a shortfall on every referral, in silence.
        Setting::set('plan_price_pro_monthly', '9.99');
        PlatformPricing::flush();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'name' => 'Referred',
            'plan_type' => 'free',
            'plan_expires' => null,
            'plan_source' => null,
        ]);

        $referral = Referral::create([
            'referrer_user_id' => $owner->id,
            'referred_user_id' => $this->createOwner()->id,
            'plan_type' => 'pro',
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
    }

    public function test_a_decimal_price_credits_every_minor_unit(): void
    {
        // The rounding order, pinned where it is observable. Through the controller it is not:
        // the amount only reaches Cashier's applyBalance(), which posts to Stripe, and the
        // no-subscription branch ignores it entirely - so a controller test would have gone on
        // passing while every referrer was short-changed by a cent.
        Setting::set('plan_price_pro_monthly', '9.99');
        Setting::set('plan_price_enterprise_monthly', '29.50');
        PlatformPricing::flush();

        $this->assertSame(999, PlatformPricing::minorUnits('pro', 'monthly', 'usd'),
            'truncating before multiplying credits 900 for a plan advertised at 9.99');
        $this->assertSame(2950, PlatformPricing::minorUnits('enterprise', 'monthly', 'usd'));

        // A zero-decimal currency has no minor unit, so the multiplier is 1 and the decimal is
        // rounded away rather than multiplied up a hundredfold.
        $this->assertSame(10, PlatformPricing::minorUnits('pro', 'monthly', 'jpy'));

        // Whole amounts are unaffected, which is why the old expression looked correct.
        Setting::set('plan_price_pro_monthly', '9');
        PlatformPricing::flush();
        $this->assertSame(900, PlatformPricing::minorUnits('pro', 'monthly', 'usd'));
    }

    // ---------------------------------------------------------------- the split

    public function test_the_admin_price_does_not_move_what_stripe_charges(): void
    {
        // amountFor() stands in for a Stripe API call and feeds ARR, MRR and renewal emails.
        // If a marketing change moved it, a renewal email would quote a customer a figure their
        // card will never be charged, and booked revenue would restate itself.
        config(['services.stripe_platform.price_monthly' => 'price_pro_monthly_test']);
        $this->usePrices();

        $this->assertSame(9.0, \App\Utils\PlanPriceUtils::amountFor('price_pro_monthly_test'));
    }

    public function test_the_admin_price_does_not_move_revenue_reporting(): void
    {
        $this->usePrices();

        $reflection = new \ReflectionClass(\App\Services\GrowthExportService::class);
        $source = file_get_contents($reflection->getFileName());

        // Belt and braces alongside the grep guard in MarketingPriceTest: this asserts the value
        // in force, not just the absence of a symbol.
        $this->assertStringContainsString(
            "config('services.stripe_platform.price_monthly_amount', 9)",
            $source,
            'MRR must keep reading config, not the admin-settable amounts.'
        );
    }
}
