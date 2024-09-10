<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimVenue extends Mailable
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
        $venue = $this->event->venue;
        $role = $this->event->role;
        $user = $this->event->user;

        return new Envelope(
            subject: str_replace(
                        [':role', ':venue'], 
                        [$role->name, $venue->name],
                        __('messages.claim_your_venue')),
            replyTo: [
                new Address($user->email, $user->name),
            ],                        
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
            markdown: 'mail.venue.claim',
            with: [
                'event' => $event,
                'role' => $role,
                'venue' => $venue,
                'user' => $user,
                'unsubscribe_url' => route('role.unsubscribe', ['subdomain' => $venue->subdomain]),
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
        $role = $this->event->role;

        return new Headers(
            text: [
                'List-Unsubscribe' => '<' . route('role.unsubscribe', ['subdomain' => $role->subdomain]) . '>',
            ],
        );
    }
}
