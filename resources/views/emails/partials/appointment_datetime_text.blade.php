@php
    // Plain-text twin of emails/partials/appointment_datetime.blade.php - see the notes there.
    $apptUse24 = (bool) ($role->use_24_hour_time ?? false);
    $apptGuestTz = $sale->guestTimezone();
    $apptScheduleTz = \App\Utils\AppointmentTimeUtils::scheduleTimezone($event);
    $apptShown = \App\Utils\AppointmentTimeUtils::render($event, $apptGuestTz, $apptUse24);
    $apptInSchedule = ($apptGuestTz && $apptGuestTz !== $apptScheduleTz)
        ? \App\Utils\AppointmentTimeUtils::render($event, $apptScheduleTz, $apptUse24)
        : null;
@endphp
{{ __('messages.date') }}: {{ $apptShown['date'] }}
{{ __('messages.time') }}: {{ $apptShown['time'] }} ({{ $apptShown['tz'] }})
@if ($apptInSchedule)
{{ __('messages.appointments_schedule_in') }} {{ $apptInSchedule['tz'] }} ({{ $apptInSchedule['time'] }})
@endif
