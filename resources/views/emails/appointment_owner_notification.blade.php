@php
    $theme = \App\Utils\EmailTheme::owner($role ?? null);
    $heading = match ($kind) {
        'pending' => __('messages.appointment_owner_pending_heading'),
        'cancelled' => __('messages.appointment_owner_cancelled_heading'),
        // Both move kinds lead with "a booking has moved": a re-pending move is a change to
        // something already approved first, and a decision second.
        'rescheduled', 'rescheduled_pending' => __('messages.appointment_owner_rescheduled_heading'),
        default => __('messages.appointment_owner_booked_heading'),
    };
    $tone = match ($kind) {
        'cancelled' => 'danger',
        'pending', 'rescheduled_pending' => 'warning',
        default => null,
    };

    // Owner-facing: the SCHEDULE's zone stays primary because that is the owner's own
    // clock. The guest's zone is appended so the owner knows what the guest was shown.
    $ownerUse24 = (bool) ($role->use_24_hour_time ?? false);
    $ownerScheduleTz = \App\Utils\AppointmentTimeUtils::scheduleTimezone($event);
    $ownerShown = \App\Utils\AppointmentTimeUtils::render($event, $ownerScheduleTz, $ownerUse24);
    $ownerGuestTz = $sale->guestTimezone();
    $ownerGuestShown = ($ownerGuestTz && $ownerGuestTz !== $ownerScheduleTz)
        ? \App\Utils\AppointmentTimeUtils::render($event, $ownerGuestTz, $ownerUse24)
        : null;
    // The zone and time read left to right in every language; isolated (U+2066/U+2069) so an RTL
    // mail does not reorder them.
    $ownerGuestLine = $ownerGuestShown
        ? __('messages.appointments_times_shown_in')." \u{2066}".$ownerGuestShown['tz'].': '.$ownerGuestShown['time']."\u{2069}"
        : null;
    $apptName = $type?->name ?? $event->name;
@endphp
<x-email.layout :theme="$theme" :title="$heading" :preheader="$apptName.' · '.$sale->name.' · '.$ownerShown['date']">
<x-email.heading :eyebrow="$heading" :tone="$tone" auto>{{ $apptName }}</x-email.heading>

@if (! empty($shortNotice))
<x-email.callout tone="warning">{{ $shortNotice }}</x-email.callout>
@endif

@if ($showRefund)
<x-email.callout tone="warning">{{ __('messages.appointment_owner_refund_note', ['amount' => strtoupper($event->ticket_currency_code).' '.number_format((float) $sale->payment_amount, 2), 'reference' => $sale->transaction_reference ?: '-']) }}</x-email.callout>
@endif

<x-email.details panel>
<x-email.item :label="__('messages.date')" wide :caption="$ownerGuestLine">{{ $ownerShown['date'] }} &middot; <bdi dir="ltr">{{ $ownerShown['time'] }} ({{ $ownerShown['tz'] }})</bdi></x-email.item>
<x-email.item :label="__('messages.name')">{{ $sale->name }}</x-email.item>
@if ($sale->phone)
<x-email.item :label="__('messages.phone')" ltr>{{ $sale->phone }}</x-email.item>
@endif
<x-email.item :label="__('messages.email')" wide ltr>{{ $sale->email }}</x-email.item>
@if ((float) $sale->payment_amount > 0)
<x-email.item :label="__('messages.price')" wide>{{ strtoupper($event->ticket_currency_code) }} {{ number_format((float) $sale->payment_amount, 2) }} &middot; {{ ($paidLabel ?? ($sale->status === 'paid')) ? __('messages.paid') : __('messages.unpaid') }}</x-email.item>
@endif
</x-email.details>

@if ($event->description)
<x-email.quote :text="$event->description" />
@endif

<x-email.button :href="$bookingsUrl">{{ in_array($kind, ['pending', 'rescheduled_pending'], true) ? __('messages.appointment_owner_review') : __('messages.view') }}</x-email.button>

<x-slot:footer>
@include('emails.partials.notification_email_footer', ['scheduleName' => $role?->name])
</x-slot:footer>
</x-email.layout>
