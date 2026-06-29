<!DOCTYPE html>
<html @if ($isRtl ?? false) dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.event_changed_heading') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; @if ($isRtl ?? false) text-align: right; @endif">
    <div style="background-color: #4E81FA; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">{{ __('messages.event_changed_heading') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }}@if (! empty($recipientName)), {{ $recipientName }}@endif,</p>

        <p style="font-size: 16px;">{{ __('messages.event_changed_body', ['event' => $event->name]) }}</p>

        @if (! empty($note))
        <div style="background-color: #fff; padding: 15px 20px; border-radius: 8px; margin: 20px 0; border-{{ ($isRtl ?? false) ? 'right' : 'left' }}: 4px solid #9ca3af;">
            <p style="margin: 0 0 6px 0; font-size: 13px; color: #666; font-weight: bold;">{{ __('messages.organizer_note') }}</p>
            <p style="margin: 0; font-size: 15px; color: #333;">{!! nl2br(e($note)) !!}</p>
        </div>
        @endif

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-{{ ($isRtl ?? false) ? 'right' : 'left' }}: 4px solid #4E81FA;">
            <p style="margin: 0 0 15px 0; font-size: 13px; color: #666; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.event_changed_whats_changed') }}</p>

            @if (isset($display['date']))
            <p style="margin: 0 0 6px 0; font-size: 14px; color: #333;"><strong>{{ __('messages.event_changed_date_label') }}</strong></p>
            @if (! empty($display['date']['old']))
            <p style="margin: 0; font-size: 14px; color: #999;">{{ __('messages.event_changed_previously') }}: <s>{{ $display['date']['old'] }} {{ $display['date']['old_tz'] }}</s></p>
            @endif
            <p style="margin: 0 0 6px 0; font-size: 15px; color: #333;">{{ __('messages.event_changed_now') }}: <strong>{{ $display['date']['new'] }} {{ $display['date']['new_tz'] }}</strong></p>
            @if (! empty($display['date']['delta']))
            <p style="margin: 0 0 16px 0; font-size: 13px; color: #4E81FA;">{{ $display['date']['delta'] }}</p>
            @endif
            @endif

            @if (isset($display['location']))
            <p style="margin: {{ isset($display['date']) ? '16px' : '0' }} 0 6px 0; font-size: 14px; color: #333;"><strong>{{ __('messages.event_changed_location_label') }}</strong></p>
            @php($loc = $display['location'])
            @if ($loc['variant'] === 'moved_online')
            <p style="margin: 0; font-size: 14px; color: #333;">{{ __('messages.event_changed_moved_online') }}</p>
            @elseif ($loc['variant'] === 'moved_in_person')
            <p style="margin: 0; font-size: 14px; color: #333;">{{ __('messages.event_changed_moved_in_person', ['venue' => $loc['new_venue']]) }}</p>
            @elseif ($loc['variant'] === 'online_updated')
            <p style="margin: 0; font-size: 14px; color: #333;">{{ __('messages.event_changed_online_updated') }}</p>
            @else
            <p style="margin: 0; font-size: 14px; color: #333;">{{ __('messages.event_changed_venue') }}</p>
            @if (! empty($loc['old_venue']))
            <p style="margin: 6px 0 0 0; font-size: 14px; color: #999;">{{ __('messages.event_changed_previously') }}: <s>{{ $loc['old_venue'] }}</s></p>
            @endif
            @if (! empty($loc['new_venue']))
            <p style="margin: 0; font-size: 15px; color: #333;">{{ __('messages.event_changed_now') }}: <strong>{{ $loc['new_venue'] }}</strong></p>
            @endif
            @endif
            @endif
        </div>

        <div style="background-color: white; padding: 15px 20px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0 0 6px 0; font-size: 16px;"><strong>{{ $event->name }}</strong></p>
            <p style="margin: 0; font-size: 14px; color: #666;">
                @if (isset($display['date'])){{ $display['date']['new'] }} {{ $display['date']['new_tz'] }}@endif
            </p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $eventUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.event_changed_cta') }}
            </a>
        </div>

        @if (! empty($icalUrl))
        <p style="text-align: center; font-size: 14px; margin: 0 0 6px 0;">
            <a href="{{ $icalUrl }}" style="color: #4E81FA;">{{ __('messages.update_your_calendar') }}</a>
        </p>
        <p style="text-align: center; font-size: 12px; color: #999; margin: 0;">{{ __('messages.update_your_calendar_note') }}</p>
        @endif

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            {{ __('messages.thank_you_for_using') }}
        </p>
    </div>
</body>
</html>
