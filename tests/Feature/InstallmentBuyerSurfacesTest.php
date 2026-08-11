<?php

namespace Tests\Feature;

use App\Services\InstallmentService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The three places a buyer meets their payment plan: their ticket page, the standalone pay page,
 * and the door.
 */
class InstallmentBuyerSurfacesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function scaffold(): array
    {
        $owner = $this->createOwner();
        $owner->stripe_account_id = 'acct_merchant';
        $owner->save();

        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'payment_method' => 'stripe',
            'installments_enabled' => true,
            'installment_count' => 4,
            // Inside the check-in window so scanned() reaches the payment branch.
            'starts_at' => now()->addHours(2)->format('Y-m-d H:i:s'),
        ]);
        $ticket = $this->createTicket($event, ['price' => 1000, 'quantity' => 10]);
        $sale = $this->createSale($event, $role, [
            'status' => 'paid', 'payment_amount' => 1000, 'email' => 'buyer@gmail.com',
        ], $ticket);

        $plan = app(InstallmentService::class)->createPlan($sale, $event, 1000.00, 'USD');
        $plan->installments->firstWhere('sequence', 1)->update(['status' => 'paid', 'paid_at' => now()]);
        $plan->update(['amount_paid' => 250, 'card_brand' => 'visa', 'card_last4' => '4242']);

        return [$owner, $role, $event, $sale, $plan->fresh('installments')];
    }

    // ---- The standalone pay page ----

    public function test_the_pay_page_opens_with_the_plan_secret(): void
    {
        [, , , , $plan] = $this->scaffold();

        $this->get(route('installment.view', [
            'plan_id' => UrlUtils::encodeId($plan->id),
            'secret' => $plan->secret,
        ]))->assertOk()->assertSee('250');
    }

    /**
     * The plan's secret is deliberately not the sale's, so a forwarded ticket link cannot reach
     * somebody else's schedule or pay against it.
     */
    public function test_a_wrong_secret_is_refused(): void
    {
        [, , , $sale, $plan] = $this->scaffold();

        $this->get(route('installment.view', [
            'plan_id' => UrlUtils::encodeId($plan->id),
            'secret' => 'not-the-secret',
        ]))->assertNotFound();

        // Even the SALE's own secret must not open the plan page.
        $this->get(route('installment.view', [
            'plan_id' => UrlUtils::encodeId($plan->id),
            'secret' => $sale->secret,
        ]))->assertNotFound();
    }

    public function test_the_pay_page_does_not_leak_stripe_credentials(): void
    {
        [, , , , $plan] = $this->scaffold();
        $plan->update(['stripe_customer_id' => 'cus_leak', 'stripe_payment_method_id' => 'pm_leak']);

        $response = $this->get(route('installment.view', [
            'plan_id' => UrlUtils::encodeId($plan->id),
            'secret' => $plan->secret,
        ]))->assertOk();

        $response->assertDontSee('cus_leak');
        $response->assertDontSee('pm_leak');
    }

    public function test_paying_a_cancelled_plan_is_refused(): void
    {
        [, , , , $plan] = $this->scaffold();
        app(InstallmentService::class)->cancelPlan($plan, 'sale_refunded');

        $this->post(route('installment.pay', [
            'plan_id' => UrlUtils::encodeId($plan->id),
            'secret' => $plan->secret,
        ]), ['mode' => 'next'])
            ->assertRedirect();

        // Nothing was reopened.
        $this->assertSame('cancelled', $plan->fresh()->status);
    }

    // ---- The door ----

    public function test_a_current_plan_scans_normally(): void
    {
        [$owner, , $event, $sale] = $this->scaffold();

        $response = $this->actingAs($owner)->post(route('ticket.scanned', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]))->assertOk();

        $response->assertJsonMissing(['payment_status' => 'overdue']);
        $this->assertArrayHasKey('attendee', $response->json());
    }

    /**
     * The point of the whole door treatment: a plan in arrears does NOT scan through, but it also
     * does not come back as a bare error. The operator gets the name and the balance so they can
     * take payment or wave the guest in.
     */
    public function test_an_overdue_plan_returns_an_actionable_payload_not_an_error(): void
    {
        [$owner, , $event, $sale, $plan] = $this->scaffold();
        $plan->update(['status' => 'delinquent', 'delinquent_at' => now()]);

        $response = $this->actingAs($owner)->post(route('ticket.scanned', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]))->assertOk();

        $json = $response->json();

        $this->assertSame('overdue', $json['payment_status'] ?? null);
        // Not an `error` key: that is what paints the scanner red and hides every detail.
        $this->assertArrayNotHasKey('error', $json);
        $this->assertSame('Buyer Name', $json['attendee'] ?? null);
        $this->assertNotEmpty($json['amount_due'] ?? null);
        $this->assertNotEmpty($json['amount_paid'] ?? null);
    }

    /**
     * Paying up must restore entry without anyone intervening.
     */
    public function test_clearing_the_arrears_restores_the_scan(): void
    {
        [$owner, , $event, $sale, $plan] = $this->scaffold();
        $plan->update(['status' => 'delinquent', 'delinquent_at' => now()]);
        $plan->update(['status' => 'active', 'delinquent_at' => null]);

        $response = $this->actingAs($owner)->post(route('ticket.scanned', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]))->assertOk();

        $this->assertArrayNotHasKey('payment_status', $response->json());
    }

    // ---- The buyer's ticket page ----

    public function test_the_ticket_page_shows_the_plan(): void
    {
        [, , $event, $sale] = $this->scaffold();

        $this->get(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]))->assertOk()->assertSee(__('messages.payment_plan'));
    }

    public function test_an_overdue_ticket_page_says_on_hold_not_void(): void
    {
        [, , $event, $sale, $plan] = $this->scaffold();
        $plan->update(['status' => 'delinquent', 'delinquent_at' => now()]);

        $response = $this->get(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]))->assertOk();

        $response->assertSee(__('messages.ticket_on_hold'));
        // The schema word must never surface.
        $response->assertDontSee('delinquent');
    }
}
