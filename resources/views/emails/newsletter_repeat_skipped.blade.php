@php($theme = \App\Utils\EmailTheme::owner($role))
<x-email.layout :theme="$theme" :title="__('messages.newsletter_repeat_skipped_subject', ['schedule' => $role->name])" :preheader="$reasonText">
<x-email.heading tone="warning">{{ __('messages.newsletter_repeat_skipped_heading') }}</x-email.heading>

{{-- The greeting string carries its own comma ("Hi :name,", Arabic's "،"). --}}
<x-email.text>{{ __('messages.email_settings_failed_email_greeting', ['name' => $recipient->name ?? $recipient->email]) }}</x-email.text>

<x-email.text>{{ __('messages.newsletter_repeat_skipped_intro', ['subject' => $subjectLine, 'schedule' => $role->name, 'date' => $dueText]) }}</x-email.text>

<x-email.callout tone="warning">{{ $reasonText }}</x-email.callout>

<x-email.text>{{ $nextText ? __('messages.newsletter_repeat_skipped_next', ['date' => $nextText]) : __('messages.newsletter_repeat_skipped_ended') }}</x-email.text>

<x-email.button :href="$editUrl">{{ __('messages.newsletter_repeat_skipped_action') }}</x-email.button>
</x-email.layout>
