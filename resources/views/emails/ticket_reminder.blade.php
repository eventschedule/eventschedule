@php
    $theme = \App\Utils\EmailTheme::guest($role ?? null);

    // The occurrence is the sale's date, as in the confirmation (a moved event re-dates its sales).
    $occurrence = $sale->event_date;
    $isRsvp = $sale->isRsvp();
@endphp
{{-- The inbox preview says what the subject cannot: where. --}}
@php($preheader = trim(($event->venue ? (string) $event->venue->name : '').' '))
<x-email.layout :theme="$theme" :title="$headline" :preheader="$preheader !== '' ? $preheader.' · '.__('messages.ticket_reminder_see_you') : __('messages.ticket_reminder_see_you')">
<x-slot:hero>
<x-email.flyer :event="$event" />
</x-slot:hero>

<x-email.heading :eyebrow="$eyebrow" auto>{{ $event->name }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $sale->name }},</x-email.text>
<x-email.text>{{ __('messages.ticket_reminder_see_you') }}</x-email.text>

<x-email.event :event="$event" :date="$occurrence" :role="$role ?? null" :lang="$lang ?? null" />

@if ($event->event_url)
{{-- An online event's way in, as the appointment reminder gives it: a ticket holder is who it is for. --}}
<x-email.details>
<x-email.item :label="__('messages.online')" wide :ltr="(bool) $event->eventUrlHref()">@if ($joinHref = $event->eventUrlHref())<x-email.link :href="$joinHref">{{ $event->event_url }}</x-email.link>@else<bdi>{{ $event->event_url }}</bdi>@endif</x-email.item>
</x-email.details>
@endif

{{-- The ticket itself, in the body: the newest mail in the inbox is what gets opened at the door,
     and an attachment is two taps further on a phone. Embedded, never hotlinked. --}}
<x-email.qr :src="$message->embedData(\App\Utils\QrCodeUtils::png($ticketUrl), 'ticket-qr-code.png', 'image/png')" :alt="__('messages.qr_code')" :caption="__('messages.ticket_reminder_show_code')" />

<x-email.button :href="$ticketUrl">@if ($isRsvp){{ __('messages.view_registration') }}@else{{ __('messages.view_your_tickets') }}@endif</x-email.button>

@include('emails.partials.wallet-badges')

@php($ticketNotes = $event->parsedTicketNotesHtml($sale->event_date, $role))
@if ($ticketNotes && trim(strip_tags($ticketNotes)) !== '')
<x-email.section :label="__('messages.important_information')" />
<x-email.prose>{!! \App\Utils\UrlUtils::convertUrlsToLinks($ticketNotes) !!}</x-email.prose>
@endif

@if ($canCancel)
<x-email.text variant="small">{{ __('messages.ticket_reminder_cant_make_it') }} <x-email.link :href="$ticketUrl">{{ $isRsvp ? __('messages.cancel_registration') : __('messages.ticket_reminder_cancel_ticket') }}</x-email.link></x-email.text>
@endif

<x-slot:footer>
<x-email.footer>{{ $isRsvp ? __('messages.ticket_reminder_why_rsvp', ['event' => $event->name]) : __('messages.ticket_reminder_why', ['event' => $event->name]) }}@if ($event->user?->email) {{ __('messages.event_support_contact') }}: <x-email.link :href="'mailto:'.$event->user->email" muted>{{ $event->user->email }}</x-email.link>@endif</x-email.footer>
</x-slot:footer>
</x-email.layout>
