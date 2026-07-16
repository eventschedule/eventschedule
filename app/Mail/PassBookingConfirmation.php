<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\PassBookingService;
use App\Utils\QrCodeUtils;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Confirms a single advance booking a pass holder made for an occurrence. The
 * QR is the holder's pass link (one QR works for every booked date - the door
 * scanner reconciles by date), so this mirrors TicketPurchase's QR approach.
 */
class PassBookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected Sale $sale,
        protected Event $bookedEvent,
        protected string $date,
        protected ?Role $role = null,
    ) {}

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
            subject: __('messages.pass_booking_confirmation').' - '.$this->bookedEvent->name,
            from: new Address($fromAddress, $fromName),
        );
    }

    public function content(): Content
    {
        // The pass link lives on the sale's home event (where the secret resolves),
        // which may differ from the booked event for a multi-event pass.
        $manageUrl = canonical_url(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($this->sale->event_id),
            'secret' => $this->sale->secret,
        ], false));

        $qrCodeData = QrCodeUtils::png($manageUrl);

        // Cancellation deadline for this booking, when the pass sets one. A
        // deadline already in the past (the booking was made inside the cutoff
        // window) is not worth printing as a date - the blade shows the
        // policy's consequence instead.
        $passTicket = app(PassBookingService::class)->passSaleTicket($this->sale)?->ticket;
        $cancelDeadline = $passTicket?->passCancelDeadlineUtc($this->bookedEvent, $this->date);

        return new Content(
            view: 'emails.pass_booking_confirmation',
            text: 'emails.pass_booking_confirmation_text',
            with: [
                'sale' => $this->sale,
                'bookedEvent' => $this->bookedEvent,
                'date' => $this->date,
                'role' => $this->role,
                'dateLabel' => $this->bookedEvent->localStartsAt(true, $this->date),
                'manageUrl' => $manageUrl,
                'qrCodeData' => $qrCodeData,
                'cancelDeadlineLabel' => $cancelDeadline
                    ? $this->bookedEvent->localizedInstantLabel($cancelDeadline)
                    : null,
                'cancelDeadlinePassed' => $cancelDeadline ? $cancelDeadline->isPast() : false,
                'lateCancelPolicy' => $cancelDeadline ? $passTicket->passLateCancelPolicy() : null,
            ]
        );
    }
}
