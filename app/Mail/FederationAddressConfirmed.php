<?php

namespace App\Mail;

use App\Models\FederatedInstance;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells an operator that the network now has their install at its new address, and that their
 * listings are being sent again with links to it.
 *
 * Sent by FederationAddressService::adopt(), on an admin's Accept or on a verified redirect from
 * the old address: never on what an install reported alone, since a claim arrives from whatever
 * holds the install's secret.
 */
class FederationAddressConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public FederatedInstance $instance) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.federation_address_confirmed_subject'),
            replyTo: [new Address((string) config('app.support_email'))],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.federation_address_confirmed',
            text: 'emails.federation_address_confirmed_text',
            with: [
                // The only thing printed about the install, link-broken: see displayHost().
                'host' => $this->instance->displayHost(),
            ],
        );
    }
}
