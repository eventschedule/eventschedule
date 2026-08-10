<?php

namespace App\Mail;

use App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionTrialEnding extends Mailable
{
    use Queueable, SerializesModels;

    protected Role $role;

    protected string $amount;

    protected string $planLabel;

    protected string $trialEndDate;

    protected bool $hasCard;

    /**
     * True for a comped plan being wound down by app:wind-down-comped-plans rather than a real
     * Stripe trial. Both of the existing copy families are wrong for it: there is no
     * subscription, so no card will be charged, and adding a payment method achieves nothing -
     * the owner has to start a subscription, so an email telling them otherwise lets the plan
     * lapse while they believe they have acted.
     */
    protected bool $windDown;

    public function __construct(Role $role, string $amount, string $planLabel, string $trialEndDate, bool $hasCard = true, bool $windDown = false)
    {
        $this->role = $role;
        $this->amount = $amount;
        $this->planLabel = $planLabel;
        $this->trialEndDate = $trialEndDate;
        $this->hasCard = $hasCard;
        $this->windDown = $windDown;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->hasCard && ! $this->windDown
                ? __('messages.subscription_trial_ending_subject')
                : __('messages.subscription_trial_ending_subject_no_card'),
        );
    }

    public function content(): Content
    {
        $portalUrl = config('app.hosted')
            ? route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'plan'])
            : null;

        return new Content(
            view: 'emails.subscription.trial-ending',
            text: 'emails.subscription.trial-ending_text',
            with: [
                'role' => $this->role,
                'amount' => $this->amount,
                'planLabel' => $this->planLabel,
                'trialEndDate' => $this->trialEndDate,
                'portalUrl' => $portalUrl,
                'hasCard' => $this->hasCard && ! $this->windDown,
                'windDown' => $this->windDown,
            ]
        );
    }
}
