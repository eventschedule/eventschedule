<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'revenue'])

    {{-- What schedules have sold and what plans are billing. The order is "what needs a person
         first": the four totals, then whatever a payment left to settle (each panel is there only
         when it has a row, and AdminAlertService links to it by its id), then the trend, the
         subscriptions and the sales themselves. --}}
    @php
        $icons = \App\Utils\RealtimeIcons::PATHS;
        $figure = 'dashboard-stat-value text-3xl font-bold text-center text-gray-900 dark:text-white';
        $caption = 'mt-0.5 text-xs text-gray-500 dark:text-gray-400 text-center';
        // A signed amount is marked left-to-right, as on the dashboard: in a right-to-left
        // language the sign otherwise lands after the number.
        $plus = fn ($text) => new \Illuminate\Support\HtmlString('<span dir="ltr">+'.e($text).'</span>');
        $ago = fn ($date) => \Illuminate\Support\Carbon::parse($date)->diffForHumans();
    @endphp

    <div class="page-head">
        <p class="page-lead">{{ __('messages.admin_revenue_lead') }}</p>
        <div class="page-actions">
            @include('admin.partials._date-range-filter', ['range' => $range])
        </div>
    </div>

    <div class="page-shell page-stack">

        {{-- Approve and Refund answer with `success`, a key the layout does not toast and the
             page never printed: pressing either looked like nothing had happened. --}}
        <x-page-flash :keys="['success' => 'success']" />
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
            <x-admin-stat-tile :label="__('messages.total_revenue')" :icon="$icons['paid']"
                tint="bg-emerald-50 dark:bg-emerald-500/10" ink="text-emerald-500" glow="rgba(16, 185, 129, 0.15)">
                <div class="flex flex-col items-center">
                    <span class="{{ $figure }}">{{ plan_price($totalRevenue) }}</span>
                    <span class="{{ $caption }}">{{ $plus(plan_price($revenueInPeriod)) }} @lang('messages.in_period')</span>
                </div>
                {{-- Sales taken in another currency are never added to the figure above: each is
                     its own total, in its own currency. --}}
                @if ($otherRevenue)
                <x-slot:footer>
                    <span dir="ltr">{{ collect($otherRevenue)->map(fn ($total, $currency) => \App\Utils\MoneyUtils::format($total, $currency))->implode(' · ') }}</span>
                </x-slot:footer>
                @endif
            </x-admin-stat-tile>

            <x-admin-stat-tile :label="__('messages.total_sales')" :icon="$icons['order']"
                tint="bg-blue-50 dark:bg-blue-500/10" ink="text-blue-500" glow="rgba(59, 130, 246, 0.15)">
                <div class="flex flex-col items-center">
                    <span class="{{ $figure }}">{{ number_format($totalSales) }}</span>
                    <span class="{{ $caption }}">{{ $plus(number_format($salesInPeriod)) }} @lang('messages.in_period')</span>
                </div>
                {{-- Red only past the point where refunds are worth a look. --}}
                <x-slot:footer>
                    @lang('messages.refund_rate') <span dir="ltr" class="font-medium {{ $refundRate > 5 ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-gray-300' }}">{{ $refundRate }}%</span>
                </x-slot:footer>
            </x-admin-stat-tile>

            <x-admin-stat-tile :label="__('messages.pending_revenue')" :icon="$icons['trial']"
                tint="bg-amber-50 dark:bg-amber-500/10" ink="text-amber-500" glow="rgba(245, 158, 11, 0.15)">
                <div class="flex flex-col items-center">
                    <span class="{{ $figure }}">{{ plan_price($pendingRevenue) }}</span>
                    <span class="{{ $caption }}">{{ number_format($pendingSales) }} @lang('messages.pending_sales')</span>
                </div>
                @if ($otherPending)
                <x-slot:footer>
                    <span dir="ltr">{{ collect($otherPending)->map(fn ($total, $currency) => \App\Utils\MoneyUtils::format($total, $currency))->implode(' · ') }}</span>
                </x-slot:footer>
                @endif
            </x-admin-stat-tile>

            {{-- The label and its value stay together: BoostMarkupCurrencyTest reads the amount
                 from the HTML that follows this label. --}}
            <x-admin-stat-tile :label="__('messages.boost_markup_revenue')" :icon="$icons['campaign']"
                tint="bg-purple-50 dark:bg-purple-500/10" ink="text-purple-500" glow="rgba(168, 85, 247, 0.15)">
                <div class="flex flex-col items-center">
                    <span class="{{ $figure }}">{{ \App\Utils\MoneyUtils::format($boostMarkupTotal, $boostMarkupCurrency) }}</span>
                    <span class="{{ $caption }}">{{ $plus(\App\Utils\MoneyUtils::format($boostMarkupInPeriod, $boostMarkupCurrency)) }} @lang('messages.in_period')</span>
                </div>
            </x-admin-stat-tile>
        </div>

        {{-- Payments whose amount is not the order's total: a person approves or refunds each. --}}
        @if ($mismatchSales->count() > 0 || $mismatchBoosts->count() > 0)
        <section id="amount-mismatch" class="insight-attention">
            <x-page-notice tone="warn" :title="__('messages.amount_mismatch_sales')">{{ __('messages.amount_mismatch_help') }}</x-page-notice>

            @if ($mismatchSales->count() > 0)
            <div class="ap-card rounded-xl overflow-hidden page-scroll">
                <table class="page-table is-wide">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.event')</th>
                            <th scope="col">@lang('messages.customer')</th>
                            <th scope="col" class="c-num">@lang('messages.expected_amount')</th>
                            <th scope="col" class="c-num">@lang('messages.paid_amount')</th>
                            <th scope="col">@lang('messages.reference')</th>
                            <th scope="col">@lang('messages.date')</th>
                            <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mismatchSales as $sale)
                        <tr>
                            <td class="c-main c-strong"><bdi>{{ \Illuminate\Support\Str::limit($sale->event?->name ?? '-', 30) }}</bdi></td>
                            <td class="c-wrap"><bdi>{{ $sale->name }}</bdi><span class="c-sub" dir="ltr">{{ $sale->email }}</span></td>
                            <td class="c-num" data-label="{{ __('messages.expected_amount') }}">{{ \App\Utils\MoneyUtils::format($sale->calculateTotal(), $sale->event?->ticket_currency_code) }}</td>
                            <td class="c-num c-strong" data-label="{{ __('messages.paid_amount') }}">{{ \App\Utils\MoneyUtils::format($sale->payment_amount, $sale->event?->ticket_currency_code) }}</td>
                            <td class="c-mono c-quiet" title="{{ $sale->transaction_reference }}">{{ $sale->transaction_reference ? \Illuminate\Support\Str::limit($sale->transaction_reference, 15) : '' }}</td>
                            <td class="c-date" title="{{ $sale->created_at->format('Y-m-d H:i:s') }}">{{ $sale->created_at->diffForHumans() }}</td>
                            <td class="c-actions">
                                <form method="POST" action="{{ route('admin.sale.approve', $sale->id) }}">
                                    @csrf
                                    <button type="submit" class="event-link" data-confirm="{{ __('messages.approve_sale_confirm') }}">@lang('messages.approve_sale')</button>
                                </form>
                                {{-- Asked of the driver, because the row's transaction_reference being
                                     non-empty proves nothing: a hand-marked sale carries the translated
                                     manual_payment string, and Invoice Ninja carries an invoice id. Offering
                                     Refund on those only ever produced an error and left the sale parked. --}}
                                @php
                                    $mismatchDriver = payment_gateways()->get($sale->payment_method);
                                @endphp
                                @if ($mismatchDriver?->supportsRefunds() && $mismatchDriver->refundReferenceFor($sale) !== null)
                                <form method="POST" action="{{ route('admin.sale.refund', $sale->id) }}">
                                    @csrf
                                    {{-- Gateway-neutral: this control is driver-gated, and Stripe stopped being the only rail
                                         that can move money the moment PayPal shipped. --}}
                                    <button type="submit" class="event-link is-danger" data-confirm="{{ __('messages.refund_sale_confirm', ['gateway' => $mismatchDriver->label($sale->event?->user)]) }}">@lang('messages.refund_sale')</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if ($mismatchBoosts->count() > 0)
            <x-page-card flush :title="__('messages.amount_mismatch_boosts')">
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.event')</th>
                            <th scope="col">@lang('messages.user')</th>
                            <th scope="col" class="c-num">@lang('messages.paid_amount')</th>
                            <th scope="col">@lang('messages.date')</th>
                            <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mismatchBoosts as $campaign)
                        <tr>
                            <td class="c-main c-strong"><bdi>{{ \Illuminate\Support\Str::limit($campaign->event?->name ?? '-', 30) }}</bdi></td>
                            <td class="c-wrap"><bdi>{{ $campaign->user?->name }}</bdi><span class="c-sub" dir="ltr">{{ $campaign->user?->email }}</span></td>
                            <td class="c-num c-strong" data-label="{{ __('messages.paid_amount') }}">{{ \App\Utils\MoneyUtils::format($campaign->total_charged, $campaign->currency_code) }}</td>
                            <td class="c-date" title="{{ $campaign->created_at->format('Y-m-d H:i:s') }}">{{ $campaign->created_at->diffForHumans() }}</td>
                            <td class="c-actions">
                                <form method="POST" action="{{ route('admin.boost.approve', $campaign->id) }}">
                                    @csrf
                                    <button type="submit" class="event-link" data-confirm="{{ __('messages.approve_boost_confirm') }}">@lang('messages.approve_sale')</button>
                                </form>
                                <form method="POST" action="{{ route('admin.boost.refund', $campaign->id) }}">
                                    @csrf
                                    <button type="submit" class="event-link is-danger" data-confirm="{{ __('messages.refund_boost_confirm') }}">@lang('messages.refund_sale')</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-page-card>
            @endif
        </section>
        @endif

        {{-- Subscriptions still billing for a schedule that has been deleted --}}
        @if ($orphanedSubscriptions->count() > 0)
        <section id="orphaned-subscriptions" class="insight-attention">
            <x-page-notice tone="error" :title="__('messages.orphaned_subscriptions')">{{ __('messages.orphaned_subscriptions_help') }}</x-page-notice>

            <div class="ap-card rounded-xl overflow-hidden">
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.schedule')</th>
                            <th scope="col">Stripe ID</th>
                            <th scope="col">@lang('messages.status')</th>
                            <th scope="col">@lang('messages.date')</th>
                            <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orphanedSubscriptions as $subscription)
                        <tr>
                            {{-- Both null when the schedule row is gone entirely. --}}
                            <td class="c-main c-strong"><bdi>{{ \Illuminate\Support\Str::limit($subscription->role_name ?: $subscription->role_subdomain ?: '-', 30) }}</bdi></td>
                            <td class="c-mono c-wrap" dir="ltr">{{ $subscription->stripe_id }}</td>
                            <td><span class="event-status is-bad" dir="ltr">{{ $subscription->stripe_status }}</span></td>
                            <td class="c-date" title="{{ $subscription->created_at }}">{{ $ago($subscription->created_at) }}</td>
                            <td class="c-actions">
                                <form method="POST" action="{{ route('admin.subscriptions.cancel_orphaned', ['subscription' => \App\Utils\UrlUtils::encodeId($subscription->id)]) }}"
                                      data-confirm="{{ __('messages.cancel_orphaned_subscription_confirm', ['id' => $subscription->stripe_id]) }}">
                                    @csrf
                                    <button type="submit" class="event-link is-danger">@lang('messages.cancel_in_stripe')</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- Subscriptions on a price ID config no longer names --}}
        @if ($unrecognizedSubscriptions->count() > 0)
        <section id="unrecognized-subscriptions" class="insight-attention">
            <x-page-notice tone="error" :title="__('messages.unrecognized_subscriptions')">{{ __('messages.unrecognized_subscriptions_help') }}</x-page-notice>

            <div class="ap-card rounded-xl overflow-hidden">
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.schedule')</th>
                            <th scope="col">@lang('messages.price')</th>
                            <th scope="col">@lang('messages.status')</th>
                            <th scope="col">@lang('messages.date')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($unrecognizedSubscriptions as $subscription)
                        <tr>
                            {{-- Both are null for a subscription whose schedule was deleted; the
                                 leftJoin keeps that row deliberately, so it needs a placeholder. --}}
                            <td class="c-main c-strong"><bdi>{{ \Illuminate\Support\Str::limit($subscription->role_name ?: $subscription->role_subdomain ?: '-', 30) }}</bdi></td>
                            <td class="c-mono c-wrap" dir="ltr">{{ $subscription->stripe_price }}</td>
                            <td><span class="event-status is-bad" dir="ltr">{{ $subscription->stripe_status }}</span></td>
                            <td class="c-date" title="{{ $subscription->created_at }}">{{ $ago($subscription->created_at) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- Refund claims the gateway never confirmed.

             Its own panel rather than a column on the mismatch table above: nothing in the app
             will ever resolve one, because retrying a refund whose idempotency key has expired is
             how one refund becomes two. Someone settles it against the gateway's dashboard, using
             the reference below, and until they do the claim holds its amount against the sale's
             refundable balance. --}}
        @if ($unconfirmedRefunds->count() > 0)
        <section id="unconfirmed-refunds" class="insight-attention">
            <x-page-notice tone="error" :title="__('messages.unconfirmed_refunds')">{{ __('messages.unconfirmed_refunds_help') }}</x-page-notice>

            <div class="ap-card rounded-xl overflow-hidden page-scroll">
                <table class="page-table is-wide">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.event')</th>
                            <th scope="col" class="c-num">@lang('messages.amount')</th>
                            <th scope="col">@lang('messages.status')</th>
                            <th scope="col">@lang('messages.reference')</th>
                            <th scope="col">@lang('messages.error')</th>
                            <th scope="col">@lang('messages.date')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($unconfirmedRefunds as $refund)
                        <tr>
                            <td class="c-main c-strong"><bdi>{{ \Illuminate\Support\Str::limit($refund->sale?->event?->name ?? '-', 30) }}</bdi></td>
                            {{-- The refund's own snapshotted currency, not the event's: the event's
                                 can be edited after the fact and this is a historical figure. --}}
                            <td class="c-num c-strong" data-label="{{ __('messages.amount') }}">{{ \App\Utils\MoneyUtils::format($refund->amount, $refund->currency_code) }}</td>
                            <td><span class="event-status is-bad" dir="ltr">{{ $refund->status }}</span></td>
                            {{-- The gateway's refund id once we have one, otherwise the idempotency
                                 key, which is what an operator searches the dashboard for when the
                                 call's outcome was never reported back. --}}
                            <td class="c-mono c-quiet" dir="ltr" title="{{ $refund->gateway_refund_id ?: $refund->idempotency_key }}">{{ \Illuminate\Support\Str::limit($refund->gateway_refund_id ?: $refund->idempotency_key, 24) }}</td>
                            <td class="c-quiet c-wrap" title="{{ $refund->last_error }}">{{ $refund->last_error ? \Illuminate\Support\Str::limit($refund->last_error, 40) : '' }}</td>
                            <td class="c-date" title="{{ $refund->created_at->format('Y-m-d H:i:s') }}">{{ $refund->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- The line is one currency, the platform's, and says so when there is another in play. --}}
        <x-page-card :title="__('messages.revenue_trend')" :lead="($otherRevenue || $otherPending) ? $platformCurrency : null">
            <div class="h-64">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </x-page-card>

        {{-- One strip for the seven figures that were two rows of boxes, the first four of them
             coloured for no reason a reader could name. Past due is the only one that asks for
             anything, so it is the only one that changes colour. --}}
        @if (config('app.hosted'))
        <x-page-card flush :title="__('messages.subscription_health')">
            <div class="page-stats is-auto insight-strip">
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($activeSubscriptions) }}</div>
                    <div class="page-stat-label">@lang('messages.active_subscriptions')</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($trialingSubscriptions) }}</div>
                    <div class="page-stat-label">@lang('messages.trialing_subscriptions')</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value {{ $pastDueSubscriptions > 0 ? 'is-bad' : '' }}">{{ number_format($pastDueSubscriptions) }}</div>
                    <div class="page-stat-label">@lang('messages.past_due')</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($canceledSubscriptions) }}</div>
                    <div class="page-stat-label">@lang('messages.canceled_subscriptions')</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($rolesOnTrial) }}</div>
                    <div class="page-stat-label">@lang('messages.on_free_trial')</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($convertedFromTrial) }}</div>
                    <div class="page-stat-label">@lang('messages.converted_from_trial')</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($expiredTrialsNoSub) }}</div>
                    <div class="page-stat-label">@lang('messages.expired_trials_no_sub')</div>
                </div>
            </div>
        </x-page-card>
        @endif

        <x-page-card flush :title="__('messages.recent_sales')">
            @if ($recentSales->count() > 0)
            <div class="page-scroll">
                <table class="page-table is-wide is-hover">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.event')</th>
                            <th scope="col">@lang('messages.customer')</th>
                            <th scope="col" class="c-num">@lang('messages.amount')</th>
                            <th scope="col">@lang('messages.method')</th>
                            <th scope="col">@lang('messages.status')</th>
                            <th scope="col">@lang('messages.reference')</th>
                            <th scope="col">@lang('messages.date')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentSales as $sale)
                        <tr>
                            <td class="c-main c-strong" title="{{ $sale->event?->name }}">
                                <bdi>{{ \Illuminate\Support\Str::limit($sale->event?->name ?? '-', 30) }}</bdi>
                                <span class="c-sub"><a href="{{ route('role.view_guest', ['subdomain' => $sale->subdomain]) }}" class="event-link" target="_blank" rel="noopener" dir="ltr">{{ $sale->subdomain }}</a></span>
                            </td>
                            <td class="c-wrap"><bdi>{{ $sale->name }}</bdi></td>
                            <td class="c-num c-strong" data-label="{{ __('messages.amount') }}">{{ \App\Utils\MoneyUtils::format($sale->payment_amount, $sale->event?->ticket_currency_code) }}</td>
                            <td class="c-quiet">{{ $sale->payment_method }}</td>
                            <td>
                                {{-- The word the rest of the portal uses for the state. It was the raw
                                     column with a capital, which read "Amount_mismatch". --}}
                                @if (\Illuminate\Support\Facades\Lang::has('messages.'.$sale->status))
                                <x-sale-status :status="$sale->status" :class="$sale->status === 'amount_mismatch' ? 'is-warn' : ''" />
                                @else
                                <span class="event-status">{{ $sale->status }}</span>
                                @endif
                            </td>
                            <td class="c-mono c-quiet" dir="ltr" title="{{ $sale->transaction_reference }}">{{ $sale->transaction_reference ? \Illuminate\Support\Str::limit($sale->transaction_reference, 15) : '' }}</td>
                            <td class="c-date" title="{{ $sale->created_at->format('Y-m-d H:i:s') }}">{{ $sale->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <x-page-empty compact :title="__('messages.no_sales_yet')" />
            @endif
        </x-page-card>
    </div>

    <x-slot name="head">
        @include('admin.partials._insight-styles')
    </x-slot>

    {{-- Chart.js --}}
    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>

    <script {!! nonce_attr() !!}>
        // Matches the plan_price() totals in the cards above, so the axis cannot disagree
        // with them. JSON-encoded rather than interpolated, so a glyph is quoted safely.
        // (Do not write the directive name in this comment - Blade compiles it here too.)
        const REVENUE_CURRENCY_SYMBOL = @json(\App\Utils\PlatformCurrency::symbol());
        function initCharts() {
            if (typeof Chart === 'undefined') {
                setTimeout(initCharts, 50);
                return;
            }

            // The portal's own palette, read from its tokens. This used to ask the operating
            // system whether it was dark, so a light portal on a dark machine drew its charts
            // with black gridlines; and a hex could not follow the six palettes.
            const apStyle = getComputedStyle(document.documentElement);
            const apColor = (token, fallback) => {
                const value = apStyle.getPropertyValue(token).trim();
                return value ? 'rgb(' + value.split(/\s+/).join(', ') + ')' : fallback;
            };
            const isDarkMode = document.documentElement.classList.contains('dark');

            const textColor = apColor('--ap-ink-3', isDarkMode ? '#9CA3AF' : '#6B7280');
            const gridColor = apColor('--ap-border', isDarkMode ? '#2d2d30' : '#E5E7EB');

            // Revenue Trend Chart
            const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
            new Chart(revenueTrendCtx, {
                type: 'line',
                data: {
                    labels: @json($trendData['labels']),
                    datasets: [{
                        label: @json(__('messages.revenue')),
                        data: @json($trendData['revenue']),
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: textColor
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: textColor,
                                callback: function(value) {
                                    return REVENUE_CURRENCY_SYMBOL + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Initialize charts when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }
    </script>

</x-app-admin-layout>
