<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Utils\QrCodeUtils;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class TicketPurchase extends Mailable
{
    use Queueable, SerializesModels;

    protected $sale;

    protected $event;

    protected $role;

    /**
     * Create a new message instance.
     */
    public function __construct(Sale $sale, Event $event, ?Role $role = null)
    {
        $this->sale = $sale;
        $this->event = $event;
        $this->role = $role;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        // If role has email settings, use those for from address
        if ($this->role && $this->role->hasEmailSettings()) {
            $emailSettings = $this->role->getEmailSettings();
            if (! empty($emailSettings['from_address'])) {
                $fromAddress = $emailSettings['from_address'];
            }
            if (! empty($emailSettings['from_name'])) {
                $fromName = $emailSettings['from_name'];
            }
        }

        // A gift-card-covered order is a purchase, not a free reservation
        $subjectKey = $this->sale->payment_amount == 0 && $this->sale->groupTotalGiftCard() == 0
            ? 'messages.ticket_reservation_confirmation'
            : 'messages.ticket_purchase_confirmation';

        return new Envelope(
            subject: __($subjectKey).' - '.$this->event->name,
            from: new Address($fromAddress, $fromName),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $ticketUrl = canonical_url(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'secret' => $this->sale->secret,
        ], false));

        $qrCodeData = QrCodeUtils::png($ticketUrl);

        // Gift card feedback for the buyer: their deduction plus the balance left on the
        // card at send time (the live balance stays on the card page).
        $giftCardAmount = $this->sale->isPrimarySale()
            ? $this->sale->groupTotalGiftCard()
            : (float) ($this->sale->gift_card_amount ?? 0);
        $giftCard = $this->sale->giftCard;
        if (! $giftCard && $giftCardAmount > 0 && $this->sale->group_id) {
            $giftCard = Sale::where('group_id', $this->sale->group_id)
                ->whereNotNull('gift_card_id')->first()?->giftCard;
        }

        return new Content(
            view: 'emails.ticket_purchase',
            text: 'emails.ticket_purchase_text',
            with: [
                'sale' => $this->sale,
                'event' => $this->event,
                'role' => $this->role,
                'ticketUrl' => $ticketUrl,
                'qrCodeData' => $qrCodeData,
                'giftCardAmount' => $giftCardAmount,
                'giftCard' => $giftCard,
            ]
        );
    }

    /**
     * Get the message headers.
     */
    public function headers(): Headers
    {
        if ($this->role) {
            return new Headers(
                text: [
                    'List-Unsubscribe' => '<'.route('role.unsubscribe', ['subdomain' => $this->role->subdomain]).'>',
                    'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                ],
            );
        }

        return new Headers;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
