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

    /**
     * Create a new message instance.
     */
    public function __construct(Role $role, string $imageData, string $eventText)
    {
        $this->role = $role;
        $this->imageData = $imageData;
        $this->eventText = $eventText;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        // If role has email settings, use those for from address
        if ($this->role && $this->role->hasEmailSettings()) {
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
                'scheduleUrl' => $this->role->getGuestUrl(),
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
