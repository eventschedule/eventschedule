<?php

namespace App\Mail;

use App\Mail\Concerns\SendsToNotificationEmail;
use App\Models\Event;
use App\Models\EventFeedback;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class FeedbackNotification extends Mailable
{
    use Queueable, SendsToNotificationEmail, SerializesModels;

    protected $feedback;

    protected $sale;

    protected $event;

    protected $role;

    protected $recipient;

    public function __construct(EventFeedback $feedback, Sale $sale, Event $event, ?Role $role = null, ?User $recipient = null)
    {
        $this->feedback = $feedback;
        $this->sale = $sale;
        $this->event = $event;
        $this->role = $role;
        $this->recipient = $recipient;
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

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
            subject: __('messages.feedback_notification_subject', ['event' => $this->event->name]),
            from: new Address($fromAddress, $fromName),
        );
    }

    public function content(): Content
    {
        $salesUrl = route('sales').'?tab=feedback';

        return new Content(
            view: 'emails.feedback_notification',
            text: 'emails.feedback_notification_text',
            with: [
                'feedback' => $this->feedback,
                'sale' => $this->sale,
                'event' => $this->event,
                'role' => $this->role,
                'recipient' => $this->recipient,
                'salesUrl' => $salesUrl,
                'notificationEmailUnsubscribeUrl' => $this->notificationEmailUnsubscribeUrl,
            ]
        );
    }

    public function headers(): Headers
    {
        return $this->listUnsubscribeHeaders($this->role);
    }

    public function attachments(): array
    {
        return [];
    }
}
