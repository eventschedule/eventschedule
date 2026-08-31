<?php

namespace App\Mail;

use App\Mail\Concerns\ResolvesScheduleSender;
use App\Models\Role;
use App\Models\RoleSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * "X has added new events" - the email the whole audience feature promises and nothing sent.
 *
 * A DIGEST, not one message per event. subscribe_panel_body offers "an email when :schedule adds a
 * new event" and the confirmation email narrows that to "at most one email every few days, only
 * when there is something new", so a schedule that publishes a season in one sitting owes its
 * audience one message, not thirty.
 *
 * Unlike NewsletterEmail this carries no open or click tracking. Nobody composed it, so there is
 * no campaign to report on, and an announcement people did not ask a human for should be the least
 * surveilled mail the platform sends.
 */
class EventAnnouncement extends Mailable
{
    use Queueable, ResolvesScheduleSender, SerializesModels;

    /**
     * @param  Collection<int, \App\Models\Event>  $events
     */
    public function __construct(
        public Role $role,
        public RoleSubscriber $subscriber,
        public Collection $events,
        public string $unsubscribeUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans_choice(
                'messages.announcement_subject',
                $this->events->count(),
                ['schedule' => $this->role->name, 'count' => $this->events->count()],
            ),
            from: $this->scheduleFrom($this->role),
            replyTo: $this->scheduleReplyTo($this->role),
        );
    }

    /**
     * The header SubscriptionConfirmation never set.
     *
     * POST /sub/u/{token} has been CSRF-exempt for RFC 8058 one-click since the feature shipped,
     * but nothing advertised it, so no mail client could offer the native affordance. Bulk mail
     * without it earns spam complaints instead of unsubscribes.
     */
    public function headers(): Headers
    {
        $fromAddress = config('mail.from.address');

        if ($this->role->hasEmailSettings()) {
            $settings = $this->role->getEmailSettings();
            if (! empty($settings['from_address'])) {
                $fromAddress = $settings['from_address'];
            }
        }

        return new Headers(text: [
            'List-Unsubscribe' => '<mailto:'.$fromAddress.'?subject=unsubscribe>, <'.$this->unsubscribeUrl.'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            'Precedence' => 'bulk',
            'Content-Language' => $this->role->language_code ?: 'en',
        ]);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event_announcement',
            text: 'emails.event_announcement_text',
            with: [
                'isRtl' => in_array(app()->getLocale(), ['ar', 'he']),
            ],
        );
    }
}
