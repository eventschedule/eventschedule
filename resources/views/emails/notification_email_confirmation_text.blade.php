{{ __('messages.notification_email_confirm_heading', ['schedule' => $role->name]) }}

{{ __('messages.notification_email_confirm_intro', ['name' => $requesterName, 'schedule' => $role->name]) }}

{{ __('messages.notification_email_confirm_body') }}

{{ __('messages.notification_email_confirm_button') }}: {{ $confirmUrl }}

{{ __('messages.notification_email_confirm_expires', ['days' => \App\Services\NotificationEmailService::VERIFY_TTL_DAYS]) }}

{{ __('messages.notification_email_confirm_ignore') }}
