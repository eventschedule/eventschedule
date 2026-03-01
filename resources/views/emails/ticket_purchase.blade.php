<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $sale->isRsvp() ? __('messages.registration_confirmation') : ($sale->calculateTotal() == 0 ? __('messages.ticket_reservation_confirmation') : __('messages.ticket_purchase_confirmation')) }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ $sale->isRsvp() ? __('messages.registration_confirmation') : ($sale->calculateTotal() == 0 ? __('messages.ticket_reservation_confirmation') : __('messages.ticket_purchase_confirmation')) }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $sale->name }},</p>

        <p>{{ $sale->isRsvp() ? __('messages.registration_confirmation') : ($sale->calculateTotal() == 0 ? __('messages.thank_you_for_reserving_tickets') : __('messages.thank_you_for_purchasing_tickets')) }}</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <h2 style="margin-top: 0; color: #4E81FA;">{{ $event->name }}</h2>
            <p style="margin: 10px 0;"><strong>{{ __('messages.date') }}:</strong> {{ $event->getStartDateTime($sale->event_date, true)->format('F j, Y') }}</p>
            <p style="margin: 10px 0;"><strong>{{ __('messages.time') }}:</strong> {{ $event->getStartEndTime($sale->event_date) }}</p>
            <p style="margin: 10px 0;"><strong>{{ __('messages.attendee') }}:</strong> {{ $sale->name }}</p>
            @if (! $sale->isRsvp())
            <p style="margin: 10px 0;"><strong>{{ __('messages.number_of_attendees') }}:</strong> {{ $sale->quantity() }}</p>
            @endif
        </div>

        @if ($sale->saleTickets->count() > 0)
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #4E81FA;">{{ __('messages.ticket_details') }}</h3>
            @foreach ($sale->saleTickets as $saleTicket)
                <p style="margin: 10px 0;">
                    <strong>{{ $saleTicket->ticket->type ?: __('messages.ticket') }}</strong>
                    x {{ $saleTicket->quantity }}
                </p>
            @endforeach
        </div>
        @endif

        <!--
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;">
            <h3 style="margin-top: 0; color: #4E81FA; margin-bottom: 15px;">{{ __('messages.ticket_qr_code') ?: 'Your Ticket QR Code' }}</h3>
            <div style="display: inline-block; padding: 15px; background-color: #f9f9f9; border-radius: 8px;">
                <img src="{{ $message->embedData($qrCodeData, 'ticket-qr-code.png', 'image/png') }}" alt="Ticket QR Code" style="max-width: 200px; height: auto;" />
            </div>
            <p style="margin-top: 15px; font-size: 14px; color: #666;">{{ __('messages.scan_qr_code_to_view_ticket') ?: 'Scan this QR code to view your ticket' }}</p>
        </div>
        -->

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $ticketUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ $sale->isRsvp() ? __('messages.view_registration') : __('messages.view_your_tickets') }}
            </a>
        </div>
        
        @if ($event->ticket_notes_html)
        <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <h4 style="margin-top: 0; color: #856404;">{{ __('messages.notes') }}:</h4>
            <div style="color: #856404;">
                {!! \App\Utils\UrlUtils::convertUrlsToLinks($event->ticket_notes_html) !!}
            </div>
        </div>
        @endif
        
        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            {{ __('messages.event_support_contact') }}: <a href="mailto:{{ $event->user->email }}" style="color: #4E81FA;">{{ $event->user->email }}</a>
        </p>
    </div>
</body>
</html>

