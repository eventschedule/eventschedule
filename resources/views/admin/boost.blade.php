<x-app-admin-layout>
    <x-slot name="head">
        {{-- The one select in a card's head, at the size of the list it narrows: the layout gives
             every select 0.75rem by 1rem of padding and a 1.15rem type with !important. --}}
        <style {!! nonce_attr() !!}>
            .boost-status-filter {
              padding-block: 0.4rem !important;
              padding-inline: 0.75rem 2.25rem !important;
              font-size: 0.875rem !important;
              line-height: 1.25rem !important;
            }
            /* The chart beside the list of who boosts most: the list needs the wider half. */
            .boost-split {
              display: grid;
              grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
              gap: 1rem;
            }
            @media (max-width: 1023.98px) {
              .boost-split {
                grid-template-columns: minmax(0, 1fr);
              }
            }
            /* A list of figures with many columns: tighter cells, so it fits a laptop without
               scrolling, and a first column in line with the card's title above it. */
            @media (min-width: 640px) {
              .page-table.is-dense th,
              .page-table.is-dense td {
                padding-inline: 0.625rem;
              }
              .page-table.is-dense th:first-child,
              .page-table.is-dense td:first-child {
                padding-inline-start: 1.25rem;
              }
              .page-table.is-dense th:last-child,
              .page-table.is-dense td:last-child {
                padding-inline-end: 1.25rem;
              }
              /* Auto layout gave the two columns of names what was left over, which was the
                 width of a word: an address broke in the middle of "test". */
              .boost-campaigns th:first-child {
                width: 28%;
              }
              .boost-campaigns th:nth-child(2) {
                width: 17%;
              }
            }
        </style>
    </x-slot>

    @php
        // Every figure on this page is Meta-boost money, billed in the ad account's
        // currency. It used to render a literal '$' regardless.
        $boostCurrency = config('services.meta.default_currency', 'USD');
        $boostSymbol = \App\Utils\MoneyUtils::symbol($boostCurrency);

        // Markup, refunds, credit, spending limits and billing records are hosted only. A plain
        // selfhost boosts on the operator's own Meta account: it charges no markup and no fee,
        // and Role::getBoostMaxBudget() never reads a per-schedule limit off hosted.
        $hosted = (bool) config('app.hosted');

        // A campaign's state as the tone its mark wears (a dot and a word, not a coloured pill).
        $statusTones = [
            'active' => 'is-on',
            'paused' => 'is-warn',
            'pending_payment' => 'is-warn',
            'pending_review' => 'is-warn',
            'completed' => 'is-info',
            'failed' => 'is-bad',
            'rejected' => 'is-bad',
        ];
        $statusWord = fn ($status) => \Illuminate\Support\Facades\Lang::has('messages.boost_status_'.$status)
            ? __('messages.boost_status_'.$status)
            : ucfirst(str_replace('_', ' ', (string) $status));
        $field = 'rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]';
    @endphp

    @include('admin.partials._navigation', ['active' => 'boost'])

    <div class="page-head">
        <p class="page-lead">{{ $hosted ? __('messages.admin_boost_lead') : __('messages.admin_boost_lead_selfhost') }}</p>
        <div class="page-actions">
            @include('admin.partials._date-range-filter', ['range' => $range])
        </div>
    </div>

    <div class="page-shell page-stack">
        {{-- What a form on this page answered. Both forms post here, and the answer used to sit
             inside the first of them whichever one had been sent. What a form got wrong is
             listed by layouts/app-admin, above the page. --}}
        <x-page-flash :keys="['success' => 'success']" />

        {{-- Campaigns and money, then the rates: two strips where there were nine boxes. --}}
        <div class="ap-card rounded-xl page-stats is-auto">
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($totalCampaignsAllTime) }}</div>
                <div class="page-stat-label">@lang('messages.total_campaigns')</div>
                <div class="page-stat-sub">{{ number_format($totalCampaignsInPeriod) }} @lang('messages.in_period')</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value {{ $activeCampaigns > 0 ? 'is-good' : '' }}">{{ number_format($activeCampaigns) }}</div>
                <div class="page-stat-label">@lang('messages.active_campaigns')</div>
            </div>
            @if ($hosted)
            <div class="page-stat">
                <div class="page-stat-value">{{ \App\Utils\MoneyUtils::format($markupRevenue, $markupCurrency) }}</div>
                <div class="page-stat-label">@lang('messages.markup_revenue')</div>
            </div>
            @endif
            <div class="page-stat">
                <div class="page-stat-value">{{ \App\Utils\MoneyUtils::format($totalAdSpend, $boostCurrency) }}</div>
                <div class="page-stat-label">@lang('messages.total_ad_spend')</div>
            </div>
            @if ($hosted)
            <div class="page-stat">
                <div class="page-stat-value {{ $totalRefunds > 0 ? 'is-bad' : '' }}">{{ \App\Utils\MoneyUtils::format($totalRefunds, $boostCurrency) }}</div>
                <div class="page-stat-label">@lang('messages.total_refunds')</div>
            </div>
            @endif
        </div>

        <div class="ap-card rounded-xl page-stats is-auto">
            <div class="page-stat">
                <div class="page-stat-value"><span dir="ltr">{{ number_format($avgCtr, 2) }}%</span></div>
                <div class="page-stat-label">@lang('messages.avg_ctr')</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value">{{ \App\Utils\MoneyUtils::format($avgCpc, $boostCurrency) }}</div>
                <div class="page-stat-label">@lang('messages.avg_cpc')</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value">{{ \App\Utils\MoneyUtils::format($avgCpm, $boostCurrency) }}</div>
                <div class="page-stat-label">@lang('messages.avg_cpm')</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value {{ $rejectionRate > 20 ? 'is-bad' : '' }}"><span dir="ltr">{{ number_format($rejectionRate, 1) }}%</span></div>
                <div class="page-stat-label">@lang('messages.rejection_rate')</div>
            </div>
        </div>

        {{-- Promotion review queue. Approve-before-serve: a campaign here has already been
             paid for and is waiting, so it sits above the general campaign table. --}}
        @if ($pendingPromotions->count() > 0)
        <x-page-card id="promo-queue" class="scroll-mt-4"
            :title="trans_choice('messages.admin_alert_promos_pending', $pendingPromotions->count(), ['count' => $pendingPromotions->count()])"
            :lead="__('messages.promotion_review_intro')">
            <div class="page-stack">
                @foreach ($pendingPromotions as $promo)
                @php $ad = $promo->ads->first(); @endphp
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="flex items-start gap-4 min-w-0">
                            @if ($ad?->image_url)
                            <img src="{{ $ad->image_url }}" alt="" class="h-16 w-16 flex-none rounded-lg object-cover bg-gray-100 dark:bg-gray-800">
                            @endif
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-gray-100 truncate"><bdi>{{ $ad?->headline ?? $promo->event?->name }}</bdi></p>
                                @if ($ad?->primary_text)
                                <p class="text-sm text-gray-500 dark:text-gray-400"><bdi>{{ $ad->primary_text }}</bdi></p>
                                @endif
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    <bdi>{{ $promo->role?->name }}</bdi> &middot; <span dir="ltr">{{ $promo->user?->email }}</span>
                                    &middot; <span dir="ltr">{{ $promo->getCurrencySymbol() }}{{ number_format($promo->user_budget, 2) }}</span>
                                    &middot; {{ strtoupper($promo->pricing_model) }}
                                </p>
                                @if ($ad?->destination_url)
                                <p class="mt-1 text-xs break-all text-gray-400 dark:text-gray-500" dir="ltr">{{ $ad->destination_url }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Reject with its reason first, Approve (the one that goes on) last. --}}
                        <div class="flex flex-none flex-col gap-2 sm:flex-row sm:items-center">
                            <form method="POST" action="{{ route('admin.promotions.reject', ['campaign' => $promo->id]) }}" class="flex gap-2">
                                @csrf
                                <input type="text" name="moderation_notes" maxlength="2000" dir="auto"
                                    placeholder="{{ __('messages.reason') }}" aria-label="{{ __('messages.reason') }}"
                                    class="w-40 min-w-0 flex-1 text-sm {{ $field }}">
                                <x-danger-button type="submit">@lang('messages.reject')</x-danger-button>
                            </form>
                            <form method="POST" action="{{ route('admin.promotions.approve', ['campaign' => $promo->id]) }}">
                                @csrf
                                <x-brand-button type="submit" class="w-full">@lang('messages.approve')</x-brand-button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </x-page-card>
        @endif

        {{-- Alerts --}}
        @if ($stuckPending->count() > 0 || $failedCampaigns->count() > 0 || $disapprovedCampaigns->count() > 0)
        <x-page-notice tone="error" id="boost-alerts" class="scroll-mt-4">
            <div class="space-y-2">
                @if ($stuckPending->count() > 0)
                <div>
                    <p class="font-semibold">@lang('messages.stuck_pending_alert', ['count' => $stuckPending->count()])</p>
                    <ul class="mt-1 text-xs list-disc list-inside">
                        @foreach ($stuckPending as $stuck)
                        <li><bdi>{{ $stuck->event?->name ?? $stuck->name }}</bdi>@if ($stuck->user?->email) &middot; <span dir="ltr">{{ $stuck->user->email }}</span>@endif <span>({{ $stuck->created_at->diffForHumans() }})</span></li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if ($failedCampaigns->count() > 0)
                <div>
                    <p class="font-semibold">@lang('messages.failed_campaigns_alert', ['count' => $failedCampaigns->count()])</p>
                    <ul class="mt-1 text-xs list-disc list-inside">
                        @foreach ($failedCampaigns as $failed)
                        <li><bdi>{{ $failed->event?->name ?? $failed->name }}</bdi>@if ($failed->user?->email) &middot; <span dir="ltr">{{ $failed->user->email }}</span>@endif <span>({{ $failed->created_at->diffForHumans() }})</span></li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if ($disapprovedCampaigns->count() > 0)
                <div>
                    <p class="font-semibold">@lang('messages.disapproved_campaigns_alert', ['count' => $disapprovedCampaigns->count()])</p>
                    <ul class="mt-1 text-xs list-disc list-inside">
                        @foreach ($disapprovedCampaigns as $disapproved)
                        <li><bdi>{{ $disapproved->event?->name ?? $disapproved->name }}</bdi>@if ($disapproved->user?->email) &middot; <span dir="ltr">{{ $disapproved->user->email }}</span>@endif</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </x-page-notice>
        @endif

        {{-- Status donut + top boosters --}}
        <div class="boost-split">
            <x-page-card :title="__('messages.status_distribution')">
                @if (array_sum($statusDistribution) > 0)
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
                @else
                <x-page-empty compact :title="__('messages.no_campaigns_yet')" />
                @endif
            </x-page-card>

            <x-page-card :title="__('messages.top_boosters')" flush>
                @if ($topBoosters->count() > 0)
                <div class="page-scroll">
                    <table class="page-table is-dense">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.schedule')</th>
                                <th scope="col" class="c-num">@lang('messages.campaigns')</th>
                                <th scope="col" class="c-num">@lang('messages.budget')</th>
                                <th scope="col" class="c-num">@lang('messages.spend')</th>
                                <th scope="col" class="c-num">@lang('messages.clicks')</th>
                                <th scope="col" class="c-num">@lang('messages.limit')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topBoosters as $booster)
                            @php
                                $boosterRole = $topBoosterRoles[$booster->role_id] ?? null;
                            @endphp
                            <tr>
                                <td class="c-main c-strong"><span dir="ltr">{{ $boosterRole?->subdomain ?? '-' }}</span></td>
                                <td class="c-num" data-label="{{ __('messages.campaigns') }}">{{ $booster->campaign_count }}</td>
                                <td class="c-num" data-label="{{ \Illuminate\Support\Str::ucfirst(__('messages.budget')) }}">{{ \App\Utils\MoneyUtils::format($booster->total_budget, $boostCurrency) }}</td>
                                <td class="c-num" data-label="{{ __('messages.spend') }}">{{ \App\Utils\MoneyUtils::format($booster->total_spend ?? 0, $boostCurrency) }}</td>
                                <td class="c-num" data-label="{{ __('messages.clicks') }}">{{ number_format($booster->total_clicks ?? 0) }}</td>
                                <td class="c-num" data-label="{{ __('messages.limit') }}">{{ $boosterRole ? \App\Utils\MoneyUtils::format($boosterRole->getBoostMaxBudget(), $boostCurrency) : '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <x-page-empty compact :title="__('messages.no_campaigns_yet')" />
                @endif
            </x-page-card>
        </div>

        {{-- Performance line chart. Both lines come from boost billing records, which only a
             hosted install writes, so off it the chart could only ever be empty. --}}
        @if ($hosted)
        <x-page-card :title="__('messages.revenue_trend')">
            @if (count($trendLabels) > 0)
            <div class="h-64">
                <canvas id="performanceChart"></canvas>
            </div>
            @else
            <x-page-empty compact :title="__('messages.no_data_for_period')" />
            @endif
        </x-page-card>

        {{-- Credit and limit: two small forms side by side, each with the list of who has one. --}}
        <div class="page-grid2">
            <x-page-card :title="__('messages.grant_boost_credit')" :lead="__('messages.grant_boost_credit_lead')">
                <form action="{{ route('admin.boost.grant_credit') }}" method="POST" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="relative min-w-[140px] flex-1">
                        <x-input-label for="credit-subdomain" :value="__('messages.schedule_subdomain')" />
                        <input type="text" name="subdomain" id="credit-subdomain" required autocomplete="off" dir="ltr" data-subdomain-autocomplete
                            class="mt-1 block w-full {{ $field }}">
                        <div data-subdomain-dropdown class="hidden absolute start-0 end-0 top-full mt-1"></div>
                    </div>
                    <div class="w-28">
                        <x-input-label for="credit-amount" :value="__('messages.amount').' ('.$boostSymbol.')'" />
                        <input type="number" name="amount" id="credit-amount" required min="1" max="1000" step="0.01" dir="ltr"
                            class="mt-1 block w-full {{ $field }}">
                    </div>
                    <x-brand-button type="submit">@lang('messages.grant_credit')</x-brand-button>
                </form>

                @if ($rolesWithCredit->count() > 0)
                <div class="mt-5">
                <p class="event-group-label">@lang('messages.schedules_with_credit')</p>
                <dl class="page-kv">
                    @foreach ($rolesWithCredit as $creditRole)
                    <div>
                        <dt><span dir="ltr">{{ $creditRole->subdomain }}</span></dt>
                        <dd>{{ \App\Utils\MoneyUtils::format($creditRole->boost_credit, $boostCurrency) }}</dd>
                    </div>
                    @endforeach
                </dl>
                </div>
                @else
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_schedules_with_credit')</p>
                @endif
            </x-page-card>

            <x-page-card :title="__('messages.set_spending_limit')"
                :lead="__('messages.set_spending_limit_lead', ['amount' => \App\Utils\MoneyUtils::format(config('services.meta.boost_default_limit', 10), $boostCurrency)])">
                <form action="{{ route('admin.boost.set_limit') }}" method="POST" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="relative min-w-[140px] flex-1">
                        <x-input-label for="limit-subdomain" :value="__('messages.schedule_subdomain')" />
                        <input type="text" name="subdomain" id="limit-subdomain" required autocomplete="off" dir="ltr" data-subdomain-autocomplete
                            class="mt-1 block w-full {{ $field }}">
                        <div data-subdomain-dropdown class="hidden absolute start-0 end-0 top-full mt-1"></div>
                    </div>
                    <div class="w-28">
                        <x-input-label for="limit-amount" :value="__('messages.max_budget').' ('.$boostSymbol.')'" />
                        <input type="number" name="amount" id="limit-amount" required min="1" max="{{ config('services.meta.max_budget', 1000) }}" step="0.01" dir="ltr"
                            class="mt-1 block w-full {{ $field }}">
                    </div>
                    <x-brand-button type="submit">@lang('messages.set_limit')</x-brand-button>
                </form>

                @if ($rolesWithLimit->count() > 0)
                <div class="mt-5">
                <p class="event-group-label">@lang('messages.schedules_with_custom_limits')</p>
                <dl class="page-kv">
                    @foreach ($rolesWithLimit as $limitRole)
                    <div>
                        <dt><span dir="ltr">{{ $limitRole->subdomain }}</span></dt>
                        <dd>{{ \App\Utils\MoneyUtils::format($limitRole->boost_max_budget, $boostCurrency) }}</dd>
                    </div>
                    @endforeach
                </dl>
                </div>
                @else
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_custom_limits', ['amount' => \App\Utils\MoneyUtils::format(config('services.meta.boost_default_limit', 10), $boostCurrency)])</p>
                @endif
            </x-page-card>
        </div>
        @endif

        {{-- Every campaign. The event sits under the campaign's name, the account under its
             schedule and the day it was made under its state, which leaves eight columns where
             there were eleven: the list fits a laptop without scrolling sideways. --}}
        <x-page-card :title="__('messages.campaigns')" flush>
            <x-slot name="aside">
                <select id="status-filter" aria-label="{{ __('messages.status') }}" class="boost-status-filter {{ $field }}">
                    <option value="">@lang('messages.all_statuses')</option>
                    @foreach (['active', 'paused', 'completed', 'cancelled', 'failed', 'pending_payment', 'rejected'] as $s)
                    <option value="{{ $s }}" {{ $statusFilter === $s ? 'selected' : '' }}>@lang('messages.boost_status_' . $s)</option>
                    @endforeach
                </select>
            </x-slot>

            @if ($campaigns->count() > 0)
            <div class="page-scroll">
                <table class="page-table is-wide is-dense boost-campaigns">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.campaign')</th>
                            <th scope="col">@lang('messages.schedule')</th>
                            <th scope="col">@lang('messages.status')</th>
                            <th scope="col" class="c-num">@lang('messages.budget')</th>
                            <th scope="col" class="c-num">@lang('messages.spend')</th>
                            <th scope="col" class="c-num">@lang('messages.impressions')</th>
                            <th scope="col" class="c-num">@lang('messages.clicks')</th>
                            <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campaigns as $campaign)
                        <tr>
                            <td class="c-main c-strong">
                                <bdi>{{ $campaign->name }}</bdi>
                                @if ($campaign->event?->name && $campaign->event->name !== $campaign->name)
                                <span class="c-sub"><bdi>{{ $campaign->event->name }}</bdi></span>
                                @endif
                            </td>
                            <td>
                                @if ($campaign->role)
                                <a href="{{ route('role.view_admin', ['subdomain' => $campaign->role->subdomain, 'tab' => 'schedule']) }}" class="event-link" dir="ltr">{{ $campaign->role->subdomain }}</a>
                                @endif
                                @if ($campaign->user?->email)
                                <span class="c-sub" dir="ltr">{{ $campaign->user->email }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="event-status {{ $statusTones[$campaign->status] ?? '' }}">{{ $statusWord($campaign->status) }}</span>
                                <span class="c-sub whitespace-nowrap">{{ $campaign->created_at->translatedFormat('M j, Y') }}</span>
                            </td>
                            <td class="c-num" data-label="{{ \Illuminate\Support\Str::ucfirst(__('messages.budget')) }}">{{ \App\Utils\MoneyUtils::format($campaign->user_budget, $campaign->currency_code) }}</td>
                            <td class="c-num" data-label="{{ __('messages.spend') }}">{{ \App\Utils\MoneyUtils::format($campaign->actual_spend ?? 0, $campaign->currency_code) }}</td>
                            <td class="c-num" data-label="{{ __('messages.impressions') }}">{{ number_format($campaign->impressions ?? 0) }}</td>
                            <td class="c-num" data-label="{{ __('messages.clicks') }}">{{ number_format($campaign->clicks ?? 0) }}</td>
                            <td class="c-actions">
                                <a href="{{ route('boost.show', ['hash' => $campaign->hashedId()]) }}" class="event-link">@lang('messages.view')</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <x-page-empty compact :title="__('messages.no_campaigns_found')" />
            @endif

            @if ($campaigns->hasPages())
            <x-slot name="foot">{{ $campaigns->links() }}</x-slot>
            @endif
        </x-page-card>

        @if ($hosted)
        {{-- Recent billing records --}}
        <x-page-card :title="__('messages.recent_billing_records')" flush>
            @if ($recentBilling->count() > 0)
            <div class="page-scroll">
                <table class="page-table is-wide is-dense">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.campaign')</th>
                            <th scope="col">@lang('messages.type')</th>
                            <th scope="col" class="c-num">@lang('messages.amount')</th>
                            <th scope="col" class="c-num">@lang('messages.markup')</th>
                            <th scope="col">@lang('messages.status')</th>
                            <th scope="col">@lang('messages.notes')</th>
                            <th scope="col">@lang('messages.date')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentBilling as $record)
                        @php
                            $recordStatus = ['completed' => __('messages.boost_status_completed'), 'pending' => __('messages.pending'), 'failed' => __('messages.failed')][$record->status] ?? ucfirst((string) $record->status);
                        @endphp
                        <tr>
                            <td class="c-main c-strong"><bdi>{{ $record->campaign?->name ?? '-' }}</bdi></td>
                            <td><span class="event-status {{ $record->type === 'charge' ? 'is-on' : 'is-bad' }}">{{ $record->type === 'charge' ? __('messages.charge') : ($record->type === 'refund' ? __('messages.refund') : ucfirst((string) $record->type)) }}</span></td>
                            <td class="c-num" data-label="{{ __('messages.amount') }}">{{ \App\Utils\MoneyUtils::format($record->amount, $record->campaign?->currency_code) }}</td>
                            <td class="c-num" data-label="{{ __('messages.markup') }}">{{ \App\Utils\MoneyUtils::format($record->markup_amount ?? 0, $record->campaign?->currency_code) }}</td>
                            <td class="c-quiet">{{ $recordStatus }}</td>
                            <td class="c-quiet">@if ($record->notes)<bdi>{{ $record->notes }}</bdi>@endif</td>
                            <td class="c-date">{{ $record->created_at->translatedFormat('M j, Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <x-page-empty compact :title="__('messages.no_billing_records')" />
            @endif
        </x-page-card>
        @endif
    </div>

    {{-- Chart.js --}}
    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>
        // The markup currency, not the Meta one. This chart plots two datasets that can be
        // denominated differently - ad spend is always Meta, markup revenue spans both rails -
        // so the axis follows the one that varies. They agree whenever a single rail is in use,
        // and on a selfhost, where there is no Meta spend to plot, this is the only honest label.
        // JSON-encoded rather than interpolated, so a glyph is quoted safely.
        // (Do not write the directive name in this comment - Blade compiles it here too.)
        const BOOST_CURRENCY_SYMBOL = @json(\App\Utils\MoneyUtils::symbol($markupCurrency));

        // The charts' ink and grid lines come from the palette that is showing (six of them),
        // not from two greys chosen for "light" and "dark".
        const palette = getComputedStyle(document.documentElement);
        const token = function (name) {
            return 'rgb(' + palette.getPropertyValue(name).trim() + ')';
        };
        const textColor = token('--ap-ink-3');
        const gridColor = token('--ap-border');

        // Status filter auto-submit
        document.getElementById('status-filter').addEventListener('change', function() {
            var url = new URL(window.location.href);
            if (this.value) {
                url.searchParams.set('status', this.value);
            } else {
                url.searchParams.delete('status');
            }
            // A page of the old list may not exist in the narrowed one.
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });

        // Status Distribution Donut Chart
        @if (array_sum($statusDistribution) > 0)
        const statusColors = {
            'active': '#10B981',
            'paused': '#F59E0B',
            'completed': '#3B82F6',
            'cancelled': '#6B7280',
            'failed': '#EF4444',
            'pending_payment': '#F97316',
            'rejected': '#DC2626',
            'draft': '#9CA3AF',
        };

        const statusData = @json($statusDistribution);
        // The states in the reader's language, as the list under the chart names them.
        const statusLabels = @json(collect($statusDistribution)->keys()->map($statusWord)->values());
        const statusValues = Object.values(statusData);
        const statusBgColors = Object.keys(statusData).map(s => statusColors[s] || '#9CA3AF');

        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: statusBgColors,
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { color: textColor, font: { size: 12 } }
                    }
                }
            }
        });
        @endif

        // Performance Line Chart
        @if ($hosted && count($trendLabels) > 0)
        new Chart(document.getElementById('performanceChart'), {
            type: 'line',
            data: {
                labels: @json($trendLabels),
                datasets: [
                    {
                        label: @json(__('messages.ad_spend')),
                        data: @json($adSpendData),
                        borderColor: getComputedStyle(document.documentElement).getPropertyValue('--brand-blue').trim(),
                        backgroundColor: 'rgba(78, 129, 250, 0.1)',
                        fill: true,
                        tension: 0.3,
                    },
                    {
                        label: @json(__('messages.markup_revenue')),
                        data: @json($markupData),
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.3,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    x: {
                        ticks: { color: textColor },
                        grid: { color: gridColor }
                    },
                    y: {
                        ticks: {
                            color: textColor,
                            callback: function(value) { return BOOST_CURRENCY_SYMBOL + value.toFixed(0); }
                        },
                        grid: { color: gridColor }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: textColor }
                    }
                }
            }
        });
        @endif
    </script>

    @include('admin.partials._subdomain-autocomplete')
</x-app-admin-layout>
