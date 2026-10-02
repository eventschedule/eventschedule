@php($theme = \App\Utils\EmailTheme::owner($role ?? null))
<x-email.layout :theme="$theme" :title="__('messages.new_poll_options_notification_subject', ['name' => $role->name, 'count' => $optionCount])" :preheader="__('messages.new_poll_options_notification_subject', ['name' => $role->name, 'count' => $optionCount])">
<x-email.heading>{{ __('messages.pending_poll_options') }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }},</x-email.text>

<x-email.highlight :value="(string) $optionCount" :caption="trans_choice('messages.new_poll_options_caption', $optionCount, ['name' => $role->name])" />

<x-email.button :href="$actionUrl">{{ __('messages.view_details') }}</x-email.button>

<x-slot:footer>
@if (empty($notificationEmailUnsubscribeUrl))
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]" />
@endif
@include('emails.partials.notification_email_footer', ['scheduleName' => $role?->name])
</x-slot:footer>
</x-email.layout>
