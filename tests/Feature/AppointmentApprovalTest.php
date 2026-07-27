<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Sale;
use App\Services\AppointmentService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class AppointmentApprovalTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function allDays(): array
    {
        return array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]);
    }

    /** Book a slot as a guest and return [event, sale, slotUtc]. */
    private function book($role, $type): array
    {
        $from = Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $slots = app(AppointmentService::class)->availableSlots($type, $from, 1);
        $slot = $slots['days'][array_key_first($slots['days'])][0]['utc'];

        $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot, 'guest_timezone' => 'America/New_York',
        ])->assertOk();

        $event = Event::where('appointment_type_id', $type->id)->firstOrFail();

        return [$event, Sale::where('event_id', $event->id)->firstOrFail(), $slot];
    }

    private function slotIsOffered($role, $type, string $date, string $slot): bool
    {
        $days = $this->getJson(route('appointments.slots', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]).'?from='.$date.'&days=1')->json('days');

        return collect($days[$date] ?? [])->pluck('utc')->contains($slot);
    }

    public function test_owner_accept_confirms_pending_booking(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays(), 'requires_approval' => true]);

        [$event, $sale] = $this->book($role, $type);
        $this->assertNull($event->roles()->where('roles.id', $role->id)->first()->pivot->is_accepted);
        $this->assertNull($sale->confirmed_at);

        $this->actingAs($owner)->post(route('event.accept', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));

        $this->assertTrue((bool) $event->roles()->where('roles.id', $role->id)->first()->pivot->is_accepted);
        $this->assertNotNull($sale->fresh()->confirmed_at);
    }

    public function test_owner_decline_cancels_and_frees_slot(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays(), 'requires_approval' => true]);

        [$event, $sale, $slot] = $this->book($role, $type);

        $this->actingAs($owner)->post(route('event.decline', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));

        $this->assertSame('cancelled', $sale->fresh()->status);
        $this->assertTrue((bool) $event->fresh()->is_cancelled);
        $this->assertTrue($this->slotIsOffered($role, $type, $sale->event_date, $slot));
    }

    public function test_owner_cancel_event_cancels_the_booking(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);

        [$event, $sale] = $this->book($role, $type);

        $this->actingAs($owner)->post(route('event.cancel', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));

        $this->assertSame('cancelled', $sale->fresh()->status);
        $this->assertTrue((bool) $event->fresh()->is_cancelled);
    }

    public function test_owner_delete_appointment_converts_to_cancel(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);

        [$event, $sale] = $this->book($role, $type);

        $this->actingAs($owner)->delete(route('event.delete', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));

        // The event row survives (cancelled), so the Sale/refund trail is preserved.
        $this->assertNotNull(Event::find($event->id));
        $this->assertTrue((bool) Event::find($event->id)->is_cancelled);
        $this->assertSame('cancelled', $sale->fresh()->status);
    }

    public function test_editing_an_appointment_event_redirects_to_bookings(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);

        [$event] = $this->book($role, $type);

        $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertRedirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']));
    }

    /**
     * accept() used to hardcode a redirect to the schedule tab and ignore redirect_to entirely, so
     * the inline Approve on the bookings list would have thrown the owner out to the calendar.
     */
    public function test_redirect_to_appointments_returns_to_the_pending_bookings_view(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $expected = route('role.view_admin', [
            'subdomain' => $role->subdomain, 'tab' => 'appointments', 'view' => 'bookings', 'filter' => 'pending',
        ]);

        foreach (['event.accept', 'event.decline'] as $i => $action) {
            $type = $this->createAppointmentType($role, [
                'slug' => 'redirect-'.$i,
                'weekly_windows' => $this->allDays(),
                'requires_approval' => true,
            ]);
            [$event] = $this->book($role, $type);

            $this->actingAs($owner)
                ->post(route($action, ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]), [
                    'redirect_to' => 'appointments',
                ])
                ->assertRedirect($expected);
        }
    }

    public function test_declining_still_defaults_to_the_requests_tab(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays(), 'requires_approval' => true]);
        [$event] = $this->book($role, $type);

        // No redirect_to: the pre-existing default for decline() must be unchanged.
        $this->actingAs($owner)
            ->post(route('event.decline', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertRedirect('/'.$role->subdomain.'/requests');
    }

    public function test_a_past_booking_cannot_be_approved(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays(), 'requires_approval' => true]);

        [$event, $sale] = $this->book($role, $type);

        // The slot has come and gone while the owner sat on the decision.
        $event->forceFill(['starts_at' => Carbon::now('UTC')->subDay()->format('Y-m-d H:i:s')])->saveQuietly();

        $this->actingAs($owner)
            ->post(route('event.accept', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]), [
                'redirect_to' => 'appointments',
            ])
            ->assertSessionHas('error', __('messages.appointments_cannot_approve_past'));

        // Still pending, and no "You're booked!" went out.
        $this->assertNull($event->fresh()->roles()->where('roles.id', $role->id)->first()->pivot->is_accepted);
        $this->assertNull($sale->fresh()->confirmed_at);
    }
}
