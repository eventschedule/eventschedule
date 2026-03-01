<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Role;
use App\Models\TicketWaitlist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class WaitlistNotification extends Mailable
{
    use Queueable, SerializesModels;

    protected $entry;

    protected $event;

    protected $role;

    public function __construct(TicketWaitlist $entry, Event $event, ?Role $role = null)
    {
        $this->entry = $entry;
        $this->event = $event;
        $this->role = $role;
    }

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
            subject: __('messages.waitlist_tickets_available').' - '.$this->event->name,
            from: new Address($fromAddress, $fromName),
        );
    }

    public function content(): Content
    {
        $eventUrl = $this->event->getGuestUrl($this->entry->subdomain, $this->entry->event_date);
        $eventUrl .= (str_contains($eventUrl, '?') ? '&' : '?').'tickets=true';

        return new Content(
            view: 'emails.waitlist_notification',
            text: 'emails.waitlist_notification_text',
            with: [
                'entry' => $this->entry,
                'event' => $this->event,
                'role' => $this->role,
                'eventUrl' => $eventUrl,
                'unsubscribeUrl' => $this->role ? route('role.unsubscribe', ['subdomain' => $this->role->subdomain]) : '',
            ]
        );
    }

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

    public function attachments(): array
    {
        return [];
    }
}
