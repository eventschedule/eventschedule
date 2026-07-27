@php
    // Shared by every GUEST-facing appointment mail so the four templates cannot drift apart.
    // The picker showed the guest their own zone and we stored it, so the mail has to agree.
    //
    // The role's own preference, not get_use_24_hour_time(): that helper prefers the logged-in
    // user, and these mails are frequently rendered inside an owner-triggered request - which would
    // stamp the owner's clock preference onto the guest's email.
    $apptUse24 = (bool) ($role->use_24_hour_time ?? false);
    $apptGuestTz = $sale->guestTimezone();
    $apptScheduleTz = \App\Utils\AppointmentTimeUtils::scheduleTimezone($event);
    $apptShown = \App\Utils\AppointmentTimeUtils::render($event, $apptGuestTz, $apptUse24);
    $apptInSchedule = ($apptGuestTz && $apptGuestTz !== $apptScheduleTz)
        ? \App\Utils\AppointmentTimeUtils::render($event, $apptScheduleTz, $apptUse24)
        : null;
@endphp
<p style="margin: 10px 0;"><strong>{{ __('messages.date') }}:</strong> {{ $apptShown['date'] }}</p>
<p style="margin: 10px 0;"><strong>{{ __('messages.time') }}:</strong> {{ $apptShown['time'] }} ({{ $apptShown['tz'] }})</p>
@if ($apptInSchedule)
<p style="margin: 4px 0; font-size: 13px; color: #888;">{{ __('messages.appointments_schedule_in') }} {{ $apptInSchedule['tz'] }} ({{ $apptInSchedule['time'] }})</p>
@endif
