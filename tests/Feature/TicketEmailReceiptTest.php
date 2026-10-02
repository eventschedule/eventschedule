<?php

namespace Tests\Feature;

use App\Mail\TicketPurchase;
use App\Models\GiftCard;
use App\Utils\MoneyUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The ticket confirmation doubles as a receipt. A gift card's deduction sits directly above the
 * total, as at checkout: printed after it, "Total $70" then "-$30" read as $40 paid.
 */
class TicketEmailReceiptTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    public function test_a_gift_card_deduction_reads_above_the_total_it_explains(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
        $ticket = $this->createTicket($event, ['price' => 50]);

        $card = new GiftCard;
        $card->forceFill([
            'role_id' => $role->id, 'code' => GiftCard::generateCode(), 'secret' => strtolower(Str::random(32)),
            'amount' => 50, 'remaining_amount' => 20, 'currency_code' => 'USD', 'status' => 'active',
            'payment_method' => 'cash', 'purchaser_name' => 'Alice', 'purchaser_email' => 'alice@test.dev', 'recipient_name' => 'Sam', 'recipient_email' => 'sam@test.dev',
            'activated_at' => now(), 'expires_at' => now()->addYear(),
        ])->save();

        $sale = $this->createSale($event, $role, ['payment_amount' => 70, 'gift_card_id' => $card->id, 'gift_card_amount' => 30], $ticket, 2);

        $html = (new TicketPurchase($sale->fresh()->load('saleTickets.ticket'), $event, $role))->render();

        $deduction = strpos($html, '-'.e(MoneyUtils::format(30, 'USD')));
        $total = strpos($html, '>'.e(__('messages.total')).'<');
        $this->assertNotFalse($deduction, 'The gift card deduction is missing.');
        $this->assertNotFalse($total, 'The total is missing.');
        $this->assertLessThan($total, $deduction, 'The gift card deduction must read above the total.');
        $this->assertStringContainsString(e(MoneyUtils::format(70, 'USD')), $html);

        // Printed once, in the receipt, not again in the gift card section below it.
        $this->assertSame(1, substr_count($html, '-'.e(MoneyUtils::format(30, 'USD'))));
        $this->assertStringContainsString(e(__('messages.gift_card_remaining_balance')), $html);
    }
}
