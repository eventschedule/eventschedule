<?php

namespace Tests\Feature;

use App\Models\GiftCard;
use App\Models\Sale;
use App\Models\SaleInstallmentPlan;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Guest checkout with a payment plan.
 *
 * Every case here posts `installments=1` directly, bypassing the form's own logic, because that
 * is exactly what a hostile or stale client does. The server has to reach the same verdict from
 * the database alone.
 */
class InstallmentCheckoutTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function scaffold(array $eventAttrs = [], array $ticketAttrs = []): array
    {
        $owner = $this->createOwner();
        $owner->stripe_account_id = 'acct_merchant';
        $owner->stripe_completed_at = now();
        $owner->save();

        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, array_merge([
            'payment_method' => 'stripe',
            'ticket_currency_code' => 'USD',
            'tickets_enabled' => true,
            'installments_enabled' => true,
            'installment_count' => 4,
            'installment_final_days_before' => 14,
            'starts_at' => now()->addMonths(8)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ], $eventAttrs));

        $ticket = $this->createTicket($event, array_merge(['price' => 1000, 'quantity' => 10], $ticketAttrs));

        return [$role, $event, $ticket];
    }

    private function checkout($role, $event, $ticket, array $extra = [])
    {
        return $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), array_merge([
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d'),
            'subdomain' => $role->subdomain,
            'name' => 'Wine Buyer',
            'email' => 'buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
        ], $extra));
    }

    private function latestPlan(): ?SaleInstallmentPlan
    {
        return SaleInstallmentPlan::with('installments')->latest('id')->first();
    }

    public function test_a_plan_is_created_with_the_right_split(): void
    {
        [$role, $event, $ticket] = $this->scaffold();

        $this->checkout($role, $event, $ticket, ['installments' => '1', 'installments_consent' => '1']);

        $plan = $this->latestPlan();
        $this->assertNotNull($plan, 'Expected an installment plan');
        $this->assertSame('USD', $plan->currency);
        $this->assertEqualsWithDelta(1000.0, (float) $plan->total_amount, 0.001);
        $this->assertSame(4, $plan->installments->count());
        $this->assertEqualsWithDelta(250.0, (float) $plan->installments->firstWhere('sequence', 1)->amount, 0.001);

        // Installment 1 is already in flight at checkout; the rest wait for the cron.
        $this->assertSame('processing', $plan->installments->firstWhere('sequence', 1)->status);
        $this->assertSame('scheduled', $plan->installments->firstWhere('sequence', 2)->status);

        // The mandate is recorded, which is the only dispute artefact we hold.
        $this->assertNotNull($plan->mandate_accepted_at);
        $this->assertSame('acct_merchant', $plan->stripe_account_id);
    }

    /**
     * The mandate must be genuinely given, not merely displayed.
     *
     * The checkbox originally carried no `name`, so it was never submitted and only disabled the
     * button in the browser - while createPlan() stamped `mandate_accepted_at` regardless. A
     * hand-posted `installments=1` therefore produced a timestamped record asserting the buyer had
     * authorised recurring charges they never saw. That is the one artefact we would offer to
     * defend a disputed charge, so a falsifiable one is worse than none.
     */
    public function test_a_plan_is_refused_without_consent(): void
    {
        [$role, $event, $ticket] = $this->scaffold();

        $response = $this->checkout($role, $event, $ticket, ['installments' => '1']);

        $response->assertSessionHasErrors('installments_consent');
        $this->assertNull($this->latestPlan(), 'No plan may exist without a given mandate');
    }

    public function test_no_plan_without_the_installments_field(): void
    {
        [$role, $event, $ticket] = $this->scaffold();

        $this->checkout($role, $event, $ticket);

        $this->assertNull($this->latestPlan());
    }

    public function test_a_disabled_event_refuses_a_hand_posted_plan(): void
    {
        [$role, $event, $ticket] = $this->scaffold(['installments_enabled' => false]);

        $this->checkout($role, $event, $ticket, ['installments' => '1', 'installments_consent' => '1']);

        $this->assertNull($this->latestPlan(), 'A hand-posted flag must not create a plan');
        // The order itself still goes through as a normal purchase.
        $this->assertNotNull(Sale::latest('id')->first());
    }

    public function test_a_non_stripe_event_refuses_a_hand_posted_plan(): void
    {
        [$role, $event, $ticket] = $this->scaffold(['payment_method' => 'cash']);

        $this->checkout($role, $event, $ticket, ['installments' => '1', 'installments_consent' => '1']);

        $this->assertNull($this->latestPlan());
    }

    /**
     * A pass has its own validity clock and is capped at one per order; splitting it would let a
     * multi-month entitlement be redeemed while most of it is unpaid.
     */
    public function test_a_pass_ticket_refuses_a_plan(): void
    {
        [$role, $event, $ticket] = $this->scaffold([], ['is_pass' => true, 'price' => 1000]);

        $this->checkout($role, $event, $ticket, ['installments' => '1', 'installments_consent' => '1']);

        $this->assertNull($this->latestPlan());
    }

    /**
     * The event is too close for four monthly payments to finish with the runway to spare.
     */
    public function test_an_event_too_close_refuses_a_plan(): void
    {
        [$role, $event, $ticket] = $this->scaffold([
            'starts_at' => now()->addMonths(2)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->checkout($role, $event, $ticket, ['installments' => '1', 'installments_consent' => '1']);

        $this->assertNull($this->latestPlan());
    }

    public function test_an_order_below_the_minimum_refuses_a_plan(): void
    {
        [$role, $event, $ticket] = $this->scaffold(['installment_min_order_amount' => 5000]);

        $this->checkout($role, $event, $ticket, ['installments' => '1', 'installments_consent' => '1']);

        $this->assertNull($this->latestPlan());
    }

    /**
     * A gift card can zero the order, in which case checkout takes the free path and marks the
     * sale paid outright. No plan must survive that, or it sits `active` forever with
     * installments nothing will ever charge.
     *
     * Two things independently prevent it, and this test pins the SECOND: the plan is created
     * after the zero-total branch, and - the part actually load-bearing here - a zeroed order
     * fails the per-part Stripe minimum, so ineligibleReason() refuses it wherever it is called
     * from. Verified by A/B: moving plan creation before the zero check leaves this green.
     */
    public function test_a_gift_card_that_zeroes_the_order_creates_no_plan(): void
    {
        [$role, $event, $ticket] = $this->scaffold();

        $card = GiftCard::create([
            'role_id' => $role->id,
            'code' => GiftCard::generateCode(),
            'secret' => \Illuminate\Support\Str::random(32),
            'amount' => 1000,
            'remaining_amount' => 1000,
            'currency_code' => 'USD',
            'status' => 'active',
            'payment_method' => 'stripe',
            'purchaser_name' => 'Gifter',
            'purchaser_email' => 'gifter@gmail.com',
            'recipient_name' => 'Wine Buyer',
            'recipient_email' => 'buyer@gmail.com',
        ]);

        $this->checkout($role, $event, $ticket, [
            'installments' => '1',
            'installments_consent' => '1',
            'gift_card_code' => $card->code,
        ]);

        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertSame('paid', $sale->status, 'A fully gift-carded order is paid outright');
        $this->assertNull(
            $this->latestPlan(),
            'A zeroed order must not leave an orphan plan with uncharged installments'
        );
    }
}
