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

    public function __construct(Role $role, string $amount, string $planLabel, string $trialEndDate)
    {
        $this->role = $role;
        $this->amount = $amount;
        $this->planLabel = $planLabel;
        $this->trialEndDate = $trialEndDate;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.subscription_trial_ending_subject'),
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
            ]
        );
    }
}
