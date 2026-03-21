<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportMessageNotification extends Mailable
{
    use Queueable, SerializesModels;

    protected $messageBody;

    protected $senderName;

    protected $isAdminReply;

    protected $replyUrl;

    public function __construct(string $messageBody, string $senderName, bool $isAdminReply, string $replyUrl)
    {
        $this->messageBody = $messageBody;
        $this->senderName = $senderName;
        $this->isAdminReply = $isAdminReply;
        $this->replyUrl = $replyUrl;
    }

    public function envelope(): Envelope
    {
        $subject = $this->isAdminReply
            ? 'New reply from Event Schedule Support'
            : 'New support message from '.$this->senderName;

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support-message',
            text: 'emails.support-message-text',
            with: [
                'messageBody' => $this->messageBody,
                'senderName' => $this->senderName,
                'isAdminReply' => $this->isAdminReply,
                'replyUrl' => $this->replyUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
