@php($theme = \App\Utils\EmailTheme::owner($role ?? null))
<x-email.layout :theme="$theme" :title="__('messages.gift_card_sold')" :preheader="$amount.' · '.__('messages.gift_card_sale_notification_intro', ['schedule' => $role->name])">
<x-email.heading>{{ __('messages.gift_card_sold') }}</x-email.heading>

@if ($recipient)
<x-email.text>{{ __('messages.hello') }} {{ $recipient->name }},</x-email.text>
@endif

<x-email.text>{{ __('messages.gift_card_sale_notification_intro', ['schedule' => $role->name]) }}</x-email.text>

<x-email.highlight :value="$amount" :caption="__('messages.payment').': '.__('messages.'.$giftCard->payment_method)" />

<x-email.details>
<x-email.item :label="__('messages.purchaser')" :caption="$giftCard->purchaser_email">{{ $giftCard->purchaser_name }}</x-email.item>
<x-email.item :label="__('messages.recipient')" :caption="$giftCard->recipient_email">{{ $giftCard->recipient_name }}</x-email.item>
</x-email.details>

<x-email.button :href="$salesUrl">{{ __('messages.view_gift_cards') }}</x-email.button>

<x-slot:footer>
@include('emails.partials.notification_email_footer', ['scheduleName' => $role?->name])
</x-slot:footer>
</x-email.layout>
