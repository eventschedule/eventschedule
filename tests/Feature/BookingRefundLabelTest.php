<?php

namespace Tests\Feature;

use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\Feature\Concerns\FakesStripeRefunds;
use Tests\TestCase;

/**
 * The desktop Sales menu's refund label for an appointment booking.
 *
 * A booking is not a ticket, and the phone menu already says plain "Refund" for it. The desktop
 * menu said "Refund Ticket" for every gateway refund.
 */
class BookingRefundLabelTest extends TestCase
{
    use CreatesScheduleData;
    use FakesStripeRefunds;
    use RefreshDatabase;

    /**
     * The text of the desktop menu's refund button for one sale.
     *
     * Read off the button itself: messages.refund_ticket is also the refund dialog's heading, which
     * renders on every Sales page, so a page-wide assertSee() on either label pins nothing.
     */
    private function desktopRefundLabel(string $html, int $saleId): string
    {
        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $buttons = (new \DOMXPath($dom))->query(sprintf(
            '//button[@data-sale-action="refund"][@data-sale-id="%s"]',
            UrlUtils::encodeId($saleId)
        ));
        $this->assertSame(1, $buttons->length, 'one desktop refund button for the sale');

        return trim(preg_replace('/\s+/', ' ', $buttons->item(0)->textContent));
    }

    public function test_a_paid_booking_offers_refund_and_a_ticket_keeps_refund_ticket(): void
    {
        $this->fakeStripeRefunds();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $type = $this->createAppointmentType($role, ['price' => 50, 'payment_method' => 'stripe']);
        $booking = $this->createEvent($role, [
            'creator_role_id' => $role->id,
            'appointment_type_id' => $type->id,
            'ticket_currency_code' => 'USD',
        ]);
        $bookingSale = $this->createSale($booking, $role, [
            'email' => 'guest@gmail.com',
            'payment_amount' => 50,
            'payment_method' => 'stripe',
            'status' => 'paid',
            'transaction_reference' => 'pi_test_'.uniqid(),
        ]);

        $event = $this->createEvent($role, ['tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 10]);
        $ticketSale = $this->createSale($event, $role, [
            'email' => 'buyer@gmail.com',
            'payment_amount' => 20,
            'payment_method' => 'stripe',
            'status' => 'paid',
            'transaction_reference' => 'pi_test_'.uniqid(),
        ], $ticket);

        $html = $this->actingAs($owner)->get(route('sales'))->assertOk()->getContent();

        $this->assertSame(__('messages.refund'), $this->desktopRefundLabel($html, $bookingSale->id));
        $this->assertSame(__('messages.refund_ticket'), $this->desktopRefundLabel($html, $ticketSale->id));
    }
}
