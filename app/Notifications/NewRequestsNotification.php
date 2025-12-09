<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRequestsNotification extends Notification
{
    use Queueable;

    protected $role;
    protected $requestCount;
    protected $ccEmails;
    
    /**
     * Create a new notification instance.
     */
    public function __construct($role, $requestCount, $ccEmails)
    {
        $this->role = $role;
        $this->requestCount = $requestCount;
        $this->ccEmails = $ccEmails;
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
    public function toMail(object $notifiable): MailMessage
    {
        $subject = __('messages.new_requests_notification_subject', ['name' => $this->role->name, 'count' => $this->requestCount]);
        $line = __('messages.new_requests_notification_line', ['name' => $this->role->name, 'count' => $this->requestCount]);

        return (new MailMessage)
                    ->subject($subject)
                    ->line($line)
                    ->cc($this->ccEmails)
                    ->action(__('messages.view_details'), route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'requests']))
                    ->line(__('messages.thank_you_for_using'));
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

    /**
     * Get the notification's mail headers.
     */
    public function toMailHeaders(): array
    {
        return [
            'List-Unsubscribe' => '<' . route('role.unsubscribe', ['subdomain' => $this->role->subdomain]) . '>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ];
    }
}

