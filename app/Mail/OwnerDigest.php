<?php

namespace App\Mail;

use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * The weekly owner digest (app:send-owner-digests): what happened last week on each of an owner's
 * active schedules, and what is coming up. The one recurring touchpoint an owner gets: every other
 * owner email is triggered by a single event, so a schedule running quietly heard nothing at all.
 *
 * $sections is built by the command, one entry per schedule, so the mail itself runs no queries
 * and renders the same numbers whenever the queue gets to it.
 */
class OwnerDigest extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{name: string, url: string, views: int, followers: int, subscribers: int, tickets: int, rsvps: int, upcoming: array<int, array{name: string, date: string}>}>  $sections
     */
    public function __construct(public User $user, public array $sections) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: count($this->sections) === 1
                ? __('messages.owner_digest_subject_one', ['schedule' => $this->sections[0]['name']])
                : __('messages.owner_digest_subject_many', ['count' => count($this->sections)]),
        );
    }

    /** A plain link, not One-Click: user.unsubscribe is a GET, and it is what the footer links to. */
    public function headers(): Headers
    {
        return new Headers(text: ['List-Unsubscribe' => '<'.$this->unsubscribeUrl().'>']);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.owner_digest',
            text: 'emails.owner_digest_text',
            with: [
                'user' => $this->user,
                'sections' => $this->sections,
                'dashboardUrl' => app_url(route('home', [], false)),
                'unsubscribeUrl' => $this->unsubscribeUrl(),
                // Schedule and event names are interpolated into prose.
                'isRtl' => in_array(app()->getLocale(), ['ar', 'he']),
            ],
        );
    }

    /** Signed over the BASE64 value, as unsubscribeUser() verifies it. Matches ActivationNudge. */
    private function unsubscribeUrl(): string
    {
        $encodedEmail = base64_encode($this->user->email);

        return route('user.unsubscribe', ['email' => $encodedEmail, 'sig' => UrlUtils::signEmail($encodedEmail)]);
    }
}
