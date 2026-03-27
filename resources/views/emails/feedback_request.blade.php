<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.feedback_request_subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.feedback_how_was') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $sale->name }},</p>

        <p>{{ __('messages.feedback_rate_event') }}</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <h2 style="margin-top: 0; color: #4E81FA;">{{ $event->name }}</h2>
            <p style="margin: 10px 0;"><strong>{{ __('messages.date') }}:</strong> {{ $event->is_multi_day ? $event->getDateRangeDisplay($sale->event_date) : $event->getStartDateTime($sale->event_date, true)->translatedFormat('F j, Y') }}</p>
            <p style="margin: 10px 0;"><strong>{{ __('messages.time') }}:</strong> {{ $event->getStartEndTime($sale->event_date) }}</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $feedbackUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.feedback_submit') }}
            </a>
        </div>

        @if($event->isFanContentEnabled() && $event->getGuestUrl())
            @php
                $types = [];
                if ($event->isFanPhotosEnabled()) $types[] = mb_strtolower(__('messages.fan_photos_enabled'));
                if ($event->isFanVideosEnabled()) $types[] = mb_strtolower(__('messages.fan_videos_enabled'));
                if ($event->isFanCommentsEnabled()) $types[] = mb_strtolower(__('messages.fan_comments_enabled'));
            @endphp
            <div style="background-color: #f0f4ff; padding: 16px 20px; border-radius: 8px; margin: 20px 0;">
                <p style="font-size: 14px; font-weight: bold; margin: 0 0 6px 0; color: #333;">{{ __('messages.feedback_share_content') }}</p>
                <p style="font-size: 13px; margin: 0 0 8px 0; color: #555;">{{ __('messages.feedback_share_content_description', ['types' => implode(', ', $types)]) }}</p>
                <a href="{{ $event->getGuestUrl() }}#event-media-section" style="font-size: 13px; color: #4E81FA;">{{ __('messages.feedback_share_content_link') }}</a>
            </div>
        @endif

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            @php
                $emailSettings = $role ? $role->getEmailSettings() : [];
                $supportEmail = !empty($emailSettings['from_address']) ? $emailSettings['from_address'] : ($event->user?->email ?? config('mail.from.address'));
            @endphp
            {{ __('messages.event_support_contact') }}: <a href="mailto:{{ $supportEmail }}" style="color: #4E81FA;">{{ $supportEmail }}</a>
        </p>
    </div>
</body>
</html>
