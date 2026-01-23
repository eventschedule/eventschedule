<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class SignupVerificationCode extends Notification
{
    use Queueable;

    protected $code;

    /**
     * Create a new notification instance.
     */
    public function __construct($code)
    {
        $this->code = $code;
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

        return new class($this->code, $email) extends Mailable
        {
            use SerializesModels;

            protected $code;

            protected $email;

            public function __construct($code, $email)
            {
                $this->code = $code;
                $this->email = $email;

                // Set the recipient
                if ($this->email) {
                    $this->to($this->email);
                }
            }

            public function envelope(): Envelope
            {
                return new Envelope(
                    subject: __('messages.signup_verification_code_subject'),
                );
            }

            public function content(): Content
            {
                return new Content(
                    view: 'emails.signup_verification_code',
                    with: [
                        'code' => $this->code,
                    ]
                );
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
