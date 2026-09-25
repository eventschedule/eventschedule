<?php

namespace App\Mail;

use App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Asks a schedule's shared notification address to confirm it wants the schedule's notifications
 * (NotificationEmailService::sendVerification()). Until someone opens the link and confirms,
 * nothing else is sent to it, and ignoring this keeps it that way.
 */
class NotificationEmailConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Role $role,
        public string $confirmUrl,
        public ?string $requesterName = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.notification_email_confirm_subject', ['schedule' => $this->role->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification_email_confirmation',
            text: 'emails.notification_email_confirmation_text',
            with: [
                'role' => $this->role,
                'confirmUrl' => $this->confirmUrl,
                'requesterName' => $this->requesterName ?: $this->role->name,
            ],
        );
    }
}
