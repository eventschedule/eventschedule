{{-- The operator telling an owner that the schedule's page looks like a private calendar
     (App\Mail\CalendarPrivacyMail). What was found is named by kind and never quoted: no code or
     link from the events is printed here. --}}
{{-- One block, never the one-line form, which a later block would swallow. --}}
@php
    $theme = \App\Utils\EmailTheme::owner($role);
    $done = $hidden > 0 || $paused;
@endphp
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

{{-- Only the steps that apply: choosing a calendar is for a calendar sync (CalDAV has no chooser,
     its connection is made again), checking hidden events only when some were hidden, turning the
     sync back on only when it was paused. --}}
@php
    $steps = [];
    if ($sync) {
        $steps[] = [__('messages.calendar_privacy_mail_step_choose_title'), __($sync === \App\Services\CalendarArrival::CALDAV ? 'messages.calendar_privacy_mail_step_choose_caldav' : 'messages.calendar_privacy_mail_step_choose'), null];
    }
    if ($hidden > 0 || ! $sync) {
        $steps[] = [__('messages.calendar_privacy_mail_step_check_title'), __('messages.calendar_privacy_mail_step_check'), $reviewUrl];
    }
    if ($paused) {
        $steps[] = [__('messages.calendar_privacy_mail_step_resume_title'), __('messages.calendar_privacy_mail_step_resume'), null];
    }
@endphp
@if ($steps)
<x-email.section :label="__('messages.calendar_privacy_mail_steps_title')" />
@foreach ($steps as $i => [$stepTitle, $stepText, $stepUrl])
<x-email.step :number="(string) ($i + 1)" :title="$stepTitle" :last="$loop->last">{{ $stepText }}@if ($stepUrl) <x-email.link :href="$stepUrl">{{ __('messages.calendar_privacy_mail_step_check_link') }}</x-email.link>@endif</x-email.step>
@endforeach
@endif

@if ($sync)
<x-email.button :href="$chooseUrl">{{ __('messages.calendar_privacy_mail_action') }}</x-email.button>
@else
<x-email.button :href="$reviewUrl">{{ __('messages.calendar_privacy_mail_step_check_link') }}</x-email.button>
@endif

@if ($codes)
<x-email.text>{{ __('messages.calendar_privacy_mail_codes_note') }}</x-email.text>
@endif

<x-email.text>{{ __('messages.calendar_privacy_mail_reply') }}</x-email.text>
</x-email.layout>
