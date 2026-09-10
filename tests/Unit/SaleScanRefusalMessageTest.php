<?php

namespace Tests\Unit;

use App\Models\Sale;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Sale::scanRefusalMessage() is the door's allowlist, shared by the single-ticket scanner and the
 * pass scanner: only `paid` comes in, and every other status says why it is turned away. Anything
 * the list does not name fails closed. Built in memory; no database.
 */
class SaleScanRefusalMessageTest extends TestCase
{
    public function test_a_paid_sale_is_let_in(): void
    {
        $this->assertNull((new Sale(['status' => 'paid']))->scanRefusalMessage());
    }

    public static function refusals(): array
    {
        return [
            'unpaid' => ['unpaid', 'messages.this_ticket_is_not_paid'],
            'cancelled' => ['cancelled', 'messages.this_ticket_is_cancelled'],
            'refunded' => ['refunded', 'messages.this_ticket_is_refunded'],
            'expired' => ['expired', 'messages.this_ticket_is_expired'],
            'amount_mismatch' => ['amount_mismatch', 'messages.this_ticket_is_pending_review'],
            'a status the list does not know' => ['on_hold', 'messages.this_ticket_is_not_valid'],
            'no status at all' => [null, 'messages.this_ticket_is_not_valid'],
        ];
    }

    #[DataProvider('refusals')]
    public function test_every_other_status_is_refused_with_its_own_message(?string $status, string $messageKey): void
    {
        $message = (new Sale(['status' => $status]))->scanRefusalMessage();

        $this->assertSame(__($messageKey), $message);
        // A missing key would come back as the key itself, which the door would show verbatim.
        $this->assertStringStartsNotWith('messages.', $message);
    }
}
