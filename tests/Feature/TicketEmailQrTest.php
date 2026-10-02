<?php

namespace Tests\Feature;

use App\Mail\TicketPurchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The user guide and the marketing pages promise a QR code with every confirmation email ("After
 * registering they receive a confirmation email with a QR code for check-in"). For months that
 * promise was kept by accident: the body's QR block was hidden inside an HTML comment, which Blade
 * still ran, so the image was attached anyway. The email redesign took the comment out and, with
 * it, the QR - which no test noticed. This pins the promise on what is actually sent.
 */
class TicketEmailQrTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    private function send(array $saleAttrs, int $price): \Symfony\Component\Mime\Email
    {
        config(['mail.default' => 'array']);
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => $price]);
        $sale = $this->createSale($event, $role, $saleAttrs, $ticket, 2);

        Mail::mailer('array')->to('buyer@example.com')->sendNow(new TicketPurchase($sale->fresh()->load('saleTickets.ticket'), $event, $role));

        return app('mail.manager')->mailer('array')->getSymfonyTransport()->messages()->last()->getOriginalMessage();
    }

    public function test_paid_and_free_tickets_both_arrive_with_their_qr_code(): void
    {
        foreach ([[['payment_amount' => 20], 10], [['payment_amount' => 0], 0]] as [$saleAttrs, $price]) {
            $email = $this->send($saleAttrs, $price);

            $qr = collect($email->getAttachments())->first(fn ($part) => $part->getFilename() === 'ticket-qr-code.png');
            $this->assertNotNull($qr, 'The confirmation email lost its QR code.');
            $this->assertSame("\x89PNG", substr($qr->getBody(), 0, 4));

            // The body stays as cd43333dc decided: no QR there, and nothing hidden in a comment.
            $this->assertStringNotContainsString('ticket-qr-code', (string) $email->getHtmlBody());
            $this->assertStringNotContainsString('<!-- ', (string) $email->getHtmlBody());
        }
    }
}
