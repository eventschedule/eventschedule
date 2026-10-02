@php
    $theme = \App\Utils\EmailTheme::guest($role ?? null);
    // The previous time, rendered in whichever zone the guest is being shown the new one in, so the
    // two lines are actually comparable. Derived from the scalar old start, never from the event -
    // the event already holds the NEW time by the time this renders.
    $apptUse24 = (bool) ($role->use_24_hour_time ?? false);
    $oldTz = $sale->guestTimezone() ?: \App\Utils\AppointmentTimeUtils::scheduleTimezone($event);
    // parseUtcInstant, not a bare createFromFormat: a legacy/restored date-only value would throw
    // here, killing the job so the guest is never told their appointment moved.
    $oldStart = \App\Utils\AppointmentTimeUtils::parseUtcInstant($oldStartsAt)?->setTimezone($oldTz);
@endphp
<x-email.layout :theme="$theme" :title="__('messages.appointment_rescheduled_heading')" :preheader="$intro">
<x-email.heading :eyebrow="__('messages.appointment_rescheduled_heading')" :tone="! empty($pending) ? 'warning' : null" auto>{{ $type?->name ?? $event->name }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $sale->name }},</x-email.text>
<x-email.text>{{ $intro }}</x-email.text>

@if (! empty($note))
<x-email.quote :label="__('messages.organizer_note')" :text="$note" />
@endif

<x-email.section :label="__('messages.event_changed_whats_changed')" />
<x-email.details panel>
@if ($oldStart)
<x-email.item :label="__('messages.event_changed_previously')" wide><s class="es-ink-3" style="font-weight: 400; color: #64748b;">{{ $oldStart->translatedFormat('l, F j, Y') }} <bdi dir="ltr">{{ $oldStart->format($apptUse24 ? 'H:i' : 'g:i A') }} ({{ $oldTz }})</bdi></s></x-email.item>
@endif
@include('emails.partials.appointment_datetime', ['apptLabel' => __('messages.event_changed_now')])
{{-- Only a web link is ever an href; free text (a meeting ID, "call me") is printed as text. --}}
@if ($event->event_url)
<x-email.item :label="__('messages.online')" wide :ltr="(bool) $event->eventUrlHref()">@if ($joinHref = $event->eventUrlHref())<x-email.link :href="$joinHref">{{ $event->event_url }}</x-email.link>@else<bdi>{{ $event->event_url }}</bdi>@endif</x-email.item>
@elseif ($type && $type->location_type === 'in_person' && $type->location_address)
<x-email.item :label="__('messages.location')" wide><bdi>{{ $type->location_address }}</bdi></x-email.item>
@elseif ($type && $type->location_type === 'phone')
<x-email.item :label="__('messages.phone')" wide ltr>{{ $type->location_phone ?: $sale->phone }}</x-email.item>
@endif
</x-email.details>

{{-- iTIP handling still varies by client, so say the honest thing rather than assume the
     attached invite always replaces the old entry cleanly. --}}
<x-email.text variant="small">{{ __('messages.update_your_calendar_note') }}</x-email.text>

<x-email.button :href="$manageUrl">{{ __('messages.appointments_manage_booking') }}</x-email.button>
<x-email.url :href="$manageUrl" :intro="__('messages.appointments_manage_link_hint')" />
</x-email.layout>
