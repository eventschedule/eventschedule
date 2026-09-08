<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
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
     * The name that fills the subject's :role placeholder ("X scheduled an event at your venue").
     *
     * Event::role() is talent-only and returns null for a curator- or venue-created event with no
     * performers on it, and both subject builders dereferenced ->name straight off it - so that
     * event's venue invitation died inside the queued job, sending nothing and telling nobody. The
     * organizer is the honest answer anyway: they are who scheduled it, and replyTo already points
     * at them.
     */
    protected function schedulerName(): string
    {
        $event = $this->event;

        return $event->role()?->name
            ?: ($event->creatorRole?->name ?: ($event->user?->name ?: ''));
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $event = $this->event;
        $venue = $event->venue;
        $user = $event->user;
        $curator = $event->curator();

        if ($curator) {
            $subject = __('messages.claim_your_venue_curated');
        } else {
            $subject = __('messages.claim_your_venue');
        }

        return new Envelope(
            subject: str_replace(
                [':role', ':venue', ':event', ':curator'],
                [$this->schedulerName(), $venue->name, $event->name, $curator ? $curator->name : ''],
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
        $venue = $event->venue;
        $user = $event->user;
        $curator = $event->curator();

        if ($curator) {
            $subject = __('messages.claim_your_venue_curated');
        } else {
            $subject = __('messages.claim_your_venue');
        }

        return new Content(
            view: 'mail.venue.claim',
            text: 'mail.venue.claim_text',
            with: [
                'event' => $event,
                'schedulerName' => $this->schedulerName(),
                'venue' => $venue,
                'user' => $user,
                'subject' => str_replace(
                    [':role', ':venue', ':event', ':curator'],
                    [$this->schedulerName(), $venue->name, $event->name, $curator ? $curator->name : ''],
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
        // The VENUE, not $event->role(): this mail is addressed to the venue, and unsubscribing
        // the first performer on the bill instead is both useless to the recipient and a way to
        // silence somebody else's invitations.
        $venue = $this->event->venue;

        return new Headers(
            text: [
                'List-Unsubscribe' => '<'.route('role.unsubscribe', ['subdomain' => $venue->subdomain]).'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }
}
