<?php

namespace Tests\Feature;

use App\Mail\AppointmentBookedNotification;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentDeclined;
use App\Mail\AppointmentPaymentDue;
use App\Mail\AppointmentPending;
use App\Mail\AppointmentReminder;
use App\Mail\AppointmentRescheduled;
use App\Models\Event;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Every appointment mail renders on the shared email layout: one <h1>, no raw i18n key, and well
 * under Gmail's 102KB clip. AppointmentPaymentDue had no render coverage at all before this.
 */
class EmailAppointmentRenderTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function booking(): array
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['name' => 'Consult', 'price' => 25, 'currency_code' => 'USD']);

        $event = new Event;
        $event->name = 'Consult - Jane';
        $event->starts_at = now()->addDays(2)->format('Y-m-d H:i:s');
        $event->duration = 0.5;
        $event->timezone = 'America/New_York';
        $event->ticket_currency_code = 'USD';
        $event->is_private = true;
        $event->creator_role_id = $role->id;
        $event->user_id = $role->user_id;
        $event->appointment_type_id = $type->id;
        $event->slug = 'x-'.strtolower(Str::random(8));
        $event->save();
        $event->roles()->attach($role->id, ['is_accepted' => true]);

        $sale = new Sale;
        $sale->event_id = $event->id;
        $sale->subdomain = $role->subdomain;
        $sale->name = 'Jane';
        $sale->email = 'jane@gmail.com';
        $sale->event_date = now()->addDays(2)->format('Y-m-d');
        $sale->status = 'unpaid';
        $sale->payment_method = 'cash';
        $sale->payment_amount = 25;
        $sale->guest_timezone = 'Europe/Paris';
        $sale->secret = strtolower(Str::random(32));
        $sale->save();

        return [$role, $type, $event->fresh(), $sale->fresh()];
    }

    private function assertRendersCleanly(string $html, string $what): void
    {
        $this->assertSame(1, substr_count($html, '<h1'), "{$what}: exactly one <h1>");
        $this->assertStringNotContainsString('messages.', $html, "{$what}: raw i18n key");
        $this->assertLessThan(80 * 1024, strlen($html), "{$what}: approaching Gmail's 102KB clip");
        $this->assertStringContainsString('Consult', $html, "{$what}: the appointment is named");
    }

    public function test_every_appointment_mail_renders_on_the_shared_layout(): void
    {
        [$role, $type, $event, $sale] = $this->booking();
        $old = now()->addDay()->format('Y-m-d H:i:s');

        $mails = [
            'confirmed' => new AppointmentConfirmed($sale, $event, $role, $type),
            'reminder' => new AppointmentReminder($sale, $event, $role, $type),
            'rescheduled' => new AppointmentRescheduled($sale, $event, $role, $type, $old, false, 'Running late today.'),
            'pending' => new AppointmentPending($sale, $event, $role, $type),
            'payment due' => new AppointmentPaymentDue($sale, $event, $role, $type),
            'declined' => new AppointmentDeclined($sale, $event, $role, $type),
            'cancelled' => new AppointmentCancelled($sale, $event, $role, $type),
        ];
        foreach (['booked', 'pending', 'cancelled', 'rescheduled', 'rescheduled_pending'] as $kind) {
            $mails["owner {$kind}"] = new AppointmentBookedNotification($sale, $event, $role, $type, $kind, null, $old);
        }

        foreach ($mails as $what => $mail) {
            $this->assertRendersCleanly($mail->render(), $what);
        }
    }

    public function test_payment_due_says_so_and_leads_to_the_booking(): void
    {
        [$role, $type, $event, $sale] = $this->booking();

        $html = (new AppointmentPaymentDue($sale, $event, $role, $type))->render();

        $this->assertStringContainsString(e(__('messages.appointments_awaiting_payment')), $html);
        $this->assertStringContainsString(e(__('messages.appointment_payment_due_intro', ['schedule' => $role->name])), $html);
        // The state is in the eyebrow's colour, so a payment that is due reads as one. The class
        // attribute, not the bare name: the layout's dark-mode rules name every tone class.
        $this->assertStringContainsString('class="es-ink-warning"', $html);
        // Nothing is confirmed yet, so no add-to-calendar.
        $this->assertStringNotContainsString('calendar.google.com', $html);
        $this->assertStringContainsString(route('appointments.manage', [
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]), $html);
    }

    public function test_only_a_confirmed_booking_offers_add_to_calendar(): void
    {
        [$role, $type, $event, $sale] = $this->booking();

        $this->assertStringContainsString('calendar.google.com', (new AppointmentConfirmed($sale, $event, $role, $type))->render());
        $this->assertStringContainsString('calendar.google.com', (new AppointmentReminder($sale, $event, $role, $type))->render());

        foreach ([AppointmentPending::class, AppointmentDeclined::class, AppointmentCancelled::class] as $class) {
            $this->assertStringNotContainsString('calendar.google.com', (new $class($sale, $event, $role, $type))->render(), $class);
        }
    }
}
