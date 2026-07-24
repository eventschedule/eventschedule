<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Sale;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Every weekday open 09:00-17:00 so any near date has slots. */
    private function allDays(): array
    {
        return array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]);
    }

    private function firstSlotUtc(string $subdomain, string $slug): string
    {
        $from = Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $response = $this->getJson(route('appointments.slots', ['subdomain' => $subdomain, 'typeSlug' => $slug]).'?from='.$from.'&days=1');
        $response->assertOk();
        $days = $response->json('days');
        $firstDate = array_key_first($days);

        return $days[$firstDate][0]['utc'];
    }

    public function test_booking_picker_template_methods_are_defined(): void
    {
        // The server render succeeds even if a Vue method is missing; the mount then throws at
        // runtime. Guard that gap: every method the template invokes must be defined in the component.
        $src = file_get_contents(resource_path('views/appointments/book-type.blade.php'));
        foreach (['hasSlots', 'selectDate', 'armSlot', 'confirmSlot', 'changeMonth', 'jumpToNext', 'localTime', 'localDate', 'localHour', 'fetchMonth', 'mergeDays', 'submit'] as $m) {
            $this->assertMatchesRegularExpression(
                '/\b'.$m.'\s*\([^)]*\)\s*\{/',
                $src,
                "The booking picker template invokes {$m}() but the Vue component does not define it."
            );
        }
    }

    public function test_book_pages_render(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);

        // The picker page renders the Vue app container.
        $this->get(route('appointments.book_type', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]))
            ->assertOk()
            ->assertSee('booking-app');

        // A single active type redirects the chooser straight to it.
        $this->get(route('appointments.book', ['subdomain' => $role->subdomain]))
            ->assertRedirect(route('appointments.book_type', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]));
    }

    public function test_free_booking_creates_confirmed_sale_and_consumes_slot(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);

        $slot = $this->firstSlotUtc($role->subdomain, $type->slug);

        $response = $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane Guest',
            'email' => 'jane@gmail.com',
            'slot' => $slot,
            'guest_timezone' => 'America/New_York',
            'notes' => 'Looking forward to it',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('/appointment/view/', $response->json('redirect_url'));

        $event = Event::where('appointment_type_id', $type->id)->firstOrFail();
        $this->assertTrue((bool) $event->is_private);
        $this->assertFalse((bool) $event->feedback_enabled);
        $this->assertFalse((bool) $event->tickets_enabled);
        $this->assertSame(0.5, (float) $event->duration);
        $this->assertTrue((bool) $event->roles()->where('roles.id', $role->id)->first()->pivot->is_accepted);

        $sale = Sale::where('event_id', $event->id)->firstOrFail();
        $this->assertSame('paid', $sale->status);
        $this->assertNotNull($sale->confirmed_at);
        $this->assertSame(32, strlen((string) $sale->secret));

        $ticket = Ticket::where('event_id', $event->id)->firstOrFail();
        $this->assertSame(1, array_sum(json_decode($ticket->sold, true) ?: [])); // one seat held

        // The slot is no longer offered.
        $from = Carbon::parse($sale->event_date)->format('Y-m-d');
        $slots = $this->getJson(route('appointments.slots', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]).'?from='.$from.'&days=1')->json('days');
        $labels = collect($slots[$from] ?? [])->pluck('utc');
        $this->assertNotContains($slot, $labels);
    }

    public function test_double_booking_same_slot_is_rejected(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);
        $slot = $this->firstSlotUtc($role->subdomain, $type->slug);

        $params = ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug];
        $payload = ['name' => 'A', 'email' => 'a@gmail.com', 'slot' => $slot, 'guest_timezone' => 'America/New_York'];

        $this->postJson(route('appointments.book.store', $params), $payload)->assertOk();

        $second = $this->postJson(route('appointments.book.store', $params), array_merge($payload, ['email' => 'b@gmail.com']));
        $second->assertStatus(422);
        $this->assertNotNull($second->json('error'));

        $this->assertSame(1, Event::where('appointment_type_id', $type->id)->count());
    }

    public function test_guest_can_cancel_and_free_the_slot(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);
        $slot = $this->firstSlotUtc($role->subdomain, $type->slug);

        $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot, 'guest_timezone' => 'America/New_York',
        ])->assertOk();

        $event = Event::where('appointment_type_id', $type->id)->firstOrFail();
        $sale = Sale::where('event_id', $event->id)->firstOrFail();

        $this->post(route('appointments.manage_cancel', [
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]));

        $this->assertSame('cancelled', $sale->fresh()->status);
        $this->assertTrue((bool) $event->fresh()->is_cancelled);

        // Slot is offered again.
        $from = Carbon::parse($sale->event_date)->format('Y-m-d');
        $slots = $this->getJson(route('appointments.slots', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]).'?from='.$from.'&days=1')->json('days');
        $this->assertContains($slot, collect($slots[$from] ?? [])->pluck('utc'));
    }

    public function test_requires_approval_creates_pending_booking(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays(), 'requires_approval' => true]);
        $slot = $this->firstSlotUtc($role->subdomain, $type->slug);

        $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot, 'guest_timezone' => 'America/New_York',
        ])->assertOk();

        $event = Event::where('appointment_type_id', $type->id)->firstOrFail();
        $pivot = $event->roles()->where('roles.id', $role->id)->first()->pivot;
        $this->assertNull($pivot->is_accepted); // pending approval

        $sale = Sale::where('event_id', $event->id)->firstOrFail();
        $this->assertNull($sale->confirmed_at); // not confirmed until accepted
    }
}
