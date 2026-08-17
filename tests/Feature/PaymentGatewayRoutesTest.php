<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Services\Payments\PaymentGatewayManager;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The generic payments/{gateway}/... endpoints, which exist so a new gateway needs no routes of its
 * own. These pin the guards that sit in front of every driver.
 */
class PaymentGatewayRoutesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function saleOnMethod(string $method): Sale
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['payment_method' => $method]);

        return Sale::create([
            'event_id' => $event->id,
            'event_date' => $event->starts_at,
            'user_id' => $owner->id,
            'subdomain' => $role->subdomain,
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'status' => 'unpaid',
            'payment_method' => $method,
            'payment_amount' => 100,
            'secret' => \Illuminate\Support\Str::random(32),
        ]);
    }

    public function test_an_unknown_gateway_is_not_routable(): void
    {
        $sale = $this->saleOnMethod('cash');
        $id = UrlUtils::encodeId($sale->id);

        $this->post("/payments/not_a_gateway/webhook/{$id}")->assertNotFound();
        $this->get("/payments/not_a_gateway/return/{$id}")->assertNotFound();
        $this->get("/payments/not_a_gateway/cancel/{$id}")->assertNotFound();
    }

    public function test_a_callback_cannot_settle_another_gateways_sale(): void
    {
        // The guard that matters most here. Without it a driver would run its own signature check,
        // against its own credentials, over a sale that belongs to a different rail and a different
        // merchant account.
        $sale = $this->saleOnMethod('cash');
        $id = UrlUtils::encodeId($sale->id);

        $this->post("/payments/stripe/webhook/{$id}")->assertNotFound();
        $this->get("/payments/stripe/return/{$id}")->assertNotFound();
        $this->get("/payments/stripe/cancel/{$id}")->assertNotFound();
    }

    public function test_a_callback_for_a_missing_sale_is_rejected(): void
    {
        $missing = UrlUtils::encodeId(987654321);

        $this->post("/payments/cash/webhook/{$missing}")->assertNotFound();
        $this->get("/payments/cash/return/{$missing}")->assertNotFound();
    }

    public function test_the_webhook_route_is_exempt_from_csrf(): void
    {
        // A provider cannot carry our session token, so a 419 here would mean every callback is lost.
        // Asserted by the absence of 419 rather than a success code, because the driver's own reply is
        // its business.
        $sale = $this->saleOnMethod('cash');
        $id = UrlUtils::encodeId($sale->id);

        $response = $this->post("/payments/cash/webhook/{$id}");

        $this->assertNotSame(419, $response->getStatusCode());
    }

    public function test_the_default_cancel_releases_an_unpaid_sale(): void
    {
        $sale = $this->saleOnMethod('cash');

        $this->get('/payments/cash/cancel/'.UrlUtils::encodeId($sale->id))->assertRedirect();

        // Seats go back via the Sale::booted release hooks, which the status change fires.
        $this->assertSame('expired', $sale->fresh()->status);
    }

    public function test_the_default_cancel_leaves_a_paid_sale_alone(): void
    {
        $sale = $this->saleOnMethod('cash');
        $sale->status = 'paid';
        $sale->save();

        $this->get('/payments/cash/cancel/'.UrlUtils::encodeId($sale->id))->assertRedirect();

        // A late or replayed cancel must never un-pay a sale: expiry already has its own restore
        // hooks, and running them against a paid sale would give back seats that are still sold.
        $this->assertSame('paid', $sale->fresh()->status);
    }

    public function test_connect_requires_a_gateway_with_credential_fields(): void
    {
        $owner = $this->createOwner();

        // Stripe and Invoice Ninja connect through their own flows, so the generic form must not be a
        // second, unvalidated way into their credentials.
        $this->actingAs($owner)->post('/payments/stripe/connect')->assertNotFound();
        $this->actingAs($owner)->post('/payments/cash/connect')->assertNotFound();
        $this->actingAs($owner)->post('/payments/not_a_gateway/connect')->assertNotFound();
    }

    public function test_connect_requires_authentication(): void
    {
        $this->post('/payments/stripe/connect')->assertRedirect();
    }

    public function test_only_gateways_with_settings_get_a_tab(): void
    {
        $keys = array_keys(app(PaymentGatewayManager::class)->withSettings());

        // Cash has nothing to configure, so it must not produce an empty tab.
        $this->assertSame(['stripe', 'invoiceninja', 'payment_url', 'payfast'], $keys);
    }

    public function test_the_payment_settings_section_renders(): void
    {
        // The tab strip and every tab body now come from the registry via @include, so a broken
        // partial path or an unbalanced extraction shows up as a 500 here rather than in production.
        $owner = $this->createOwner();
        $this->createRole($owner);

        $this->actingAs($owner)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('payment-tab-stripe', escape: false)
            ->assertSee('payment-tab-invoiceninja', escape: false)
            ->assertSee('payment-tab-payment-url', escape: false)
            // Payfast has no settings blade of its own - its tab comes entirely from
            // credentialFields() via the shared credentials partial, which is the whole point.
            ->assertSee('payment-tab-payfast', escape: false)
            ->assertSee('payfast_merchant_id', escape: false);
    }
}
