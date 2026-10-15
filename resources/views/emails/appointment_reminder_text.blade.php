{{ __('messages.hello') }} {{ $sale->name }},

{{ __('messages.appointment_reminder_intro', ['schedule' => $role?->name ?? '']) }}

{{ $type?->name ?? $event->name }}
@if ($apptHost = $event->appointmentHostName())
{{ __('messages.appointments_with_host', ['name' => $apptHost]) }}
@endif
@include('emails.partials.appointment_datetime_text')
@if ($event->event_url)
{{ __('messages.online') }}: {{ $event->event_url }}
@endif

{{ __('messages.appointments_manage_booking') }}:
{{ $manageUrl }}
