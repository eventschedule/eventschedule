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
            @if ($isAdminReply)
                Reply from Support
            @else
                New Support Message
            @endif
        </h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">
            @if ($isAdminReply)
                You have a new reply from Event Schedule Support:
            @else
                New support message from <strong>{{ $senderName }}</strong>:
            @endif
        </p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <p style="margin: 0; font-size: 14px; color: #333;">{{ $messageBody }}</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $replyUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                @if ($isAdminReply)
                    Log in to reply
                @else
                    View conversation
                @endif
            </a>
        </div>

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            Thanks,<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
