<?php

namespace Tests\Feature;

use App\Models\AppointmentType;
use App\Models\Event;
use App\Models\Sale;
use App\Services\AppointmentService;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class AppointmentPaymentTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function allDays(): array
    {
        return array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]);
    }

    private function firstSlot(AppointmentType $type): string
    {
        $from = Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $slots = app(AppointmentService::class)->availableSlots($type, $from, 1);
        $date = array_key_first($slots['days']);

        return $slots['days'][$date][0]['utc'];
    }

    public function test_stripe_booking_is_unpaid_then_confirms_on_paid_webhook(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => $this->allDays(),
            'price' => 50, 'currency_code' => 'USD', 'payment_method' => 'stripe',
        ]);
        $slot = $this->firstSlot($type);

        // Call the service directly so we exercise the create path without hitting the Stripe API.
        $sale = app(AppointmentService::class)->book($type, $role, ['name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot]);

        $this->assertSame('unpaid', $sale->status);
        $this->assertNull($sale->confirmed_at);
        $this->assertEquals(1, (int) $sale->event->expire_unpaid_tickets); // stripe holds 1h
        $this->assertTrue((bool) $sale->event->roles()->where('roles.id', $role->id)->first()->pivot->is_accepted);

        // Simulate the Stripe webhook marking the sale paid.
        $sale->status = 'paid';
        $sale->save();
        (new EmailService)->sendSaleConfirmationEmails($sale->fresh());

        $this->assertNotNull($sale->fresh()->confirmed_at);
    }

    public function test_cash_confirm_is_idempotent_on_mark_paid(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => $this->allDays(),
            'price' => 25, 'currency_code' => 'USD', 'payment_method' => 'cash',
        ]);
        $slot = $this->firstSlot($type);

        $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot, 'guest_timezone' => 'America/New_York',
        ])->assertOk();

        $event = Event::where('appointment_type_id', $type->id)->firstOrFail();
        $sale = Sale::where('event_id', $event->id)->firstOrFail();

        $this->assertSame('unpaid', $sale->status);   // cash: balance due
        $this->assertNotNull($sale->confirmed_at);      // confirmed at booking (no approval)
        $firstConfirmedAt = $sale->confirmed_at->timestamp;

        // Owner marks it paid: the confirm branch must be a no-op (no re-send / re-sync).
        $sale->status = 'paid';
        $sale->save();
        (new EmailService)->sendSaleConfirmationEmails($sale->fresh());

        $this->assertSame($firstConfirmedAt, $sale->fresh()->confirmed_at->timestamp);
    }
}
