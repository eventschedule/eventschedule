@php
    $theme = \App\Utils\EmailTheme::owner($role ?? null);
    $rating = (int) $feedback->rating;
    $stars = str_repeat('★', max(0, min(5, $rating))).str_repeat('☆', max(0, 5 - $rating));
@endphp
<x-email.layout :theme="$theme" :title="__('messages.feedback_notification_subject', ['event' => $event->name])" :preheader="$stars.' · '.$event->name">
<x-email.heading :eyebrow="__('messages.new_feedback_received')" :subtitle="$feedback->event_date ? $event->getStartDateTime($feedback->event_date, true)->translatedFormat('F j, Y') : null" auto>{{ $event->name }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $recipient?->name }},</x-email.text>

<x-email.highlight :value="$stars" :caption="__('messages.rating').': '.$feedback->rating.'/5'" />

<x-email.details>
<x-email.item :label="__('messages.attendee')" :caption="$sale->email" wide>{{ $sale->name }}</x-email.item>
</x-email.details>

@if ($feedback->comment)
<x-email.quote :label="__('messages.comment')" :text="$feedback->comment" />
@endif

<x-email.button :href="$salesUrl">{{ __('messages.view_feedback') }}</x-email.button>

<x-slot:footer>
@include('emails.partials.notification_email_footer', ['scheduleName' => $role?->name])
</x-slot:footer>
</x-email.layout>
