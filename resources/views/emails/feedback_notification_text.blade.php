{{ __('messages.new_feedback_received') }}

{{ __('messages.hello') }} {{ $recipient?->name }},

{{ $event->name }}
@if ($feedback->event_date)
{{ $event->getStartDateTime($feedback->event_date, true)->translatedFormat('F j, Y') }}
@endif

{{ __('messages.attendee') }}: {{ $sale->name }} ({{ $sale->email }})

{{ __('messages.rating') }}: {{ $feedback->rating }}/5

@if ($feedback->comment)
{{ __('messages.comment') }}:
{{ $feedback->comment }}
@endif

{{ __('messages.view_feedback') }}: {{ $salesUrl }}

{{ __('messages.thank_you_for_using') }}
