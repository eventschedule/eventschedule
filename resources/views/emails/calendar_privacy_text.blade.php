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
@php
    $steps = \App\Mail\CalendarPrivacyMail::steps($sync, $hidden, $paused, $chooseUrl, $reviewUrl, $scheduleUrl);
@endphp
@if ($steps)
{{ __('messages.calendar_privacy_mail_steps_title') }}
@foreach ($steps as $i => $step)
{{ $i + 1 }}. {{ $step['title'] }}: {{ $step['text'] }}@if ($step['url']) {{ $step['url'] }}@endif

@endforeach

@endif
@if ($codes)
{{ __('messages.calendar_privacy_mail_codes_note') }}

@endif

{{ __('messages.calendar_privacy_mail_reply') }}
