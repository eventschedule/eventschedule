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

/**
 * Guest notice that their appointment moved.
 *
 * Modelled on AppointmentConfirmed rather than AppointmentLifecycleMail on purpose: lifecycle
 * subclasses cannot attach anything, and this mail's whole job is to carry the updated invite. The
 * attachment uses METHOD:REQUEST so the guest's existing calendar entry MOVES instead of a second one
 * appearing beside it - which is what a PUBLISH re-send would do, SEQUENCE bump or not.
 */
class AppointmentRescheduled extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $oldStartsAt  The previous UTC `starts_at`. A scalar, not a model read: with
     *                               SerializesModels the event is re-fetched at render time and would
     *                               report the NEW time on both sides of "moved from X to Y".
     * @param  bool  $pending  The move sent the booking back for approval, so nothing is booked yet.
     * @param  ?string  $note  Optional organizer note, owner-initiated moves only.
     */
    public function __construct(
        protected Sale $sale,
        protected Event $event,
        protected ?Role $role = null,
        protected ?AppointmentType $type = null,
        protected string $oldStartsAt = '',
        protected bool $pending = false,
        protected ?string $note = null,
    ) {}

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
            subject: __('messages.appointment_rescheduled_subject', ['name' => $this->type?->name ?? $this->event->name]),
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
            view: 'emails.appointment_rescheduled',
            text: 'emails.appointment_rescheduled_text',
            with: [
                'sale' => $this->sale,
                'event' => $this->event,
                'role' => $this->role,
                'type' => $this->type,
                'manageUrl' => $manageUrl,
                'oldStartsAt' => $this->oldStartsAt,
                'pending' => $this->pending,
                'note' => $this->note,
                'intro' => $this->pending
                    ? __('messages.appointment_rescheduled_pending_intro', ['schedule' => $this->role?->name ?? ''])
                    : __('messages.appointment_rescheduled_intro', ['schedule' => $this->role?->name ?? '']),
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
            Attachment::fromData(
                fn () => IcsUtils::buildInvite($this->event, $this->role, $this->sale, 'REQUEST'),
                'appointment.ics'
            )->withMime('text/calendar'),
        ];
    }
}
