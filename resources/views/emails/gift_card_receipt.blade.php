<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.gift_card_receipt_title') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.gift_card_receipt_title') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $giftCard->purchaser_name }},</p>

        <p>{{ __('messages.gift_card_receipt_intro', ['schedule' => $role->name]) }}</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <p style="margin: 10px 0;"><strong>{{ __('messages.amount') }}:</strong> {{ \App\Utils\MoneyUtils::format($giftCard->amount, $giftCard->currency_code) }}</p>
            <p style="margin: 10px 0;"><strong>{{ __('messages.recipient') }}:</strong> {{ $giftCard->recipient_name }} ({{ $giftCard->recipient_email }})</p>
            <p style="margin: 10px 0;"><strong>{{ __('messages.gift_card_code') }}:</strong> <span dir="ltr" style="font-family: 'Courier New', monospace; font-weight: bold;">{{ $giftCard->formattedCode() }}</span></p>
            @if ($giftCard->expires_at)
            <p style="margin: 10px 0;"><strong>{{ __('messages.expires') }}:</strong> {{ $giftCard->expires_at->format('M j, Y') }}</p>
            @endif
        </div>

        <p>{{ __('messages.gift_card_receipt_recipient_emailed', ['email' => $giftCard->recipient_email]) }}</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $cardUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.view_gift_card') }}
            </a>
        </div>
    </div>
</body>
</html>
