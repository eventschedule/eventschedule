<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.gift_card_recipient_title') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">🎁 {{ __('messages.gift_card_recipient_title') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $giftCard->recipient_name }},</p>

        <p>{{ __('messages.gift_card_recipient_intro', ['name' => $giftCard->purchaser_name, 'schedule' => $role->name]) }}</p>

        @if ($giftCard->message)
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA; font-style: italic;">
            "{{ $giftCard->message }}"
            <div style="margin-top: 8px; font-style: normal; font-size: 13px; color: #666;">- {{ $giftCard->purchaser_name }}</div>
        </div>
        @endif

        <div style="background-color: white; padding: 25px; border-radius: 8px; margin: 20px 0; text-align: center;">
            <div style="font-size: 32px; font-weight: bold; color: #4E81FA;">{{ \App\Utils\MoneyUtils::format($giftCard->amount, $giftCard->currency_code) }}</div>
            <div style="margin-top: 15px; font-size: 13px; color: #666;">{{ __('messages.gift_card_code') }}</div>
            <div dir="ltr" style="font-family: 'Courier New', monospace; font-size: 22px; font-weight: bold; letter-spacing: 2px; margin-top: 5px;">{{ $giftCard->formattedCode() }}</div>
            @if ($giftCard->expires_at)
            <div style="margin-top: 15px; font-size: 13px; color: #666;">{{ __('messages.gift_card_valid_until', ['date' => $giftCard->expires_at->format('M j, Y')]) }}</div>
            @endif
        </div>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #4E81FA;">{{ __('messages.gift_card_how_to_redeem') }}</h3>
            <ol style="margin: 0; padding-left: 20px;">
                <li style="margin: 8px 0;">{{ __('messages.gift_card_redeem_step_1', ['schedule' => $role->name]) }}</li>
                <li style="margin: 8px 0;">{{ __('messages.gift_card_redeem_step_2') }}</li>
                <li style="margin: 8px 0;">{{ __('messages.gift_card_redeem_step_3') }}</li>
            </ol>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $cardUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.view_gift_card') }}
            </a>
        </div>

        @if ($scheduleUrl)
        <p style="text-align: center;">
            <a href="{{ $scheduleUrl }}" style="color: #4E81FA;">{{ __('messages.gift_card_browse_events', ['schedule' => $role->name]) }}</a>
        </p>
        @endif
    </div>
</body>
</html>
