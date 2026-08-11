{{ $kind === 'overdue' ? __('messages.installment_digest_overdue_heading') : __('messages.installment_digest_due_heading') }}

{{ $kind === 'overdue' ? __('messages.installment_digest_overdue_body') : __('messages.installment_digest_due_body') }}

@foreach ($rows as $row)
- {{ $row['name'] }} ({{ $row['event'] }}): {{ \App\Utils\MoneyUtils::format($row['amount'], $row['currency']) }} {{ $row['due_at'] }}
@endforeach

{{ \App\Utils\MoneyUtils::format($total, $currency ?? 'USD') }} {{ trans_choice('messages.installment_digest_across', count($rows), ['count' => count($rows)]) }}

{{ route('sales', ['tab' => 'installments']) }}
