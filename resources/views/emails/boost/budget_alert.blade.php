@php
    $theme = \App\Utils\EmailTheme::account();
    $eventName = $event?->name ?? __('messages.deleted_event');
    $money = fn ($amount) => $campaign->getCurrencySymbol().number_format($amount, 2);
@endphp
<x-email.layout :theme="$theme" :title="__('messages.boost_email_budget_alert_subject')" :preheader="__('messages.boost_budget_75_percent', ['event' => $eventName])">
<x-email.heading :eyebrow="__('messages.boost_email_budget_alert_subject')" tone="warning" auto>{{ $eventName }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $campaign->user?->name ?? '' }},</x-email.text>
<x-email.text>{{ __('messages.boost_budget_75_percent', ['event' => $eventName]) }}</x-email.text>

<x-email.details panel>
<x-email.item :label="__('messages.budget')">{{ $money($campaign->user_budget) }}</x-email.item>
<x-email.item :label="__('messages.amount_spent')">{{ $money($campaign->actual_spend) }}</x-email.item>
<x-email.item :label="__('messages.impressions')">{{ number_format($campaign->impressions) }}</x-email.item>
<x-email.item :label="__('messages.clicks')">{{ number_format($campaign->clicks) }}</x-email.item>
</x-email.details>

<x-email.button :href="$boostUrl">{{ __('messages.view_campaign') }}</x-email.button>
</x-email.layout>
