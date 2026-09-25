<?php

namespace App\Notifications;

use App\Services\NotificationEmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
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
        $actionUrl = route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']);
        $unsubscribeUrl = route('role.unsubscribe', ['subdomain' => $this->role->subdomain]);

        // The copy for the schedule's shared notification address (routed on demand, so the
        // notifiable is anonymous) unsubscribes by removing that address; nobody there has an
        // account for the generic route to act on.
        $notificationEmailUnsubscribeUrl = null;
        if ($notifiable instanceof AnonymousNotifiable) {
            $notificationEmailUnsubscribeUrl = NotificationEmailService::unsubscribeUrl($this->role);
            $unsubscribeUrl = $notificationEmailUnsubscribeUrl;
        }

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.new_poll_options', [
                'role' => $this->role,
                'optionCount' => $this->optionCount,
                'actionUrl' => $actionUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
                'notificationEmailUnsubscribeUrl' => $notificationEmailUnsubscribeUrl,
            ])
            ->text('emails.new_poll_options_text', [
                'role' => $this->role,
                'optionCount' => $this->optionCount,
                'actionUrl' => $actionUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
                'notificationEmailUnsubscribeUrl' => $notificationEmailUnsubscribeUrl,
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
