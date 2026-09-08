<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class ClaimRole extends Mailable
{
    use Queueable, SerializesModels;

    protected $event;

    protected $role;

    /**
     * Create a new message instance.
     *
     * $role is WHO this invitation is for, and it has to be passed in. EventRepo dispatches one of
     * these per invited performer, but every one of them used to recompute its own recipient as
     * $event->role(), which is `roles->first(fn ($r) => $r->isTalent())` - the first act on the
     * bill. On a three-act night that put act A's name in all three subjects, act A's email
     * address into the sign-up link mailed to acts B and C, and act A's schedule behind their
     * unsubscribe links, so B opting out silenced A.
     *
     * Nullable, with the old behaviour as the fallback, only because SendQueuedEmail serialises
     * the whole Mailable onto the queue: a job written before this deploy has to deserialise.
     */
    public function __construct($event, $role = null)
    {
        $this->event = $event;
        $this->role = $role;
    }

    /** The invited performer, falling back to the pre-deploy behaviour for a queued job. */
    protected function invitedRole()
    {
        return $this->role ?: $this->event->role();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $event = $this->event;
        $role = $this->invitedRole();
        $user = $event->user;
        $curator = $event->curator();

        if ($curator) {
            $subject = __('messages.claim_your_role_curated');
        } else {
            $subject = __('messages.claim_your_role');
        }

        return new Envelope(
            subject: str_replace(
                [':venue', ':role', ':event', ':curator'],
                [$event->getVenueDisplayName(), $role->name, $event->name, $curator ? $curator->name : ''],
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
        $role = $this->invitedRole();
        $user = $event->user;
        $curator = $event->curator();

        if ($curator) {
            $subject = __('messages.claim_your_role_curated');
        } else {
            $subject = __('messages.claim_your_role');
        }

        return new Content(
            view: 'mail.role.claim',
            text: 'mail.role.claim_text',
            with: [
                'event' => $event,
                'role' => $role,
                'user' => $user,
                'subject' => str_replace(
                    [':venue', ':role', ':event', ':curator'],
                    [$event->getVenueDisplayName(), $role->name, $event->name, $curator ? $curator->name : ''],
                    $subject),
                'unsubscribe_url' => route('role.unsubscribe', ['subdomain' => $role->subdomain]),
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
        $role = $this->invitedRole();

        return new Headers(
            text: [
                'List-Unsubscribe' => '<'.route('role.unsubscribe', ['subdomain' => $role->subdomain]).'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }
}
