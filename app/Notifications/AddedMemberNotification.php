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
    protected $admin;
    
    /**
     * Create a new notification instance.
     */
    public function __construct($role, $user, $admin)
    {
        $this->role = $role;
        $this->user = $user;
        $this->admin = $admin;
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
                    ->replyTo($this->user->email, $this->user->name)
                    ->subject(str_replace(':name', $this->role->name, __('messages.added_to_team')))
                    ->line(str_replace([':name', ':user'], [$this->role->name, $this->admin->name], __('messages.added_to_team_detail')))
                    ->action(
                        $newUser ? __('messages.set_new_password') : __('messages.get_started'), 
                        $newUser ? route('password.request', ['email' => $this->user->email]) : route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']))
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
