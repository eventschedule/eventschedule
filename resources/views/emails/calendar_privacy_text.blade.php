{{ __('messages.calendar_privacy_mail_heading') }}

{{ __('messages.calendar_privacy_mail_greeting', ['name' => $owner->name ?: $owner->email]) }}

{{ __('messages.calendar_privacy_mail_intro', ['name' => $role->name]) }} {{ $pageUrl }}

@if ($found)
{{ __('messages.calendar_privacy_mail_found') }}
@foreach ($found as $line)
- {{ $line }}
@endforeach

@endif
@if ($hidden > 0 || $paused)
{{ __('messages.calendar_privacy_mail_done_title') }}
@if ($hidden > 0)
{{ trans_choice('messages.calendar_privacy_mail_done_hidden', $hidden, ['count' => $hidden]) }}
@endif
@if ($paused)
{{ __('messages.calendar_privacy_mail_done_paused') }}
@endif
{{ __('messages.calendar_privacy_mail_done_safe') }}

@endif
{{ __('messages.calendar_privacy_mail_steps_title') }}
1. {{ __('messages.calendar_privacy_mail_step_choose_title') }}: {{ __('messages.calendar_privacy_mail_step_choose') }}
2. {{ __('messages.calendar_privacy_mail_step_check_title') }}: {{ __('messages.calendar_privacy_mail_step_check') }} {{ $reviewUrl }}
3. {{ __('messages.calendar_privacy_mail_step_resume_title') }}: {{ __('messages.calendar_privacy_mail_step_resume') }}

{{ __('messages.calendar_privacy_mail_action') }}: {{ $chooseUrl }}

{{ __('messages.calendar_privacy_mail_codes_note') }}

{{ __('messages.calendar_privacy_mail_reply') }}
