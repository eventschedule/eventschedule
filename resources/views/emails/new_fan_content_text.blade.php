{{ __('messages.pending_fan_content') }}

{{ __('messages.hello') }},

{!! trans_choice('messages.new_fan_content_line', $fanContentCount, ['count' => $fanContentCount, 'event' => $event->name]) !!}

{{ __('messages.view_details') }}: {{ $actionUrl }}

{{ __('messages.thank_you_for_using') }}

{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
