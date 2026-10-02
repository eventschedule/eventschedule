{{-- Sent from the platform's own address to the person who asked this schedule to list their event,
     so the platform's voice with the deciding schedule's identity, signed by the platform as before. --}}
@php($theme = \App\Utils\EmailTheme::owner($role ?? null))
{{-- dir comes from the layout, which repeats it on every layout table: Gmail's web client strips it
     from <html> and <body>. --}}
<x-email.layout :theme="$theme" :title="$subject" :preheader="str_replace(':venue', $role->name, __('messages.request_declined_body'))">
<x-email.heading :eyebrow="$subject" tone="neutral" auto>{{ $event->name }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }}@if (trim((string) $recipient->name) !== '') {{ $recipient->firstName() }}@endif,</x-email.text>

<x-email.text>{{ str_replace(':venue', $role->name, __('messages.request_declined_body')) }}</x-email.text>

<x-email.details panel>
<x-email.item :label="__('messages.date')" wide>{{ $eventDate }}</x-email.item>
@if ($event->getVenueDisplayName())
<x-email.item :label="__('messages.venue')" wide>{{ $event->getVenueDisplayName() }}</x-email.item>
@endif
</x-email.details>

<x-slot:footer>
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]">{{ __('messages.request_decision_why', ['schedule' => $role->name]) }}</x-email.footer>
</x-slot:footer>
</x-email.layout>
