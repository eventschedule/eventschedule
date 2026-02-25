<?php

namespace App\Mail;

use App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionPaymentFailed extends Mailable
{
    use Queueable, SerializesModels;

    protected Role $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.subscription_payment_failed_subject'),
        );
    }

    public function content(): Content
    {
        $portalUrl = config('app.hosted')
            ? route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'plan'])
            : null;

        return new Content(
            view: 'emails.subscription.payment-failed',
            text: 'emails.subscription.payment-failed_text',
            with: [
                'role' => $this->role,
                'portalUrl' => $portalUrl,
            ]
        );
    }
}
