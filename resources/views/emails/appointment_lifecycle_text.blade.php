{{ __('messages.hello') }} {{ $sale->name }},

{{ $intro }}

{{ $type?->name ?? $event->name }}
{{ __('messages.date') }}: {{ $event->getStartDateTime($sale->event_date, true, $event->timezone)->format('l, F j, Y') }}
{{ __('messages.time') }}: {{ $event->getStartEndTime($sale->event_date) }} ({{ $event->timezone }})

@if ($rebookUrl)
{{ __('messages.appointments_book_again') }}: {{ $rebookUrl }}
@else
{{ __('messages.appointments_manage_link_hint') }}
{{ $manageUrl }}
@endif
