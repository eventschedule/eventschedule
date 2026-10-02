@php
    $theme = \App\Utils\EmailTheme::account();
    $eventName = $event?->name ?? __('messages.deleted_event');
    $money = fn ($amount) => $campaign->getCurrencySymbol().number_format($amount, 2);
@endphp
<x-email.layout :theme="$theme" :title="__('messages.boost_email_created_subject')" :preheader="__('messages.boost_email_created_body')">
<x-email.heading :eyebrow="__('messages.boost_email_created_subject')" auto>{{ $eventName }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $campaign->user?->name ?? '' }},</x-email.text>
<x-email.text>{{ __('messages.boost_email_created_body') }}</x-email.text>

<x-email.details panel>
<x-email.item :label="__('messages.budget')">{{ $money($campaign->user_budget) }}</x-email.item>
<x-email.item :label="__('messages.total_charged')">{{ $money($campaign->getTotalCost()) }}</x-email.item>
@if ($campaign->scheduled_end)
<x-email.item :label="__('messages.ends')">{{ $campaign->scheduled_end->translatedFormat('F j, Y') }}</x-email.item>
@endif
</x-email.details>

<x-email.button :href="$boostUrl">{{ __('messages.view_campaign') }}</x-email.button>
</x-email.layout>
