<?php

namespace App\Mail;

use App\Models\AppointmentType;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Utils\IcsUtils;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    protected $sale;

    protected $event;

    protected $role;

    protected $type;

    public function __construct(Sale $sale, Event $event, ?Role $role = null, ?AppointmentType $type = null)
    {
        $this->sale = $sale;
        $this->event = $event;
        $this->role = $role;
        $this->type = $type;
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        if ($this->role && $this->role->hasEmailSettings()) {
            $settings = $this->role->getEmailSettings();
            if (! empty($settings['from_address'])) {
                $fromAddress = $settings['from_address'];
            }
            if (! empty($settings['from_name'])) {
                $fromName = $settings['from_name'];
            }
        }

        return new Envelope(
            subject: __('messages.appointment_confirmed_subject', ['name' => $this->type?->name ?? $this->event->name]),
            from: new Address($fromAddress, $fromName),
        );
    }

    public function content(): Content
    {
        $manageUrl = route('appointments.manage', [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'secret' => $this->sale->secret,
        ]);

        return new Content(
            view: 'emails.appointment_confirmed',
            text: 'emails.appointment_confirmed_text',
            with: [
                'sale' => $this->sale,
                'event' => $this->event,
                'role' => $this->role,
                'type' => $this->type,
                'manageUrl' => $manageUrl,
            ],
        );
    }

    public function headers(): Headers
    {
        if ($this->role) {
            return new Headers(
                text: [
                    'List-Unsubscribe' => '<'.route('role.unsubscribe', ['subdomain' => $this->role->subdomain]).'>',
                    'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                ],
            );
        }

        return new Headers;
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => IcsUtils::buildInvite($this->event, $this->role), 'appointment.ics')
                ->withMime('text/calendar'),
        ];
    }
}
