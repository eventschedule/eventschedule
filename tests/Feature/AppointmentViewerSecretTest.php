<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The bookings list's Preview link opens the guest's manage page, and its URL carries the guest's
 * secret - which alone lets its holder cancel, reschedule or pay as the guest. A read-only viewer is
 * refused every one of those actions on this list, so it must not be handed the link that performs
 * them all.
 */
class AppointmentViewerSecretTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array{0: Role, 1: Sale} */
    private function confirmedBooking(): array
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]),
        ]);

        $from = Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $slots = app(AppointmentService::class)->availableSlots($type, $from, 1);
        $slot = $slots['days'][array_key_first($slots['days'])][0]['utc'];

        $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot, 'guest_timezone' => 'America/New_York',
        ])->assertOk();

        $event = Event::where('appointment_type_id', $type->id)->firstOrFail();

        return [$role, Sale::where('event_id', $event->id)->firstOrFail()];
    }

    private function bookingsUrl(Role $role): string
    {
        return route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments', 'view' => 'bookings']);
    }

    public function test_the_owner_gets_the_preview_link(): void
    {
        [$role, $sale] = $this->confirmedBooking();

        $this->actingAs($role->owner())
            ->get($this->bookingsUrl($role))
            ->assertOk()
            ->assertSee($sale->secret);
    }

    public function test_a_viewer_does_not_get_the_guests_secret(): void
    {
        [$role, $sale] = $this->confirmedBooking();
        $viewer = $this->createOwner();
        $this->followRole($viewer, $role, 'viewer');

        $this->actingAs($viewer)
            ->get($this->bookingsUrl($role))
            ->assertOk()
            ->assertSee('Jane')
            ->assertDontSee($sale->secret);
    }
}
