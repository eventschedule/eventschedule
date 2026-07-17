{{ __('messages.gift_card_sold') }}
@if ($recipient)

{{ __('messages.hello') }} {{ $recipient->name }},
@endif

{{ __('messages.gift_card_sale_notification_intro', ['schedule' => $role->name]) }}

{{ __('messages.amount') }}: {{ $amount }}
{{ __('messages.purchaser') }}: {{ $giftCard->purchaser_name }} ({{ $giftCard->purchaser_email }})
{{ __('messages.recipient') }}: {{ $giftCard->recipient_name }} ({{ $giftCard->recipient_email }})
{{ __('messages.payment') }}: {{ __('messages.'.$giftCard->payment_method) }}

{{ __('messages.view_gift_cards') }}: {{ $salesUrl }}
