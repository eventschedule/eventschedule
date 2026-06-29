{{ __('messages.event_changed_heading') }}

{{ __('messages.hello') }}@if (! empty($recipientName)), {{ $recipientName }}@endif,

{{ __('messages.event_changed_body', ['event' => $event->name]) }}
@if (! empty($note))

{{ __('messages.organizer_note') }}:
{{ $note }}
@endif

{{ __('messages.event_changed_whats_changed') }}
@if (isset($display['date']))

{{ __('messages.event_changed_date_label') }}
@if (! empty($display['date']['old'])){{ __('messages.event_changed_previously') }}: {{ $display['date']['old'] }} {{ $display['date']['old_tz'] }}
@endif
{{ __('messages.event_changed_now') }}: {{ $display['date']['new'] }} {{ $display['date']['new_tz'] }}
@if (! empty($display['date']['delta'])){{ $display['date']['delta'] }}
@endif
@endif
@if (isset($display['location']))

{{ __('messages.event_changed_location_label') }}
@php($loc = $display['location'])
@if ($loc['variant'] === 'moved_online'){{ __('messages.event_changed_moved_online') }}
@elseif ($loc['variant'] === 'moved_in_person'){{ __('messages.event_changed_moved_in_person', ['venue' => $loc['new_venue']]) }}
@elseif ($loc['variant'] === 'online_updated'){{ __('messages.event_changed_online_updated') }}
@else{{ __('messages.event_changed_venue') }}
@if (! empty($loc['old_venue'])){{ __('messages.event_changed_previously') }}: {{ $loc['old_venue'] }}@endif

@if (! empty($loc['new_venue'])){{ __('messages.event_changed_now') }}: {{ $loc['new_venue'] }}@endif
@endif
@endif

{{ __('messages.event_changed_cta') }}: {{ $eventUrl }}
@if (! empty($icalUrl))

{{ __('messages.update_your_calendar') }}: {{ $icalUrl }}
{{ __('messages.update_your_calendar_note') }}
@endif

{{ __('messages.thank_you_for_using') }}
