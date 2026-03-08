<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackupImportComplete extends Mailable
{
    use Queueable, SerializesModels;

    protected array $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.backup_import_email_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.backup_import_complete',
            text: 'emails.backup_import_complete_text',
            with: [
                'report' => $this->report,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
