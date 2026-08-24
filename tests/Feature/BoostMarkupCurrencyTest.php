<?php

namespace Tests\Feature;

use App\Models\BoostBillingRecord;
use App\Models\BoostCampaign;
use App\Models\Setting;
use App\Models\User;
use App\Services\BoostBillingService;
use App\Utils\PlatformCurrency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * "Boost Markup Revenue on Dashboard still shows $0 even though we selected ZAR Currency."
 *
 * The tile formatted with config('services.meta.default_currency', 'USD') - the Meta ad account's
 * currency, a variable that is not in .env.example and is therefore USD on every selfhost. So the
 * figure printed dollars on an install set to ZAR, directly above a chart axis already rendering R.
 *
 * markup_amount is also written by network promotions, which bill in PROMOTIONS_CURRENCY, and on a
 * selfhost the Meta markup rate is forced to 0 - so on the reporting operator's install every
 * non-zero markup was network revenue wearing a Meta label.
 */
class BoostMarkupCurrencyTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        PlatformCurrency::flush();
        config([
            'app.platform_currency' => 'USD',
            // Unset, exactly as it ships: absent from .env.example, so this is what a selfhost has.
            'services.meta.default_currency' => null,
        ]);
    }

    protected function tearDown(): void
    {
        PlatformCurrency::flush();

        parent::tearDown();
    }

    private function useCurrency(string $code): void
    {
        Setting::set('platform_currency', $code);
        PlatformCurrency::flush();
    }

    private function adminActing(): User
    {
        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        return $admin;
    }

    private function chargeOf(float $markup, string $currency): BoostBillingRecord
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role);

        $campaign = BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'name' => 'Campaign in '.$currency,
            'currency_code' => $currency,
            'user_budget' => 100,
            'status' => 'active',
        ]);

        return BoostBillingRecord::create([
            'boost_campaign_id' => $campaign->id,
            'type' => 'charge',
            'amount' => $markup * 5,
            'markup_amount' => $markup,
            'status' => 'completed',
        ]);
    }

    public function test_with_no_campaigns_it_labels_zero_in_the_platform_currency(): void
    {
        // The reported case exactly: an operator who has never run a campaign, on ZAR.
        $this->useCurrency('ZAR');

        $this->assertSame('ZAR', BoostBillingService::markupCurrency());
    }

    public function test_it_takes_the_currency_carrying_the_most_markup(): void
    {
        $this->useCurrency('ZAR');

        $this->chargeOf(100, 'EUR');
        $this->chargeOf(10, 'USD');

        // Not the newest campaign: ordering by recency would make a fixed historical total change
        // its label every time a campaign in the other currency was created.
        $this->assertSame('EUR', BoostBillingService::markupCurrency());
    }

    public function test_it_ignores_records_outside_the_window(): void
    {
        $this->useCurrency('ZAR');

        $old = $this->chargeOf(500, 'EUR');
        $old->forceFill(['created_at' => now()->subYear()])->save();

        $this->chargeOf(5, 'USD');

        $this->assertSame('USD', BoostBillingService::markupCurrency(now()->subDays(7), now()));
        $this->assertSame('EUR', BoostBillingService::markupCurrency());
    }

    public function test_it_ignores_refunds_and_uncompleted_charges(): void
    {
        $this->useCurrency('ZAR');

        $record = $this->chargeOf(500, 'EUR');
        $record->forceFill(['type' => 'refund'])->save();

        $this->chargeOf(5, 'USD');

        $this->assertSame('USD', BoostBillingService::markupCurrency());
    }

    /**
     * The symptom, on the page the report named.
     *
     * A regex over the rendered tile rather than assertSee: the page is full of other money, so
     * "contains R0" would pass on an unrelated figure while the tile still said $0.
     */
    public function test_the_admin_dashboard_tile_reads_the_platform_currency(): void
    {
        $this->useCurrency('ZAR');
        $this->adminActing();

        $html = $this->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '~boost_markup_revenue|Boost markup revenue~i',
            str_replace('&nbsp;', ' ', $html),
            'the tile is missing entirely, so this test proves nothing'
        );
        $this->assertStringContainsString('R0', $this->tileValueAfter($html, __('messages.boost_markup_revenue')));
    }

    public function test_the_admin_revenue_tiles_read_the_platform_currency(): void
    {
        $this->useCurrency('ZAR');
        $this->adminActing();

        $html = $this->get(route('admin.revenue'))->assertOk()->getContent();
        $value = $this->tileValueAfter($html, __('messages.boost_markup_revenue'));

        $this->assertStringContainsString('R0', $value);
        $this->assertStringNotContainsString('$', $value,
            'the tile disagrees with the plan_price() figures and the chart axis beside it');
    }

    public function test_a_campaigns_own_currency_still_wins_over_the_platform_one(): void
    {
        // Display-only fix: it must not relabel money that was actually taken in dollars.
        $this->useCurrency('ZAR');
        $this->chargeOf(40, 'USD');
        $this->adminActing();

        $html = $this->get(route('admin.revenue'))->assertOk()->getContent();

        $this->assertStringContainsString('$40', $this->tileValueAfter($html, __('messages.boost_markup_revenue')));
    }

    /** The rendered text immediately following a stat panel's label. */
    private function tileValueAfter(string $html, string $label): string
    {
        $position = strpos($html, $label);

        $this->assertNotFalse($position, "no stat panel labelled '{$label}' on the page");

        return preg_replace('~\s+~', ' ', strip_tags(substr($html, $position, 400)));
    }
}
