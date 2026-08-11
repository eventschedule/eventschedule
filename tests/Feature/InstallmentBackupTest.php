<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleInstallmentPlan;
use App\Services\BackupService;
use App\Services\InstallmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A restored backup is a RECORD, never a live mandate.
 *
 * The restore lands on a brand new schedule owned by a different Stripe connected account, and
 * the buyer authorised the original organizer to charge them, not this one. If the installments
 * came back `scheduled`, the next cron run would email real buyers about payments on a schedule
 * that never sold them anything.
 */
class InstallmentBackupTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_restored_plan_is_inert_and_carries_no_credentials(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'payment_method' => 'stripe',
            'installments_enabled' => true,
            'installment_count' => 4,
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);
        $ticket = $this->createTicket($event, ['price' => 1000, 'quantity' => 10]);
        $sale = $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 1000], $ticket);

        $plan = app(InstallmentService::class)->createPlan($sale, $event, 1000.00, 'USD');
        $plan->update([
            'amount_paid' => 250.00,
            'stripe_customer_id' => 'cus_secret',
            'stripe_payment_method_id' => 'pm_secret',
            'card_brand' => 'visa',
            'card_last4' => '4242',
        ]);
        $plan->installments->firstWhere('sequence', 1)->update([
            'status' => 'paid', 'paid_at' => now(), 'transaction_reference' => 'pi_first',
        ]);

        $svc = app(BackupService::class);
        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        // The export file must not contain the payment credentials or the plan's payment link.
        $encoded = json_encode($data);
        $this->assertStringNotContainsString('cus_secret', $encoded);
        $this->assertStringNotContainsString('pm_secret', $encoded);
        $this->assertStringNotContainsString($plan->secret, $encoded);

        // ...but it must keep what the organizer needs to reconcile by hand.
        $this->assertStringContainsString('pi_first', $encoded);

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $newRole = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();
        $newEvent = Event::where('creator_role_id', $newRole->id)->firstOrFail();
        $newSale = Sale::where('event_id', $newEvent->id)->firstOrFail();

        $restored = SaleInstallmentPlan::where('sale_id', $newSale->id)->with('installments')->firstOrFail();

        // Inert.
        $this->assertSame('cancelled', $restored->status);
        $this->assertSame(
            0,
            $restored->installments->whereNotIn('status', ['paid', 'cancelled'])->count(),
            'A restored plan must have nothing left for the cron to charge'
        );

        // No credentials survived the round trip.
        $this->assertNull($restored->stripe_customer_id);
        $this->assertNull($restored->stripe_payment_method_id);
        $this->assertNotSame($plan->secret, $restored->secret);

        // The history is intact and truthful.
        $paid = $restored->installments->firstWhere('sequence', 1);
        $this->assertSame('paid', $paid->status);
        $this->assertSame('pi_first', $paid->transaction_reference);
        $this->assertSame(4, $restored->installments->count());
        $this->assertSame('visa', $restored->card_brand);
    }

    /**
     * The plan model hides its secret and Stripe ids, so nothing that serializes a Sale - the
     * webhook delivery log and GET /api/sales both do - can leak them.
     */
    public function test_plan_serialization_hides_credentials(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'payment_method' => 'stripe',
            'installments_enabled' => true,
            'installment_count' => 3,
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);
        $ticket = $this->createTicket($event, ['price' => 900, 'quantity' => 10]);
        $sale = $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 900], $ticket);

        $plan = app(InstallmentService::class)->createPlan($sale, $event, 900.00, 'USD');
        $plan->update(['stripe_customer_id' => 'cus_leak', 'stripe_payment_method_id' => 'pm_leak']);

        $encoded = json_encode($plan->fresh()->toArray());

        $this->assertStringNotContainsString('cus_leak', $encoded);
        $this->assertStringNotContainsString('pm_leak', $encoded);
        $this->assertStringNotContainsString($plan->secret, $encoded);

        // The hand-built summary is what surfaces instead, and it is safe.
        $summary = json_encode($plan->fresh()->toSummaryData());
        $this->assertStringNotContainsString('cus_leak', $summary);
        $this->assertStringNotContainsString($plan->secret, $summary);
        $this->assertStringContainsString('amount_remaining', $summary);
    }
}
