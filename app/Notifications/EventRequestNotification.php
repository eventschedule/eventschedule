<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventRequestNotification extends Notification
{
    use Queueable;

    protected $venue;
    protected $role;
    
    /**
     * Create a new notification instance.
     */
    public function __construct($venue, $role)
    {
        $this->role = $role;
        $this->venue = $venue;
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
        return (new MailMessage)
                    ->replyTo($this->role->user->email, $this->role->user->name)
                    ->subject(str_replace(':name', $this->role->name, __('messages.new_request')))
                    ->line(str_replace(':name', $this->role->name, __('messages.new_request')))
                    ->action(__('messages.view_details'), route('role.view_admin', ['subdomain' => $this->venue->subdomain, 'tab' => 'requests']))
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
            'List-Unsubscribe' => '<' . route('role.unsubscribe', ['subdomain' => $this->venue->subdomain]) . '>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ];
    }
}
