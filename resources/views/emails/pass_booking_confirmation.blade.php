@php
    $theme = \App\Utils\EmailTheme::guest($role ?? null);
    $start = $bookedEvent->starts_at ? $bookedEvent->getStartDateTime($date, true) : null;
    $when = $start ? ($bookedEvent->is_multi_day ? $bookedEvent->getDateRangeDisplay($date) : $start->translatedFormat('l, F j')) : null;
@endphp
<x-email.layout :theme="$theme" :title="__('messages.pass_booking_confirmation')" :preheader="$bookedEvent->name.($when ? ' · '.$when : '')">
<x-slot:hero>
<x-email.flyer :event="$bookedEvent" />
</x-slot:hero>

<x-email.heading :eyebrow="__('messages.pass_booking_confirmation')" auto>{{ $bookedEvent->name }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $sale->name }},</x-email.text>
<x-email.text>{{ __('messages.pass_booking_confirmation_intro') }}</x-email.text>

<x-email.event :event="$bookedEvent" :date="$date" :role="$role ?? null" />

<x-email.details>
<x-email.item :label="__('messages.attendee')">{{ $sale->name }}</x-email.item>
</x-email.details>

@if (! empty($cancelDeadlineLabel))
<x-email.callout tone="warning">
@if (! empty($cancelDeadlinePassed))
{{ $lateCancelPolicy === 'block'
    ? __('messages.pass_cancel_email_closed', ['minutes' => \App\Services\PassBookingService::CANCEL_GRACE_MINUTES])
    : __('messages.pass_cancel_email_no_credit', ['minutes' => \App\Services\PassBookingService::CANCEL_GRACE_MINUTES]) }}
@else
{{ $lateCancelPolicy === 'block'
    ? __('messages.pass_cancel_email_deadline_block', ['deadline' => $cancelDeadlineLabel])
    : __('messages.pass_cancel_email_deadline_forfeit', ['deadline' => $cancelDeadlineLabel]) }}
@endif
</x-email.callout>
@endif

<x-email.qr :src="$message->embedData($qrCodeData, 'pass-qr-code.png', 'image/png')" :alt="__('messages.ticket_qr_code')" :caption="__('messages.scan_qr_code_to_view_ticket')" />

<x-email.button :href="$manageUrl">{{ __('messages.manage_my_pass') }}</x-email.button>

@php($ticketNotes = $bookedEvent->parsedTicketNotesHtml($date, $role))
@if ($ticketNotes && trim(strip_tags($ticketNotes)) !== '')
<x-email.section :label="__('messages.important_information')" />
<x-email.prose>{!! \App\Utils\UrlUtils::convertUrlsToLinks($ticketNotes) !!}</x-email.prose>
@endif

@if ($bookedEvent->user?->email)
<x-slot:footer>
<x-email.footer>{{ __('messages.event_support_contact') }}: <x-email.link :href="'mailto:'.$bookedEvent->user->email" muted>{{ $bookedEvent->user->email }}</x-email.link></x-email.footer>
</x-slot:footer>
@endif
</x-email.layout>
