@php
    $theme = \App\Utils\EmailTheme::account();

    $preheader = match ($state) {
        'live' => __('messages.federation_welcome_preheader_live'),
        'received' => __('messages.federation_welcome_preheader_received'),
        default => __('messages.federation_welcome_preheader_empty'),
    };

    $statusTitle = match ($state) {
        'live' => trans_choice('messages.federation_welcome_live_title', $liveCount, ['count' => number_format($liveCount)]),
        'received' => trans_choice('messages.federation_welcome_received_title', $receivedCount, ['count' => number_format($receivedCount)]),
        default => __('messages.federation_welcome_empty_title'),
    };
@endphp
{{-- Every link in this mail is one this site chose (FederationWelcomeTest): the layout prints none
     of its own, and the install's host is only ever printed through $bold, which breaks it with a
     zero-width space so no mail client turns it into a link. --}}
<x-email.layout :theme="$theme" :title="__('messages.federation_welcome_subject')" :preheader="$preheader">
<x-email.heading :subtitle="$bold('messages.federation_welcome_subheading', ['host' => $host], ['host'])">{{ __('messages.federation_welcome_heading') }}</x-email.heading>

<x-email.text>{{ __('messages.federation_welcome_intro') }}</x-email.text>

{{-- Where the install stands, with the one action that matters now directly under it, so nobody
     scrolls past three steps to find it. --}}
<x-email.callout tone="info" :title="$statusTitle" :gap="16">
@if ($state === 'empty')
{{ $bold('messages.federation_welcome_empty_body', $labels, ['undecided', 'option']) }}
@endif
</x-email.callout>
<x-email.button :href="$ctaUrl">{{ $ctaLabel }}</x-email.button>

<x-email.section :label="__('messages.federation_welcome_steps_title')" />

<x-email.step number="1" :title="__('messages.federation_welcome_step_list_title')">
@if ($oneClick)
{{ $bold('messages.federation_welcome_step_list_body_oneclick', $labels + ['host' => $host], ['host', 'section']) }}
@else
{{ $bold('messages.federation_welcome_step_list_body_manual', $labels + ['host' => $host], ['host', 'setting', 'option']) }}
@endif
@if ($showUpdateTip)
<div style="height: 12px; line-height: 12px; font-size: 0;">&nbsp;</div>
<x-email.callout tone="neutral" :gap="0">
{{ $bold('messages.federation_welcome_step_update_tip', ['version' => $installedVersion, 'required' => $requiredVersion], ['required']) }}
<x-email.link :href="$updateGuideUrl">{{ __('messages.federation_welcome_update_link') }}</x-email.link>
</x-email.callout>
@endif
</x-email.step>

<x-email.step number="2" :title="__('messages.federation_welcome_step_qualify_title')">
{{ __('messages.federation_welcome_step_qualify_body') }}
</x-email.step>

<x-email.step number="3" :title="__('messages.federation_welcome_step_sync_title')" last>
{{ __('messages.federation_welcome_step_sync_body') }}
<div style="height: 10px; line-height: 10px; font-size: 0;">&nbsp;</div>
<x-email.code value="php artisan federation:push" :gap="0" />
</x-email.step>

<x-email.section />
<x-email.text>
@if ($state !== 'empty')
<x-email.link :href="$guideUrl">{{ __('messages.federation_welcome_guide') }}</x-email.link>&nbsp;&middot;&nbsp;
@endif
<x-email.link :href="$browseUrl">{{ __('messages.federation_welcome_browse') }}</x-email.link>
</x-email.text>
<x-email.text variant="small">{{ __('messages.federation_welcome_reply') }}</x-email.text>

<x-slot:footer>
<x-email.footer>{{ $bold('messages.federation_welcome_why', ['host' => $host], ['host']) }}</x-email.footer>
</x-slot:footer>
</x-email.layout>
