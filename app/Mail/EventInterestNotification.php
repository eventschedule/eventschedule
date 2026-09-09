<?php

namespace App\Mail;

use App\Mail\Concerns\ResolvesScheduleSender;
use App\Models\Event;
use App\Models\EventInterest;
use App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * The two things somebody who asked about an event actually gets: tickets went on sale, and the
 * event is coming up.
 *
 * One class for both, because the only difference is three strings - splitting it would duplicate
 * the headers, the sender resolution and the RTL handling three ways.
 *
 * Carries List-Unsubscribe with the row's OWN token. The nearest existing model,
 * WaitlistNotification, points that header at RoleController::unsubscribe(), which matches on
 * Role::where('email', ...) - so a Gmail one-click POST, which carries neither an address nor a
 * signature, fails validation and the header is decorative. Bulk mail with a dead one-click
 * unsubscribe earns spam complaints instead of unsubscribes, on a From address shared by every
 * schedule on the platform.
 */
class EventInterestNotification extends Mailable
{
    use Queueable, ResolvesScheduleSender, SerializesModels;

    public const KIND_TICKETS = 'tickets';

    public const KIND_REMINDER = 'reminder';

    public const KIND_CHANGE = 'change';

    public const KIND_CANCELLED = 'cancelled';

    public function __construct(
        public Role $role,
        public Event $event,
        public EventInterest $interest,
        public string $kind,
        public string $eventUrl,
        public string $unsubscribeUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.event_interest_'.$this->kind.'_subject', ['event' => $this->event->name]),
            from: $this->scheduleFrom($this->role),
            replyTo: $this->scheduleReplyTo($this->role),
        );
    }

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
            view: 'emails.event_interest',
            text: 'emails.event_interest_text',
            with: [
                // The body interpolates a user-supplied event name into prose, so the direction has
                // to follow the recipient's locale - the same reason EventChanged and
                // EventCancelled set it and OnboardingNudge does not.
                'isRtl' => in_array(app()->getLocale(), ['ar', 'he']),
                'heading' => __('messages.event_interest_'.$this->kind.'_heading'),
                'body' => __('messages.event_interest_'.$this->kind.'_body', ['event' => $this->event->name]),
                'button' => __('messages.event_interest_'.$this->kind.'_button'),
            ],
        );
    }
}
