{{ __('messages.gift_card_receipt_title') }}

{{ __('messages.hello') }} {{ $giftCard->purchaser_name }},

{{ __('messages.gift_card_receipt_intro', ['schedule' => $role->name]) }}

{{ __('messages.amount') }}: {{ \App\Utils\MoneyUtils::format($giftCard->amount, $giftCard->currency_code) }}
{{ __('messages.recipient') }}: {{ $giftCard->recipient_name }} ({{ $giftCard->recipient_email }})
{{ __('messages.gift_card_code') }}: {{ $giftCard->formattedCode() }}
@if ($giftCard->expires_at)
{{ __('messages.expires') }}: {{ $giftCard->expires_at->format('M j, Y') }}
@endif

{{ __('messages.gift_card_receipt_recipient_emailed', ['email' => $giftCard->recipient_email]) }}

{{ __('messages.view_gift_card') }}: {{ $cardUrl }}
