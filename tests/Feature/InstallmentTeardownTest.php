<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Models\SaleInstallmentPlan;
use App\Models\Ticket;
use App\Services\InstallmentService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The four ways an installment plan must stop charging.
 *
 * This is the highest-stakes behaviour in the feature. The sale is `paid` from the first
 * installment onward, so nothing about a dead order is self-evident from its status: if any of
 * these paths fails to cancel the schedule, app:charge-installments goes on debiting a real card
 * every month for an order, an event or a schedule that no longer exists.
 *
 * Each test asserts on the INSTALLMENT rows, not just the plan, because the cron selects
 * installments - a plan marked cancelled with live `scheduled` rows underneath it would still
 * charge.
 */
class InstallmentTeardownTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function service(): InstallmentService
    {
        return app(InstallmentService::class);
    }

    /**
     * A four-part plan on a paid sale, one installment already collected. This is the shape that
     * matters: money has moved, the ticket is valid, and three charges are still pending.
     */
    private function planFor(Sale $sale, Event $event): SaleInstallmentPlan
    {
        $plan = $this->service()->createPlan($sale, $event, 1000.00, 'USD');

        // Settle installment 1, the way the Stripe webhook would.
        $first = $plan->installments->firstWhere('sequence', 1);
        $first->update(['status' => 'paid', 'paid_at' => now(), 'transaction_reference' => 'pi_first']);
        $plan->update(['amount_paid' => 250.00]);

        return $plan->fresh('installments');
    }

    private function scaffold(array $eventAttrs = []): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, array_merge([
            'payment_method' => 'stripe',
            'installments_enabled' => true,
            'installment_count' => 4,
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ], $eventAttrs));
        $ticket = $this->createTicket($event, ['price' => 1000, 'quantity' => 10]);
        $sale = $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 1000], $ticket);

        return [$owner, $role, $event, $sale];
    }

    private function assertNothingLeftToCharge(SaleInstallmentPlan $plan): void
    {
        $plan->refresh()->load('installments');

        $this->assertSame('cancelled', $plan->status);

        $chargeable = $plan->installments
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->count();

        $this->assertSame(0, $chargeable, 'Installments are still chargeable after teardown');

        // The collected payment must survive as a record - the organizer needs its reference to
        // refund by hand.
        $paid = $plan->installments->firstWhere('sequence', 1);
        $this->assertSame('paid', $paid->status);
        $this->assertSame('pi_first', $paid->transaction_reference);
    }

    public function test_refunding_the_sale_stops_the_remaining_charges(): void
    {
        [, , $event, $sale] = $this->scaffold();
        $plan = $this->planFor($sale, $event);

        $sale->status = 'refunded';
        $sale->save();

        $this->assertNothingLeftToCharge($plan);
    }

    public function test_cancelling_the_sale_stops_the_remaining_charges(): void
    {
        [, , $event, $sale] = $this->scaffold();
        $plan = $this->planFor($sale, $event);

        $sale->status = 'cancelled';
        $sale->save();

        $this->assertNothingLeftToCharge($plan);
    }

    /**
     * ReleaseTickets expires abandoned sales through the same status branch. A plan sale should
     * never be `unpaid` in practice, but the hook has to hold if one ever is.
     */
    public function test_expiring_the_sale_stops_the_remaining_charges(): void
    {
        [, , $event, $sale] = $this->scaffold();
        $plan = $this->planFor($sale, $event);

        $sale->status = 'expired';
        $sale->save();

        $this->assertNothingLeftToCharge($plan);
    }

    /**
     * Cancelling an event deliberately leaves its sales `paid`, so this path never reaches
     * Sale::booted(). Without its own hook the buyer keeps paying monthly for an event that is
     * not happening.
     */
    public function test_cancelling_the_event_stops_the_remaining_charges(): void
    {
        [$owner, $role, $event, $sale] = $this->scaffold();
        $plan = $this->planFor($sale, $event);

        $response = $this->actingAs($owner)->post(
            route('event.cancel', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)])
        );

        $response->assertRedirect();
        $this->assertTrue($event->fresh()->is_cancelled);

        // The sale is untouched by design - that is exactly why the plan needed its own teardown.
        $this->assertSame('paid', $sale->fresh()->status);

        $this->assertNothingLeftToCharge($plan);
    }

    /**
     * A venue or curator schedule's events and sales outlive $role->delete(), so the plans would
     * keep charging with nobody able to see them.
     */
    public function test_deleting_the_schedule_stops_the_remaining_charges(): void
    {
        [$owner, $role, $event, $sale] = $this->scaffold();
        $plan = $this->planFor($sale, $event);

        $response = $this->actingAs($owner)->delete(
            route('role.delete', ['subdomain' => $role->subdomain])
        );

        $response->assertRedirect();
        $this->assertNothingLeftToCharge($plan);
    }

    /**
     * cancelPlan() is reached from several paths at once (a cancel cascade saves every sibling
     * row), so it has to be safe to call repeatedly.
     */
    public function test_teardown_is_idempotent(): void
    {
        [, , $event, $sale] = $this->scaffold();
        $plan = $this->planFor($sale, $event);

        $this->service()->cancelPlan($plan, 'first');
        $this->service()->cancelPlan($plan->fresh(), 'second');
        $this->service()->cancelPlan($plan->fresh(), 'third');

        $this->assertNothingLeftToCharge($plan);

        // The first reason wins; a repeat call must not overwrite the audit trail.
        $cancelled = SaleInstallment::where('sale_installment_plan_id', $plan->id)
            ->where('status', 'cancelled')->first();
        $this->assertSame('first', $cancelled->last_error);
    }

    /**
     * A completed plan has nothing to cancel and must not be re-labelled as cancelled, or the
     * organizer's tab would report a fully paid order as abandoned.
     */
    public function test_a_completed_plan_is_not_reopened_or_relabelled(): void
    {
        [, , $event, $sale] = $this->scaffold();
        $plan = $this->planFor($sale, $event);

        $plan->installments()->update(['status' => 'paid', 'paid_at' => now()]);
        $plan->update(['status' => 'completed', 'amount_paid' => 1000.00]);

        $this->service()->cancelPlan($plan->fresh(), 'sale_refunded');

        $this->assertSame('completed', $plan->fresh()->status);
        $this->assertSame(4, $plan->fresh()->installments->where('status', 'paid')->count());
    }

    /**
     * A sale with no plan at all is by far the common case; the hook must not blow up on it.
     */
    public function test_a_sale_without_a_plan_cancels_normally(): void
    {
        [, , , $sale] = $this->scaffold();

        $sale->status = 'cancelled';
        $sale->save();

        $this->assertSame('cancelled', $sale->fresh()->status);
        $this->assertNull(SaleInstallmentPlan::where('sale_id', $sale->id)->first());
    }
}
