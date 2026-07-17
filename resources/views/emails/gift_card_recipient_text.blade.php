{{ __('messages.gift_card_recipient_title') }}

{{ __('messages.hello') }} {{ $giftCard->recipient_name }},

{{ __('messages.gift_card_recipient_intro', ['name' => $giftCard->purchaser_name, 'schedule' => $role->name]) }}
@if ($giftCard->message)

"{{ $giftCard->message }}"
- {{ $giftCard->purchaser_name }}
@endif

{{ __('messages.amount') }}: {{ \App\Utils\MoneyUtils::format($giftCard->amount, $giftCard->currency_code) }}
{{ __('messages.gift_card_code') }}: {{ $giftCard->formattedCode() }}
@if ($giftCard->expires_at)
{{ __('messages.gift_card_valid_until', ['date' => $giftCard->expires_at->format('M j, Y')]) }}
@endif

{{ __('messages.gift_card_how_to_redeem') }}
1. {{ __('messages.gift_card_redeem_step_1', ['schedule' => $role->name]) }}
2. {{ __('messages.gift_card_redeem_step_2') }}
3. {{ __('messages.gift_card_redeem_step_3') }}

{{ __('messages.view_gift_card') }}: {{ $cardUrl }}
@if ($scheduleUrl)

{{ __('messages.gift_card_browse_events', ['schedule' => $role->name]) }}: {{ $scheduleUrl }}
@endif
