<?php

namespace App\Notifications;

use Carbon\Carbon;
use Config;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Facades\URL;

// This notification must NOT implement ShouldQueue - user is waiting for the verification email
class VerifyEmail extends BaseVerifyEmail
{
    protected $type;

    protected $subdomain;

    protected $notifiable;

    public function __construct($type = 'user', $subdomain = '')
    {
        $this->type = $type;
        $this->subdomain = $subdomain;
    }

    public function toMail($notifiable)
    {
        $this->notifiable = $notifiable;
        $verificationUrl = $this->verificationUrl($notifiable);

        return new class($verificationUrl, $this->toMailHeaders(), $notifiable) extends Mailable
        {
            protected $verificationUrl;

            protected $headers;

            protected $notifiable;

            public function __construct($verificationUrl, $headers, $notifiable)
            {
                $this->verificationUrl = $verificationUrl;
                $this->headers = $headers;
                $this->notifiable = $notifiable;

                // Set the recipient
                $this->to($notifiable->getEmailForVerification());
            }

            public function envelope(): Envelope
            {
                return new Envelope(
                    subject: __('messages.verify_email_subject', ['app' => config('app.name')]),
                );
            }

            public function content(): Content
            {
                return new Content(
                    markdown: 'vendor.notifications.email',
                    with: [
                        'greeting' => __('messages.hello').'!',
                        'introLines' => [__('messages.verify_email_intro')],
                        'actionText' => __('messages.verify_email_button'),
                        'actionUrl' => $this->verificationUrl,
                        'displayableActionUrl' => $this->verificationUrl,
                        'outroLines' => [],
                        'salutation' => __('messages.thanks').",\n\n".config('app.name'),
                        'level' => 'primary',
                    ],
                );
            }

            public function headers(): Headers
            {
                return new Headers(
                    text: $this->headers,
                );
            }
        };
    }

    protected function verificationUrl($notifiable)
    {
        // Signed by path: the request that checks the link may see another scheme or host than
        // the one that mailed it (a proxy that is not trusted, the app subdomain), and a signature
        // over the whole address would then fail for the person it was sent to.
        $path = URL::temporarySignedRoute(
            $this->type == 'user' ? 'verification.verify' : 'role.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            $this->type == 'user'
                ? ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
                : ['subdomain' => $this->subdomain, 'hash' => sha1($notifiable->getEmailForVerification())],
            absolute: false
        );

        return URL::to($path);
    }

    /**
     * Get the notification's mail headers.
     */
    public function toMailHeaders(): array
    {
        if ($this->type == 'role' && $this->subdomain && $this->notifiable?->email) {
            return [
                'List-Unsubscribe' => '<'.\App\Utils\UrlUtils::roleUnsubscribeOneClickUrl($this->notifiable->email).'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ];
        }

        if ($this->type == 'user' && $this->notifiable) {
            return [
                'List-Unsubscribe' => '<'.route('user.unsubscribe', ['email' => base64_encode($this->notifiable->email), 'sig' => \App\Utils\UrlUtils::signEmail(base64_encode($this->notifiable->email))]).'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ];
        }

        return [];
    }
}
