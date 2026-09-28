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
}
