<!DOCTYPE html>
<html @if ($isRtl ?? false) dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heading }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">{{ $event->name }}</h1>
        <p style="margin: 6px 0 0; font-size: 15px; opacity: 0.9;">{{ $heading }}</p>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="margin: 0 0 16px; font-size: 15px;">{{ $body }}</p>

        {{-- getStartDateTime() with no timezone override, so the date renders in the SCHEDULE's
             timezone. An occurrence falls on a given day because of where it happens, not because
             of where the reader is sitting. --}}
        <div style="font-size: 14px; color: #666;">
            {{-- starts_at guarded, not just ?->. getStartDateTime() has no null guard: it reaches
                 Carbon::createFromFormat('Y-m-d H:i:s', null) and THROWS, so the ?-> never runs and
                 the whole message dies. Dateless events are a supported capture target (a
                 "Subscriptions" container), and EventChangeNotifier reaches them with no date
                 check of its own. --}}
            @if ($event->starts_at)
                {{ $event->is_multi_day
                    ? $event->getDateRangeDisplay()
                    : $event->getStartDateTime($interest->event_date ?: null, true)?->translatedFormat('F j, Y') }}
            @endif
            @if ($event->venue && $event->venue->name)
                &middot; {{ $event->venue->name }}
            @endif
        </div>

        <div style="text-align: center; margin: 28px 0 8px;">
            <a href="{{ $eventUrl }}"
               style="background-color: #4E81FA; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: 600; display: inline-block;">
                {{ $button }}
            </a>
        </div>
    </div>

    <div style="text-align: center; padding: 16px 0; font-size: 12px; color: #999;">
        {{-- The line that turns "who is this?" into an unsubscribe rather than a spam complaint.
             Worded for THIS list, not the schedule-wide one: somebody who asked about one event has
             not subscribed to the schedule, and telling them they had would be untrue. --}}
        <p style="margin: 0 0 8px;">{{ __('messages.event_interest_why_receiving', ['event' => $event->name]) }}</p>
        <a href="{{ $unsubscribeUrl }}" style="color: #999;">{{ __('messages.unsubscribe') }}</a>
    </div>
</body>
</html>
