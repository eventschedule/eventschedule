@php
    $theme = \App\Utils\EmailTheme::guest($role ?? null);
    $title = ($isRsvp ?? false) ? __('messages.waitlist_spots_available') : __('messages.waitlist_tickets_available');
    $start = $event->starts_at ? $event->getStartDateTime($entry->event_date, true) : null;
    $when = $start ? ($event->is_multi_day ? $event->getDateRangeDisplay($entry->event_date) : $start->translatedFormat('l, F j')) : null;
@endphp
<x-email.layout :theme="$theme" :title="$title" :preheader="$event->name.($when ? ' · '.$when : '')">
<x-email.heading :eyebrow="$title" auto>{{ $event->name }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }}, {{ $entry->name }}!</x-email.text>
<x-email.text>{{ ($isRsvp ?? false) ? __('messages.waitlist_rsvp_notification_body') : __('messages.waitlist_notification_body') }}</x-email.text>

{{-- Nothing is booked yet, so nothing to add to a calendar. --}}
<x-email.event :event="$event" :date="$entry->event_date" :role="$role ?? null" :calendar="false" />

<x-email.button :href="$eventUrl">{{ ($isRsvp ?? false) ? __('messages.waitlist_rsvp_notification_cta') : __('messages.waitlist_notification_cta') }}</x-email.button>

@if ($unsubscribeUrl)
<x-slot:footer>
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]" />
</x-slot:footer>
@endif
</x-email.layout>
