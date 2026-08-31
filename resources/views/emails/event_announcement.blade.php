<!DOCTYPE html>
<html @if ($isRtl ?? false) dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ trans_choice('messages.announcement_subject', $events->count(), ['schedule' => $role->name, 'count' => $events->count()]) }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">{{ $role->name }}</h1>
        <p style="margin: 6px 0 0; font-size: 15px; opacity: 0.9;">{{ trans_choice('messages.announcement_heading', $events->count(), ['count' => $events->count()]) }}</p>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        @foreach ($events as $event)
            {{-- getStartDateTime() with no timezone override, so the date renders in the
                 SCHEDULE's timezone. An occurrence falls on a given day because of where it
                 happens, not because of where the reader is sitting. --}}
            <div style="padding: 14px 0; @if (! $loop->last) border-bottom: 1px solid #e5e5e5; @endif">
                <a href="{{ $event->getGuestUrl($role->subdomain, null, true) }}"
                   style="font-size: 17px; font-weight: 600; color: #1f2937; text-decoration: none;">{{ $event->name }}</a>
                <div style="font-size: 14px; color: #666; margin-top: 4px;">
                    {{ $event->is_multi_day
                        ? $event->getDateRangeDisplay()
                        : $event->getStartDateTime(null, true)?->translatedFormat('F j, Y') }}
                    @if ($event->venue && $event->venue->name)
                        &middot; {{ $event->venue->name }}
                    @endif
                </div>
            </div>
        @endforeach

        <div style="text-align: center; margin: 28px 0 8px;">
            <a href="{{ $role->getGuestUrl(true) }}"
               style="background-color: #4E81FA; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: 600; display: inline-block;">
                {{ __('messages.announcement_view_schedule') }}
            </a>
        </div>
    </div>

    <div style="text-align: center; padding: 16px 0; font-size: 12px; color: #999;">
        {{-- messages.subscription_why_receiving shipped translated into all 13 languages and was
             rendered nowhere. It is the line that turns "who is this?" into an unsubscribe rather
             than a spam complaint. --}}
        <p style="margin: 0 0 8px;">{{ __('messages.subscription_why_receiving', ['schedule' => $role->name]) }}</p>
        <a href="{{ $unsubscribeUrl }}" style="color: #999;">{{ __('messages.unsubscribe') }}</a>
    </div>
</body>
</html>
