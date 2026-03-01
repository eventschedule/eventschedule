{{ __('messages.feedback_how_was') }}

{{ __('messages.hello') }} {{ $sale->name }},

{{ __('messages.feedback_rate_event') }}

{{ $event->name }}

{{ __('messages.date') }}: {{ $event->getStartDateTime($sale->event_date, true)->format('F j, Y') }}
{{ __('messages.time') }}: {{ $event->getStartEndTime($sale->event_date) }}

{{ __('messages.feedback_submit') }}: {{ $feedbackUrl }}

{{ __('messages.event_support_contact') }}: {{ $event->user->email }}
