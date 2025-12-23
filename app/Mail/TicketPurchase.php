<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use App\Models\Sale;
use App\Models\Event;
use App\Models\Role;
use App\Utils\UrlUtils;

class TicketPurchase extends Mailable
{
    use Queueable, SerializesModels;

    protected $sale;
    protected $event;
    protected $role;

    /**
     * Create a new message instance.
     */
    public function __construct(Sale $sale, Event $event, Role $role = null)
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
            if (!empty($emailSettings['from_address'])) {
                $fromAddress = $emailSettings['from_address'];
            }
            if (!empty($emailSettings['from_name'])) {
                $fromName = $emailSettings['from_name'];
            }
        }

        return new Envelope(
            subject: __('messages.ticket_purchase_confirmation') . ' - ' . $this->event->name,
            from: new Address($fromAddress, $fromName),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $ticketUrl = route('ticket.view', [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'secret' => $this->sale->secret
        ]);

        return new Content(
            view: 'emails.ticket_purchase',
            with: [
                'sale' => $this->sale,
                'event' => $this->event,
                'role' => $this->role,
                'ticketUrl' => $ticketUrl,
            ]
        );
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

