{{--
  Installments tab of ticket/sales. An action queue, not a ledger: overdue rows sort first (done in
  getInstallmentsData), and the summary leads with what has actually been collected.
--}}

@if ($installments->isEmpty())
<div class="ap-card rounded-xl">
    <x-page-empty :title="__('messages.no_installment_plans_yet')" :text="__('messages.no_installment_plans_yet_help')"
        icon="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
</div>
@else
{{-- Says out loud what the Sales tab cannot: those totals recognise the whole ticket at
     purchase, because the sale is `paid` from the first installment. Left only to the
     docs this is a guaranteed support ticket. --}}
<div class="page-head">
    <p class="page-lead">{{ __('messages.installments_revenue_note') }}</p>
</div>

<div class="page-stack">
    {{-- Grouped per currency. This page aggregates across every schedule the user owns, so a
         single summed number would be a lie the moment they run one event in EUR and another in
         GBP. Same treatment the gift-cards partial uses. --}}
    @foreach ($installmentTotals as $totals)
    @php $overdue = $installments->where('currency', $totals['currency'])->where('is_overdue', true)->count(); @endphp
    <div class="ap-card rounded-xl page-stats is-auto">
        <div class="page-stat">
            <div class="page-stat-value">{{ $totals['count'] }}</div>
            <div class="page-stat-label">{{ trans_choice('messages.installments_plan_count', $totals['count'], ['count' => $totals['count']]) }}</div>
        </div>
        <div class="page-stat">
            <div class="page-stat-value is-good">{{ \App\Utils\MoneyUtils::format($totals['collected'], $totals['currency']) }}</div>
            <div class="page-stat-label">{{ __('messages.installments_collected') }}</div>
        </div>
        <div class="page-stat">
            <div class="page-stat-value">{{ \App\Utils\MoneyUtils::format($totals['outstanding'], $totals['currency']) }}</div>
            <div class="page-stat-label">{{ __('messages.installments_outstanding') }}</div>
        </div>
        @if ($overdue > 0)
        <div class="page-stat">
            <div class="page-stat-value is-warn">{{ $overdue }}</div>
            <div class="page-stat-label">{{ __('messages.installments_overdue') }}</div>
        </div>
        @endif
    </div>
    @endforeach

    @if ($installmentForecast->isNotEmpty())
    <section class="ap-card rounded-xl page-card is-flush">
        <div class="page-card-head">
            <h2 class="page-card-title">{{ __('messages.installments_expected_by_month') }}</h2>
        </div>
        <div class="page-stats is-auto">
            @foreach ($installmentForecast as $month)
            <div class="page-stat">
                <div class="page-stat-value">{{ \App\Utils\MoneyUtils::format($month['amount'], $month['currency']) }}</div>
                <div class="page-stat-label">{{ $month['label'] }} <span class="c-quiet">({{ $month['count'] }})</span></div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    <div class="ap-card rounded-xl overflow-hidden">
        <div class="page-scroll">
            <table class="page-table is-wide">
                <thead>
                    <tr>
                        <th scope="col">{{ __('messages.name') }}</th>
                        <th scope="col">{{ __('messages.event') }}</th>
                        <th scope="col">{{ __('messages.payment_plan') }}</th>
                        <th scope="col" class="c-num">{{ __('messages.installments_collected') }}</th>
                        <th scope="col" class="c-num">{{ __('messages.installments_outstanding') }}</th>
                        <th scope="col">{{ __('messages.date') }}</th>
                        <th scope="col">{{ __('messages.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($installments as $row)
                    @php
                        $planHasReferences = $row['payments']->where('reference', '!=', null)->isNotEmpty();
                        $planMark = match ($row['status']) {
                            'completed' => ['messages.installment_status_completed', 'is-on'],
                            'overdue' => ['messages.installment_status_overdue', 'is-warn'],
                            'cancelled' => ['messages.installment_status_cancelled', ''],
                            default => ['messages.installment_status_active', 'is-info'],
                        };
                    @endphp
                    <tr>
                        <td class="c-main">
                            <div class="sales-customer">
                                {{-- Every payment reference, one per charge. Refunding the sale walks
                                     these, because the sale's single transaction_reference cannot identify
                                     N charges; they stay listed so an organizer can reconcile a leg by
                                     hand against their own dashboard. --}}
                                @if ($planHasReferences)
                                <button type="button" class="sales-open" data-toggle-row="plan-{{ $loop->index }}" aria-expanded="false">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                    <span class="sr-only">{{ __('messages.details') }}</span>
                                </button>
                                @endif
                                <div class="page-person-text">
                                    {{-- Buyer-supplied: x-user-text is the house guard for names
                                         on AP surfaces. --}}
                                    <span class="c-strong"><bdi><x-user-text>{{ $row['name'] }}</x-user-text></bdi></span>
                                    <span class="c-sub"><x-user-text dir="ltr" title="{{ $row['email'] }}">{{ $row['email'] }}</x-user-text></span>
                                </div>
                            </div>
                        </td>
                        <td class="c-wrap c-event"><bdi><x-user-text>{{ $row['event'] }}</x-user-text></bdi></td>
                        <td class="c-plan" data-label="{{ __('messages.payment_plan') }}">
                            {{ $row['progress'] }}
                            @if ($row['card'])
                            <span class="c-sub">
                                {{ $row['card'] }}
                                @if ($row['card_expiring'])
                                <span class="event-status is-warn">{{ __('messages.installment_card_expiring') }}</span>
                                @endif
                            </span>
                            @endif
                        </td>
                        <td class="c-num c-cap" data-label="{{ __('messages.installments_collected') }}">{{ \App\Utils\MoneyUtils::format($row['collected'], $row['currency']) }}</td>
                        <td class="c-num c-strong c-cap" data-label="{{ __('messages.installments_outstanding') }}">{{ \App\Utils\MoneyUtils::format($row['outstanding'], $row['currency']) }}</td>
                        <td class="c-due">
                            {{ $row['next_due'] ?? '' }}
                            @if ($row['error'])
                            {{-- Never the raw Stripe code: "waiting for the buyer to
                                 confirm with their bank" and "card declined" call for
                                 completely different responses from the organizer. --}}
                            <span class="c-sub"><span class="event-status is-warn">{{ $row['error'] }}</span></span>
                            @endif
                        </td>
                        <td><span class="event-status {{ $planMark[1] }}">{{ __($planMark[0]) }}</span></td>
                    </tr>

                    {{-- The two states nothing automatic will resolve. Money that arrived and
                         could not be applied is never auto-applied, and a charge with an
                         unknown outcome is never retried, because a retry after Stripe's
                         idempotency key expires is how a timeout becomes a double charge. Both
                         were being written and read by nobody, so the organizer had no way to
                         learn either had happened. --}}
                    @if ($row['unmatched'] || $row['needs_check'])
                    <tr class="sales-detail">
                        <td colspan="7" class="c-main">
                            <x-page-notice tone="warn">
                                @if ($row['unmatched'])
                                <p>{{ __('messages.installment_unmatched_notice', ['amount' => \App\Utils\MoneyUtils::format($row['unmatched'], $row['currency'])]) }}</p>
                                @endif
                                @if ($row['needs_check'])
                                <p>{{ __('messages.installment_needs_check_notice') }}</p>
                                @endif
                            </x-page-notice>
                        </td>
                    </tr>
                    @endif

                    @if ($planHasReferences)
                    <tr class="detail-row-plan-{{ $loop->index }} sales-detail hidden">
                        <td colspan="7" class="c-main">
                            <ul class="sales-lines sales-detail-body">
                                @foreach ($row['payments'] as $payment)
                                <li>
                                    <span>{{ $payment['due_at'] }}</span>
                                    <span>{{ \App\Utils\MoneyUtils::format($payment['amount'], $row['currency']) }}</span>
                                    {{-- Each state named. This read "Scheduled" for
                                         everything unpaid, so a failed, parked or
                                         cancelled payment was indistinguishable
                                         from one simply not due yet. --}}
                                    <span>{{ __(match ($payment['status']) {
                                        'paid' => 'messages.paid',
                                        'processing' => 'messages.installment_status_processing',
                                        'failed' => 'messages.installment_payment_failed',
                                        'cancelled' => 'messages.installment_status_cancelled',
                                        'awaiting_customer' => 'messages.installment_payment_awaiting_buyer',
                                        'awaiting_reconciliation' => 'messages.installment_error_reconcile',
                                        default => 'messages.scheduled',
                                    }) }}</span>
                                    @if ($payment['reference'])
                                    <span class="c-mono" dir="ltr">{{ $payment['reference'] }}</span>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
