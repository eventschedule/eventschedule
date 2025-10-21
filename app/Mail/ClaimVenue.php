<?php

namespace App\Mail;

use App\Support\MailTemplateManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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

    protected MailTemplateManager $templates;

    /**
     * Create a new message instance.
     */
    public function __construct($event)
    {
        $this->event = $event;
        $this->templates = app(MailTemplateManager::class);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $data = $this->templateData();

        return new Envelope(
            subject: $this->templates->renderSubject('claim_venue', $data),
            replyTo: [
                new Address($data['organizer_email'], $data['organizer_name']),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $data = $this->templateData();

        return new Content(
            markdown: 'mail.templates.generic',
            with: [
                'body' => $this->templates->renderBody('claim_venue', $data),
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
        $role = $this->event?->role();

        if (! $role) {
            return new Headers();
        }

        return new Headers(
            text: [
                'List-Unsubscribe' => '<' . route('role.unsubscribe', ['subdomain' => $role->subdomain]) . '>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }

    protected function templateData(): array
    {
        $event = $this->event;
        $role = $event?->role();
        $venue = $event?->venue;
        $user = $event?->user;
        $curator = $event?->curator();

        $venueEmail = $venue?->email;
        $encodedEmail = $venueEmail ? base64_encode($venueEmail) : null;

        $defaultEmail = config('mail.from.address') ?? config('mail.mailers.smtp.username') ?? 'no-reply@example.com';
        $defaultName = config('mail.from.name') ?? config('app.name');

        return [
            'event_name' => $event?->name ?? '',
            'role_name' => $role?->name ?? '',
            'venue_name' => $venue?->name ?? '',
            'curator_name' => $curator?->name ?? '',
            'organizer_name' => $user?->name ?? $defaultName,
            'organizer_email' => $user?->email ?? $defaultEmail,
            'event_url' => $event?->getGuestUrl() ?? '',
            'sign_up_url' => $encodedEmail ? route('sign_up', ['email' => $encodedEmail]) : route('sign_up'),
            'unsubscribe_url' => $encodedEmail
                ? route('role.show_unsubscribe', ['email' => $encodedEmail])
                : route('role.show_unsubscribe'),
            'app_name' => config('app.name'),
            'is_curated' => (bool) $curator,
        ];
    }
}
