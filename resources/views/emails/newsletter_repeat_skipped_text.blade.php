{{ __('messages.newsletter_repeat_skipped_heading') }}

{{ __('messages.email_settings_failed_email_greeting', ['name' => $recipient->name ?? $recipient->email]) }}

{!! __('messages.newsletter_repeat_skipped_intro', ['subject' => $subjectLine, 'schedule' => $role->name, 'date' => $dueText]) !!}

{!! $reasonText !!}

{{ $nextText ? __('messages.newsletter_repeat_skipped_next', ['date' => $nextText]) : __('messages.newsletter_repeat_skipped_ended') }}

{{ __('messages.newsletter_repeat_skipped_action') }}: {{ $editUrl }}

{{ __('messages.thank_you_for_using') }}
