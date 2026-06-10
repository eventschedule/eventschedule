<?php

namespace App\Mail;

use App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class GraphicEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $role;

    protected $imageData;

    protected $eventText;

    protected $usePlatformSender;

    /**
     * Create a new message instance.
     *
     * @param  bool  $usePlatformSender  When true, keep the platform's default
     *                                   From identity even if the schedule has
     *                                   custom SMTP settings. Used when the
     *                                   schedule's custom SMTP is failing and
     *                                   we fall back to the platform mailer, so
     *                                   SPF/DKIM stay aligned.
     */
    public function __construct(Role $role, string $imageData, string $eventText, bool $usePlatformSender = false)
    {
        $this->role = $role;
        $this->imageData = $imageData;
        $this->eventText = $eventText;
        $this->usePlatformSender = $usePlatformSender;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        // If role has email settings, use those for from address (unless we're
        // falling back to the platform mailer, which keeps the platform sender
        // so SPF/DKIM stay aligned).
        if (! $this->usePlatformSender && $this->role && $this->role->hasEmailSettings()) {
            $emailSettings = $this->role->getEmailSettings();
            if (! empty($emailSettings['from_address'])) {
                $fromAddress = $emailSettings['from_address'];
            }
            if (! empty($emailSettings['from_name'])) {
                $fromName = $emailSettings['from_name'];
            }
        }

        return new Envelope(
            subject: __('messages.upcoming_events').' - '.$this->role->getDisplayName(),
            from: new Address($fromAddress, $fromName),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.graphic_email',
            text: 'emails.graphic_email_text',
            with: [
                'role' => $this->role,
                'eventText' => $this->eventText,
            ]
        );
    }

    /**
     * Get the message headers.
     */
    public function headers(): Headers
    {
        return new Headers(
            text: [
                'List-Unsubscribe' => '<'.route('role.unsubscribe', ['subdomain' => $this->role->subdomain]).'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->imageData, $this->role->subdomain.'-upcoming-events.png')
                ->withMime('image/png'),
        ];
    }
}
