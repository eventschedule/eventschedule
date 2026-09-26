<?php

namespace App\Notifications;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

// This notification must NOT implement ShouldQueue - user is waiting for the verification code
class SignupVerificationCode extends Notification
{
    protected $code;

    /**
     * Where "Continue sign-up" in the mail leads, or null for no button.
     *
     * Only the sign-up page passes one. The guest-add flow and the API share this notification,
     * and neither has a page to come back to. The URL carries the address and step=code, never
     * the code: the visitor still types it, which is what proves they read this mail.
     */
    protected ?string $continueUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct($code, ?string $continueUrl = null)
    {
        $this->code = $code;
        $this->continueUrl = $continueUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        // Get email from notifiable (handles both User objects and AnonymousNotifiable from route)
        $email = null;

        if (method_exists($notifiable, 'getEmailForVerification')) {
            $email = $notifiable->getEmailForVerification();
        } elseif (method_exists($notifiable, 'routeNotificationFor')) {
            $email = $notifiable->routeNotificationFor('mail');
        } elseif (isset($notifiable->email)) {
            $email = $notifiable->email;
        }

        return new class($this->code, $email, $this->continueUrl) extends Mailable
        {
            use SerializesModels;

            protected $code;

            protected $email;

            protected $continueUrl;

            public function __construct($code, $email, $continueUrl)
            {
                $this->code = $code;
                $this->email = $email;
                $this->continueUrl = $continueUrl;

                // Set the recipient
                if ($this->email) {
                    $this->to($this->email);
                }
            }

            public function envelope(): Envelope
            {
                return new Envelope(
                    subject: __('messages.signup_verification_code_subject', ['code' => $this->code]),
                );
            }

            public function content(): Content
            {
                return new Content(
                    view: 'emails.signup_verification_code',
                    text: 'emails.signup_verification_code_text',
                    with: [
                        'code' => $this->code,
                        'continueUrl' => $this->continueUrl,
                    ]
                );
            }

            public function headers(): \Illuminate\Mail\Mailables\Headers
            {
                if ($this->email) {
                    return new \Illuminate\Mail\Mailables\Headers(
                        text: [
                            'List-Unsubscribe' => '<'.route('user.unsubscribe', ['email' => base64_encode($this->email), 'sig' => \App\Utils\UrlUtils::signEmail(base64_encode($this->email))]).'>',
                            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                        ],
                    );
                }

                return new \Illuminate\Mail\Mailables\Headers;
            }
        };
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
