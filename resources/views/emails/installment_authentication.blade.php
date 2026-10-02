@php
    $theme = \App\Utils\EmailTheme::guest($role ?? null);
    $heading = __('messages.installment_authentication_heading');
    $emailSettings = $role ? $role->getEmailSettings() : [];
    $supportEmail = !empty($emailSettings['from_address']) ? $emailSettings['from_address'] : ($event?->user?->email ?? config('mail.from.address'));
@endphp
<x-email.layout :theme="$theme" :title="$heading" :preheader="__('messages.installment_authentication_body')">
@if ($event)
<x-email.heading :eyebrow="$heading" auto>{{ $event->name }}</x-email.heading>
@else
<x-email.heading>{{ $heading }}</x-email.heading>
@endif

<x-email.text>{{ __('messages.hello') }} {{ $sale?->name }},</x-email.text>
<x-email.text>{{ __('messages.installment_authentication_body') }}</x-email.text>

<x-email.callout tone="success">{{ __('messages.installment_ticket_still_valid') }}</x-email.callout>

@include('emails.partials.installment_schedule', ['plan' => $plan])

@if (empty($plan->id))
<x-email.button :note="__('messages.test_email_note')">{{ __('messages.installment_confirm_payment') }}</x-email.button>
@else
<x-email.button :href="$payUrl">{{ __('messages.installment_confirm_payment') }}</x-email.button>
@endif

<x-slot:footer>
<x-email.footer>{{ __('messages.event_support_contact') }}: <x-email.link :href="'mailto:'.$supportEmail" muted>{{ $supportEmail }}</x-email.link></x-email.footer>
</x-slot:footer>
</x-email.layout>
