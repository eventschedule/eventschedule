<!DOCTYPE html>
<html @if ($isRtl ?? false) dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.owner_digest_heading') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.owner_digest_heading') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $user->firstName() }},</p>
        <p>{{ __('messages.owner_digest_intro') }}</p>

        @foreach ($sections as $section)
        <div style="background-color: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h2 style="margin: 0 0 10px; font-size: 18px;"><a href="{{ $section['url'] }}" style="color: #333; text-decoration: none;">{{ $section['name'] }}</a></h2>
            <table role="presentation" style="width: 100%; border-collapse: collapse; font-size: 14px;">
                @foreach (['views', 'followers', 'subscribers', 'tickets', 'rsvps'] as $metric)
                    @if ($section[$metric] > 0 || $metric === 'views')
                    <tr>
                        <td style="padding: 3px 0; color: #666;">{{ __('messages.owner_digest_'.$metric) }}</td>
                        <td style="padding: 3px 0; text-align: end; font-weight: bold;">{{ number_format($section[$metric]) }}</td>
                    </tr>
                    @endif
                @endforeach
            </table>
            @if (! empty($section['upcoming']))
                <p style="margin: 12px 0 4px; font-weight: bold; font-size: 14px;">{{ __('messages.owner_digest_coming_up') }}</p>
                <ul style="margin: 0; padding-inline-start: 20px; font-size: 14px;">
                    @foreach ($section['upcoming'] as $occurrence)
                        <li>{{ $occurrence['date'] }} - {{ $occurrence['name'] }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
        @endforeach

        @if ($more > 0)
        <p style="font-size: 14px; color: #666;">{{ trans_choice('messages.owner_digest_more', $more, ['count' => $more]) }}</p>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $dashboardUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.owner_digest_cta') }}
            </a>
        </div>

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            {{ __('messages.owner_digest_why') }}
            <a href="{{ $unsubscribeUrl }}" style="color: #999;">{{ __('messages.unsubscribe') }}</a>
        </p>
    </div>
</body>
</html>
