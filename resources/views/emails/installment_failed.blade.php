@php
    $theme = \App\Utils\EmailTheme::guest($role ?? null);
    $heading = __('messages.installment_failed_heading');
    $body = __('messages.installment_failed_body', [
        'amount' => \App\Utils\MoneyUtils::format($installment?->amount ?? 0, $plan->currency),
        'card' => $cardLabel ?: __('messages.your_card'),
    ]);
    $emailSettings = $role ? $role->getEmailSettings() : [];
    $supportEmail = !empty($emailSettings['from_address']) ? $emailSettings['from_address'] : ($event?->user?->email ?? config('mail.from.address'));
@endphp
<x-email.layout :theme="$theme" :title="$heading" :preheader="$body">
@if ($event)
<x-email.heading :eyebrow="$heading" tone="warning" auto>{{ $event->name }}</x-email.heading>
@else
<x-email.heading>{{ $heading }}</x-email.heading>
@endif

<x-email.text>{{ __('messages.hello') }} {{ $sale?->name }},</x-email.text>
<x-email.text>{{ $body }}</x-email.text>

<x-email.callout tone="success">{{ __('messages.installment_ticket_still_valid') }}</x-email.callout>

@if ($installment?->next_attempt_at)
<x-email.text>{{ __('messages.installment_failed_retry', ['date' => $installment->next_attempt_at->translatedFormat('j M Y')]) }}</x-email.text>
@endif

@include('emails.partials.installment_schedule', ['plan' => $plan])

@if (empty($plan->id))
<x-email.button :note="__('messages.test_email_note')">{{ __('messages.pay_now') }}</x-email.button>
@else
<x-email.button :href="$payUrl">{{ __('messages.pay_now') }}</x-email.button>
@endif

<x-slot:footer>
<x-email.footer>{{ __('messages.event_support_contact') }}: <x-email.link :href="'mailto:'.$supportEmail" muted>{{ $supportEmail }}</x-email.link></x-email.footer>
</x-slot:footer>
</x-email.layout>
