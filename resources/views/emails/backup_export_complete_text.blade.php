{{ __('messages.backup_export_email_subject') }}

{{ __('messages.backup_export_email_intro') }}

{{ __('messages.backup_included_schedules') }}:
@foreach ($scheduleNames as $name)
- {{ $name }}
@endforeach

{{ __('messages.backup_download_button') }}: {{ $downloadUrl }}

{{ __('messages.backup_download_expires', ['date' => $expiresAt->format('F j, Y')]) }}

{{ __('messages.backup_pii_warning') }}
