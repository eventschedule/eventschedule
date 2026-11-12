{{ __('messages.data_export_email_subject') }}

{{ __('messages.data_export_email_intro') }}

{{ __('messages.backup_download_button') }}: {!! $downloadUrl !!}

{{ __('messages.backup_download_expires', ['date' => $expiresAt->localDate(true, false)]) }}

{{ __('messages.data_export_email_warning') }}
