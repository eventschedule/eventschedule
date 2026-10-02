@php($theme = \App\Utils\EmailTheme::guest($role ?? null))
<x-email.layout :theme="$theme" :title="__('messages.gift_card_receipt_title')" :preheader="__('messages.gift_card_receipt_intro', ['schedule' => $role->name])">
<x-email.heading>{{ __('messages.gift_card_receipt_title') }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $giftCard->purchaser_name }},</x-email.text>
<x-email.text>{{ __('messages.gift_card_receipt_intro', ['schedule' => $role->name]) }}</x-email.text>

<x-email.details>
<x-email.item :label="__('messages.amount')">{{ \App\Utils\MoneyUtils::format($giftCard->amount, $giftCard->currency_code) }}</x-email.item>
@if ($giftCard->expires_at)
<x-email.item :label="__('messages.expires')">{{ $giftCard->expires_at->translatedFormat('M j, Y') }}</x-email.item>
@endif
<x-email.item :label="__('messages.recipient')" :caption="$giftCard->recipient_email" wide>{{ $giftCard->recipient_name }}</x-email.item>
</x-email.details>

<x-email.highlight :value="$giftCard->formattedCode()" :caption="__('messages.gift_card_code')" mono ltr />

<x-email.text>{{ __('messages.gift_card_receipt_recipient_emailed', ['email' => $giftCard->recipient_email]) }}</x-email.text>

<x-email.button :href="$cardUrl">{{ __('messages.view_gift_card') }}</x-email.button>
</x-email.layout>
