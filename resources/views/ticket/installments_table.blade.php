{{--
  Installments tab. An action queue, not a ledger: overdue rows sort first (done in
  getInstallmentsData), and the summary leads with what has actually been collected.
--}}

@if ($installments->isEmpty())
    <div class="ap-card rounded-xl p-6 text-center">
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.no_installment_plans_yet') }}</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-xl mx-auto">{{ __('messages.no_installment_plans_yet_help') }}</p>
    </div>
@else
    {{-- Grouped per currency. This page aggregates across every schedule the user owns, so a
         single summed number would be a lie the moment they run one event in EUR and another in
         GBP. Same treatment the gift-cards partial uses. --}}
    <div class="ap-card rounded-xl p-6 mb-4">
        @foreach ($installmentTotals as $totals)
            <div class="flex flex-wrap items-baseline gap-x-6 gap-y-2 {{ ! $loop->first ? 'mt-3 pt-3 border-t border-gray-200 dark:border-gray-700' : '' }}">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $totals['count'] }}</span>
                    {{ trans_choice('messages.installments_plan_count', $totals['count'], ['count' => $totals['count']]) }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ \App\Utils\MoneyUtils::format($totals['collected'], $totals['currency']) }}</span>
                    {{ __('messages.installments_collected') }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ \App\Utils\MoneyUtils::format($totals['outstanding'], $totals['currency']) }}</span>
                    {{ __('messages.installments_outstanding') }}
                </span>
                @php $overdue = $installments->where('currency', $totals['currency'])->where('is_overdue', true)->count(); @endphp
                @if ($overdue > 0)
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        <span class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $overdue }}</span>
                        {{ __('messages.installments_overdue') }}
                    </span>
                @endif
            </div>
        @endforeach

        {{-- Says out loud what the Sales tab cannot: those totals recognise the whole ticket at
             purchase, because the sale is `paid` from the first installment. Left only to the
             docs this is a guaranteed support ticket. --}}
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
            {{ __('messages.installments_revenue_note') }}
        </p>
    </div>

    @if ($installmentForecast->isNotEmpty())
        <div class="ap-card rounded-xl p-6 mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.installments_expected_by_month') }}</h3>
            <div class="flex flex-wrap gap-x-6 gap-y-2">
                @foreach ($installmentForecast as $month)
                    <div class="text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ $month['label'] }}</span>
                        <span class="font-semibold text-gray-900 dark:text-gray-100 ms-2">{{ \App\Utils\MoneyUtils::format($month['amount'], $month['currency']) }}</span>
                        <span class="text-gray-400 dark:text-gray-500 ms-1">({{ $month['count'] }})</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="ap-card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('messages.name') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('messages.event') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('messages.payment_plan') }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('messages.installments_collected') }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('messages.installments_outstanding') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('messages.date') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('messages.status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($installments as $row)
                        <tr class="{{ $row['is_overdue'] ? 'bg-amber-50/50 dark:bg-amber-900/10' : '' }}">
                            <td class="px-4 py-3 text-sm">
                                {{-- Buyer-supplied, and this page is a Vue-free Blade view, but
                                     <x-user-text> is the house guard for names on AP surfaces. --}}
                                <div class="font-medium text-gray-900 dark:text-gray-100"><x-user-text>{{ $row['name'] }}</x-user-text></div>
                                <div class="text-gray-500 dark:text-gray-400"><x-user-text>{{ $row['email'] }}</x-user-text></div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300"><x-user-text>{{ $row['event'] }}</x-user-text></td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                {{ $row['progress'] }}
                                @if ($row['card'])
                                    <div class="text-xs {{ $row['card_expiring'] ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400 dark:text-gray-500' }}">
                                        {{ $row['card'] }}
                                        @if ($row['card_expiring'])
                                            <span class="block">{{ __('messages.installment_card_expiring') }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-end text-gray-900 dark:text-gray-100">{{ \App\Utils\MoneyUtils::format($row['collected'], $row['currency']) }}</td>
                            <td class="px-4 py-3 text-sm text-end font-medium text-gray-900 dark:text-gray-100">{{ \App\Utils\MoneyUtils::format($row['outstanding'], $row['currency']) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                {{ $row['next_due'] ?? '' }}
                                @if ($row['error'])
                                    {{-- Never the raw Stripe code: "waiting for the buyer to
                                         confirm with their bank" and "card declined" call for
                                         completely different responses from the organizer. --}}
                                    <div class="text-xs text-amber-600 dark:text-amber-400">{{ $row['error'] }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $chip = match ($row['status']) {
                                        'completed' => ['messages.installment_status_completed', 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'],
                                        'overdue' => ['messages.installment_status_overdue', 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'],
                                        'cancelled' => ['messages.installment_status_cancelled', 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
                                        default => ['messages.installment_status_active', 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'],
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $chip[1] }}">{{ __($chip[0]) }}</span>
                            </td>
                        </tr>

                        {{-- Every payment reference, so the organizer can find the charges to
                             refund on their own Stripe dashboard. Nothing in this app refunds a
                             Connect ticket sale, and the sale's single transaction_reference
                             cannot identify N charges. --}}
                        @if ($row['payments']->where('reference', '!=', null)->isNotEmpty())
                            <tr class="bg-gray-50 dark:bg-[#252526]">
                                <td colspan="7" class="px-4 py-2">
                                    <details>
                                        <summary class="text-xs text-gray-500 dark:text-gray-400 cursor-pointer">{{ __('messages.details') }}</summary>
                                        <div class="mt-2 space-y-1">
                                            @foreach ($row['payments'] as $payment)
                                                <div class="text-xs text-gray-600 dark:text-gray-400 flex flex-wrap gap-x-4">
                                                    <span>{{ $payment['due_at'] }}</span>
                                                    <span>{{ \App\Utils\MoneyUtils::format($payment['amount'], $row['currency']) }}</span>
                                                    <span>{{ $payment['status'] === 'paid' ? __('messages.paid') : __('messages.scheduled') }}</span>
                                                    @if ($payment['reference'])
                                                        <span class="font-mono">{{ $payment['reference'] }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
