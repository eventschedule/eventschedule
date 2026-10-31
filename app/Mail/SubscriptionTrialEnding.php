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
                ? __('messages.subscription_trial_ending_subject', ['schedule' => $this->role->name])
                : __('messages.subscription_trial_ending_subject_no_card', ['schedule' => $this->role->name]),
        );
    }

    /**
     * One piece of advice about the trial's paid event, when there is one on sale: a trial that
     * ends with nothing sold is a subscription that does not stay. No way to get paid comes first,
     * because nothing can sell without it; then a paid event with no orders, named, with its link.
     * Null when the schedule has no upcoming paid event of its own, or it has orders.
     *
     * @return array{key: string, params: array<string, string>, url: string}|null
     */
    private function sellingTip(): ?array
    {
        $user = $this->role->user;
        if (! $user || ! config('app.hosted')) {
            return null;
        }

        $event = $this->role->events()
            ->where('events.creator_role_id', $this->role->id)
            ->where('events.is_draft', false)
            ->where('events.is_private', false)
            ->where('events.is_cancelled', false)
            ->where('events.tickets_enabled', true)
            ->whereNull('events.appointment_type_id')
            ->hasUpcomingOccurrence()
            ->whereHas('tickets', fn ($t) => $t->where('tickets.is_deleted', false)
                ->where('tickets.is_addon', false)
                ->where('tickets.price', '>', 0))
            ->orderBy('events.starts_at')
            ->first(['events.*']);

        if (! $event) {
            return null;
        }

        if (empty(payment_gateways()->connectedFor($user))) {
            return [
                'key' => 'messages.subscription_trial_ending_tip_no_gateway',
                'params' => ['event' => (string) $event->name],
                'url' => app_url(route('profile.edit', [], false).'#section-payment-methods'),
            ];
        }

        if ($event->sales()->where('sales.is_deleted', false)->exists()) {
            return null;
        }

        return [
            'key' => 'messages.subscription_trial_ending_tip_no_orders',
            'params' => ['event' => (string) $event->name],
            'url' => (string) $event->getGuestUrl($this->role->subdomain, null, true),
        ];
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
                'sellingTip' => $this->windDown ? null : $this->sellingTip(),
            ]
        );
    }
}
