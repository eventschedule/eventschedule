@php
    $theme = \App\Utils\EmailTheme::account();
    $eventName = $event?->name ?? __('messages.deleted_event');
    $money = fn ($amount) => $campaign->getCurrencySymbol().number_format($amount, 2);
@endphp
<x-email.layout :theme="$theme" :title="__('messages.boost_email_completed_subject')" :preheader="__('messages.boost_email_completed_body')">
<x-email.heading :eyebrow="__('messages.boost_email_completed_subject')" tone="success" auto>{{ $eventName }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $campaign->user?->name ?? '' }},</x-email.text>
<x-email.text>{{ __('messages.boost_email_completed_body') }}</x-email.text>

<x-email.details panel>
<x-email.item :label="__('messages.impressions')">{{ number_format($campaign->impressions) }}</x-email.item>
<x-email.item :label="__('messages.reach')">{{ number_format($campaign->reach) }}</x-email.item>
<x-email.item :label="__('messages.clicks')">{{ number_format($campaign->clicks) }}</x-email.item>
@if ($campaign->conversions > 0)
<x-email.item :label="__('messages.conversions')">{{ $campaign->conversions }}</x-email.item>
@endif
<x-email.item :label="__('messages.amount_spent')">{{ $money($campaign->actual_spend) }}</x-email.item>
</x-email.details>

@if ($refundAmount > 0)
<x-email.callout tone="success" :title="__('messages.boost_unspent_refund')">{{ __('messages.boost_refund_amount') }}: {{ $money($refundAmount) }}</x-email.callout>
@endif

<x-email.button :href="$boostUrl">{{ __('messages.view_results') }}</x-email.button>
</x-email.layout>
