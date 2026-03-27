{{ ($isRsvp ?? false) ? __('messages.waitlist_spots_available') : __('messages.waitlist_tickets_available') }}

{{ __('messages.hello') }}, {{ $entry->name }}!

{{ ($isRsvp ?? false) ? __('messages.waitlist_rsvp_notification_body') : __('messages.waitlist_notification_body') }}

{{ $event->name }}
@if ($entry->event_date){{ $event->is_multi_day ? $event->getDateRangeDisplay($entry->event_date) : $event->getStartDateTime($entry->event_date, true)->format('F j, Y') }} {{ $event->getStartEndTime($entry->event_date) }}

@endif
{{ ($isRsvp ?? false) ? __('messages.waitlist_rsvp_notification_cta') : __('messages.waitlist_notification_cta') }}: {{ $eventUrl }}

{{ __('messages.thank_you_for_using') }}
@if ($unsubscribeUrl)

{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
@endif
