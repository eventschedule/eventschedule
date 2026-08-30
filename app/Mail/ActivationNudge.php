<?php

namespace App\Mail;

use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
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
 * One shell, one CTA, keyed on the nudge. Each key is an independent trigger rather than a
 * stage in a sequence - see the schedule_nudges table.
 */
class ActivationNudge extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Role $role,
        public string $nudgeKey,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.activation_nudge_subject_'.$this->nudgeKey, ['schedule' => $this->role->name]),
        );
    }

    public function content(): Content
    {
        $user = $this->role->user;

        // The signature covers the BASE64 value, not the raw address: unsubscribeUser()
        // verifies against $request->email before decoding it, so signing the raw address
        // would produce a link that always fails. Matches OnboardingNudge.
        $encodedEmail = base64_encode($user->email);

        return new Content(
            view: 'emails.activation_nudge',
            text: 'emails.activation_nudge_text',
            with: [
                'user' => $user,
                'role' => $this->role,
                'nudgeKey' => $this->nudgeKey,
                'ctaUrl' => $this->ctaUrl(),
                // This mail interpolates a user-supplied schedule name into prose, so an
                // unmarked RTL body renders the Latin name in the wrong place. Same test
                // EventChanged and EventCancelled use.
                'isRtl' => in_array(app()->getLocale(), ['ar', 'he']),
                'unsubscribeUrl' => route('user.unsubscribe', [
                    'email' => $encodedEmail,
                    'sig' => UrlUtils::signEmail($encodedEmail),
                ]),
            ],
        );
    }

    /**
     * Where the one button goes. Every nudge lands on the screen that does the thing it asks
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
            // Tickets hang off an event, and idle schedules need a new date, so both land on
            // the schedule's own admin page.
            default => app_url(route('role.view_admin', [
                'subdomain' => $this->role->subdomain,
                'tab' => 'schedule',
            ], false)),
        };
    }
}
