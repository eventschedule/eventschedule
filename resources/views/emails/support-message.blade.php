<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@if ($isAdminReply) Reply from Support @else New Support Message @endif</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">
            @if ($isAdminReply && $isGuest)
                {{ $senderName }} replied
            @elseif ($isAdminReply)
                Reply from Support
            @elseif ($isGuest)
                New Website Chat
            @else
                New Support Message
            @endif
        </h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">
            @if ($isAdminReply && $isGuest)
                {{ $senderName }} from Event Schedule answered your question:
            @elseif ($isAdminReply)
                You have a new reply from Event Schedule Support:
            @elseif ($isGuest && $senderName)
                <strong>{{ $senderName }}</strong>, a website visitor, wrote:
            @elseif ($isGuest)
                A website visitor wrote:
            @else
                New support message from <strong>{{ $senderName }}</strong>:
            @endif
        </p>

        @foreach ($messageBodies as $messageBody)
            <div style="background-color: white; padding: 20px; border-radius: 8px; margin: {{ $loop->first ? '20px' : '12px' }} 0 {{ $loop->last ? '20px' : '0' }};">
                <p style="margin: 0; font-size: 14px; color: #333; white-space: pre-wrap;">{{ $messageBody }}</p>
            </div>
        @endforeach

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $replyUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                @if ($isAdminReply && $isGuest)
                    Continue the conversation
                @elseif ($isAdminReply)
                    Log in to reply
                @else
                    View conversation
                @endif
            </a>
        </div>

        @if ($isAdminReply)
            <p style="font-size: 14px; color: #666; text-align: center; margin: 0;">
                Or just reply to this email.
            </p>
        @endif

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            Thanks,<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
