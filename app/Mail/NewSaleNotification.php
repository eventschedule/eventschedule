<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Utils\MoneyUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class NewSaleNotification extends Mailable
{
    use Queueable, SerializesModels;

    protected $sale;

    protected $event;

    protected $role;

    protected $recipient;

    /**
     * Create a new message instance.
     */
    public function __construct(Sale $sale, Event $event, ?Role $role = null, ?User $recipient = null)
    {
        $this->sale = $sale;
        $this->event = $event;
        $this->role = $role;
        $this->recipient = $recipient;

        $this->sale->loadMissing('saleTickets.ticket');
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
            subject: __('messages.new_sale_notification_subject', ['event' => $this->event->name]),
            from: new Address($fromAddress, $fromName),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $salesUrl = route('sales');

        $currencyCode = $this->event->ticket_currency_code ?: 'USD';
        $total = MoneyUtils::format($this->sale->payment_amount ?? $this->sale->calculateTotal(), $currencyCode);

        $statusKey = match ($this->sale->status) {
            'paid' => 'messages.paid',
            'pending' => 'messages.pending',
            default => 'messages.unpaid',
        };

        return new Content(
            view: 'emails.new_sale_notification',
            text: 'emails.new_sale_notification_text',
            with: [
                'sale' => $this->sale,
                'event' => $this->event,
                'role' => $this->role,
                'recipient' => $this->recipient,
                'salesUrl' => $salesUrl,
                'total' => $total,
                'paymentStatus' => __($statusKey),
                'unsubscribeUrl' => $this->role ? route('role.unsubscribe', ['subdomain' => $this->role->subdomain]) : '',
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
