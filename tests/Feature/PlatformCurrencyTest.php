<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Utils\MoneyUtils;
use App\Utils\PlatformCurrency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The installation's own currency: the symbol beside every price Event Schedule quotes for
 * itself, and the fallback currency for a new event.
 *
 * The amounts were centralised on STRIPE_PRICE_*_AMOUNT long before the symbol was, so the whole
 * marketing site read "${{ $proMonthly }}" - config moved the number while the dollar sign stayed
 * welded on. An operator selling in another currency had to edit Blade files by hand, and lost
 * that work on every upgrade.
 */
class PlatformCurrencyTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        PlatformCurrency::flush();
        config(['app.platform_currency' => 'USD']);
    }

    protected function tearDown(): void
    {
        PlatformCurrency::flush();

        parent::tearDown();
    }

    private function adminActing(): User
    {
        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        return $admin;
    }

    private function useCurrency(string $code): void
    {
        Setting::set('platform_currency', $code);
        PlatformCurrency::flush();
    }

    // ---------------------------------------------------------------- resolution

    public function test_it_falls_back_to_config_then_usd(): void
    {
        $this->assertSame('USD', PlatformCurrency::code());
        $this->assertSame('$9', plan_price(9));

        config(['app.platform_currency' => 'ZAR']);
        PlatformCurrency::flush();

        $this->assertSame('ZAR', PlatformCurrency::code());
    }

    public function test_the_setting_overrides_config(): void
    {
        config(['app.platform_currency' => 'EUR']);
        $this->useCurrency('ZAR');

        $this->assertSame('ZAR', PlatformCurrency::code());
        $this->assertSame('R', PlatformCurrency::symbol());
        $this->assertSame('R9', plan_price(9));
        $this->assertSame('R0', plan_price(0));
    }

    /**
     * code() memoizes for the request, because /pricing renders ~40 prices in one response and
     * Setting::get() reaches the cache store on every call. A save has to drop that memo or the
     * redirect straight after it renders the currency the request started with.
     */
    public function test_flush_drops_the_per_request_memo(): void
    {
        $this->assertSame('USD', PlatformCurrency::code());

        Setting::set('platform_currency', 'ZAR');
        $this->assertSame('USD', PlatformCurrency::code(), 'memoized within the request');

        PlatformCurrency::flush();
        $this->assertSame('ZAR', PlatformCurrency::code());
    }

    public function test_a_currency_without_a_glyph_falls_back_to_its_code(): void
    {
        $this->useCurrency('CHF');

        $this->assertSame('9 CHF', plan_price(9));
        $this->assertSame('CHF', MoneyUtils::symbol('CHF'));
    }

    /**
     * A zero-decimal currency has no minor unit, so a plan price must not grow a ".00" tail and
     * the Stripe multiplier must be 1 rather than 100.
     */
    public function test_a_zero_decimal_currency_is_formatted_without_minor_units(): void
    {
        $this->useCurrency('JPY');

        $this->assertSame('¥1,200', plan_price(1200));
        $this->assertSame(1, MoneyUtils::getSmallestUnitMultiplier(PlatformCurrency::code()));
    }

    // ---------------------------------------------------------------- default for new events

    public function test_it_is_the_fallback_currency_for_a_schedule_with_no_country(): void
    {
        $this->useCurrency('ZAR');

        $this->assertSame('ZAR', MoneyUtils::getCurrencyForCountry(null));
        $this->assertSame('ZAR', MoneyUtils::getCurrencyForCountry(''));
    }

    public function test_a_schedule_with_a_country_keeps_its_own_currency(): void
    {
        $this->useCurrency('ZAR');

        $this->assertSame('GBP', MoneyUtils::getCurrencyForCountry('GB'));
        $this->assertSame('JPY', MoneyUtils::getCurrencyForCountry('JP'));
        $this->assertSame('EUR', MoneyUtils::getCurrencyForCountry('DE'));
    }

    // ---------------------------------------------------------------- the admin endpoint

    public function test_an_admin_can_store_the_currency(): void
    {
        $this->adminActing();

        $this->post(route('admin.settings.update_currency'), ['platform_currency' => 'ZAR'])
            ->assertRedirect(route('admin.settings'));

        Cache::flush();
        PlatformCurrency::flush();
        $this->assertSame('ZAR', Setting::get('platform_currency'));
        $this->assertSame('R9', plan_price(9));
    }

    public function test_a_non_admin_cannot_change_the_currency(): void
    {
        $this->actingAs($this->createOwner());

        $this->post(route('admin.settings.update_currency'), ['platform_currency' => 'ZAR']);

        Cache::flush();
        $this->assertNull(Setting::get('platform_currency'));
    }

    /**
     * Validated against storage/currencies.json, not just size:3. Nothing else in the app checks
     * a currency code against that list, so a typo would otherwise render as a bare three-letter
     * suffix on every price the platform quotes.
     */
    public function test_a_currency_outside_the_offered_list_is_rejected(): void
    {
        $this->adminActing();

        $this->post(route('admin.settings.update_currency'), ['platform_currency' => 'XYZ'])
            ->assertSessionHasErrors('platform_currency');

        Cache::flush();
        $this->assertNull(Setting::get('platform_currency'));
    }

    public function test_the_card_is_rendered_for_every_install(): void
    {
        $this->adminActing();

        $this->get(route('admin.settings'))
            ->assertOk()
            ->assertSee(__('messages.platform_currency_title'))
            ->assertSee('name="platform_currency"', false);
    }
}
