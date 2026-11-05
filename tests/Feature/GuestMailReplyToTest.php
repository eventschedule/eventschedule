<?php

namespace Tests\Feature;

use App\Mail\FeedbackRequest;
use App\Mail\InstallmentReminder;
use App\Mail\TicketPurchase;
use App\Services\InstallmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A buyer who replies to their confirmation reaches the organizer the mail names as its "Event
 * support contact", not the platform's inbox the mail was sent From.
 */
class GuestMailReplyToTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    public function test_a_reply_to_the_ticket_confirmation_goes_to_the_event_support_contact(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
        $ticket = $this->createTicket($event, ['price' => 0]);
        $sale = $this->createSale($event, $role, ['payment_amount' => 0], $ticket);

        $mail = new TicketPurchase($sale->fresh()->load('saleTickets.ticket'), $event->fresh(), $role);

        $this->assertTrue($mail->hasReplyTo($owner->email));
        $this->assertFalse($mail->hasReplyTo(config('mail.from.address')));
        $this->assertStringContainsString('mailto:'.$owner->email, $mail->render());
    }

    public function test_the_feedback_request_replies_to_the_address_its_footer_names(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $sale = $this->createSale($event, $role, ['status' => 'paid']);

        $this->assertTrue((new FeedbackRequest($sale, $event->fresh(), $role))->hasReplyTo($owner->email));
    }

    public function test_an_installment_mail_replies_to_the_event_support_contact(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, [
            'payment_method' => 'stripe',
            'installments_enabled' => true,
            'installment_count' => 2,
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);
        $ticket = $this->createTicket($event, ['price' => 100, 'quantity' => 10]);
        $sale = $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 100], $ticket);
        $plan = app(InstallmentService::class)->createPlan($sale, $event, 100.00, 'USD');

        $mail = new InstallmentReminder($plan->fresh('installments'), $plan->installments->last(), $role);

        $this->assertTrue($mail->hasReplyTo($owner->email));
    }
}
