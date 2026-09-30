@if ($isAdminReply && $isGuest)
{{ $senderName }} from Event Schedule answered your question:
@elseif ($isAdminReply)
You have a new reply from Event Schedule Support:
@elseif ($isGuest && $senderName)
{{ $senderName }}, a website visitor, wrote:
@elseif ($isGuest)
A website visitor wrote:
@else
New support message from {{ $senderName }}:
@endif

@foreach ($messageBodies as $messageBody)
{{ $messageBody }}

@endforeach
@if ($isAdminReply && $isGuest)
Continue the conversation: {{ $replyUrl }}

Or just reply to this email.
@elseif ($isAdminReply)
Log in to reply: {{ $replyUrl }}

Or just reply to this email.
@else
View conversation: {{ $replyUrl }}
@endif

Thanks,
{{ config('app.name') }}
