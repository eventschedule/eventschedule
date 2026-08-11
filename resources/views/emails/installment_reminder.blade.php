<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.installment_reminder_heading') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.installment_reminder_heading') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $sale?->name }},</p>

        <p>{{ __('messages.installment_reminder_body', [
            'date' => $installment?->due_at?->translatedFormat('j M Y'),
            'amount' => \App\Utils\MoneyUtils::format($installment?->amount ?? 0, $plan->currency),
            'card' => $cardLabel ?: __('messages.your_card'),
            'number' => $installment?->sequence,
            'count' => $plan->installment_count,
            'event' => $event?->name,
        ]) }}</p>

        <p style="color: #555;">{{ __('messages.installment_reminder_nothing_to_do') }}</p>

        @include('emails.partials.installment_schedule', ['plan' => $plan])

        <div style="text-align: center; margin: 30px 0;">
            @if(empty($plan->id))
                <span style="display: inline-block; background-color: #ccc; color: white; padding: 15px 30px; border-radius: 5px; font-weight: bold; font-size: 16px;">{{ __('messages.update_payment_card') }}</span>
                <p style="font-size: 12px; color: #999; margin-top: 8px;">{{ __('messages.test_email_note') }}</p>
            @else
                <a href="{{ $payUrl }}" style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">{{ __('messages.update_payment_card') }}</a>
            @endif
        </div>

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            @php
                $emailSettings = $role ? $role->getEmailSettings() : [];
                $supportEmail = !empty($emailSettings['from_address']) ? $emailSettings['from_address'] : ($event?->user?->email ?? config('mail.from.address'));
            @endphp
            {{ __('messages.event_support_contact') }}: <a href="mailto:{{ $supportEmail }}" style="color: #4E81FA;">{{ $supportEmail }}</a>
        </p>
    </div>
</body>
</html>
