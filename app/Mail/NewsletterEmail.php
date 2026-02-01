<?php

namespace App\Mail;

use App\Models\Newsletter;
use App\Models\NewsletterRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class NewsletterEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $newsletter;

    protected $recipient;

    protected $renderedHtml;

    public function __construct(Newsletter $newsletter, NewsletterRecipient $recipient, string $renderedHtml)
    {
        $this->newsletter = $newsletter;
        $this->recipient = $recipient;
        $this->renderedHtml = $renderedHtml;
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        $role = $this->newsletter->role;
        if ($role && $role->hasEmailSettings()) {
            $emailSettings = $role->getEmailSettings();
            if (! empty($emailSettings['from_address'])) {
                $fromAddress = $emailSettings['from_address'];
            }
            if (! empty($emailSettings['from_name'])) {
                $fromName = $emailSettings['from_name'];
            }
        }

        return new Envelope(
            subject: $this->newsletter->subject,
            from: new Address($fromAddress, $fromName),
        );
    }

    public function content(): Content
    {
        $style = array_merge(
            \App\Models\Newsletter::defaultStyleSettings(),
            $this->newsletter->style_settings ?? []
        );
        $events = app(\App\Services\NewsletterService::class)
            ->getUpcomingEvents($this->newsletter->role, $this->newsletter->event_ids);
        $unsubscribeUrl = url('/nl/u/'.$this->recipient->token);

        return new Content(
            view: 'emails.newsletter_rendered',
            text: 'emails.newsletter_text',
            with: [
                'renderedHtml' => $this->renderedHtml,
                'newsletter' => $this->newsletter,
                'role' => $this->newsletter->role,
                'events' => $events,
                'style' => $style,
                'unsubscribeUrl' => $unsubscribeUrl,
            ],
        );
    }

    public function headers(): Headers
    {
        $unsubscribeUrl = url('/nl/u/'.$this->recipient->token);

        return new Headers(
            text: [
                'List-Unsubscribe' => '<'.$unsubscribeUrl.'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
