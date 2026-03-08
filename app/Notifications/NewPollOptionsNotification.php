<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPollOptionsNotification extends Notification
{
    use Queueable;

    protected $role;
    protected $optionCount;

    public function __construct($role, $optionCount)
    {
        $this->role = $role;
        $this->optionCount = $optionCount;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = __('messages.new_poll_options_notification_subject', ['name' => $this->role->name, 'count' => $this->optionCount]);
        $actionUrl = route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'events']);
        $unsubscribeUrl = route('role.unsubscribe', ['subdomain' => $this->role->subdomain]);

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.new_poll_options', [
                'role' => $this->role,
                'optionCount' => $this->optionCount,
                'actionUrl' => $actionUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
            ])
            ->text('emails.new_poll_options_text', [
                'role' => $this->role,
                'optionCount' => $this->optionCount,
                'actionUrl' => $actionUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
            ])
            ->withSymfonyMessage(function ($message) use ($unsubscribeUrl) {
                $message->getHeaders()->addTextHeader('List-Unsubscribe', '<'.$unsubscribeUrl.'>');
                $message->getHeaders()->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
            });
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
