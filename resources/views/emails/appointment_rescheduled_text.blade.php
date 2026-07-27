@php
    // Plain-text twin of emails/appointment_rescheduled.blade.php - see the notes there.
    $apptUse24 = (bool) ($role->use_24_hour_time ?? false);
    $oldTz = $sale->guestTimezone() ?: \App\Utils\AppointmentTimeUtils::scheduleTimezone($event);
    $oldStart = \App\Utils\AppointmentTimeUtils::parseUtcInstant($oldStartsAt)?->setTimezone($oldTz);
@endphp
{{ __('messages.appointment_rescheduled_heading') }}

{{ __('messages.hello') }} {{ $sale->name }},

{{ $intro }}
@if (! empty($note))

{{ __('messages.organizer_note') }}: {{ $note }}
@endif

{{ $type?->name ?? $event->name }}

{{ __('messages.event_changed_whats_changed') }}
@if ($oldStart)
{{ __('messages.event_changed_previously') }}: {{ $oldStart->translatedFormat('l, F j, Y') }} {{ $oldStart->format($apptUse24 ? 'H:i' : 'g:i A') }} ({{ $oldTz }})
@endif
{{ __('messages.event_changed_now') }}:
@include('emails.partials.appointment_datetime_text')
@if ($event->event_url)
{{ __('messages.online') }}: {{ $event->event_url }}
@elseif ($type && $type->location_type === 'in_person' && $type->location_address)
{{ __('messages.location') }}: {{ $type->location_address }}
@elseif ($type && $type->location_type === 'phone')
{{ __('messages.phone') }}: {{ $type->location_phone ?: $sale->phone }}
@endif

{{ __('messages.update_your_calendar_note') }}

{{ __('messages.appointments_manage_booking') }}: {{ $manageUrl }}

{{ __('messages.appointments_manage_link_hint') }}
