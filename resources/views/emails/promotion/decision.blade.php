<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $approved ? __('messages.promotion_email_approved_subject') : __('messages.promotion_email_rejected_subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: {{ $approved ? '#28a745' : '#dc3545' }}; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">
            {{ $approved ? __('messages.promotion_email_approved_subject') : __('messages.promotion_email_rejected_subject') }}
        </h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $campaign->user?->name ?? '' }},</p>

        <p>{{ $approved ? __('messages.promotion_email_approved_body') : __('messages.promotion_email_rejected_body') }}</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid {{ $approved ? '#28a745' : '#dc3545' }};">
            <h2 style="margin-top: 0; color: {{ $approved ? '#28a745' : '#dc3545' }};">{{ $event?->name ?? __('messages.deleted_event') }}</h2>
            @if (! $approved && $notes)
            <p style="margin: 10px 0;"><strong>{{ __('messages.reason') }}:</strong> {{ $notes }}</p>
            @endif
        </div>

        @if (! $approved)
        <div style="background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745;">
            <p style="margin: 0; color: #155724;"><strong>{{ __('messages.boost_full_refund_issued') }}</strong></p>
        </div>
        @endif

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" style="background-color: #4E81FA; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
                {{ __('messages.view_campaign') }}
            </a>
        </p>
    </div>
</body>
</html>
