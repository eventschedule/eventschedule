<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.boost_email_completed_subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.boost_email_completed_subject') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $campaign->user?->name ?? '' }},</p>

        <p>{{ __('messages.boost_email_completed_body') }}</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <h2 style="margin-top: 0; color: #4E81FA;">{{ $event?->name ?? __('messages.deleted_event') }}</h2>
            <p style="margin: 10px 0;"><strong>{{ __('messages.impressions') }}:</strong> {{ number_format($campaign->impressions) }}</p>
            <p style="margin: 10px 0;"><strong>{{ __('messages.reach') }}:</strong> {{ number_format($campaign->reach) }}</p>
            <p style="margin: 10px 0;"><strong>{{ __('messages.clicks') }}:</strong> {{ number_format($campaign->clicks) }}</p>
            @if ($campaign->conversions > 0)
            <p style="margin: 10px 0;"><strong>{{ __('messages.conversions') }}:</strong> {{ $campaign->conversions }}</p>
            @endif
            <p style="margin: 10px 0;"><strong>{{ __('messages.amount_spent') }}:</strong> {{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->actual_spend, 2) }}</p>
        </div>

        @if ($refundAmount > 0)
        <div style="background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745;">
            <p style="margin: 0; color: #155724;"><strong>{{ __('messages.boost_unspent_refund') }}</strong></p>
            <p style="margin: 5px 0 0; color: #155724;">{{ __('messages.boost_refund_amount') }}: {{ $campaign->getCurrencySymbol() }}{{ number_format($refundAmount, 2) }}</p>
        </div>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $boostUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.view_results') }}
            </a>
        </div>

        <p style="font-size: 12px; color: #999;">{{ config('app.name') }}</p>
    </div>
</body>
</html>
