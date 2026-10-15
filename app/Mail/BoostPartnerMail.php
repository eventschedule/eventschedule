<?php

namespace App\Mail;

use App\Models\BoostCampaign;
use App\Models\BoostPartner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Every mail of a partner boost's lifecycle, to the partner or to the Business that asked.
 * One view (emails.boost.partner) says what happened for each kind; the platform is writing
 * about a schedule, so it is the owner voice, with the schedule named as `the ":name"
 * schedule`.
 *
 * Kinds: request_received, request_resent, approved, declined, rejected, live, stopped,
 * access_problem, connected.
 */
class BoostPartnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public const KINDS = ['request_received', 'request_resent', 'approved', 'declined', 'rejected', 'live', 'stopped', 'access_problem', 'connected'];

    public function __construct(
        public string $kind,
        public ?BoostCampaign $campaign,
        public ?BoostPartner $partner = null,
        public bool $toPartner = false,
    ) {
        $this->partner = $partner ?? $campaign?->partner;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('messages.boost_partner_mail_'.$this->kind.'_subject', $this->replacements()));
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.boost.partner',
            text: 'emails.boost.partner_text',
            with: [
                'kind' => $this->kind,
                'campaign' => $this->campaign,
                'partner' => $this->partner,
                'toPartner' => $this->toPartner,
                'replace' => $this->replacements(),
                'role' => $this->campaign?->role,
                'url' => $this->url(),
                'bodyKey' => $this->bodyKey(),
                'stopReason' => $this->stopReason(),
            ],
        );
    }

    public function replacements(): array
    {
        return [
            'schedule' => $this->campaign?->role?->getDisplayName(false) ?? (string) $this->campaign?->schedule_name,
            // No campaign (the connection mails): no event to name, so the heading falls back to
            // the Page's name rather than to "Deleted event".
            'event' => ! $this->campaign ? ''
                : ($this->campaign->event?->translatedName() ?? (string) ($this->campaign->event_name ?: __('messages.deleted_event'))),
            'page' => $this->partner?->displayName() ?? '',
            'name' => $this->campaign?->user?->name ?? '',
        ];
    }

    protected function url(): string
    {
        if (! $this->campaign) {
            return $this->kind === 'connected'
                ? route('boost.partner.requests')
                : route('profile.edit').'#section-boost-account';
        }

        // Requests lists only what waits on the partner; anything else opens the boost itself.
        if ($this->toPartner && in_array($this->kind, ['request_received', 'request_resent'], true)) {
            return route('boost.partner.requests').'#request-'.$this->campaign->hashedId();
        }

        return route('boost.show', ['hash' => $this->campaign->hashedId()]);
    }

    /** The body's key: an approval of a boost that is already paid says it is on its way. */
    public function bodyKey(): string
    {
        if ($this->kind === 'approved' && $this->campaign?->isPaidCurrent()) {
            // Approved and paid: on its way, or (the partner's account not usable yet) waiting
            // only for that. The mail is rendered when it is sent, so it reads the boost as it is.
            return $this->campaign->status === 'ready'
                ? 'messages.boost_partner_ready_waits_for_page'
                : 'messages.boost_partner_mail_approved_paid_body';
        }

        // A request withdrawn before it ran never "will not run again": say what happened.
        if ($this->kind === 'stopped' && $this->campaign?->stop_code === \App\Services\BoostPartnerService::STOP_WITHDRAWN) {
            return 'messages.boost_partner_stop_code_withdrawn';
        }

        return 'messages.boost_partner_mail_'.$this->kind.'_body';
    }

    /** Why a boost stopped, in words: the note given, or the sentence for its code. */
    public function stopReason(): ?string
    {
        if (! $this->campaign) {
            return null;
        }

        if ($this->campaign->stop_reason) {
            return $this->campaign->stop_reason;
        }

        $key = match ($this->campaign->stop_code) {
            \App\Services\BoostPartnerService::STOP_BY_PARTNER => 'boost_partner_stop_code_partner',
            \App\Services\BoostPartnerService::STOP_BY_BUSINESS => 'boost_partner_stop_code_business',
            \App\Services\BoostPartnerService::STOP_ACCESS_WITHDRAWN => 'boost_partner_stop_code_access',
            \App\Services\BoostPartnerService::STOP_EVENT_REMOVED => 'boost_partner_stop_code_event',
            default => null,
        };

        return $key ? __('messages.'.$key, ['page' => $this->partner?->displayName() ?? '']) : null;
    }
}
