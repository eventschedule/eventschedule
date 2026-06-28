<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Utils\UrlUtils;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
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
        $manageUrl = route('ticket.view', [
            'event_id' => UrlUtils::encodeId($this->sale->event_id),
            'secret' => $this->sale->secret,
        ]);

        $qrCode = QrCode::create($manageUrl)->setSize(200)->setMargin(10);
        $qrCodeData = (new PngWriter)->write($qrCode)->getString();

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
            ]
        );
    }
}
