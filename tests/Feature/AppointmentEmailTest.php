<?php

namespace Tests\Feature;

use App\Mail\AppointmentBookedNotification;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentDeclined;
use App\Mail\AppointmentPending;
use App\Mail\AppointmentReminder;
use App\Models\Event;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class AppointmentEmailTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_all_appointment_mailables_render(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['name' => 'Consult', 'price' => 25, 'currency_code' => 'USD', 'payment_method' => 'cash']);

        $event = new Event;
        $event->name = 'Consult - Jane';
        $event->starts_at = now()->addDay()->format('Y-m-d H:i:s');
        $event->duration = 0.5;
        $event->timezone = 'America/New_York';
        $event->ticket_currency_code = 'USD';
        $event->is_private = true;
        $event->creator_role_id = $role->id;
        $event->user_id = $role->user_id;
        $event->appointment_type_id = $type->id;
        $event->description = 'Notes from Jane: please call the front desk';
        $event->slug = 'x-'.strtolower(Str::random(8));
        $event->save();
        $event->roles()->attach($role->id, ['is_accepted' => true]);

        $sale = new Sale;
        $sale->event_id = $event->id;
        $sale->subdomain = $role->subdomain;
        $sale->name = 'Jane';
        $sale->email = 'jane@gmail.com';
        $sale->phone = '+15551234567';
        $sale->event_date = now()->addDay()->setTimezone('America/New_York')->format('Y-m-d');
        $sale->status = 'paid';
        $sale->payment_method = 'cash';
        $sale->payment_amount = 25;
        $sale->transaction_reference = 'ch_test123';
        $sale->secret = strtolower(Str::random(32));
        $sale->save();

        // Guest-facing mailables: body renders AND the subject is translated (the :name is
        // interpolated - a bare/unprefixed i18n key would leave the raw key and no interpolation).
        foreach ([AppointmentConfirmed::class, AppointmentReminder::class, AppointmentPending::class, AppointmentDeclined::class, AppointmentCancelled::class] as $class) {
            $mail = new $class($sale, $event, $role, $type);
            $rendered = $mail->render();
            $this->assertNotEmpty($rendered);
            $this->assertStringContainsString('Consult', $rendered);
            $this->assertStringContainsString('Consult', $mail->envelope()->subject, "{$class} subject did not resolve/interpolate (raw i18n key?)");
            $this->assertStringNotContainsString('appointment_', $rendered, "{$class} body leaked a raw i18n key");
        }

        // Owner notification, all three kinds (cancelled shows the refund note).
        foreach (['booked', 'pending', 'cancelled'] as $kind) {
            $mail = new AppointmentBookedNotification($sale, $event, $role, $type, $kind);
            $rendered = $mail->render();
            $this->assertNotEmpty($rendered);
            $this->assertStringContainsString('jane@gmail.com', $rendered);
            $this->assertStringContainsString('Consult', $mail->envelope()->subject, "owner {$kind} subject did not resolve (raw i18n key?)");
        }
    }
}
