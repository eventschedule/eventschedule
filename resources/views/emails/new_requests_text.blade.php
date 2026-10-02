{{ __('messages.pending') }} {{ __('messages.requests') }}

{{ __('messages.hello') }},

{!! trans_choice('messages.new_requests_line', $requestCount, ['count' => $requestCount, 'name' => $role->name]) !!}

{{ __('messages.view_details') }}: {{ $actionUrl }}

{{ __('messages.thank_you_for_using') }}

@if (empty($notificationEmailUnsubscribeUrl))
{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
@endif
@include('emails.partials.notification_email_footer_text', ['scheduleName' => $role?->name])
