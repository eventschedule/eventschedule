@php($theme = \App\Utils\EmailTheme::owner($role ?? null))
<x-email.layout :theme="$theme" :title="__('messages.email_settings_failed_email_subject', ['schedule' => $role->name])" :preheader="__('messages.email_settings_failed_email_consequence')">
<x-email.heading>{{ __('messages.email_settings_failed_email_heading') }}</x-email.heading>

{{-- The greeting string carries its own comma ("Hi :name,", Arabic's "،"). --}}
<x-email.text>{{ __('messages.email_settings_failed_email_greeting', ['name' => $recipient->name ?? $recipient->email]) }}</x-email.text>

<x-email.text>{!! __('messages.email_settings_failed_email_intro', [
    'schedule' => '<strong>'.e($role->name).'</strong>',
    'date' => $failedAt ? $failedAt->isoFormat('LLL') : '',
]) !!}</x-email.text>

<x-email.callout tone="warning">{{ __('messages.email_settings_failed_email_consequence') }}</x-email.callout>

@if (! empty($errorMessage))
<x-email.code :value="$errorMessage" />
@endif

<x-email.button :href="$editUrl">{{ __('messages.email_settings_failed_email_action') }}</x-email.button>

<x-email.section :label="__('messages.email_settings_failed_email_common_causes_title')" />
<x-email.list :items="[
    __('messages.email_settings_failed_email_cause_password'),
    __('messages.email_settings_failed_email_cause_2fa'),
    __('messages.email_settings_failed_email_cause_disabled'),
]" />
</x-email.layout>
