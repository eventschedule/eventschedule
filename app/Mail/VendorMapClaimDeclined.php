<?php

namespace App\Mail;

use App\Models\Role;
use App\Models\User;
use App\Models\VendorMap;
use App\Models\VendorMapOccupant;
use App\Services\VendorMaps\VendorMapKinds;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * "Not this time": the organizer declined a vendor's request (VendorClaimNotifier::tellVendors()),
 * with the organizer's note when there is one. Short and kind; the owner voice on the vendor's
 * page, naming the market by its own name (not as a "schedule": to a vendor it is the
 * organizer), in the market's language.
 */
class VendorMapClaimDeclined extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string[]  $dates  the dates asked for; empty means every date of the market
     */
    public function __construct(
        public VendorMap $map,
        public VendorMapOccupant $occupant,
        public Role $vendor,
        public User $recipient,
        public array $dates = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine());
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor-map.claim-declined',
            text: 'emails.vendor-map.claim-declined_text',
            with: [
                'map' => $this->map,
                'market' => $this->map->role,
                'vendor' => $this->vendor,
                'recipient' => $this->recipient,
                'mailSubject' => $this->subjectLine(),
                'spotWord' => VendorMapKinds::say($this->map->kind, 'spot', ['number' => (string) $this->occupant->spot?->label]),
                'note' => filled($this->occupant->note) ? trim((string) $this->occupant->note) : null,
                'unsubscribeUrl' => $this->unsubscribeUrl(),
            ],
        );
    }

    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<'.$this->unsubscribeUrl().'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    private function subjectLine(): string
    {
        return __('messages.vm_mail_declined_subject', ['name' => $this->map->role->name]);
    }

    private function unsubscribeUrl(): string
    {
        return UrlUtils::userUnsubscribeUrl($this->recipient->email, app()->getLocale());
    }
}
