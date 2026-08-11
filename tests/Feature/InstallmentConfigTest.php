<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Repos\EventRepo;
use App\Services\InstallmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Event-level installment configuration: the Pro gate, the runway clamp, and eligibility.
 *
 * The gate follows the repo's scrub-not-reject idiom, which has a subtlety worth pinning: turning
 * the flag ON without Pro is refused, but a schedule that has already lapsed keeps the flag it had.
 * Rewriting stored config on someone's next unrelated save is how the pass feature broke once.
 */
class InstallmentConfigTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function service(): InstallmentService
    {
        return app(InstallmentService::class);
    }

    /**
     * Feature tests run with plan gates bypassed, so a real denial needs hosted mode plus a
     * genuinely free, expired schedule.
     */
    private function freeRole()
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);

        return $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subYear()->format('Y-m-d'),
        ]);
    }

    private function save(array $input, ?Event $event, $role): Event
    {
        $request = Request::create('/', 'POST', array_merge([
            'name' => 'Test Event',
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1,
            'payment_method' => 'stripe',
            'ticket_currency_code' => 'USD',
        ], $input));

        // saveEvent() reads the acting user off the request, and the FIRST argument is the
        // schedule, not the user - that is what supplies the Pro gate.
        $request->setUserResolver(fn () => $role->user);

        return app(EventRepo::class)->saveEvent($role, $request, $event);
    }

    public function test_a_free_schedule_cannot_turn_installments_on(): void
    {
        $role = $this->freeRole();

        $event = $this->save([
            'installments_enabled' => 1,
            'installment_count' => 4,
        ], null, $role);

        $this->assertFalse((bool) $event->fresh()->installments_enabled);
    }

    /**
     * The scrub must not rewrite what is already stored. A Pro schedule that lapses keeps
     * collecting on plans its buyers already authorised.
     */
    public function test_a_lapsed_schedule_keeps_installments_it_already_had(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, [
            'payment_method' => 'stripe',
            'installments_enabled' => true,
            'installment_count' => 4,
        ]);

        // Now the plan lapses.
        config(['app.hosted' => true, 'app.is_testing' => false]);
        $role->update(['plan_type' => 'free', 'plan_expires' => now()->subYear()->format('Y-m-d')]);

        $saved = $this->save([
            'name' => 'Renamed',
            'installments_enabled' => 1,
            'installment_count' => 4,
        ], $event->fresh(), $role->fresh());

        $this->assertTrue((bool) $saved->fresh()->installments_enabled);
    }

    public function test_the_collection_runway_is_clamped_to_a_safe_floor(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');

        // A zero runway would let the last charge fall the day before the doors open.
        $event = $this->save([
            'installments_enabled' => 1,
            'installment_count' => 4,
            'installment_final_days_before' => 0,
        ], null, $role);

        $this->assertSame(
            InstallmentService::MIN_FINAL_DAYS_BEFORE,
            (int) $event->fresh()->installment_final_days_before
        );
    }

    public function test_the_count_is_clamped_to_the_supported_range(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');

        $event = $this->save([
            'installments_enabled' => 1,
            'installment_count' => 99,
        ], null, $role);

        $this->assertSame(InstallmentService::MAX_COUNT, (int) $event->fresh()->installment_count);
    }

    // ---- Eligibility ----

    public function test_a_non_stripe_event_is_never_eligible(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, [
            'payment_method' => 'cash',
            'installments_enabled' => true,
            'installment_count' => 4,
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->assertSame(
            'messages.installments_requires_stripe',
            $this->service()->ineligibleReason($event, 1000, 'USD', null)
        );
    }

    public function test_an_order_below_the_minimum_is_refused(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, [
            'payment_method' => 'stripe',
            'installments_enabled' => true,
            'installment_count' => 4,
            'installment_min_order_amount' => 500,
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->assertSame(
            'messages.installments_below_minimum',
            $this->service()->ineligibleReason($event, 100, 'USD', null)
        );
        $this->assertNull($this->service()->ineligibleReason($event, 1000, 'USD', null));
    }

    /**
     * A gift card can drag the payable remainder under Stripe's per-charge floor. Every part has
     * to clear it, not just the first.
     */
    public function test_every_part_must_clear_the_stripe_minimum(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, [
            'payment_method' => 'stripe',
            'installments_enabled' => true,
            'installment_count' => 4,
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);

        // 1.20 over four parts is 0.30 each, below the 0.50 floor.
        $this->assertSame(
            'messages.installments_below_minimum',
            $this->service()->ineligibleReason($event, 1.20, 'USD', null)
        );
    }

    /**
     * The VIP's own numbers, and the reason the runway default is 14 days: bought today, four
     * monthly payments, event in three months means the last charge lands after the doors open.
     */
    public function test_a_schedule_that_runs_past_the_event_is_refused(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, [
            'payment_method' => 'stripe',
            'installments_enabled' => true,
            'installment_count' => 4,
            'installment_final_days_before' => 14,
            'starts_at' => now()->addMonths(3)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->assertSame(
            'messages.installments_does_not_fit',
            $this->service()->ineligibleReason($event, 1000, 'USD', null)
        );

        // Three payments finish in time.
        $event->update(['installment_count' => 3]);
        $this->assertNull($this->service()->ineligibleReason($event->fresh(), 1000, 'USD', null));
    }

    /**
     * The trap the plan review caught: for a recurring event starts_at is the recurrence anchor
     * and sits in the PAST, so a fit check against it refuses every recurring event forever. The
     * buyer's chosen occurrence date is what counts.
     */
    public function test_a_recurring_event_is_judged_on_the_chosen_occurrence(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'UTC']);
        $event = $this->createEvent($role, [
            'payment_method' => 'stripe',
            'installments_enabled' => true,
            'installment_count' => 3,
            'installment_final_days_before' => 14,
            // Anchor in the past, as a recurring event's always is.
            'starts_at' => now()->subMonths(2)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'days_of_week' => '1000000',
        ]);

        // Against the anchor alone the schedule cannot possibly fit.
        $this->assertSame(
            'messages.installments_does_not_fit',
            $this->service()->ineligibleReason($event, 1000, 'USD', null)
        );

        // Against a real future occurrence it does.
        $occurrence = Carbon::now()->addMonths(6)->format('Y-m-d');
        $this->assertNull(
            $this->service()->ineligibleReason($event, 1000, 'USD', $occurrence),
            'A future occurrence of a recurring event must be eligible'
        );
    }
}
