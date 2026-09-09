{{ $event->name }}
{{ $heading }}

{{ $body }}

@if ($event->starts_at){{ $event->is_multi_day ? $event->getDateRangeDisplay() : $event->getStartDateTime($interest->event_date ?: null, true)?->translatedFormat('F j, Y') }}@endif@if ($event->venue && $event->venue->name) - {{ $event->venue->name }}@endif

{{ $button }}: {{ $eventUrl }}

--
{{ __('messages.event_interest_why_receiving', ['event' => $event->name]) }}
{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
