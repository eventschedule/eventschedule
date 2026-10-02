@php
    $theme = \App\Utils\EmailTheme::guest($role ?? null);
    $apptName = $type?->name ?? $event->name;
    $apptWhen = \App\Utils\AppointmentTimeUtils::render($event, $sale->guestTimezone(), (bool) ($role->use_24_hour_time ?? false));
@endphp
<x-email.layout :theme="$theme" :title="__('messages.appointment_confirmed_heading')" :preheader="$apptName.' · '.$apptWhen['date']">
<x-email.heading :eyebrow="__('messages.appointment_confirmed_heading')" auto>{{ $apptName }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $sale->name }},</x-email.text>
<x-email.text>{{ __('messages.appointment_confirmed_intro', ['schedule' => $role?->name ?? '']) }}</x-email.text>

<x-email.details panel>
@include('emails.partials.appointment_datetime')
{{-- Only a web link is ever an href; free text (a meeting ID, "call me") is printed as text. --}}
@if ($event->event_url)
<x-email.item :label="__('messages.online')" wide :ltr="(bool) $event->eventUrlHref()">@if ($joinHref = $event->eventUrlHref())<x-email.link :href="$joinHref">{{ $event->event_url }}</x-email.link>@else<bdi>{{ $event->event_url }}</bdi>@endif</x-email.item>
@elseif ($type && $type->location_type === 'in_person' && $type->location_address)
<x-email.item :label="__('messages.location')" wide><bdi>{{ $type->location_address }}</bdi></x-email.item>
@elseif ($type && $type->location_type === 'phone')
<x-email.item :label="__('messages.phone')" wide ltr>{{ $type->location_phone ?: $sale->phone }}</x-email.item>
@endif
</x-email.details>

@include('emails.partials.appointment_calendar')

<x-email.button :href="$manageUrl">{{ __('messages.appointments_manage_booking') }}</x-email.button>
<x-email.url :href="$manageUrl" :intro="__('messages.appointments_manage_link_hint')" />
</x-email.layout>
