@php($theme = \App\Utils\EmailTheme::owner($role ?? null))
<x-email.layout :theme="$theme" :title="__('messages.schedule_transfer_declined_subject', ['name' => $role?->name])" :preheader="__('messages.schedule_transfer_declined_intro', ['email' => $transfer->to_email, 'name' => $role?->name])">
<x-email.heading>{{ __('messages.schedule_transfer_declined_heading') }}</x-email.heading>

<x-email.text>{{ __('messages.schedule_transfer_declined_intro', ['email' => $transfer->to_email, 'name' => $role?->name]) }}</x-email.text>

<x-email.text>{{ __('messages.schedule_transfer_declined_nothing_changed') }}</x-email.text>

<x-email.button :href="$teamUrl">{{ __('messages.schedule_transfer_open_team') }}</x-email.button>
</x-email.layout>
