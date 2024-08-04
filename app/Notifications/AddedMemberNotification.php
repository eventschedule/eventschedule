<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AddedMemberNotification extends Notification
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
        $newUser = $this->user->wasRecentlyCreated;

        return (new MailMessage)
                    ->subject(str_replace(':name', $this->role->name, __('messages.added_to_team')))
                    ->line(str_replace(':name', $this->role->name, __('messages.added_to_team_detail/')))
                    ->action(
                        $newUser ? __('messages.set_new_password') : __('messages.get_started'), 
                        $newUser ? route('password.request') : route('role.view_admin', ['subdomain' => $this->role->subdomain]))
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
}
