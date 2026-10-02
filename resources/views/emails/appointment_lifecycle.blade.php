@php($theme = \App\Utils\EmailTheme::guest($role ?? null))
{{-- Pending, payment due, declined and cancelled share this template; the mailable passes the
     state as $tone so the eyebrow says it at a glance. No add-to-calendar here: none of these is a
     confirmed booking. --}}
<x-email.layout :theme="$theme" :title="$heading" :preheader="$intro">
<x-email.heading :eyebrow="$heading" :tone="$tone ?? null" auto>{{ $type?->name ?? $event->name }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $sale->name }},</x-email.text>
<x-email.text>{{ $intro }}</x-email.text>

<x-email.details panel>
@include('emails.partials.appointment_datetime')
</x-email.details>

@if ($rebookUrl)
<x-email.button :href="$rebookUrl">{{ __('messages.appointments_book_again') }}</x-email.button>
@else
<x-email.button :href="$manageUrl">{{ __('messages.appointments_manage_booking') }}</x-email.button>
<x-email.url :href="$manageUrl" :intro="__('messages.appointments_manage_link_hint')" />
@endif
</x-email.layout>
