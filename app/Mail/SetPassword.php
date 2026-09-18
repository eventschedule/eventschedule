<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * "Set your password" - the password-reset mail, reworded for an account that never had one.
 *
 * A passwordless stub is created by several paths (a confirmed newsletter subscription, an owner's
 * newsletter import, a team invite), and every one of them leaves somebody who has an account but
 * has never chosen a credential for it. The stock Illuminate ResetPassword notification tells that
 * person to "reset" a password they never set, which reads like a phishing mail.
 *
 * Deliberately a Mailable dispatched through App\Jobs\SendQueuedEmail rather than a Notification:
 * App\Notifications\VerifyEmail, the only custom notification in the repo, hardcodes its subject,
 * greeting and salutation in English for all 12 locales. SendQueuedEmail takes an explicit locale,
 * which is what the rest of this feature uses.
 *
 * It carries the SAME route('password.reset') URL the stock mail does, so NewPasswordController
 * keeps handling it and no second token type exists.
 *
 * Transactional: never gated on users.is_subscribed. Somebody who once used /user/unsubscribe has
 * that flag false for ever - nothing in the app sets it back - and gating this would leave them
 * with an account they can never sign in to.
 */
class SetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $resetUrl,
        public string $email,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.set_password_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.set_password',
            text: 'emails.set_password_text',
            with: [
                'isRtl' => in_array(app()->getLocale(), ['ar', 'he']),
                'expiresInMinutes' => config('auth.passwords.users.expire', 60),
            ],
        );
    }
}
