@php
    $theme = \App\Utils\EmailTheme::account();
    $app = config('app.name');
    $title = $isAdminReply ? __('messages.support_reply_title') : __('messages.support_new_message_title');
    $heading = match (true) {
        $isAdminReply && $isGuest => __('messages.support_replied_heading', ['name' => $senderName]),
        $isAdminReply => __('messages.support_reply_title'),
        $isGuest => __('messages.support_new_chat_title'),
        default => __('messages.support_new_message_title'),
    };
    $cta = match (true) {
        $isAdminReply && $isGuest => __('messages.support_continue_conversation'),
        $isAdminReply => __('messages.support_log_in_to_reply'),
        default => __('messages.support_view_conversation'),
    };
@endphp
<x-email.layout :theme="$theme" :title="$title" :preheader="\Illuminate\Support\Str::limit((string) ($messageBodies[0] ?? ''), 140)">
<x-email.heading>{{ $heading }}</x-email.heading>

<x-email.text>
@if ($isAdminReply && $isGuest)
{{ __('messages.support_answered_intro', ['name' => $senderName, 'app' => $app]) }}
@elseif ($isAdminReply)
{{ __('messages.support_new_reply_intro', ['app' => $app]) }}
@elseif ($isGuest && $senderName)
{!! __('messages.support_visitor_named_intro', ['name' => '<strong>'.e($senderName).'</strong>']) !!}
@elseif ($isGuest)
{{ __('messages.support_visitor_intro') }}
@else
{!! __('messages.support_new_message_intro', ['name' => '<strong>'.e($senderName).'</strong>']) !!}
@endif
</x-email.text>

@foreach ($messageBodies as $messageBody)
<x-email.quote :text="$messageBody" />
@endforeach

<x-email.button :href="$replyUrl" :note="$isAdminReply ? __('messages.support_or_reply') : null">{{ $cta }}</x-email.button>

<x-email.text variant="small">{{ __('messages.thanks') }},<br>{{ $app }}</x-email.text>
</x-email.layout>
