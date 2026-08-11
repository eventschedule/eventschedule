{{-- Shared payment-schedule table for the installment emails. Expects $plan. --}}
@php
    $currency = $plan->currency;
@endphp
<div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
    <p style="margin: 0 0 12px 0; font-weight: bold; color: #4E81FA;">{{ __('messages.your_payment_schedule') }}</p>
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        @foreach ($plan->installments as $row)
            <tr>
                <td style="padding: 6px 0; color: #555;">{{ $row->due_at?->translatedFormat('j M Y') }}</td>
                <td style="padding: 6px 0; text-align: right; color: #333;">{{ \App\Utils\MoneyUtils::format($row->amount, $currency) }}</td>
                <td style="padding: 6px 0 6px 12px; text-align: right; color: {{ $row->status === 'paid' ? '#16a34a' : '#999' }};">
                    @if ($row->status === 'paid')
                        {{ __('messages.paid') }}
                    @elseif ($row->status === 'cancelled')
                        {{ __('messages.cancelled') }}
                    @else
                        {{ __('messages.scheduled') }}
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
    <p style="margin: 14px 0 0 0; padding-top: 12px; border-top: 1px solid #eee; font-size: 14px; color: #333;">
        <strong>{{ __('messages.total') }} {{ \App\Utils\MoneyUtils::format($plan->total_amount, $currency) }}.</strong>
        {{ __('messages.installments_no_interest_short') }}
    </p>
</div>
