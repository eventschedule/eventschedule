{{ __('messages.pending') }} {{ __('messages.requests') }}

{{ __('messages.hello') }},

{{ $requestCount }} pending {{ Str::plural('request', $requestCount) }} for {{ $role->name }}

{{ __('messages.view_details') }}: {{ $actionUrl }}

{{ __('messages.thank_you_for_using') }}

{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
