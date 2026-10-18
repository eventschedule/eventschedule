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
    $steps = [];
    if ($sync) {
        $steps[] = __('messages.calendar_privacy_mail_step_choose_title').': '.__($sync === \App\Services\CalendarArrival::CALDAV ? 'messages.calendar_privacy_mail_step_choose_caldav' : 'messages.calendar_privacy_mail_step_choose');
    }
    if ($hidden > 0 || ! $sync) {
        $steps[] = __('messages.calendar_privacy_mail_step_check_title').': '.__('messages.calendar_privacy_mail_step_check').' '.$reviewUrl;
    }
    if ($paused) {
        $steps[] = __('messages.calendar_privacy_mail_step_resume_title').': '.__('messages.calendar_privacy_mail_step_resume');
    }
@endphp
@if ($steps)
{{ __('messages.calendar_privacy_mail_steps_title') }}
@foreach ($steps as $i => $step)
{{ $i + 1 }}. {{ $step }}
@endforeach

@endif
@if ($sync)
{{ __('messages.calendar_privacy_mail_action') }}: {{ $chooseUrl }}

@endif
@if ($codes)
{{ __('messages.calendar_privacy_mail_codes_note') }}

@endif

{{ __('messages.calendar_privacy_mail_reply') }}
