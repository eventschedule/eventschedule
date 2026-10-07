<?php

namespace Tests\Feature;

use App\Services\DemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * /admin/revenue sums sales platform-wide, and the demo schedule's sales are re-seeded every hour
 * as paid Stripe sales, so without an exclusion they are most of every figure on the page.
 */
class AdminRevenueTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    public function test_the_headline_figures_exclude_demo_sales(): void
    {
        $admin = $this->createOwner(true);

        // Seeded the way DemoService seeds it: the schedule's contact address is DEMO_EMAIL.
        $demo = $this->createRole($this->createOwner(), 'curator', [
            'subdomain' => DemoService::DEMO_ROLE_SUBDOMAIN, 'email' => DemoService::DEMO_EMAIL,
        ]);
        $demoEvent = $this->createEvent($demo);
        $this->createSale($demoEvent, $demo, ['status' => 'paid', 'payment_amount' => 5000]);
        $this->createSale($demoEvent, $demo, ['status' => 'unpaid', 'payment_amount' => 700]);

        $real = $this->createRole($this->createOwner());
        $realEvent = $this->createEvent($real);
        $this->createSale($realEvent, $real, ['status' => 'paid', 'payment_amount' => 30]);

        // Named before cleanSubdomain() reserved the prefix: real money, and it must count.
        $legacy = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'demo-night']);
        $legacyEvent = $this->createEvent($legacy);
        $this->createSale($legacyEvent, $legacy, ['status' => 'paid', 'payment_amount' => 20]);

        $response = $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($admin)
            ->get('/admin/revenue');

        $response->assertOk();
        $this->assertEquals(50, $response->viewData('totalRevenue'));
        $this->assertEquals(50, $response->viewData('revenueInPeriod'));
        $this->assertSame(2, $response->viewData('totalSales'));
        $this->assertSame(2, $response->viewData('salesInPeriod'));
        $this->assertSame(0, $response->viewData('pendingSales'));
        $this->assertEquals(0, $response->viewData('pendingRevenue'));

        // The table below the totals applies the same exclusion.
        $this->assertEqualsCanonicalizing(
            [$realEvent->id, $legacyEvent->id],
            $response->viewData('recentSales')->pluck('event_id')->all()
        );
    }

    /**
     * A sale has no currency of its own: it is in its event's ticket currency. The headline
     * figures summed every paid sale's amount and printed the total with the platform's symbol,
     * so 30 dollars, 40 euros and 100 shekels were "$170". Each figure is one currency now: the
     * platform's own in the tile, the others named beside it.
     */
    public function test_money_in_different_currencies_is_never_added_together(): void
    {
        $admin = $this->createOwner(true);
        $role = $this->createRole($this->createOwner());

        $usd = $this->createEvent($role, ['ticket_currency_code' => 'USD']);
        $unset = $this->createEvent($role);   // no currency set: shown as USD everywhere else
        $eur = $this->createEvent($role, ['ticket_currency_code' => 'EUR']);
        $ils = $this->createEvent($role, ['ticket_currency_code' => 'ils']);

        $this->createSale($usd, $role, ['status' => 'paid', 'payment_amount' => 30]);
        $this->createSale($unset, $role, ['status' => 'paid', 'payment_amount' => 5]);
        $this->createSale($eur, $role, ['status' => 'paid', 'payment_amount' => 40]);
        $this->createSale($ils, $role, ['status' => 'paid', 'payment_amount' => 100]);
        $this->createSale($eur, $role, ['status' => 'unpaid', 'payment_amount' => 15]);
        $this->createSale($usd, $role, ['status' => 'unpaid', 'payment_amount' => 7]);

        $response = $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($admin)
            ->get('/admin/revenue')
            ->assertOk();

        $this->assertSame('USD', platform_currency());
        $this->assertEquals(35, $response->viewData('totalRevenue'));
        $this->assertEquals(35, $response->viewData('revenueInPeriod'));
        $this->assertEquals(7, $response->viewData('pendingRevenue'));
        $this->assertEquals(['EUR' => 40.0, 'ILS' => 100.0], $response->viewData('otherRevenue'));
        $this->assertEquals(['EUR' => 15.0], $response->viewData('otherPending'));
        // Every sale is still counted as a sale.
        $this->assertSame(4, $response->viewData('totalSales'));

        $html = $response->getContent();
        $this->assertStringContainsString(e(plan_price(35)), $html);
        $this->assertStringNotContainsString(e(plan_price(175)), $html);
        $this->assertSame(1, substr_count($html, e(\App\Utils\MoneyUtils::format(40, 'EUR'))), 'euros named once, beside the total');
        $this->assertSame(1, substr_count($html, e(\App\Utils\MoneyUtils::format(100, 'ILS'))));
        $this->assertSame(1, substr_count($html, e(\App\Utils\MoneyUtils::format(15, 'EUR'))), 'and the unpaid euros beside what is pending');
    }
}
