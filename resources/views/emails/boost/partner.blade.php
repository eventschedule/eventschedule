@php
    $theme = \App\Utils\EmailTheme::owner($role);
    $tone = match ($kind) {
        'declined', 'access_problem' => 'warning',
        'rejected' => 'danger',
        'live', 'connected', 'approved' => 'success',
        default => null,
    };
    $budget = $campaign ? \App\Utils\MoneyUtils::format((float) $campaign->user_budget, $campaign->currency_code) : null;
    $version = $campaign?->currentVersion();
@endphp
<x-email.layout :theme="$theme" :title="__('messages.boost_partner_mail_'.$kind.'_subject', $replace)" :preheader="__($bodyKey, $replace)">
<x-email.heading :eyebrow="__('messages.boost_partner_mail_'.$kind.'_subject', $replace)" :tone="$tone" auto>{{ $replace['event'] ?: $replace['page'] }}</x-email.heading>

<x-email.text>{{ __($bodyKey, $replace) }}</x-email.text>

@if ($campaign && in_array($kind, ['request_received', 'request_resent'], true) && $version)
<x-email.details panel>
<x-email.item :label="__('messages.headline')">{{ $version->headline }}</x-email.item>
<x-email.item :label="__('messages.ad_budget')">{{ $budget }}</x-email.item>
@if ($campaign->scheduled_end)
<x-email.item :label="__('messages.end_date')">{{ $campaign->scheduled_end->copy()->setTimezone($campaign->role?->timezone ?: config('app.timezone'))->translatedFormat('M j, Y') }}</x-email.item>
@endif
</x-email.details>
@endif

@if ($kind === 'declined' && $campaign?->approval_note)
<x-email.callout tone="warning" :title="__('messages.boost_partner_note_from', ['page' => $replace['page']])">{{ $campaign->approval_note }}</x-email.callout>
@endif

@if ($kind === 'approved' && $campaign && ! $campaign->isPaidCurrent() && $partner?->payment_instructions)
<x-email.callout :title="__('messages.boost_partner_how_to_pay', ['page' => $replace['page']])">{{ $partner->payment_instructions }}</x-email.callout>
@endif

@if ($kind === 'rejected' && $campaign?->meta_rejection_reason)
<x-email.callout tone="danger" :title="__('messages.boost_partner_reason')">{{ $campaign->meta_rejection_reason }}</x-email.callout>
@endif

@if ($kind === 'stopped' && $stopReason && $bodyKey !== 'messages.boost_partner_stop_code_withdrawn')
<x-email.callout :title="__('messages.boost_partner_reason')">{{ $stopReason }}</x-email.callout>
@endif

<x-email.button :href="$url">{{ __('messages.boost_partner_mail_'.$kind.'_button') }}</x-email.button>
</x-email.layout>
