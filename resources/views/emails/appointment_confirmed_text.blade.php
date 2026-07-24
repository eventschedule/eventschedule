{{ __('messages.hello') }} {{ $sale->name }},

{{ __('messages.appointment_confirmed_intro', ['schedule' => $role?->name ?? '']) }}

{{ $type?->name ?? $event->name }}
{{ __('messages.date') }}: {{ $event->getStartDateTime($sale->event_date, true, $event->timezone)->format('l, F j, Y') }}
{{ __('messages.time') }}: {{ $event->getStartEndTime($sale->event_date) }} ({{ $event->timezone }})
@if ($event->event_url)
{{ __('messages.online') }}: {{ $event->event_url }}
@elseif ($type && $type->location_type === 'in_person' && $type->location_address)
{{ __('messages.location') }}: {{ $type->location_address }}
@endif

{{ __('messages.appointments_manage_booking') }}:
{{ $manageUrl }}
