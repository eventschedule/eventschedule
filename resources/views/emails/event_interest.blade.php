@php
    $theme = \App\Utils\EmailTheme::guest($role ?? null);
    $cancelled = ($kind ?? null) === \App\Mail\EventInterestNotification::KIND_CANCELLED;
@endphp
<x-email.layout :theme="$theme" :title="$heading" :preheader="$body">
<x-email.heading :eyebrow="$heading" :tone="$cancelled ? 'danger' : null" auto>{{ $event->name }}</x-email.heading>

<x-email.text>{{ $body }}</x-email.text>

{{-- getStartDateTime() with no timezone override, so the date renders in the SCHEDULE's timezone.
     An occurrence falls on a given day because of where it happens, not because of where the
     reader is sitting.

     starts_at guarded, not just ?->. getStartDateTime() has no null guard: it reaches
     Carbon::createFromFormat('Y-m-d H:i:s', null) and THROWS, so the ?-> never runs and the whole
     message dies. Dateless events are a supported capture target (a "Subscriptions" container),
     and EventChangeNotifier reaches them with no date check of its own. The event component keeps that
     guard: it reads the date only when starts_at is set. A cancelled event offers nothing to add
     to a calendar or travel to. --}}
<x-email.event :event="$event" :date="$interest->event_date ?: null" :role="$role ?? null" :calendar="! $cancelled" :map="! $cancelled" />

<x-email.button :href="$eventUrl">{{ $button }}</x-email.button>

<x-slot:footer>
{{-- The line that turns "who is this?" into an unsubscribe rather than a spam complaint. Worded
     for THIS list, not the schedule-wide one: somebody who asked about one event has not
     subscribed to the schedule, and telling them they had would be untrue. --}}
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]">{{ __('messages.event_interest_why_receiving', ['event' => $event->name]) }}</x-email.footer>
</x-slot:footer>
</x-email.layout>
