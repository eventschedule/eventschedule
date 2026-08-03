<?php

namespace App\Mail;

use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Reaches an account that signed up meaning to run a schedule and never created one.
 *
 * Three stages with different copy rather than the same mail three times: someone who has not
 * come back after three days needs a different message from someone who left an hour ago.
 * Stage 3 says outright that it is the last one.
 */
class OnboardingNudge extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public int $stage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.onboarding_nudge_subject_'.$this->stage),
        );
    }

    public function content(): Content
    {
        // The signature covers the BASE64 value, not the raw address: unsubscribeUser()
        // verifies against $request->email before decoding it, so signing the raw address
        // would produce a link that always fails. Matches CarpoolNotification.
        $encodedEmail = base64_encode($this->user->email);

        return new Content(
            view: 'emails.onboarding_nudge',
            text: 'emails.onboarding_nudge_text',
            with: [
                'user' => $this->user,
                'stage' => $this->stage,
                'startUrl' => app_url('/getting-started'),
                'unsubscribeUrl' => route('user.unsubscribe', [
                    'email' => $encodedEmail,
                    'sig' => UrlUtils::signEmail($encodedEmail),
                ]),
            ],
        );
    }
}
