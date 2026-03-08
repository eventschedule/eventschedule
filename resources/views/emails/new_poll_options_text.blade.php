{{ __('messages.pending_poll_options') }}

{{ __('messages.hello') }},

{{ $optionCount }} pending poll option {{ Str::plural('suggestion', $optionCount) }} for {{ $role->name }}

{{ __('messages.view_details') }}: {{ $actionUrl }}

{{ __('messages.thank_you_for_using') }}

{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
