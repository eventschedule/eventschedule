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
    // Times and zone names read left to right in every language. In an RTL mail they are isolated
    // (bdi in markup, U+2066/U+2069 inside a caption, which is a plain string) or "1:30 AM - 2:00 AM"
    // reorders into "AM - 2:00 AM 1:30".
    $apptScheduleLine = $apptInSchedule
        ? __('messages.appointments_schedule_in')." \u{2066}".$apptInSchedule['tz'].' ('.$apptInSchedule['time'].")\u{2069}"
        : null;
@endphp
{{-- Rows for <x-email.details>, in the GUEST's zone with the schedule's beneath. Not
     <x-email.event>: its date tile and date line are the schedule's clock, which for a guest across
     a date line is a different day from the time printed beside it. With $apptLabel the date and
     time read as one row under that label (the "Now" side of a reschedule). --}}
@if (! empty($apptLabel))
<x-email.item :label="$apptLabel" wide :caption="$apptScheduleLine">{{ $apptShown['date'] }} &middot; <bdi dir="ltr">{{ $apptShown['time'] }} ({{ $apptShown['tz'] }})</bdi></x-email.item>
@else
<x-email.item :label="__('messages.date')" wide>{{ $apptShown['date'] }}</x-email.item>
<x-email.item :label="__('messages.time')" wide ltr :caption="$apptScheduleLine">{{ $apptShown['time'] }} ({{ $apptShown['tz'] }})</x-email.item>
@endif
