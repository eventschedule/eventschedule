<?php

namespace App\Mail;

use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class EventDeclined extends Mailable
{
    use Queueable, SerializesModels;

    protected $event;

    protected $role;

    /**
     * The person being told - EventController::requestDecisionRecipient(). Carried so the footer
     * and the List-Unsubscribe header can sign THEIR address: the old footer linked to the creator
     * schedule's email, unsigned, and unsubscribed nobody.
     */
    protected User $recipient;

    /**
     * Create a new message instance.
     */
    public function __construct($event, $role, User $recipient)
    {
        $this->event = $event;
        $this->role = $role;
        $this->recipient = $recipient;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $role = $this->role;

        return new Envelope(
            subject: str_replace(':venue', $role->name, __('messages.request_declined_subject')),
            replyTo: $role->user ? [
                new Address($role->user->email, $role->user->name),
            ] : [],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $event = $this->event;
        $role = $this->role;
        $locale = app()->getLocale();

        return new Content(
            view: 'mail.event.declined',
            text: 'mail.event.declined_text',
            with: [
                'event' => $event,
                'role' => $role,
                'recipient' => $this->recipient,
                'subject' => str_replace(':venue', $role->name, __('messages.request_declined_subject')),
                // A queued send has no request, so the date is rendered in the language this
                // message is being sent in, and in the recipient's own 12/24-hour preference.
                'eventDate' => $event->localStartsAt(true, null, false, null, $locale, (bool) $this->recipient->use_24_hour_time),
                'unsubscribeUrl' => $this->unsubscribeUrl(),
                'isRtl' => is_rtl(),
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

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'List-Unsubscribe' => '<'.$this->unsubscribeUrl().'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }

    private function unsubscribeUrl(): string
    {
        return UrlUtils::userUnsubscribeUrl($this->recipient->email, app()->getLocale());
    }
}
