{{-- Plain text, so {!! !!}: an escaped apostrophe would print as &#039;. --}}
@php($app = config('app.name'))
@if ($isAdminReply && $isGuest)
{!! __('messages.support_answered_intro', ['name' => $senderName, 'app' => $app]) !!}
@elseif ($isAdminReply)
{!! __('messages.support_new_reply_intro', ['app' => $app]) !!}
@elseif ($isGuest && $senderName)
{!! __('messages.support_visitor_named_intro', ['name' => $senderName]) !!}
@elseif ($isGuest)
{!! __('messages.support_visitor_intro') !!}
@else
{!! __('messages.support_new_message_intro', ['name' => $senderName]) !!}
@endif

@foreach ($messageBodies as $messageBody)
{!! $messageBody !!}

@endforeach
@if ($isAdminReply && $isGuest)
{!! __('messages.support_continue_conversation') !!}: {!! $replyUrl !!}

{!! __('messages.support_or_reply') !!}
@elseif ($isAdminReply)
{!! __('messages.support_log_in_to_reply') !!}: {!! $replyUrl !!}

{!! __('messages.support_or_reply') !!}
@else
{!! __('messages.support_view_conversation') !!}: {!! $replyUrl !!}
@endif

{!! __('messages.thanks') !!},
{!! $app !!}
