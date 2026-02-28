<?php

namespace App\Mail;

use App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewal extends Mailable
{
    use Queueable, SerializesModels;

    protected Role $role;

    protected string $amount;

    protected string $planLabel;

    protected string $renewalDate;

    protected bool $hasCard;

    public function __construct(Role $role, string $amount, string $planLabel, string $renewalDate, bool $hasCard = true)
    {
        $this->role = $role;
        $this->amount = $amount;
        $this->planLabel = $planLabel;
        $this->renewalDate = $renewalDate;
        $this->hasCard = $hasCard;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->hasCard
                ? __('messages.subscription_renewal_subject')
                : __('messages.subscription_renewal_subject_no_card'),
        );
    }

    public function content(): Content
    {
        $portalUrl = config('app.hosted')
            ? route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'plan'])
            : null;

        return new Content(
            view: 'emails.subscription.renewal',
            text: 'emails.subscription.renewal_text',
            with: [
                'role' => $this->role,
                'amount' => $this->amount,
                'planLabel' => $this->planLabel,
                'renewalDate' => $this->renewalDate,
                'portalUrl' => $portalUrl,
                'hasCard' => $this->hasCard,
            ]
        );
    }
}
