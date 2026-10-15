@php($theme = \App\Utils\EmailTheme::owner($role ?? null))
@php($intro = __('messages.appointment_host_released_intro', ['host' => $hostName, 'name' => $role->name]))
<x-email.layout :theme="$theme" :title="__('messages.appointment_host_released_subject', ['host' => $hostName, 'name' => $role->name])" :preheader="$intro">
<x-email.heading tone="warning" :eyebrow="__('messages.appointment_host_released_heading')">{{ __('messages.appointment_host_released_subject', ['host' => $hostName, 'name' => $role->name]) }}</x-email.heading>

<x-email.text>{{ $intro }}</x-email.text>

<x-email.list :items="$typeNames" />

@if ($futureBookings > 0)
<x-email.callout tone="warning">{{ trans_choice('messages.appointment_host_released_bookings', $futureBookings, ['count' => $futureBookings, 'host' => $hostName]) }} <x-email.link :href="$bookingsUrl">{{ __('messages.appointments_host_see_bookings') }}</x-email.link></x-email.callout>
@endif

<x-email.button :href="$typesUrl">{{ __('messages.appointments_choose_host') }}</x-email.button>
</x-email.layout>
