<?php

namespace App\Notifications;

use App\Utils\UrlUtils;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Config;

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

        $mailMessage = (new MailMessage)
            ->subject('Welcome to Event Schedule, please verify your email address')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', $verificationUrl);

        // Add headers to the mail message using the modern approach
        $headers = $this->toMailHeaders();
        if (!empty($headers)) {
            $mailMessage->headers($headers);
        }

        return $mailMessage;
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
