@php
    $theme = \App\Utils\EmailTheme::account();
    $subject = $approved ? __('messages.promotion_email_approved_subject') : __('messages.promotion_email_rejected_subject');
    $body = $approved ? __('messages.promotion_email_approved_body') : __('messages.promotion_email_rejected_body');
@endphp
<x-email.layout :theme="$theme" :title="$subject" :preheader="$body">
<x-email.heading :eyebrow="$subject" :tone="$approved ? 'success' : 'danger'" auto>{{ $event?->name ?? __('messages.deleted_event') }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $campaign->user?->name ?? '' }},</x-email.text>
<x-email.text>{{ $body }}</x-email.text>

@if (! $approved && $notes)
<x-email.callout tone="danger" :title="__('messages.reason')">{{ $notes }}</x-email.callout>
@endif

@if (! $approved)
<x-email.callout tone="success"><strong>{{ __('messages.boost_full_refund_issued') }}</strong></x-email.callout>
@endif

<x-email.button :href="$url">{{ __('messages.view_campaign') }}</x-email.button>
</x-email.layout>
