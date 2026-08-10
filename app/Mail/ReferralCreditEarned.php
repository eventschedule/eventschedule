<?php

namespace App\Mail;

use App\Models\Referral;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReferralCreditEarned extends Mailable
{
    use Queueable, SerializesModels;

    protected Referral $referral;

    public function __construct(Referral $referral)
    {
        $this->referral = $referral;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.referral_credit_earned_subject'),
        );
    }

    public function content(): Content
    {
        // From config, like every other price the site quotes. Hardcoded, this email told the
        // referrer their credit was worth one number while /referrals - which now reads the same
        // config through a view composer - told them another.
        $creditValue = '$'.(int) ($this->referral->plan_type === 'enterprise'
            ? config('services.stripe_platform.enterprise_price_monthly_amount', 29)
            : config('services.stripe_platform.price_monthly_amount', 9));

        return new Content(
            view: 'emails.referral.credit-earned',
            text: 'emails.referral.credit-earned_text',
            with: [
                'referral' => $this->referral,
                'referrer' => $this->referral->referrer,
                'creditValue' => $creditValue,
                'planType' => $this->referral->plan_type,
                'dashboardUrl' => route('referrals'),
            ]
        );
    }
}
