{{-- Shown only on the copy sent to a schedule's shared notification address (SendsToNotificationEmail). --}}
@if (! empty($notificationEmailUnsubscribeUrl))
<p style="font-size: 12px; color: #999; margin-top: 20px;">
    {{ __('messages.notification_email_footer', ['schedule' => $scheduleName ?? '']) }}
    <br>
    <a href="{{ $notificationEmailUnsubscribeUrl }}" style="color: #4E81FA;">{{ __('messages.notification_email_unsubscribe_link') }}</a>
</p>
@endif
