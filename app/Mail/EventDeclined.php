<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Mail\Mailables\Address;

class EventDeclined extends Mailable
{
    use Queueable, SerializesModels;

    protected $event;
    protected $role;

    /**
     * Create a new message instance.
     */
    public function __construct($event, $role)
    {
        $this->event = $event;
        $this->role = $role;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $event = $this->event;
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
        $creatorRole = $event->creatorRole;

        return new Content(
            view: 'mail.event.declined',
            with: [
                'event' => $event,
                'role' => $role,
                'creatorRole' => $creatorRole,
                'subject' => str_replace(':venue', $role->name, __('messages.request_declined_subject')),
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
        $creatorRole = $this->event->creatorRole;

        return new Headers(
            text: [
                'List-Unsubscribe' => '<' . route('role.unsubscribe', ['subdomain' => $creatorRole->subdomain]) . '>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }
}
