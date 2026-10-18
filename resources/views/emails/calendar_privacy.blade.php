{{-- The operator telling an owner that the schedule's page looks like a private calendar
     (App\Mail\CalendarPrivacyMail). What was found is named by kind and never quoted: no code or
     link from the events is printed here. --}}
@php($theme = \App\Utils\EmailTheme::owner($role))
@php($done = $hidden > 0 || $paused)
<x-email.layout :theme="$theme" :title="__('messages.calendar_privacy_mail_subject', ['name' => $role->name])" :preheader="$done ? __('messages.calendar_privacy_mail_preheader_done') : __('messages.calendar_privacy_mail_preheader')">
<x-email.heading>{{ __('messages.calendar_privacy_mail_heading') }}</x-email.heading>

{{-- The greeting string carries its own comma ("Hi :name,", Arabic's "،"). --}}
<x-email.text>{{ __('messages.calendar_privacy_mail_greeting', ['name' => $owner->name ?: $owner->email]) }}</x-email.text>

<x-email.text>{{ __('messages.calendar_privacy_mail_intro', ['name' => $role->name]) }} <x-email.link :href="$pageUrl">{{ $pageUrl }}</x-email.link></x-email.text>

@if ($found)
<x-email.text>{{ __('messages.calendar_privacy_mail_found') }}</x-email.text>
<x-email.list :items="$found" />
@endif

@if ($done)
<x-email.callout tone="info" :title="__('messages.calendar_privacy_mail_done_title')">
@if ($hidden > 0){{ trans_choice('messages.calendar_privacy_mail_done_hidden', $hidden, ['count' => $hidden]) }} @endif
@if ($paused){{ __('messages.calendar_privacy_mail_done_paused') }} @endif
{{ __('messages.calendar_privacy_mail_done_safe') }}
</x-email.callout>
@endif

<x-email.section :label="__('messages.calendar_privacy_mail_steps_title')" />
<x-email.step number="1" :title="__('messages.calendar_privacy_mail_step_choose_title')">{{ __('messages.calendar_privacy_mail_step_choose') }}</x-email.step>
<x-email.step number="2" :title="__('messages.calendar_privacy_mail_step_check_title')">{{ __('messages.calendar_privacy_mail_step_check') }} <x-email.link :href="$reviewUrl">{{ __('messages.calendar_privacy_mail_step_check_link') }}</x-email.link></x-email.step>
<x-email.step number="3" :title="__('messages.calendar_privacy_mail_step_resume_title')" :last="true">{{ __('messages.calendar_privacy_mail_step_resume') }}</x-email.step>

<x-email.button :href="$chooseUrl">{{ __('messages.calendar_privacy_mail_action') }}</x-email.button>

<x-email.text>{{ __('messages.calendar_privacy_mail_codes_note') }}</x-email.text>

<x-email.text>{{ __('messages.calendar_privacy_mail_reply') }}</x-email.text>
</x-email.layout>
