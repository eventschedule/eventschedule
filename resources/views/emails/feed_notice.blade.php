@php($theme = \App\Utils\EmailTheme::owner($role ?? null))
<x-email.layout :theme="$theme" :title="$subject" :preheader="$subject">
<x-email.heading>{{ __('messages.feeds_mail_heading_'.$kind) }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }},</x-email.text>

<x-email.text>{{ $subject }}.</x-email.text>

@if ($kind === \App\Notifications\FeedNotification::REVIEW)
<x-email.text>{{ __('messages.feeds_mail_review_text') }}</x-email.text>
@elseif ($kind === \App\Notifications\FeedNotification::DECIDE)
{{-- The other three name the feed in their first sentence. This one is about an event, so
     which feed is said on its own line: a schedule may read several. --}}
<x-email.text>{{ __('messages.feeds_col_feed') }}: {{ $facts['feed'] }}</x-email.text>
<x-email.text>{{ trans_choice('messages.feeds_mail_decide_text', $facts['people'], ['count' => number_format($facts['people'])]) }}</x-email.text>
@if (($facts['more'] ?? 0) > 0)
<x-email.text>{{ trans_choice('messages.feeds_mail_decide_more', $facts['more'], ['count' => number_format($facts['more'])]) }}</x-email.text>
@endif
@elseif ($kind === \App\Notifications\FeedNotification::PAUSED)
<x-email.text>{{ __('messages.feeds_mail_paused_text') }}</x-email.text>
@else
<x-email.text>{{ __('messages.feeds_mail_failing_text') }}</x-email.text>
@endif

<x-email.button :href="$actionUrl">{{ __('messages.feeds_mail_button_'.$kind) }}</x-email.button>

<x-slot:footer>
@if (empty($notificationEmailUnsubscribeUrl))
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]" />
@endif
@include('emails.partials.notification_email_footer', ['scheduleName' => $role?->name])
</x-slot:footer>
</x-email.layout>
