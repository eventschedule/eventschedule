<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeletedRoleNotification extends Notification
{
    use Queueable;

    protected $role;
    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($role, $user)
    {
        $this->role = $role;
        $this->user = $user;
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
        $role = $this->role;
        $user = $this->user;

        return (new MailMessage)
                ->replyTo($user->email, $user->name)
                ->subject(str_replace(
                    ':type', 
                    strtolower(__('messages.' . $role->type)), 
                    __('messages.role_has_been_deleted'))
                )
                ->line(str_replace(
                    [':name', ':type', ':user'],
                    [$role->name, $role->type, $user->name],
                    __('messages.role_has_been_deleted_details'))
                );
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
