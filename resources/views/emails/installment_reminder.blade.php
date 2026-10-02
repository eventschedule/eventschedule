@php
    $theme = \App\Utils\EmailTheme::guest($role ?? null);
    $heading = __('messages.installment_reminder_heading');
    $body = __('messages.installment_reminder_body', [
        'date' => $installment?->due_at?->translatedFormat('j M Y'),
        'amount' => \App\Utils\MoneyUtils::format($installment?->amount ?? 0, $plan->currency),
        'card' => $cardLabel ?: __('messages.your_card'),
        'number' => $installment?->sequence,
        'count' => $plan->installment_count,
        'event' => $event?->name,
    ]);
    $emailSettings = $role ? $role->getEmailSettings() : [];
    $supportEmail = !empty($emailSettings['from_address']) ? $emailSettings['from_address'] : ($event?->user?->email ?? config('mail.from.address'));
@endphp
<x-email.layout :theme="$theme" :title="$heading" :preheader="$body">
@if ($event)
<x-email.heading :eyebrow="$heading" auto>{{ $event->name }}</x-email.heading>
@else
<x-email.heading>{{ $heading }}</x-email.heading>
@endif

<x-email.text>{{ __('messages.hello') }} {{ $sale?->name }},</x-email.text>
<x-email.text>{{ $body }}</x-email.text>
<x-email.text variant="small">{{ __('messages.installment_reminder_nothing_to_do') }}</x-email.text>

@include('emails.partials.installment_schedule', ['plan' => $plan])

@if (empty($plan->id))
<x-email.button :note="__('messages.test_email_note')">{{ __('messages.update_payment_card') }}</x-email.button>
@else
<x-email.button :href="$payUrl">{{ __('messages.update_payment_card') }}</x-email.button>
@endif

<x-slot:footer>
<x-email.footer>{{ __('messages.event_support_contact') }}: <x-email.link :href="'mailto:'.$supportEmail" muted>{{ $supportEmail }}</x-email.link></x-email.footer>
</x-slot:footer>
</x-email.layout>
