{{ __('messages.hello') }} {{ $sale?->name }},

{{ __('messages.installment_reminder_body', ['date' => $installment?->due_at?->translatedFormat('j M Y'), 'amount' => \App\Utils\MoneyUtils::format($installment?->amount ?? 0, $plan->currency), 'card' => $cardLabel ?: __('messages.your_card'), 'number' => $installment?->sequence, 'count' => $plan->installment_count, 'event' => $event?->name]) }}

{{ __('messages.installment_reminder_nothing_to_do') }}

{{ __('messages.your_payment_schedule') }}:
@foreach ($plan->installments as $row)
- {{ $row->due_at?->translatedFormat('j M Y') }}: {{ \App\Utils\MoneyUtils::format($row->amount, $plan->currency) }}@if ($row->status === 'paid') ({{ __('messages.paid') }})@endif

@endforeach

{{ __('messages.total') }} {{ \App\Utils\MoneyUtils::format($plan->total_amount, $plan->currency) }}. {{ __('messages.installments_no_interest_short') }}

{{ $payUrl }}
