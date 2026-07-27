{{ __('messages.hello') }} {{ $sale->name }},

{{ __('messages.appointment_confirmed_intro', ['schedule' => $role?->name ?? '']) }}

{{ $type?->name ?? $event->name }}
@include('emails.partials.appointment_datetime_text')
@if ($event->event_url)
{{ __('messages.online') }}: {{ $event->event_url }}
@elseif ($type && $type->location_type === 'in_person' && $type->location_address)
{{ __('messages.location') }}: {{ $type->location_address }}
@endif

{{ __('messages.appointments_manage_booking') }}:
{{ $manageUrl }}
