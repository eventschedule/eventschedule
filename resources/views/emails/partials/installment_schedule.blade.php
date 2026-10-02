{{-- Shared payment-schedule table for the installment emails. Expects $plan. Rendered inside an
     <x-email.layout>, whose theme the components read. --}}
@php
    $currency = $plan->currency;
    $scheduleRows = $plan->installments->map(fn ($row) => [
        $row->due_at?->translatedFormat('j M Y'),
        \App\Utils\MoneyUtils::format($row->amount, $currency),
        $row->status === 'paid'
            ? [__('messages.paid'), 'success']
            : ($row->status === 'cancelled' ? __('messages.cancelled') : __('messages.scheduled')),
    ])->all();
@endphp
<x-email.section :label="__('messages.your_payment_schedule')" />
<x-email.table :align="['start', 'end', 'end']" :rows="$scheduleRows" />
<x-email.text variant="small"><strong>{{ __('messages.total') }} {{ \App\Utils\MoneyUtils::format($plan->total_amount, $currency) }}.</strong> {{ __('messages.installments_no_interest_short') }}</x-email.text>
