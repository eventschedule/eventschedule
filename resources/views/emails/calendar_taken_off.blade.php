@php($theme = \App\Utils\EmailTheme::owner($role ?? null))
<x-email.layout :theme="$theme" :title="trans_choice('messages.calendar_taken_off_mail_subject', $count, ['count' => number_format($count), 'name' => $role->name])" :preheader="__('messages.calendar_taken_off_mail_preheader')">
<x-email.heading>{{ trans_choice('messages.calendar_taken_off_title', $count, ['count' => number_format($count)]) }}</x-email.heading>

<x-email.text>{{ __('messages.calendar_taken_off_body', ['provider' => $provider]) }}</x-email.text>

@if ($names)
<x-email.list :items="$names" />
@endif

<x-email.callout tone="info">{{ __('messages.calendar_taken_off_mail_nothing_deleted') }}</x-email.callout>

<x-email.button :href="$reviewUrl">{{ trans_choice('messages.calendar_see_events', $count, ['count' => number_format($count)]) }}</x-email.button>
</x-email.layout>
