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

    public function __construct($type = 'user')
    {
        $this->type = $type;
    }
    
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Email Address')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', $verificationUrl);
    }

    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            $this->type == 'user' ? 'verification.verify' : 'role.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            ['id' => UrlUtils::encodeId($notifiable->getKey()), 'hash' => sha1($notifiable->getEmailForVerification())]
        );
    }    
}
