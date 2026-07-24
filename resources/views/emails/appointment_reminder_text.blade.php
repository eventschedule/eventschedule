{{ __('messages.hello') }} {{ $sale->name }},

{{ __('messages.appointment_reminder_intro', ['schedule' => $role?->name ?? '']) }}

{{ $type?->name ?? $event->name }}
{{ __('messages.date') }}: {{ $event->getStartDateTime($sale->event_date, true, $event->timezone)->format('l, F j, Y') }}
{{ __('messages.time') }}: {{ $event->getStartEndTime($sale->event_date) }} ({{ $event->timezone }})
@if ($event->event_url)
{{ __('messages.online') }}: {{ $event->event_url }}
@endif

{{ __('messages.appointments_manage_booking') }}:
{{ $manageUrl }}
