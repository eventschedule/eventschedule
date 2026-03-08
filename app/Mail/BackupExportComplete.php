<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackupExportComplete extends Mailable
{
    use Queueable, SerializesModels;

    protected string $downloadUrl;

    protected array $scheduleNames;

    protected $expiresAt;

    public function __construct(string $downloadUrl, array $scheduleNames, $expiresAt)
    {
        $this->downloadUrl = $downloadUrl;
        $this->scheduleNames = $scheduleNames;
        $this->expiresAt = $expiresAt;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.backup_export_email_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.backup_export_complete',
            text: 'emails.backup_export_complete_text',
            with: [
                'downloadUrl' => $this->downloadUrl,
                'scheduleNames' => $this->scheduleNames,
                'expiresAt' => $this->expiresAt,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
