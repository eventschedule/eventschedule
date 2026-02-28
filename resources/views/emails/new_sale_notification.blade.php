<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.new_sale_notification_subject', ['event' => $event->name]) }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">{{ __('messages.new_sale') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.new_sale_notification_greeting', ['name' => $recipient?->name ?? __('messages.hello')]) }},</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <p style="margin: 0 0 10px 0; font-size: 18px; color: #333;">
                <strong>{{ $event->name }}</strong>
            </p>
            @if ($sale->event_date)
            <p style="margin: 0 0 15px 0; font-size: 14px; color: #666;">
                {{ $event->getStartDateTime($sale->event_date, true)->format('F j, Y') }}
                @if ($event->getStartEndTime($sale->event_date)) {{ $event->getStartEndTime($sale->event_date) }}@endif
            </p>
            @endif

            <hr style="border: none; border-top: 1px solid #eee; margin: 15px 0;">

            <p style="margin: 0 0 5px 0; font-size: 14px; color: #666;">
                <strong>{{ __('messages.buyer') }}:</strong> {{ $sale->name }} ({{ $sale->email }})
            </p>

            <hr style="border: none; border-top: 1px solid #eee; margin: 15px 0;">

            @foreach ($sale->saleTickets as $saleTicket)
            <p style="margin: 0 0 5px 0; font-size: 14px; color: #333;">
                {{ $saleTicket->ticket->name }} x {{ $saleTicket->quantity }}
            </p>
            @endforeach

            <hr style="border: none; border-top: 1px solid #eee; margin: 15px 0;">

            <p style="margin: 0 0 5px 0; font-size: 14px; color: #333;">
                <strong>{{ __('messages.total') }}:</strong> {{ $total }}
            </p>
            <p style="margin: 0; font-size: 14px; color: #333;">
                <strong>{{ __('messages.status') }}:</strong> {{ $paymentStatus }}
            </p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $salesUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.view_sales') }}
            </a>
        </div>

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            {{ __('messages.thank_you_for_using') }}
            @if ($unsubscribeUrl)
            <br><br>
            <a href="{{ $unsubscribeUrl }}" style="color: #4E81FA;">{{ __('messages.unsubscribe') }}</a>
            @endif
        </p>
    </div>
</body>
</html>
