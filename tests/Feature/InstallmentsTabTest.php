<?php

namespace Tests\Feature;

use App\Services\InstallmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Installments tab on /sales.
 *
 * The VIP's actual ask: "a dedicated tab that shows the people who opted for the installment
 * purchase, what they paid and how many installments they still have to pay."
 */
class InstallmentsTabTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function planFor($owner, string $currency = 'USD', float $total = 1000.0, array $eventAttrs = [])
    {
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, array_merge([
            'payment_method' => 'stripe',
            'installments_enabled' => true,
            'installment_count' => 4,
            'ticket_currency_code' => $currency,
            'starts_at' => now()->addMonths(8)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ], $eventAttrs));
        $ticket = $this->createTicket($event, ['price' => $total, 'quantity' => 10]);
        $sale = $this->createSale($event, $role, [
            'status' => 'paid', 'payment_amount' => $total, 'email' => 'buyer@gmail.com',
        ], $ticket);

        $plan = app(InstallmentService::class)->createPlan($sale, $event, $total, $currency);
        $plan->installments->firstWhere('sequence', 1)->update(['status' => 'paid', 'paid_at' => now()]);
        $plan->update(['amount_paid' => $total / 4]);

        return $plan->fresh('installments');
    }

    public function test_the_tab_lists_a_plan_with_progress_and_balances(): void
    {
        $owner = $this->createOwner();
        $this->planFor($owner);

        $response = $this->actingAs($owner)->get(route('sales'))->assertOk();

        $response->assertSee(__('messages.installments'));
        $response->assertSee('1 / 4');
        $response->assertSee(__('messages.installments_outstanding'));
        // The caveat has to be on the page, not only in the docs.
        $response->assertSee(__('messages.installments_revenue_note'));
    }

    /**
     * Money that arrived and could not be applied to any payment. It is deliberately never
     * auto-applied, so the organizer is the only one who can resolve it - and before this it was
     * written to the database and read by nothing at all, including the audit log, which prints
     * only `metadata` and never `new_values`.
     */
    public function test_unmatched_money_is_shown_to_the_organizer(): void
    {
        $owner = $this->createOwner();
        $plan = $this->planFor($owner);
        $plan->update(['unmatched_amount' => 120.00]);

        $response = $this->actingAs($owner)->get(route('sales'))->assertOk();

        $response->assertSee(__('messages.installment_unmatched_notice', [
            'amount' => \App\Utils\MoneyUtils::format(120.00, 'USD'),
        ]), false);
    }

    /**
     * A charge whose outcome we never learned is parked rather than retried, because a retry after
     * Stripe's idempotency key expires is how a timeout becomes a double charge. That makes it a
     * state only a human can clear, so the human has to be able to see it.
     */
    public function test_a_charge_with_an_unknown_outcome_is_shown_to_the_organizer(): void
    {
        $owner = $this->createOwner();
        $plan = $this->planFor($owner);
        $plan->installments->firstWhere('sequence', 2)
            ->update(['status' => 'awaiting_reconciliation', 'last_error' => 'timeout']);

        $response = $this->actingAs($owner)->get(route('sales'))->assertOk();

        $response->assertSee(__('messages.installment_needs_check_notice'), false);
    }

    /**
     * The per-payment breakdown called every unpaid row "Scheduled", so a failed or parked payment
     * looked identical to one simply not due yet.
     */
    public function test_the_breakdown_names_each_payment_state(): void
    {
        $owner = $this->createOwner();
        $plan = $this->planFor($owner);
        $plan->installments->firstWhere('sequence', 2)->update([
            'status' => 'failed', 'transaction_reference' => 'pi_failed_ref',
        ]);
        $plan->installments->firstWhere('sequence', 3)->update([
            'status' => 'awaiting_customer', 'last_error' => 'authentication_required',
        ]);

        $response = $this->actingAs($owner)->get(route('sales'))->assertOk();

        $response->assertSee(__('messages.installment_payment_failed'), false);
        $response->assertSee(__('messages.installment_payment_awaiting_buyer'), false);
    }

    /**
     * The forecast counted only `scheduled` rows, so a plan going wrong quietly improved the
     * figure the organizer plans against. Everything still owed belongs in it.
     */
    public function test_the_forecast_counts_money_that_is_owed_but_off_schedule(): void
    {
        $owner = $this->createOwner();
        $plan = $this->planFor($owner);

        // getInstallmentsData() scopes to auth()->user(), so the act has to come first.
        $this->actingAs($owner);

        $controller = app(\App\Http\Controllers\TicketController::class);
        $method = new \ReflectionMethod($controller, 'getInstallmentsData');
        $method->setAccessible(true);

        $before = collect($method->invoke($controller)['installmentForecast'])->sum('amount');

        // Park one payment. The money is still owed, so the total must not move.
        $plan->installments->firstWhere('sequence', 3)->update(['status' => 'awaiting_customer']);

        $after = collect($method->invoke($controller)['installmentForecast'])->sum('amount');

        $this->assertSame($before, $after, 'Parking a payment must not shrink the forecast');
        $this->assertGreaterThan(0, $after);
    }

    /**
     * /sales aggregates across every schedule the user owns, so summing two currencies into one
     * figure would be plainly wrong. Totals are grouped instead.
     */
    public function test_totals_are_grouped_per_currency(): void
    {
        $owner = $this->createOwner();
        $this->planFor($owner, 'USD', 1000.0);
        $this->planFor($owner, 'EUR', 800.0);

        $response = $this->actingAs($owner)->get(route('sales'))->assertOk();

        // Both currencies appear with their own collected figure, and neither is merged.
        $response->assertSee('250');
        $response->assertSee('200');
    }

    public function test_a_plan_on_another_owners_event_is_not_visible(): void
    {
        $owner = $this->createOwner();
        $stranger = $this->createOwner();
        $this->planFor($stranger);

        $response = $this->actingAs($owner)->get(route('sales'))->assertOk();

        $response->assertDontSee('1 / 4');
    }

    /**
     * The schema value must never surface to an organizer either; they read "Overdue".
     */
    public function test_an_overdue_plan_reads_overdue_never_delinquent(): void
    {
        $owner = $this->createOwner();
        $plan = $this->planFor($owner);
        $plan->update(['status' => 'delinquent', 'delinquent_at' => now()]);

        $response = $this->actingAs($owner)->get(route('sales'))->assertOk();

        $response->assertSee(__('messages.installment_status_overdue'));
        $response->assertDontSee('delinquent');
    }

    public function test_the_forecast_groups_scheduled_payments_by_month(): void
    {
        $owner = $this->createOwner();
        $this->planFor($owner);

        $response = $this->actingAs($owner)->get(route('sales'))->assertOk();

        $response->assertSee(__('messages.installments_expected_by_month'));
        // Three unpaid parts of 250 remain, one per month.
        $response->assertSee(now()->addMonth()->format('M Y'));
    }

    public function test_the_empty_state_points_at_where_the_setting_lives(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue');

        $response = $this->actingAs($owner)->get(route('sales'))->assertOk();

        $response->assertSee(__('messages.no_installment_plans_yet'));
    }
}
