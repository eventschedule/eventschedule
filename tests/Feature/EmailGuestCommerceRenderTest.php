<?php

namespace Tests\Feature;

use App\Mail\FeedbackRequest;
use App\Mail\GiftCardReceipt;
use App\Mail\GiftCardRecipient;
use App\Mail\PassBookingConfirmation;
use App\Mail\WaitlistNotification;
use App\Models\GiftCard;
use App\Models\TicketWaitlist;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The guest-facing commerce mails (pass bookings, gift cards, waitlist, feedback requests) render
 * on the <x-email.*> design system, and no other test renders their HTML part. A component that
 * reads a variable one of these views does not pass would otherwise surface only as a failed
 * queued job.
 */
class EmailGuestCommerceRenderTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    private function assertRendersCleanly(Mailable $mail): string
    {
        $html = $mail->render();

        $this->assertSame(1, substr_count($html, '<h1'), 'Exactly one <h1>.');
        $this->assertStringNotContainsString('messages.', $html, 'An untranslated key leaked into the mail.');
        $this->assertLessThan(80 * 1024, strlen($html), 'Gmail clips mail over 102KB, hiding everything below the cut.');

        return $html;
    }

    private function giftCard($role): GiftCard
    {
        $card = new GiftCard;
        $card->role_id = $role->id;
        $card->code = GiftCard::generateCode();
        $card->secret = strtolower(Str::random(32));
        $card->amount = 50;
        $card->remaining_amount = 50;
        $card->currency_code = 'USD';
        $card->status = 'active';
        $card->payment_method = 'cash';
        $card->purchaser_name = 'Alice Buyer';
        $card->purchaser_email = 'alice@test.dev';
        $card->recipient_name = 'Bob Recipient';
        $card->recipient_email = 'bob@test.dev';
        $card->message = 'Enjoy the show';
        $card->activated_at = now();
        $card->expires_at = now()->addYear();
        $card->save();

        return $card->fresh();
    }

    public function test_the_pass_booking_confirmation_renders_with_its_deadline_and_qr_code(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true]);
        $pass = $this->createTicket($event, [
            'type' => 'Visit Pass', 'price' => 50, 'is_pass' => true, 'pass_usage_type' => 'unlimited',
            'pass_scope' => 'this_event', 'pass_allow_booking' => true,
            'pass_cancel_cutoff_hours' => 24, 'pass_late_cancel_policy' => 'forfeit',
        ]);
        $sale = $this->createSale($event, $role, [], $pass);
        $date = Carbon::parse($event->starts_at)->format('Y-m-d');

        $mail = new PassBookingConfirmation($sale->fresh(), $event->fresh(), $date, $role);
        $html = $this->assertRendersCleanly($mail);

        $this->assertStringContainsString(e($event->name), $html);
        $this->assertStringContainsString(e(__('messages.manage_my_pass')), $html);
        $this->assertStringContainsString(e(__('messages.pass_cancel_email_deadline_forfeit', ['deadline' => $mail->content()->with['cancelDeadlineLabel']])), $html);
        // The QR code is embedded, and render() swaps a one-line cid: image for a data: URI.
        $this->assertStringContainsString('data:image/png;base64,', $html);
    }

    public function test_both_gift_card_mails_render_with_the_code(): void
    {
        $role = $this->createRole($this->createOwner());
        $card = $this->giftCard($role);

        $receipt = $this->assertRendersCleanly(new GiftCardReceipt($card, $role));
        $this->assertStringContainsString($card->formattedCode(), $receipt);
        $this->assertStringContainsString('bob@test.dev', $receipt);

        $recipient = $this->assertRendersCleanly(new GiftCardRecipient($card, $role));
        $this->assertStringContainsString($card->formattedCode(), $recipient);
        $this->assertStringContainsString('Enjoy the show', $recipient);
    }

    public function test_the_waitlist_notification_renders_with_its_link_and_unsubscribe(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $entry = TicketWaitlist::create([
            'event_id' => $event->id, 'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'subdomain' => $role->subdomain, 'name' => 'Sam', 'email' => 'sam@gmail.com', 'status' => 'waiting',
        ]);

        $html = $this->assertRendersCleanly(new WaitlistNotification($entry, $event->fresh(), $role));

        $this->assertStringContainsString('tickets=true', $html);
        $this->assertStringContainsString(e(__('messages.unsubscribe')), $html);
    }

    public function test_the_feedback_request_renders_and_a_test_send_disables_its_button(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role);
        $sale = $this->createSale($event, $role);

        $html = $this->assertRendersCleanly(new FeedbackRequest($sale->fresh(), $event->fresh(), $role));
        $this->assertStringContainsString(route('feedback.show', ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret]), $html);

        // RoleController::testFeedbackEmail sends an unsaved sale: the button is shown but inert.
        $fake = new \App\Models\Sale([
            'name' => 'Test Attendee', 'email' => 'owner@gmail.com', 'secret' => Str::random(32), 'event_id' => $event->id,
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'), 'subdomain' => $role->subdomain, 'status' => 'paid',
        ]);
        $preview = $this->assertRendersCleanly(new FeedbackRequest($fake, $event->fresh(), $role));
        $this->assertStringContainsString(e(__('messages.test_email_note')), $preview);
        $this->assertStringNotContainsString('/feedback/', $preview);
    }
}
