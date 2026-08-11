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
