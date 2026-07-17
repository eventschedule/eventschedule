<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        // A gift-card-covered order is a purchase, not a free reservation
        $isFreeReservation = $sale->payment_amount == 0 && ($giftCardAmount ?? 0) == 0;
    @endphp
    <title>{{ $sale->isRsvp() ? __('messages.registration_confirmation') : ($isFreeReservation ? __('messages.ticket_reservation_confirmation') : __('messages.ticket_purchase_confirmation')) }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ $sale->isRsvp() ? __('messages.registration_confirmation') : ($isFreeReservation ? __('messages.ticket_reservation_confirmation') : __('messages.ticket_purchase_confirmation')) }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $sale->name }},</p>

        <p>{{ $sale->isRsvp() ? __('messages.registration_confirmation') : ($isFreeReservation ? __('messages.thank_you_for_reserving_tickets') : __('messages.thank_you_for_purchasing_tickets')) }}</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <h2 style="margin-top: 0; color: #4E81FA;">{{ $event->name }}</h2>
            <p style="margin: 10px 0;"><strong>{{ __('messages.date') }}:</strong> {{ $event->is_multi_day ? $event->getDateRangeDisplay($sale->event_date) : $event->getStartDateTime($sale->event_date, true)->format('F j, Y') }}</p>
            <p style="margin: 10px 0;"><strong>{{ __('messages.time') }}:</strong> {{ $event->getStartEndTime($sale->event_date) }}</p>
            <p style="margin: 10px 0;"><strong>{{ __('messages.attendee') }}:</strong> {{ $sale->name }}</p>
            @if (! $sale->isRsvp())
            <p style="margin: 10px 0;"><strong>{{ __('messages.number_of_attendees') }}:</strong> {{ $sale->quantity() }}</p>
            @endif
        </div>

        @php
            $regularTickets = $sale->saleTickets->filter(fn($st) => $st->ticket && !$st->ticket->is_addon);
            $addonTickets = $sale->saleTickets->filter(fn($st) => $st->ticket && $st->ticket->is_addon);
        @endphp
        @if ($regularTickets->count() > 0)
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #4E81FA;">{{ __('messages.ticket_details') }}</h3>
            @foreach ($regularTickets as $saleTicket)
                <p style="margin: 10px 0;">
                    <strong>{{ $saleTicket->ticket->type ?: __('messages.ticket') }}</strong>
                    x {{ $saleTicket->quantity }}
                    @if ($saleTicket->ticket->is_pass)
                    <br><span style="display: inline-block; margin-top: 4px; font-size: 12px; color: #4E81FA;">@if ($saleTicket->ticket->pass_usage_type === 'per_occurrence'){{ __('messages.season_pass') }} &middot; {{ __('messages.pass_valid_all_dates') }}@else{{ __('messages.subscription') }}@if ($saleTicket->ticket->pass_usage_type === 'total' && $saleTicket->ticket->pass_max_uses) &middot; {{ $saleTicket->ticket->pass_max_uses }} {{ __('messages.visits') }}@elseif ($saleTicket->ticket->pass_usage_type === 'unlimited') &middot; {{ __('messages.pass_unlimited_visits') }}@endif @endif</span>
                    @endif
                </p>
            @endforeach
        </div>
        @endif
        @if ($addonTickets->count() > 0)
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #4E81FA;">{{ __('messages.add_ons') }}</h3>
            @foreach ($addonTickets as $saleTicket)
                <p style="margin: 10px 0;">
                    <strong>{{ $saleTicket->ticket->type ?: __('messages.add_on') }}</strong>
                    x {{ $saleTicket->quantity }}
                    @if ($saleTicket->ticket->url)
                        <br><a href="{{ $saleTicket->ticket->url }}" style="color: #4E81FA; font-size: 13px;">{{ $saleTicket->ticket->url }}</a>
                    @endif
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

        @if (($giftCardAmount ?? 0) > 0 && $giftCard)
        {{-- The ticket buyer may not be the card recipient, so do NOT link the secret-authed card
             view page here (it exposes the purchaser's name/message). Show amounts only. --}}
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <h3 style="margin-top: 0; color: #4E81FA;">{{ __('messages.gift_card') }}</h3>
            <p style="margin: 10px 0;">{{ __('messages.gift_card_applied_summary') }}: <strong>-{{ \App\Utils\MoneyUtils::format($giftCardAmount, $giftCard->currency_code) }}</strong></p>
            <p style="margin: 10px 0;">{{ __('messages.gift_card_remaining_balance') }}: <strong>{{ \App\Utils\MoneyUtils::format($giftCard->remaining_amount, $giftCard->currency_code) }}</strong></p>
        </div>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $ticketUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                @if ($sale->isPass()){{ __('messages.manage_my_pass') }}@elseif ($sale->isRsvp()){{ __('messages.view_registration') }}@else{{ __('messages.view_your_tickets') }}@endif
            </a>
        </div>
        
        @php $ticketNotes = $event->parsedTicketNotesHtml($sale->event_date, $role); @endphp
        @if ($ticketNotes && trim(strip_tags($ticketNotes)) !== '')
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <h3 style="margin-top: 0; color: #4E81FA;">{{ __('messages.important_information') }}</h3>
            <div style="color: #333;">
                {!! \App\Utils\UrlUtils::convertUrlsToLinks($ticketNotes) !!}
            </div>
        </div>
        @endif
        
        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            {{ __('messages.event_support_contact') }}: <a href="mailto:{{ $event->user->email }}" style="color: #4E81FA;">{{ $event->user->email }}</a>
        </p>
    </div>
</body>
</html>

