<?php

namespace App\Mail;

use App\Models\GiftCard;
use App\Models\Role;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class GiftCardReceipt extends Mailable
{
    use Queueable, SerializesModels;

    protected $giftCard;

    protected $role;

    /**
     * Create a new message instance.
     */
    public function __construct(GiftCard $giftCard, Role $role)
    {
        $this->giftCard = $giftCard;
        $this->role = $role;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        if ($this->role && $this->role->hasEmailSettings()) {
            $emailSettings = $this->role->getEmailSettings();
            if (! empty($emailSettings['from_address'])) {
                $fromAddress = $emailSettings['from_address'];
            }
            if (! empty($emailSettings['from_name'])) {
                $fromName = $emailSettings['from_name'];
            }
        }

        return new Envelope(
            subject: __('messages.gift_card_receipt_subject', [
                'amount' => MoneyUtils::format($this->giftCard->amount, $this->giftCard->currency_code),
            ]).' - '.$this->role->name,
            from: new Address($fromAddress, $fromName),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $cardUrl = canonical_url(route('gift_card.view', [
            'gift_card_id' => UrlUtils::encodeId($this->giftCard->id),
            'secret' => $this->giftCard->secret,
        ], false));

        return new Content(
            view: 'emails.gift_card_receipt',
            text: 'emails.gift_card_receipt_text',
            with: [
                'giftCard' => $this->giftCard,
                'role' => $this->role,
                'cardUrl' => $cardUrl,
                'scheduleUrl' => $this->role->getGuestUrl(true),
            ]
        );
    }

    /**
     * Get the message headers.
     */
    public function headers(): Headers
    {
        return new Headers(
            text: [
                'List-Unsubscribe' => '<'.route('role.unsubscribe', ['subdomain' => $this->role->subdomain]).'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }
}
