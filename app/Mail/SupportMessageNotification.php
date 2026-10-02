<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportMessageNotification extends Mailable
{
    use Queueable, SerializesModels;

    /** @var string[] */
    protected $messageBodies;

    /**
     * The single body this class carried before it batched. Kept so a copy queued by the
     * previous release (which serialized only this) still renders after a deploy.
     */
    protected $messageBody;

    protected $senderName;

    protected $isAdminReply;

    protected $replyUrl;

    protected $isGuest;

    /**
     * @param  string|string[]  $messageBody  One message, or every reply the recipient has not
     *                                        read yet (SendSupportReplyEmail batches them).
     * @param  string|null  $senderName  Who wrote it. Null for a visitor who gave no name or email.
     * @param  bool  $isGuest  The conversation is with a signed-out marketing-site visitor, who has
     *                         no account to log in to and continues in the chat widget instead.
     */
    public function __construct(string|array $messageBody, ?string $senderName, bool $isAdminReply, string $replyUrl, bool $isGuest = false)
    {
        $this->messageBodies = array_values((array) $messageBody);
        $this->senderName = $senderName;
        $this->isAdminReply = $isAdminReply;
        $this->replyUrl = $replyUrl;
        $this->isGuest = $isGuest;
    }

    public function envelope(): Envelope
    {
        $app = config('app.name');

        if ($this->isAdminReply) {
            $subject = $this->isGuest
                ? __('messages.support_reply_subject_guest', ['name' => $this->senderName ?: $app, 'app' => $app])
                : __('messages.support_reply_subject', ['app' => $app]);
        } elseif ($this->isGuest) {
            $subject = $this->senderName
                ? __('messages.support_chat_subject_named', ['name' => $this->senderName])
                : __('messages.support_chat_subject');
        } else {
            $subject = __('messages.support_message_subject', ['name' => $this->senderName]);
        }

        return new Envelope(
            subject: $subject,
            // A reply to a support email should reach a person, not the no-reply sender.
            replyTo: $this->isAdminReply ? [new Address(config('app.support_email'))] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support-message',
            text: 'emails.support-message-text',
            with: [
                'messageBodies' => $this->messageBodies ?? [(string) $this->messageBody],
                'senderName' => $this->senderName,
                'isAdminReply' => $this->isAdminReply,
                'replyUrl' => $this->replyUrl,
                'isGuest' => (bool) $this->isGuest,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
