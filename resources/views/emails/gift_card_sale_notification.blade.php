<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.gift_card_sold') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.gift_card_sold') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        @if ($recipient)
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $recipient->name }},</p>
        @endif

        <p>{{ __('messages.gift_card_sale_notification_intro', ['schedule' => $role->name]) }}</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <p style="margin: 10px 0;"><strong>{{ __('messages.amount') }}:</strong> {{ $amount }}</p>
            <p style="margin: 10px 0;"><strong>{{ __('messages.purchaser') }}:</strong> {{ $giftCard->purchaser_name }} ({{ $giftCard->purchaser_email }})</p>
            <p style="margin: 10px 0;"><strong>{{ __('messages.recipient') }}:</strong> {{ $giftCard->recipient_name }} ({{ $giftCard->recipient_email }})</p>
            <p style="margin: 10px 0;"><strong>{{ __('messages.payment') }}:</strong> {{ __('messages.'.$giftCard->payment_method) }}</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $salesUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.view_gift_cards') }}
            </a>
        </div>
    </div>
</body>
</html>
