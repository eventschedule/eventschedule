{{ $role->name }}
{{ trans_choice('messages.announcement_heading', $events->count(), ['count' => $events->count()]) }}

@foreach ($events as $event)
- {{ $event->name }}
  {{ $event->is_multi_day ? $event->getDateRangeDisplay() : $event->getStartDateTime(null, true)?->translatedFormat('F j, Y') }}@if ($event->venue && $event->venue->name) - {{ $event->venue->name }}@endif

  {{ $event->getGuestUrl($role->subdomain, null, true) }}
@endforeach

{{ __('messages.announcement_view_schedule') }}: {{ $role->getGuestUrl(true) }}

--
{{ __('messages.subscription_why_receiving', ['schedule' => $role->name]) }}
{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
