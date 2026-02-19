<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.boost_email_rejected_subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.boost_email_rejected_subject') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $campaign->user?->name ?? '' }},</p>

        <p>{{ __('messages.boost_email_rejected_body') }}</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;">
            <h2 style="margin-top: 0; color: #dc3545;">{{ $event?->name ?? __('messages.deleted_event') }}</h2>
            @if ($rejectionReason)
            <p style="margin: 10px 0;"><strong>{{ __('messages.reason') }}:</strong> {{ $rejectionReason }}</p>
            @endif
        </div>

        @if ($refunded)
        <div style="background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745;">
            <p style="margin: 0; color: #155724;"><strong>{{ __('messages.boost_full_refund_issued') }}</strong></p>
            <p style="margin: 5px 0 0; color: #155724;">{{ __('messages.boost_refund_amount') }}: {{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->total_charged, 2) }}</p>
        </div>
        @else
        <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <p style="margin: 0; color: #856404;"><strong>{{ __('messages.boost_refund_pending') }}</strong></p>
        </div>
        @endif

        <p>{{ __('messages.boost_try_again_suggestion') }}</p>

        <p style="font-size: 12px; color: #999;">{{ config('app.name') }}</p>
    </div>
</body>
</html>
