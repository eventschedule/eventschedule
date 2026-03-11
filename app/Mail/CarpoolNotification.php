<?php

namespace App\Mail;

use App\Models\CarpoolOffer;
use App\Models\CarpoolRequest;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class CarpoolNotification extends Mailable
{
    use Queueable, SerializesModels;

    protected $type;

    protected $event;

    protected $offer;

    protected $carpoolRequest;

    protected $role;

    protected $recipient;

    public function __construct(
        string $type,
        Event $event,
        CarpoolOffer $offer,
        ?CarpoolRequest $carpoolRequest,
        ?Role $role = null,
        ?User $recipient = null
    ) {
        $this->type = $type;
        $this->event = $event;
        $this->offer = $offer;
        $this->carpoolRequest = $carpoolRequest;
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

        $subject = match ($this->type) {
            'carpool_ride_requested' => __('messages.carpool_email_ride_requested_subject', ['event' => $this->event->name]),
            'carpool_request_approved' => __('messages.carpool_email_request_approved_subject', ['event' => $this->event->name]),
            'carpool_request_declined' => __('messages.carpool_email_request_declined_subject', ['event' => $this->event->name]),
            'carpool_offer_cancelled' => __('messages.carpool_email_offer_cancelled_subject', ['event' => $this->event->name]),
            'carpool_request_cancelled' => __('messages.carpool_email_request_cancelled_subject', ['event' => $this->event->name]),
            'carpool_reminder' => __('messages.carpool_email_reminder_subject', ['event' => $this->event->name]),
            default => __('messages.carpool_email_notification_subject', ['event' => $this->event->name]),
        };

        return new Envelope(
            subject: $subject,
            from: new Address($fromAddress, $fromName),
        );
    }

    public function content(): Content
    {
        $encodedEmail = $this->recipient ? base64_encode($this->recipient->email) : '';
        $unsubscribeUrl = $this->recipient ? route('user.unsubscribe', [
            'email' => $encodedEmail,
            'sig' => UrlUtils::signEmail($encodedEmail),
        ]) : '';

        return new Content(
            view: 'emails.carpool_notification',
            text: 'emails.carpool_notification_text',
            with: [
                'type' => $this->type,
                'event' => $this->event,
                'offer' => $this->offer,
                'carpoolRequest' => $this->carpoolRequest,
                'role' => $this->role,
                'recipient' => $this->recipient,
                'unsubscribeUrl' => $unsubscribeUrl,
            ]
        );
    }

    public function headers(): Headers
    {
        if ($this->recipient) {
            $encodedEmail = base64_encode($this->recipient->email);
            $unsubscribeUrl = route('user.unsubscribe', [
                'email' => $encodedEmail,
                'sig' => UrlUtils::signEmail($encodedEmail),
            ]);

            return new Headers(
                text: [
                    'List-Unsubscribe' => '<'.$unsubscribeUrl.'>',
                    'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                ],
            );
        }

        return new Headers;
    }

    public function attachments(): array
    {
        return [];
    }
}
