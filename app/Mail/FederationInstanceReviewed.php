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
 * Tells an operator that their install has been suspended, or approved AGAIN.
 *
 * The first approval sends FederationInstanceWelcome instead (see FederationWelcomeService), so the
 * approved copy here is only ever read by an install that was listed before: after a suspension,
 * or after a change of address sent it back for review.
 *
 * Sent only on an admin decision. Registration itself never triggers mail:
 * contact_email arrives on an unauthenticated endpoint, so mailing it there would
 * let anyone point this at an address they do not own.
 */
class FederationInstanceReviewed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public FederatedInstance $instance) {}

    public function envelope(): Envelope
    {
        $subject = $this->instance->isApproved()
            ? __('messages.federation_approved_subject')
            : __('messages.federation_suspended_subject');

        return new Envelope(
            subject: $subject,
            // The suspended copy says "reply to this email", so make sure a reply reaches a person.
            replyTo: [new Address((string) config('app.support_email'))],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.federation_instance_reviewed',
            text: 'emails.federation_instance_reviewed_text',
            with: [
                'instance' => $this->instance,
                'approved' => $this->instance->isApproved(),
                // The only thing printed about the install. Its name and site_url are whatever the
                // registrant sent, so the name is never printed and the host is link-broken.
                'host' => $this->instance->displayHost(),
                'isRtl' => in_array(app()->getLocale(), ['ar', 'he'], true),
            ],
        );
    }
}
