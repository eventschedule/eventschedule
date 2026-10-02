@php($theme = \App\Utils\EmailTheme::account())
<x-email.layout :theme="$theme" :title="__('messages.backup_export_email_subject')" :preheader="__('messages.backup_export_email_intro')">
<x-email.heading>{{ __('messages.backup_export_email_subject') }}</x-email.heading>

<x-email.text>{{ __('messages.backup_export_email_intro') }}</x-email.text>

<x-email.section :label="__('messages.backup_included_schedules')" />
<x-email.list :items="$scheduleNames" />

<x-email.button :href="$downloadUrl" :note="__('messages.backup_download_expires', ['date' => $expiresAt->translatedFormat('F j, Y')])">{{ __('messages.backup_download_button') }}</x-email.button>

<x-email.callout tone="warning">{{ __('messages.backup_pii_warning') }}</x-email.callout>
</x-email.layout>
