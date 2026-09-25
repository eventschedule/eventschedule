@if (! empty($notificationEmailUnsubscribeUrl))

{{ __('messages.notification_email_footer', ['schedule' => $scheduleName ?? '']) }}
{{ __('messages.notification_email_unsubscribe_link') }}: {{ $notificationEmailUnsubscribeUrl }}
@endif
