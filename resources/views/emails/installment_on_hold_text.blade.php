{{ __('messages.hello') }} {{ $sale?->name }},

{{ __('messages.installment_on_hold_body', ['amount' => \App\Utils\MoneyUtils::format($plan->amountRemaining(), $plan->currency), 'total' => \App\Utils\MoneyUtils::format($plan->total_amount, $plan->currency)]) }}

{{ __('messages.installment_on_hold_door') }}

{{ __('messages.your_payment_schedule') }}:
@foreach ($plan->installments as $row)
- {{ $row->due_at?->translatedFormat('j M Y') }}: {{ \App\Utils\MoneyUtils::format($row->amount, $plan->currency) }}@if ($row->status === 'paid') ({{ __('messages.paid') }})@endif

@endforeach

{{ __('messages.total') }} {{ \App\Utils\MoneyUtils::format($plan->total_amount, $plan->currency) }}. {{ __('messages.installments_no_interest_short') }}

{{ $payUrl }}
