{{ __('messages.email_settings_failed_email_heading') }}

{{ __('messages.email_settings_failed_email_greeting', ['name' => $recipient->name ?? $recipient->email]) }},

{{ __('messages.email_settings_failed_email_intro', [
    'schedule' => $role->name,
    'date' => $failedAt ? $failedAt->isoFormat('LLL') : '',
]) }}

{{ __('messages.email_settings_failed_email_consequence') }}

@if (!empty($errorMessage))
{{ $errorMessage }}

@endif
{{ __('messages.email_settings_failed_email_action') }}: {{ $editUrl }}

{{ __('messages.email_settings_failed_email_common_causes_title') }}
- {{ __('messages.email_settings_failed_email_cause_password') }}
- {{ __('messages.email_settings_failed_email_cause_2fa') }}
- {{ __('messages.email_settings_failed_email_cause_disabled') }}

{{ __('messages.thank_you_for_using') }}
