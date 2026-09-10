<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Sale;
use App\Services\PassRedemptionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A pass is turned away at the door on exactly the sale statuses a single ticket is.
 *
 * PassRedemptionService kept its own list - unpaid, cancelled, refunded - and redeemed everything
 * else, so an EXPIRED sale (a checkout that lapsed unpaid, its seats already back on sale) and an
 * amount_mismatch one still awaiting review both checked in like paid passes. Both scanners now
 * share Sale::scanRefusalMessage().
 *
 * A sale that expired is not the same thing as a pass past its valid-until date: that one is the
 * neutral `expired` pass_status, and PassBookingTest covers it.
 */
class PassRedemptionStatusTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-07-15 18:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public static function refusedStatuses(): array
    {
        return [
            'expired' => ['expired', 'messages.this_ticket_is_expired'],
            'amount_mismatch' => ['amount_mismatch', 'messages.this_ticket_is_pending_review'],
            'unpaid' => ['unpaid', 'messages.this_ticket_is_not_paid'],
            'cancelled' => ['cancelled', 'messages.this_ticket_is_cancelled'],
            'refunded' => ['refunded', 'messages.this_ticket_is_refunded'],
        ];
    }

    #[DataProvider('refusedStatuses')]
    public function test_a_pass_whose_sale_is_not_paid_is_refused(string $status, string $messageKey): void
    {
        [$event, $sale] = $this->passSale($status);

        $result = $this->redeem($sale->fresh(), $event);

        $this->assertSame(__($messageKey), $result->error ?? null);
        $this->assertFalse(property_exists($result, 'pass_status'), 'a refused sale never reaches the pass checks');
        $this->assertSame([], $this->usages($sale), "a {$status} pass must not record a visit");
    }

    public function test_an_unknown_status_is_refused_as_not_valid(): void
    {
        [$event, $sale] = $this->passSale('paid');

        // sales.status is a MySQL enum, so an unrecognised value only exists in memory: a status
        // added to the enum before the allowlist learns about it. It must fail closed.
        $sale = $sale->fresh();
        $sale->status = 'on_hold';

        $result = $this->redeem($sale, $event);

        $this->assertSame(__('messages.this_ticket_is_not_valid'), $result->error ?? null);
        $this->assertSame([], $this->usages($sale));
    }

    public function test_a_paid_pass_is_still_redeemed(): void
    {
        [$event, $sale] = $this->passSale('paid');

        $result = $this->redeem($sale->fresh(), $event);

        $this->assertFalse(property_exists($result, 'error'));
        $this->assertSame('valid', $result->pass_status);
        $this->assertCount(1, $this->usages($sale));
    }

    /**
     * A season pass sale in $status, at an event whose check-in window is open right now.
     *
     * @return array{0: Event, 1: Sale}
     */
    private function passSale(string $status): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['timezone' => 'UTC']);
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id,
            'tickets_enabled' => true,
            'starts_at' => '2026-07-15 18:00:00',
            'duration' => 4,
        ]);
        $pass = $this->createTicket($event, [
            'type' => 'Season Pass', 'quantity' => 100, 'price' => 50,
            'is_pass' => true, 'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event',
        ]);
        $sale = $this->createSale($event, $role, ['status' => $status, 'email' => $status.'@gmail.com'], $pass);

        return [$event, $sale];
    }

    private function redeem(Sale $sale, Event $event): \stdClass
    {
        return app(PassRedemptionService::class)->redeem($sale, $event->fresh(), Carbon::now());
    }

    private function usages(Sale $sale): array
    {
        return Sale::find($sale->id)->saleTickets->first(fn ($st) => $st->ticket->is_pass)->pass_usages ?? [];
    }
}
