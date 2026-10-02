@php($theme = \App\Utils\EmailTheme::guest($role ?? null))
<x-email.layout :theme="$theme" :title="__('messages.event_cancelled_heading')" :preheader="__('messages.event_cancelled_body', ['event' => $event->name])">
<x-email.heading :eyebrow="__('messages.event_cancelled_heading')" tone="danger" auto>{{ $event->name }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }}@if (! empty($recipientName)), {{ $recipientName }}@endif,</x-email.text>
<x-email.text>{{ __('messages.event_cancelled_body', ['event' => $event->name]) }}</x-email.text>

@if (! empty($partOfOrder))
<x-email.text>{{ __('messages.event_cancelled_rest_of_order_stands') }}</x-email.text>
@endif

{{-- The date that is off, with nothing to add to a calendar or travel to. --}}
<x-email.event :event="$event" :date="$date ?? null" :role="$role ?? null" :calendar="false" :map="false" />

@if (! empty($note))
<x-email.quote :label="__('messages.organizer_note')" :text="$note" />
@endif

<x-email.text variant="small">{{ __('messages.event_cancelled_refund_guidance') }}</x-email.text>
</x-email.layout>
