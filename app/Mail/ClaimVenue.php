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
        $event = $this->event;
        $venue = $event->venue;
        $role = $event->role();
        $user = $event->user;

        if ($event->curator_id) {
            $subject = __('messages.claim_your_venue_curated');
        } else {
            $subject = __('messages.claim_your_venue');
        }        

        return new Envelope(
            subject: str_replace(
                        [':role', ':venue', ':event', ':curator'], 
                        [$role->name, $venue->name, $event->name, $event->curator ? $event->curator->name : ''],
                        $subject),
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
        $role = $event->role();
        $venue = $event->venue;
        $user = $event->user;

        if ($event->curator_id) {
            $subject = __('messages.claim_your_venue_curated');
        } else {
            $subject = __('messages.claim_your_venue');
        }        

        return new Content(
            markdown: 'mail.venue.claim',
            with: [
                'event' => $event,
                'role' => $role,
                'venue' => $venue,
                'user' => $user,
                'subject' => str_replace(
                        [':role', ':venue', ':event', ':curator'], 
                        [$role->name, $venue->name, $event->name, $event->curator ? $event->curator->name : ''],
                        $subject),
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
        $role = $this->event->role();

        return new Headers(
            text: [
                'List-Unsubscribe' => '<' . route('role.unsubscribe', ['subdomain' => $role->subdomain]) . '>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }
}
