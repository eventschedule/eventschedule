{{-- Shown only on the copy sent to a schedule's shared notification address (SendsToNotificationEmail).
     Goes in the layout's footer slot: it reads $notificationEmailUnsubscribeUrl from the mail's own
     view data, which a component's template cannot see. --}}
@if (! empty($notificationEmailUnsubscribeUrl))
<p class="es-foot" style="margin: 12px 0 0; font-size: 13px; line-height: 20px; color: #475569;">
    {{ __('messages.notification_email_footer', ['schedule' => $scheduleName ?? '']) }}
    <br>
    <a href="{{ $notificationEmailUnsubscribeUrl }}" class="es-foot" style="color: #475569; text-decoration: underline;">{{ __('messages.notification_email_unsubscribe_link') }}</a>
</p>
@endif
