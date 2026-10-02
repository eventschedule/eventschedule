@php
    $theme = \App\Utils\EmailTheme::guest($role ?? null);
    $amount = \App\Utils\MoneyUtils::format($giftCard->amount, $giftCard->currency_code);
@endphp
<x-email.layout :theme="$theme" :title="__('messages.gift_card_recipient_title')" :preheader="__('messages.gift_card_recipient_intro', ['name' => $giftCard->purchaser_name, 'schedule' => $role->name])">
<x-email.heading>{{ __('messages.gift_card_recipient_title') }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $giftCard->recipient_name }},</x-email.text>
<x-email.text>{{ __('messages.gift_card_recipient_intro', ['name' => $giftCard->purchaser_name, 'schedule' => $role->name]) }}</x-email.text>

@if ($giftCard->message)
<x-email.quote :text="$giftCard->message" :cite="$giftCard->purchaser_name" />
@endif

<x-email.highlight :value="$amount" :caption="$giftCard->expires_at ? __('messages.gift_card_valid_until', ['date' => $giftCard->expires_at->translatedFormat('M j, Y')]) : null" />

{{-- The code on a neutral panel rather than a second tinted highlight, so it reads as the thing to
     copy rather than a repeat of the amount above it. One text node, always left to right. --}}
<x-email.highlight :value="$giftCard->formattedCode()" :label="__('messages.gift_card_code')" mono ltr tone="neutral" />

<x-email.section :label="__('messages.gift_card_how_to_redeem')" />
<x-email.prose>
<ol>
<li>{{ __('messages.gift_card_redeem_step_1', ['schedule' => $role->name]) }}</li>
<li>{{ __('messages.gift_card_redeem_step_2') }}</li>
<li>{{ __('messages.gift_card_redeem_step_3') }}</li>
</ol>
</x-email.prose>

<x-email.button :href="$cardUrl">{{ __('messages.view_gift_card') }}</x-email.button>

@if ($scheduleUrl)
<x-email.text align="center"><x-email.link :href="$scheduleUrl">{{ __('messages.gift_card_browse_events', ['schedule' => $role->name]) }}</x-email.link></x-email.text>
@endif
</x-email.layout>
