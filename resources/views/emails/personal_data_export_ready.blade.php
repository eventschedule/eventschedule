@php($theme = \App\Utils\EmailTheme::account())
<x-email.layout :theme="$theme" :title="__('messages.data_export_email_subject')" :preheader="__('messages.data_export_email_intro')">
<x-email.heading>{{ __('messages.data_export_email_subject') }}</x-email.heading>

<x-email.text>{{ __('messages.data_export_email_intro') }}</x-email.text>

<x-email.button :href="$downloadUrl" :note="__('messages.backup_download_expires', ['date' => $expiresAt->translatedFormat('F j, Y')])">{{ __('messages.backup_download_button') }}</x-email.button>

<x-email.callout tone="warning">{{ __('messages.data_export_email_warning') }}</x-email.callout>
</x-email.layout>
