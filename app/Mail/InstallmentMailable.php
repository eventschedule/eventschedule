<?php

namespace App\Mail;

use App\Models\Role;
use App\Models\SaleInstallment;
use App\Models\SaleInstallmentPlan;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * Shared shape for the four buyer-facing installment emails. They differ only in subject line and
 * template; the plan context, the From resolution and the payment link are identical.
 *
 * Subclasses set $template and implement subjectLine().
 */
abstract class InstallmentMailable extends Mailable
{
    use Queueable, SerializesModels;

    protected string $template;

    public function __construct(
        protected SaleInstallmentPlan $plan,
        protected ?SaleInstallment $installment = null,
        protected ?Role $role = null,
    ) {}

    abstract protected function subjectLine(): string;

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        if ($this->role && $this->role->hasEmailSettings()) {
            $emailSettings = $this->role->getEmailSettings();
            if (! empty($emailSettings['from_address'])) {
                $fromAddress = $emailSettings['from_address'];
            }
            if (! empty($emailSettings['from_name'])) {
                $fromName = $emailSettings['from_name'];
            }
        }

        return new Envelope(
            subject: $this->subjectLine(),
            from: new Address($fromAddress, $fromName),
        );
    }

    public function content(): Content
    {
        $sale = $this->plan->sale;
        $event = $sale?->event;

        return new Content(
            view: 'emails.'.$this->template,
            text: 'emails.'.$this->template.'_text',
            with: [
                'plan' => $this->plan,
                'installment' => $this->installment,
                'sale' => $sale,
                'event' => $event,
                'role' => $this->role,
                // The buyer's own plan page. Its secret is the plan's, not the sale's, so a
                // forwarded ticket link cannot be used to pay somebody else's installments.
                'payUrl' => route('installment.view', [
                    'plan_id' => UrlUtils::encodeId($this->plan->id),
                    'secret' => $this->plan->secret,
                ]),
                'cardLabel' => $this->cardLabel(),
            ]
        );
    }

    /**
     * "your Visa ending 4242" rather than "your card". A reminder that names the card reads as
     * legitimate; one that does not reads like phishing.
     */
    protected function cardLabel(): ?string
    {
        if (! $this->plan->card_brand || ! $this->plan->card_last4) {
            return null;
        }

        return ucfirst($this->plan->card_brand).' '.__('messages.card_ending').' '.$this->plan->card_last4;
    }

    public function headers(): Headers
    {
        if ($this->role) {
            return new Headers(
                text: [
                    'List-Unsubscribe' => '<'.route('role.unsubscribe', ['subdomain' => $this->role->subdomain]).'>',
                    'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                ],
            );
        }

        return new Headers;
    }

    public function attachments(): array
    {
        return [];
    }
}
