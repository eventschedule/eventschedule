@php
    $theme = \App\Utils\EmailTheme::account();
    $status = $statement->status;
    $tone = match ($status) { 'paid' => 'success', 'failed', 'action_required' => 'warning', default => null };
@endphp
<x-email.layout :theme="$theme" :title="__('messages.boost_statement_mail_subject', ['month' => $month])" :preheader="__('messages.boost_statement_mail_'.$status)">
<x-email.heading :eyebrow="__('messages.boost_statement_mail_subject', ['month' => $month])" :tone="$tone" auto>{{ $money($statement->amountDue()) }}</x-email.heading>

<x-email.text>{{ __($statement->failure_code === 'no_card' ? 'messages.boost_statement_mail_no_card' : 'messages.boost_statement_mail_'.$status) }}</x-email.text>

<x-email.details panel>
@foreach ($statement->lines as $line)
@php($lineName = ($line->campaign?->event?->translatedName() ?? $line->campaign?->event_name ?? __('messages.deleted_event')).($line->period !== $statement->period ? ' ('.__('messages.boost_statement_correction', ['month' => $line->period]).')' : ''))
<x-email.item :label="$lineName">{{ __('messages.boost_statement_total_spend') }} {{ $money($line->spend) }} &middot; {{ __('messages.boost_statement_total_fee') }} {{ $money($line->fee + $line->adjustment) }}</x-email.item>
@endforeach
<x-email.item :label="__('messages.boost_statement_total_spend')">{{ $money($statement->total_spend) }}</x-email.item>
<x-email.item :label="__('messages.boost_statement_total_fee')">{{ $money($statement->total_fee) }}</x-email.item>
@if ((float) $statement->total_adjustments != 0)
<x-email.item :label="__('messages.boost_statement_adjustments')">{{ $money($statement->total_adjustments) }}</x-email.item>
@endif
@if ((float) $statement->carried_in != 0)
<x-email.item :label="__('messages.boost_statement_carried_in')">{{ $money($statement->carried_in) }}</x-email.item>
@endif
</x-email.details>

<x-email.button :href="$url">{{ __('messages.boost_statement_view') }}</x-email.button>
</x-email.layout>
