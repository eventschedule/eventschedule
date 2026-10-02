@php($theme = \App\Utils\EmailTheme::owner($role ?? null))
<x-email.layout :theme="$theme" :title="__('messages.notification_email_confirm_subject', ['schedule' => $role->name])" :preheader="__('messages.notification_email_confirm_intro', ['name' => $requesterName, 'schedule' => $role->name])">
<x-email.heading>{{ __('messages.notification_email_confirm_heading', ['schedule' => $role->name]) }}</x-email.heading>

<x-email.text>{{ __('messages.notification_email_confirm_intro', ['name' => $requesterName, 'schedule' => $role->name]) }}</x-email.text>

<x-email.text>{{ __('messages.notification_email_confirm_body') }}</x-email.text>

<x-email.button :href="$confirmUrl">{{ __('messages.notification_email_confirm_button') }}</x-email.button>

<x-email.text variant="small">{{ __('messages.notification_email_confirm_expires', ['days' => \App\Services\NotificationEmailService::VERIFY_TTL_DAYS]) }}</x-email.text>
<x-email.text variant="small">{{ __('messages.notification_email_confirm_ignore') }}</x-email.text>
</x-email.layout>
