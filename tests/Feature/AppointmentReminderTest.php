<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Utils\IcsUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class AppointmentReminderTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** A confirmed (or pending) appointment $hoursOut from now, with a paid cash sale. */
    private function appointment(Role $role, int $hoursOut, $accepted = true): array
    {
        $type = $this->createAppointmentType($role);

        $event = new Event;
        $event->name = 'Consult - Jane';
        $event->starts_at = now()->addHours($hoursOut)->format('Y-m-d H:i:s');
        $event->duration = 0.5;
        $event->timezone = 'America/New_York';
        $event->is_private = true;
        $event->creator_role_id = $role->id;
        $event->user_id = $role->user_id;
        $event->appointment_type_id = $type->id;
        $event->slug = 'x-'.strtolower(Str::random(8));
        $event->save();
        $event->roles()->attach($role->id, ['is_accepted' => $accepted]);

        $sale = new Sale;
        $sale->event_id = $event->id;
        $sale->subdomain = $role->subdomain;
        $sale->name = 'Jane';
        $sale->email = 'jane@gmail.com';
        $sale->event_date = now()->addHours($hoursOut)->setTimezone('America/New_York')->format('Y-m-d');
        $sale->status = 'paid';
        $sale->payment_method = 'cash';
        $sale->payment_amount = 0;
        $sale->secret = strtolower(Str::random(32));
        $sale->confirmed_at = now();
        $sale->save();

        return [$event, $sale];
    }

    public function test_reminder_sent_once_for_confirmed_booking_in_window(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        [, $sale] = $this->appointment($role, 12);

        $this->artisan('app:send-appointment-reminders')->assertSuccessful();
        $this->assertNotNull($sale->fresh()->reminder_sent_at);

        $firstSentAt = $sale->fresh()->reminder_sent_at->timestamp;
        $this->artisan('app:send-appointment-reminders')->assertSuccessful();
        $this->assertSame($firstSentAt, $sale->fresh()->reminder_sent_at->timestamp); // no re-send
    }

    public function test_pending_booking_is_not_reminded(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        [, $sale] = $this->appointment($role, 12, null); // pivot null = pending approval

        $this->artisan('app:send-appointment-reminders')->assertSuccessful();
        $this->assertNull($sale->fresh()->reminder_sent_at);
    }

    public function test_booking_outside_window_is_not_reminded(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        [, $sale] = $this->appointment($role, 48); // more than 24h out

        $this->artisan('app:send-appointment-reminders')->assertSuccessful();
        $this->assertNull($sale->fresh()->reminder_sent_at);
    }

    public function test_ics_invite_is_valid(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        [$event] = $this->appointment($role, 12);

        $ics = IcsUtils::buildInvite($event->fresh(), $role);
        $this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
        $this->assertStringContainsString('BEGIN:VEVENT', $ics);
        $this->assertStringContainsString('SUMMARY:', $ics);
        $this->assertStringContainsString('DTSTART:', $ics);
    }
}
