<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimRole extends Mailable
{
    use Queueable, SerializesModels;

    protected $event;

    /**
     * Create a new message instance.
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $role = $this->event->role;
        $venue = $this->event->venue;
        $user = $this->event->user;

        return new Envelope(
            subject: str_replace(
                        [':venue', ':role'], 
                        [$venue->name, $role->name],
                        __('messages.claim_your_role')),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $event = $this->event;
        $role = $event->role;
        $venue = $event->venue;
        $user = $event->user;

        return new Content(
            markdown: 'mail.role.claim',
            with: [
                'event' => $event,
                'role' => $role,
                'venue' => $venue,
                'user' => $user,
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
