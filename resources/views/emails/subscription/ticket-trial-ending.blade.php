@php($theme = \App\Utils\EmailTheme::owner($role ?? null))
<x-email.layout :theme="$theme" :title="__('messages.ticket_trial_ending_subject', ['schedule' => $role->name, 'date' => $endDate])" :preheader="__('messages.ticket_trial_ending_body', ['schedule' => $role->name, 'date' => $endDate])">
<x-email.heading>{{ __('messages.ticket_trial_ending_subject', ['schedule' => $role->name, 'date' => $endDate]) }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $role->user?->name ?? '' }},</x-email.text>

<x-email.text>{{ __('messages.ticket_trial_ending_body', ['schedule' => $role->name, 'date' => $endDate]) }}</x-email.text>

<x-email.callout tone="info">{{ __('messages.ticket_trial_ending_buyers') }}</x-email.callout>

<x-email.button :href="$planUrl">{{ __('messages.upgrade_to_pro_plan') }}</x-email.button>
</x-email.layout>
