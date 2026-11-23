<?php

namespace App\Mail;

use App\Models\Role;
use App\Models\User;
use App\Models\VendorMap;
use App\Models\VendorMapOccupant;
use App\Services\VendorMaps\VendorMapKinds;
use App\Services\VendorMaps\VendorMapPng;
use App\Services\VendorMaps\VendorMapPresenter;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * "You're at stall 14 at ...": the organizer approved a vendor's request
 * (VendorClaimNotifier::tellVendors()). The owner voice on the VENDOR's page, naming the market
 * by its own name (ScheduleNamedInMailTest's exception: to a vendor the market is the
 * organizer, not a "schedule"), in the market's language. Carries the map with the stall marked (embedded, never
 * a remote image), the dates, the vendor's page, a link that opens the visitors' map on the stall,
 * and the signed "Not coming" link (the occupant and a hash of what the row says, so a link from
 * a stall the vendor no longer has stops working).
 */
class VendorMapClaimApproved extends Mailable
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
        public ?string $nextDate = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine());
    }

    public function content(): Content
    {
        $market = $this->map->role;

        return new Content(
            view: 'emails.vendor-map.claim-approved',
            text: 'emails.vendor-map.claim-approved_text',
            with: [
                'map' => $this->map,
                'market' => $market,
                'vendor' => $this->vendor,
                'recipient' => $this->recipient,
                'kind' => $this->map->kind(),
                'mailSubject' => $this->subjectLine(),
                'spotWord' => $this->spotWord(),
                'everyDate' => $this->dates === [],
                'dateLabels' => array_map(fn ($d) => VendorMapPresenter::longDate($d), $this->dates),
                'nextLabel' => $this->nextDate ? VendorMapPresenter::longDate($this->nextDate) : null,
                'pageUrl' => $this->vendor->getGuestUrl(true) ?: null,
                'shareUrl' => VendorMapPresenter::publicUrl($market).'?s='.rawurlencode((string) $this->occupant->spot?->label),
                'calendarUrl' => $this->calendarUrl(),
                // The link answers for a stall held for the season; a vendor here on chosen dates
                // only is not offered one that would land on "this link is out of date".
                'notComingUrl' => $this->occupant->isSeason() ? route('vendor_map.away', [
                    'subdomain' => $market->subdomain,
                    'occupant' => UrlUtils::encodeId($this->occupant->id),
                    'hash' => $this->occupant->stateHash(),
                ]) : null,
                'stallPng' => $this->stallPng(),
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
        return __('messages.vm_mail_approved_subject', ['spot' => $this->spotWord(), 'name' => $this->map->role->name]);
    }

    private function spotWord(): string
    {
        return VendorMapKinds::say($this->map->kind, 'spot', ['number' => (string) $this->occupant->spot?->label]);
    }

    /** The .ics of the next market day, where the event's page would offer one. */
    private function calendarUrl(): ?string
    {
        $event = $this->map->event;

        if (! $event || ! $this->nextDate || $event->is_draft || $event->is_internal || $event->is_private || $event->is_cancelled) {
            return null;
        }

        try {
            return $event->getAppleCalendarUrl($this->nextDate, $this->map->role->subdomain, true) ?: null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * The map with the stall marked, as PNG bytes, drawn at render time (the view embeds it with
     * $message->embedData()). Null when it cannot be drawn: the mail goes without it.
     */
    private function stallPng(): ?string
    {
        $class = VendorMapPng::class;

        if (! class_exists($class) || ! method_exists($class, 'stallImage')) {
            return null;
        }

        try {
            $png = $class::stallImage($this->map, (int) $this->occupant->spot_id);

            return is_string($png) && $png !== '' ? $png : null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function unsubscribeUrl(): string
    {
        return UrlUtils::userUnsubscribeUrl($this->recipient->email, app()->getLocale());
    }
}
