<x-mail::message>
@if ($isAdminReply)
You have a new reply from Event Schedule Support:
@else
New support message from **{{ $senderName }}**:
@endif

<x-mail::panel>
{{ $messageBody }}
</x-mail::panel>

@if ($isAdminReply)
<x-mail::button :url="$replyUrl">
Log in to reply
</x-mail::button>
@else
<x-mail::button :url="$replyUrl">
View conversation
</x-mail::button>
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
