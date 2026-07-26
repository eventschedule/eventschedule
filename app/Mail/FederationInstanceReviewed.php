<?php

namespace App\Mail;

use App\Models\FederatedInstance;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells an operator that their install has been approved or suspended.
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

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.federation_instance_reviewed',
            text: 'emails.federation_instance_reviewed_text',
            with: [
                'instance' => $this->instance,
                'approved' => $this->instance->isApproved(),
            ],
        );
    }
}
