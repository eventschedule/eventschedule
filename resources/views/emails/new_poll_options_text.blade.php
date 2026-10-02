{{ __('messages.pending_poll_options') }}

{{ __('messages.hello') }},

{!! trans_choice('messages.new_poll_options_line', $optionCount, ['count' => $optionCount, 'name' => $role->name]) !!}

{{ __('messages.view_details') }}: {{ $actionUrl }}

{{ __('messages.thank_you_for_using') }}

@if (empty($notificationEmailUnsubscribeUrl))
{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
@endif
@include('emails.partials.notification_email_footer_text', ['scheduleName' => $role?->name])
