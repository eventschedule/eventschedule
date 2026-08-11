{{ __('messages.hello') }} {{ $sale?->name }},

{{ __('messages.installment_failed_body', ['amount' => \App\Utils\MoneyUtils::format($installment?->amount ?? 0, $plan->currency), 'card' => $cardLabel ?: __('messages.your_card')]) }}

{{ __('messages.installment_ticket_still_valid') }}
@if ($installment?->next_attempt_at)

{{ __('messages.installment_failed_retry', ['date' => $installment->next_attempt_at->translatedFormat('j M Y')]) }}
@endif

{{ __('messages.your_payment_schedule') }}:
@foreach ($plan->installments as $row)
- {{ $row->due_at?->translatedFormat('j M Y') }}: {{ \App\Utils\MoneyUtils::format($row->amount, $plan->currency) }}@if ($row->status === 'paid') ({{ __('messages.paid') }})@endif

@endforeach

{{ __('messages.total') }} {{ \App\Utils\MoneyUtils::format($plan->total_amount, $plan->currency) }}. {{ __('messages.installments_no_interest_short') }}

{{ $payUrl }}
