<?php

namespace App\Mail;

use App\Models\Role;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailSettingsFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Role $role;

    public User $recipient;

    public string $errorMessage;

    public ?\Illuminate\Support\Carbon $failedAt;

    public function __construct(Role $role, User $recipient, string $errorMessage, ?\Illuminate\Support\Carbon $failedAt)
    {
        $this->role = $role;
        $this->recipient = $recipient;
        $this->errorMessage = $errorMessage;
        $this->failedAt = $failedAt;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.email_settings_failed_email_subject', ['schedule' => $this->role->name]),
        );
    }

    public function content(): Content
    {
        $editUrl = route('role.edit', ['subdomain' => $this->role->subdomain]).'#section-integrations';

        return new Content(
            view: 'emails.email_settings_failed',
            text: 'emails.email_settings_failed_text',
            with: [
                'role' => $this->role,
                'recipient' => $this->recipient,
                'errorMessage' => $this->errorMessage,
                'failedAt' => $this->failedAt,
                'editUrl' => $editUrl,
            ]
        );
    }
}
