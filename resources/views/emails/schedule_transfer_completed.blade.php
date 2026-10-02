@php
    $theme = \App\Utils\EmailTheme::owner($role ?? null);
    $subject = $forPreviousOwner ? __('messages.schedule_transfer_sent_subject', ['name' => $role?->name]) : __('messages.schedule_transfer_received_subject', ['name' => $role?->name]);
    $intro = $forPreviousOwner
        ? __('messages.schedule_transfer_sent_intro', ['name' => $role?->name, 'email' => $transfer->to_email])
        : __('messages.schedule_transfer_received_intro', ['name' => $role?->name]);
@endphp
<x-email.layout :theme="$theme" :title="$subject" :preheader="$intro">
<x-email.heading>{{ $forPreviousOwner ? __('messages.schedule_transfer_sent_heading') : __('messages.schedule_transfer_received_heading') }}</x-email.heading>

<x-email.text>{{ $intro }}</x-email.text>

<x-email.details panel>
<x-email.item :label="__('messages.schedule')" :caption="$role?->getGuestUrl(true)" wide>{{ $role?->name }}</x-email.item>
</x-email.details>

@if ($billingEnded)
<x-email.text variant="small">{{ __('messages.schedule_transfer_sent_billing') }}</x-email.text>
@elseif ($billingNoop)
<x-email.text variant="small">{{ __('messages.schedule_transfer_sent_no_billing') }}</x-email.text>
@endif

@if ($adminUrl)
<x-email.button :href="$adminUrl">{{ __('messages.schedule_transfer_open_schedule') }}</x-email.button>

@if (config('app.hosted'))
<x-email.text variant="small">{{ __('messages.schedule_transfer_received_billing') }}</x-email.text>
@endif

<x-email.text variant="small">{{ __('messages.schedule_transfer_received_payments') }}</x-email.text>
@endif
</x-email.layout>
