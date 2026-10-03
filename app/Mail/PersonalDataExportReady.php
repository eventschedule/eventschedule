<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** The "Download my data" link (App\Jobs\ExportPersonalData). Account voice. */
class PersonalDataExportReady extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $downloadUrl,
        protected $expiresAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('messages.data_export_email_subject'));
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.personal_data_export_ready',
            text: 'emails.personal_data_export_ready_text',
            with: [
                'downloadUrl' => $this->downloadUrl,
                'expiresAt' => $this->expiresAt,
            ]
        );
    }
}
