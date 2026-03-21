@if ($isAdminReply)
You have a new reply from Event Schedule Support:
@else
New support message from {{ $senderName }}:
@endif

{{ $messageBody }}

@if ($isAdminReply)
Log in to reply: {{ $replyUrl }}
@else
View conversation: {{ $replyUrl }}
@endif

Thanks,
{{ config('app.name') }}
