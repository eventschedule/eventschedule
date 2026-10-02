@php($theme = \App\Utils\EmailTheme::owner($role ?? null))
<x-email.layout :theme="$theme" :title="__('messages.new_fan_content_notification_subject', ['name' => $event->name, 'count' => $fanContentCount])" :preheader="$fanContentCount.' '.__('messages.pending_fan_content').' · '.$event->name">
<x-email.heading>{{ __('messages.pending_fan_content') }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }},</x-email.text>

<x-email.highlight :value="(string) $fanContentCount" :caption="__('messages.new_fan_content_caption', ['event' => $event->name])" />

<x-email.button :href="$actionUrl">{{ __('messages.view_details') }}</x-email.button>

<x-slot:footer>
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]" />
</x-slot:footer>
</x-email.layout>
