<?php

namespace App\Notifications;

use App\Utils\UrlUtils;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Config;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

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
                    subject: 'Welcome to Event Schedule!',
                );
            }

            public function content(): Content
            {
                return new Content(
                    markdown: 'vendor.notifications.email',
                    with: [
                        'greeting' => 'Hello!',
                        'introLines' => ['Please click the button below to verify your email address.'],
                        'actionText' => 'Verify Email Address',
                        'actionUrl' => $this->verificationUrl,
                        'displayableActionUrl' => $this->verificationUrl,
                        'outroLines' => [],
                        'salutation' => "Regards,\n\nThe Event Schedule team",
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
        return URL::temporarySignedRoute(
            $this->type == 'user' ? 'verification.verify' : 'role.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            $this->type == 'user' 
                ? ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
                : ['subdomain' => $this->subdomain, 'hash' => sha1($notifiable->getEmailForVerification())]
        );        
    }

    /**
     * Get the notification's mail headers.
     */
    public function toMailHeaders(): array
    {
        if ($this->type == 'role' && $this->subdomain) {
            return [
                'List-Unsubscribe' => '<' . route('role.unsubscribe', ['subdomain' => $this->subdomain]) . '>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ];
        }
        
        if ($this->type == 'user' && $this->notifiable) {
            return [
                'List-Unsubscribe' => '<' . route('user.unsubscribe', ['email' => base64_encode($this->notifiable->email)]) . '>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ];
        }
        
        return [];
    }
}
