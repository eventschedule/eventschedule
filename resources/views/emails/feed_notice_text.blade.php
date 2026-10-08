{{ __('messages.feeds_mail_heading_'.$kind) }}

{{ __('messages.hello') }},

{!! $subject !!}.

@if ($kind === \App\Notifications\FeedNotification::REVIEW)
{!! __('messages.feeds_mail_review_text') !!}
@elseif ($kind === \App\Notifications\FeedNotification::DECIDE)
{!! __('messages.feeds_col_feed') !!}: {!! $facts['feed'] !!}

{!! trans_choice('messages.feeds_mail_decide_text', $facts['people'], ['count' => number_format($facts['people'])]) !!}
@if (($facts['more'] ?? 0) > 0)
{!! trans_choice('messages.feeds_mail_decide_more', $facts['more'], ['count' => number_format($facts['more'])]) !!}
@endif
@elseif ($kind === \App\Notifications\FeedNotification::PAUSED)
{!! __('messages.feeds_mail_paused_text') !!}
@else
{!! __('messages.feeds_mail_failing_text') !!}
@endif

{{ __('messages.feeds_mail_button_'.$kind) }}: {{ $actionUrl }}

{{ __('messages.thank_you_for_using') }}

@if (empty($notificationEmailUnsubscribeUrl))
{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
@endif
@include('emails.partials.notification_email_footer_text', ['scheduleName' => $role?->name])
