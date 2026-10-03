<?php

namespace App\Mail;

use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * Reaches a schedule that HAS activated and then stalled.
 *
 * OnboardingNudge stops dead at whereDoesntHave('roles'), and every other guidance surface
 * stops at the same moment: the dashboard "Get Started" panel is gated on having no schedules,
 * and the step indicator ends at "create event". So the product went silent exactly when
 * someone became interesting - and the 2026-08-30 export shows what that costs. Of 438
 * schedules that publish an event, 144 ever create a ticket type and 27 ever take money, while
 * a schedule that has sold recently is a paying customer 55.6% of the time against 0.47% for
 * one that never has.
 *
 * One shell and one primary button, keyed on the nudge (no_event adds AI Import as a second
 * one where it works). Each key is an independent trigger rather than a stage in a sequence -
 * see the schedule_nudges table.
 */
class ActivationNudge extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * $ticketsUrl is the Tickets section of the event the ticket nudges are about, built by the
     * command. A URL rather than the Event: SendQueuedEmail sets deleteWhenMissingModels, so a
     * model that is deleted before the job runs would silently discard this email after its
     * schedule_nudges claim was written, and it would never be sent.
     */
    public function __construct(
        public Role $role,
        public string $nudgeKey,
        public ?string $ticketsUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.activation_nudge_subject_'.$this->nudgeKey, ['schedule' => $this->role->name]),
            // Replies reach a person on eventschedule.com. An operator platform runs this command
            // too, and support_email defaults to our address, which must not reach their owners.
            replyTo: config('app.is_nexus') && config('app.support_email')
                ? [new Address((string) config('app.support_email'))]
                : [],
        );
    }

    /**
     * RFC 8058 one-click, the same signed account-wide opt-out (users.is_subscribed) as the footer
     * link, so a mail client's own unsubscribe button works without opening the email.
     */
    public function headers(): Headers
    {
        return new Headers(
            text: [
                'List-Unsubscribe' => '<'.UrlUtils::userUnsubscribeUrl($this->role->user->email).'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }

    public function content(): Content
    {
        $user = $this->role->user;

        return new Content(
            view: 'emails.activation_nudge',
            text: 'emails.activation_nudge_text',
            with: [
                'user' => $user,
                'role' => $this->role,
                'nudgeKey' => $this->nudgeKey,
                'bodyKey' => $this->bodyKey(),
                'ctaUrl' => $this->ctaUrl(),
                'importUrl' => $this->importUrl(),
                // "Hello Sam," or just "Hello,", never firstName()'s English "there".
                'greeting' => $user->greetingName()
                    ? __('messages.hello').' '.$user->greetingName().','
                    : __('messages.hello').',',
                'unsubscribeUrl' => UrlUtils::userUnsubscribeUrl($user->email, app()->getLocale()),
            ],
        );
    }

    /**
     * The body's translation key. no_ticket_type_free offers the card-free selling trial, which
     * a past subscriber or an owner who has already had it cannot start - telling them they can
     * only for the paywall to refuse is worse than not mentioning it.
     */
    private function bodyKey(): string
    {
        if ($this->nudgeKey === 'no_ticket_type_free' && ! $this->role->isEligibleForTicketTrial()) {
            return 'messages.activation_nudge_body_no_ticket_type_free_no_trial';
        }

        return 'messages.activation_nudge_body_'.$this->nudgeKey;
    }

    /**
     * Where the primary button goes. Every nudge lands on the screen that does the thing it asks
     * for, never a generic dashboard: the ask is the whole point of the email.
     *
     * route(..., false) inside app_url(), never an absolute route() - app_url() prepends the
     * base path, so an absolute path doubles it and 404s on a path-routed install.
     */
    private function ctaUrl(): string
    {
        return match ($this->nudgeKey) {
            // Straight into the new-event form for this schedule.
            'no_event' => app_url(route('event.create', ['subdomain' => $this->role->subdomain], false)),
            // Payment methods live on the account, not the schedule.
            'no_gateway' => app_url(route('profile.edit', [], false).'#section-payment-methods'),
            // The money already arrived; show them where it landed.
            'first_sale' => app_url(route('sales', [], false)),
            // Straight to the Tickets section of the event that needs them, when the command found
            // one this schedule created; otherwise the schedule's admin page, as before.
            'no_ticket_type', 'no_ticket_type_free' => $this->ticketsUrl ?? $this->scheduleAdminUrl(),
            // Idle schedules need a new date, so they land on the schedule's own admin page.
            default => $this->scheduleAdminUrl(),
        };
    }

    private function scheduleAdminUrl(): string
    {
        return app_url(route('role.view_admin', [
            'subdomain' => $this->role->subdomain,
            'tab' => 'schedule',
        ], false));
    }

    /**
     * The quickest way to a first event: paste a flyer or a list of dates into AI Import. Only
     * offered where it works - the import needs an AI key, and an operator platform running this
     * command may have none.
     */
    private function importUrl(): ?string
    {
        if ($this->nudgeKey !== 'no_event'
            || (! config('services.google.gemini_key') && ! config('services.openai.api_key'))) {
            return null;
        }

        return app_url(route('event.show_import_ai', ['subdomain' => $this->role->subdomain], false));
    }
}
