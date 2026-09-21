<?php

namespace Tests\Feature;

use App\Models\AppointmentType;
use App\Models\Role;
use App\Services\AppointmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Charging for an appointment is a Pro feature, the same rule paid tickets follow.
 *
 * A free appointment type books on every plan; a type with a PRICE needs Pro, the
 * grandfather stamp, selfhost or the demo (AppointmentType::canTakePayment()). The gate is
 * read-side only: a priced type is always STORED, it just stops being bookable, so a schedule that
 * lapses keeps its configuration and books again on upgrade.
 *
 * The other half of the contract is what stays open. Money that has already moved is never
 * re-gated: pay() settles a booking that already holds a slot, and refusing it would let the hold
 * expire and Sale::booted cancel an accepted booking within the hour.
 */
class PaidAppointmentGateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The gate short-circuits to true on selfhost, so every test here is a hosted test.
        config(['app.hosted' => true]);
    }

    private function freeSchedule(): Role
    {
        return $this->createRole($this->createOwner(), 'talent', [
            'timezone' => 'America/New_York',
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'trial_ends_at' => null,
        ]);
    }

    private function allDays(): array
    {
        return array_fill_keys(range(0, 6), [['start' => '09:00', 'end' => '17:00']]);
    }

    private function firstSlot(AppointmentType $type): string
    {
        $from = now($type->timezone())->addDay()->format('Y-m-d');
        $slots = app(AppointmentService::class)->availableSlots($type, $from, 7);
        $date = array_key_first($slots['days']);

        return $slots['days'][$date][0]['utc'];
    }

    public function test_a_free_schedule_cannot_take_a_paid_booking_through_the_service(): void
    {
        $role = $this->freeSchedule();
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => $this->allDays(),
            'price' => 50, 'currency_code' => 'USD', 'payment_method' => 'stripe',
        ]);
        $slot = $this->firstSlot($type);

        $this->expectException(\App\Exceptions\BusinessException::class);

        app(AppointmentService::class)->book($type, $role, [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot,
        ]);
    }

    public function test_the_public_booking_page_404s_nothing_and_explains_instead(): void
    {
        $role = $this->freeSchedule();
        $type = $this->createAppointmentType($role, [
            'name' => 'Consultation',
            'weekly_windows' => $this->allDays(),
            'price' => 50, 'currency_code' => 'USD', 'payment_method' => 'stripe',
        ]);

        // A published URL that has stopped taking bookings renders an explanation, not a dead link.
        $response = $this->get(route('appointments.book_type', [
            'subdomain' => $role->subdomain,
            'typeSlug' => $type->slug,
        ]));

        $response->assertStatus(404);
        $response->assertSee('Consultation');
        $response->assertSee(__('messages.appointments_not_available'));
        // Never names the plan or sells to the guest: they are the owner's customer, not ours.
        // Asserted on the owner-facing copy and the pricing link rather than the bare word "Pro",
        // which also appears inside stopPropagation() in the page's own JavaScript.
        $response->assertDontSee(__('messages.appointments_paid_needs_pro_title'));
        $response->assertDontSee(__('messages.appointments_paid_needs_pro_body'));
        $response->assertDontSee(marketing_url('/pricing'), false);
    }

    /**
     * The explanatory 404 renders the type's NAME and DESCRIPTION, so what reaches it matters.
     *
     * An INACTIVE type was never published: there is no circulating link to keep alive, and Clone
     * creates its copy inactive, so without this filter pressing Clone puts a draft's name on a
     * public page for anyone holding the slug - no plan lapse required.
     */
    public function test_an_inactive_type_is_a_bare_404_and_never_names_itself(): void
    {
        $role = $this->freeSchedule();
        $type = $this->createAppointmentType($role, [
            'name' => 'Executive Coaching Draft',
            'description' => 'Internal notes that were never meant to be public.',
            'weekly_windows' => $this->allDays(),
            'is_active' => false,
        ]);

        $response = $this->get(route('appointments.book_type', [
            'subdomain' => $role->subdomain,
            'typeSlug' => $type->slug,
        ]));

        $response->assertStatus(404);
        $response->assertDontSee('Executive Coaching Draft');
        $response->assertDontSee('Internal notes that were never meant to be public.');
        $response->assertDontSee(__('messages.appointments_not_available'));
    }

    public function test_an_unknown_slug_is_still_a_bare_404(): void
    {
        $role = $this->freeSchedule();
        $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);

        $this->get(route('appointments.book_type', [
            'subdomain' => $role->subdomain,
            'typeSlug' => 'no-such-type',
        ]))->assertStatus(404)->assertDontSee(__('messages.appointments_not_available'));
    }

    public function test_a_free_type_books_normally_on_a_free_schedule(): void
    {
        $role = $this->freeSchedule();
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);

        $sale = app(AppointmentService::class)->book($type, $role, [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $this->firstSlot($type),
        ]);

        $this->assertSame('paid', $sale->status, 'a free booking is settled at once');
        $this->assertEquals(0, (float) $sale->payment_amount);
    }

    public function test_a_grandfathered_type_still_takes_payment_on_a_free_schedule(): void
    {
        $role = $this->freeSchedule();
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => $this->allDays(),
            'price' => 50, 'currency_code' => 'USD', 'payment_method' => 'cash',
        ]);
        $type->forceFill(['paid_grandfathered_at' => now()])->save();

        $sale = app(AppointmentService::class)->book($type->fresh(), $role, [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $this->firstSlot($type),
        ]);

        $this->assertEquals(50, (float) $sale->payment_amount);
    }

    /**
     * pay() is reachable only from the guest's own secret link, for a booking that already exists
     * and holds a slot. Refusing it would let ReleaseTickets expire the hold, which fires
     * Sale::booted -> cancelFromSale() and kills an accepted booking within the hour.
     */
    public function test_an_existing_booking_can_still_be_paid_after_the_plan_lapses(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => $this->allDays(),
            'price' => 50, 'currency_code' => 'USD', 'payment_method' => 'payment_url',
        ]);

        $sale = app(AppointmentService::class)->book($type, $role, [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $this->firstSlot($type),
        ]);
        $this->assertSame('unpaid', $sale->status);

        // The schedule drops to free AFTER the booking was taken.
        $role->forceFill([
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'trial_ends_at' => null,
        ])->save();

        $this->assertFalse($type->fresh()->canTakePayment(), 'new bookings are blocked');

        // ... but the one already taken is still settleable. A 404 here would mean the guest could
        // never pay and the hold would rot into a cancellation.
        $owner->payment_url = 'https://pay.example.com/abc';
        $owner->payment_secret = 'secret';
        $owner->save();

        $this->post(route('appointments.pay', [
            'event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id),
            'secret' => $sale->secret,
        ]))->assertStatus(302);
    }
}
