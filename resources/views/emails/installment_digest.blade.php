<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $kind === 'overdue' ? __('messages.installment_digest_overdue_heading') : __('messages.installment_digest_due_heading') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: {{ $kind === 'overdue' ? '#d97706' : '#4E81FA' }}; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">
            {{ $kind === 'overdue' ? __('messages.installment_digest_overdue_heading') : __('messages.installment_digest_due_heading') }}
        </h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">
            {{ $kind === 'overdue' ? __('messages.installment_digest_overdue_body') : __('messages.installment_digest_due_body') }}
        </p>

        <div style="background-color: white; padding: 16px; border-radius: 8px; margin: 20px 0;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <tr style="border-bottom: 1px solid #eee;">
                    <th style="text-align: left; padding: 8px 0; color: #666;">{{ __('messages.name') }}</th>
                    <th style="text-align: left; padding: 8px 0; color: #666;">{{ __('messages.event') }}</th>
                    <th style="text-align: right; padding: 8px 0; color: #666;">{{ __('messages.amount') }}</th>
                    <th style="text-align: right; padding: 8px 0; color: #666;">{{ __('messages.date') }}</th>
                </tr>
                @foreach ($rows as $row)
                    <tr style="border-bottom: 1px solid #f5f5f5;">
                        {{-- Buyer-supplied. Escaped by Blade, and this is an email so there is no
                             Vue mount to worry about. --}}
                        <td style="padding: 8px 0;">{{ $row['name'] }}</td>
                        <td style="padding: 8px 0; color: #555;">{{ $row['event'] }}</td>
                        <td style="padding: 8px 0; text-align: right;">{{ \App\Utils\MoneyUtils::format($row['amount'], $row['currency']) }}</td>
                        <td style="padding: 8px 0; text-align: right; color: #555;">{{ $row['due_at'] }}</td>
                    </tr>
                @endforeach
            </table>

            <p style="margin: 14px 0 0 0; padding-top: 12px; border-top: 1px solid #eee; font-size: 14px;">
                <strong>{{ \App\Utils\MoneyUtils::format($total, $currency ?? 'USD') }}</strong>
                {{ trans_choice('messages.installment_digest_across', count($rows), ['count' => count($rows)]) }}
            </p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('sales', ['tab' => 'installments']) }}"
               style="display: inline-block; background-color: {{ $kind === 'overdue' ? '#d97706' : '#4E81FA' }}; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.installment_digest_view_tab') }}
            </a>
        </div>
    </div>
</body>
</html>
