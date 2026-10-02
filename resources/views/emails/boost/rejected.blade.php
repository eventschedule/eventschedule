@php
    $theme = \App\Utils\EmailTheme::account();
    $eventName = $event?->name ?? __('messages.deleted_event');
@endphp
<x-email.layout :theme="$theme" :title="__('messages.boost_email_rejected_subject')" :preheader="__('messages.boost_email_rejected_body')">
<x-email.heading :eyebrow="__('messages.boost_email_rejected_subject')" tone="danger" auto>{{ $eventName }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $campaign->user?->name ?? '' }},</x-email.text>
<x-email.text>{{ __('messages.boost_email_rejected_body') }}</x-email.text>

@if ($rejectionReason)
<x-email.callout tone="danger" :title="__('messages.reason')">{{ $rejectionReason }}</x-email.callout>
@endif

@if ($refunded)
<x-email.callout tone="success" :title="__('messages.boost_full_refund_issued')">{{ __('messages.boost_refund_amount') }}: {{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->total_charged, 2) }}</x-email.callout>
@else
<x-email.callout tone="warning"><strong>{{ __('messages.boost_refund_pending') }}</strong></x-email.callout>
@endif

<x-email.text>{{ __('messages.boost_try_again_suggestion') }}</x-email.text>
</x-email.layout>
