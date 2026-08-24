<x-app-admin-layout>
    @php
        // Every figure on this page is Meta-boost money, billed in the ad account's
        // currency. It used to render a literal '$' regardless.
        $boostCurrency = config('services.meta.default_currency', 'USD');
    @endphp
    <div class="space-y-4">

        {{-- Navigation --}}
        @include('admin.partials._navigation', ['active' => 'boost'])
        @include('admin.partials._date-range-filter', ['range' => $range])

        {{-- Summary Metric Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <x-stat-panel label="{{ __('messages.total_campaigns') }}" padding="p-4">
                {{ number_format($totalCampaignsAllTime) }}
                <x-slot:subtitle>{{ number_format($totalCampaignsInPeriod) }} @lang('messages.in_period')</x-slot:subtitle>
            </x-stat-panel>
            <x-stat-panel label="{{ __('messages.active_campaigns') }}" color="green" padding="p-4">
                {{ number_format($activeCampaigns) }}
            </x-stat-panel>
            <x-stat-panel label="{{ __('messages.markup_revenue') }}" padding="p-4">
                {{ \App\Utils\MoneyUtils::format($markupRevenue, $markupCurrency) }}
            </x-stat-panel>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-stat-panel label="{{ __('messages.total_ad_spend') }}" padding="p-4">
                {{ \App\Utils\MoneyUtils::format($totalAdSpend, $boostCurrency) }}
            </x-stat-panel>
            <x-stat-panel label="{{ __('messages.total_refunds') }}" color="red" padding="p-4">
                {{ \App\Utils\MoneyUtils::format($totalRefunds, $boostCurrency) }}
            </x-stat-panel>
        </div>

        {{-- Average Performance Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-stat-panel label="{{ __('messages.avg_ctr') }}" padding="p-4">
                {{ number_format($avgCtr, 2) }}%
            </x-stat-panel>
            <x-stat-panel label="{{ __('messages.avg_cpc') }}" padding="p-4">
                {{ \App\Utils\MoneyUtils::format($avgCpc, $boostCurrency) }}
            </x-stat-panel>
            <x-stat-panel label="{{ __('messages.avg_cpm') }}" padding="p-4">
                {{ \App\Utils\MoneyUtils::format($avgCpm, $boostCurrency) }}
            </x-stat-panel>
            <x-stat-panel label="{{ __('messages.rejection_rate') }}" :color="$rejectionRate > 20 ? 'red' : null" padding="p-4">
                {{ number_format($rejectionRate, 1) }}%
            </x-stat-panel>
        </div>

        {{-- Promotion review queue. Approve-before-serve: a campaign here has already been
             paid for and is waiting, so it sits above the general campaign table. --}}
        @if ($pendingPromotions->count() > 0)
        <div id="promo-queue" class="ap-card rounded-xl shadow p-6 scroll-mt-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                {{ trans_choice('messages.admin_alert_promos_pending', $pendingPromotions->count(), ['count' => $pendingPromotions->count()]) }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">@lang('messages.promotion_review_intro')</p>

            <div class="space-y-4">
                @foreach ($pendingPromotions as $promo)
                @php $ad = $promo->ads->first(); @endphp
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="flex items-start gap-4 min-w-0">
                            @if ($ad?->image_url)
                            <img src="{{ $ad->image_url }}" alt="" class="h-16 w-16 flex-none rounded-lg object-cover bg-gray-100 dark:bg-gray-800">
                            @endif
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $ad?->headline ?? $promo->event?->name }}</p>
                                @if ($ad?->primary_text)
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $ad->primary_text }}</p>
                                @endif
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $promo->role?->name }} &middot; {{ $promo->user?->email }}
                                    &middot; {{ $promo->getCurrencySymbol() }}{{ number_format($promo->user_budget, 2) }}
                                    &middot; {{ strtoupper($promo->pricing_model) }}
                                </p>
                                @if ($ad?->destination_url)
                                <p class="mt-1 text-xs break-all text-gray-400 dark:text-gray-500">{{ $ad->destination_url }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-none flex-col gap-2 sm:flex-row">
                            <form method="POST" action="{{ route('admin.promotions.approve', ['campaign' => $promo->id]) }}">
                                @csrf
                                <x-brand-button type="submit">@lang('messages.approve')</x-brand-button>
                            </form>
                            <form method="POST" action="{{ route('admin.promotions.reject', ['campaign' => $promo->id]) }}" class="flex gap-2">
                                @csrf
                                <input type="text" name="moderation_notes" maxlength="2000"
                                    placeholder="{{ __('messages.reason') }}"
                                    class="w-40 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm">
                                <x-danger-button type="submit">@lang('messages.reject')</x-danger-button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Alerts --}}
        @if ($stuckPending->count() > 0 || $failedCampaigns->count() > 0 || $disapprovedCampaigns->count() > 0)
        <div id="boost-alerts" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4 scroll-mt-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 me-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <div class="space-y-2 w-full">
                    @if ($stuckPending->count() > 0)
                    <p class="text-sm text-red-700 dark:text-red-300 font-medium">@lang('messages.stuck_pending_alert', ['count' => $stuckPending->count()])</p>
                    <ul class="text-xs text-red-600 dark:text-red-400 list-disc list-inside">
                        @foreach ($stuckPending as $stuck)
                        <li>{{ $stuck->event?->name ?? 'N/A' }} - {{ $stuck->user?->email ?? 'N/A' }} ({{ $stuck->created_at->diffForHumans() }})</li>
                        @endforeach
                    </ul>
                    @endif

                    @if ($failedCampaigns->count() > 0)
                    <p class="text-sm text-red-700 dark:text-red-300 font-medium">@lang('messages.failed_campaigns_alert', ['count' => $failedCampaigns->count()])</p>
                    <ul class="text-xs text-red-600 dark:text-red-400 list-disc list-inside">
                        @foreach ($failedCampaigns as $failed)
                        <li>{{ $failed->event?->name ?? 'N/A' }} - {{ $failed->user?->email ?? 'N/A' }} ({{ $failed->created_at->diffForHumans() }})</li>
                        @endforeach
                    </ul>
                    @endif

                    @if ($disapprovedCampaigns->count() > 0)
                    <p class="text-sm text-red-700 dark:text-red-300 font-medium">@lang('messages.disapproved_campaigns_alert', ['count' => $disapprovedCampaigns->count()])</p>
                    <ul class="text-xs text-red-600 dark:text-red-400 list-disc list-inside">
                        @foreach ($disapprovedCampaigns as $disapproved)
                        <li>{{ $disapproved->event?->name ?? 'N/A' }} - {{ $disapproved->user?->email ?? 'N/A' }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Two-column: Status Donut + Top Boosters --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Status Distribution --}}
            <div class="ap-card rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">@lang('messages.status_distribution')</h3>
                @if (array_sum($statusDistribution) > 0)
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
                @else
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_campaigns_yet')</p>
                @endif
            </div>

            {{-- Top Boosters --}}
            <div class="ap-card rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">@lang('messages.top_boosters')</h3>
                @if ($topBoosters->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                <th class="pb-2 font-medium">@lang('messages.schedule')</th>
                                <th class="pb-2 font-medium text-end">@lang('messages.campaigns')</th>
                                <th class="pb-2 font-medium text-end">@lang('messages.budget')</th>
                                <th class="pb-2 font-medium text-end">@lang('messages.spend')</th>
                                <th class="pb-2 font-medium text-end">@lang('messages.clicks')</th>
                                <th class="pb-2 font-medium text-end">@lang('messages.limit')</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($topBoosters as $booster)
                            @php
                                $boosterRole = $topBoosterRoles[$booster->role_id] ?? null;
                            @endphp
                            <tr>
                                <td class="py-2 text-gray-900 dark:text-white">{{ $boosterRole?->subdomain ?? 'N/A' }}</td>
                                <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ $booster->campaign_count }}</td>
                                <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ \App\Utils\MoneyUtils::format($booster->total_budget, $boostCurrency) }}</td>
                                <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ \App\Utils\MoneyUtils::format($booster->total_spend ?? 0, $boostCurrency) }}</td>
                                <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ number_format($booster->total_clicks ?? 0) }}</td>
                                <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ $boosterRole ? \App\Utils\MoneyUtils::format($boosterRole->getBoostMaxBudget(), $boostCurrency) : 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_campaigns_yet')</p>
                @endif
            </div>
        </div>

        {{-- Performance Line Chart --}}
        <div class="ap-card rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">@lang('messages.revenue_trend')</h3>
            @if (count($trendLabels) > 0)
            <div class="h-64">
                <canvas id="performanceChart"></canvas>
            </div>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_data_for_period')</p>
            @endif
        </div>

        {{-- Grant Credit Section --}}
        <div class="ap-card rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">@lang('messages.grant_boost_credit')</h3>

            <form action="{{ route('admin.boost.grant_credit') }}" method="POST" class="flex flex-wrap gap-4 items-end mb-6">
                @csrf
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">@lang('messages.schedule_subdomain')</label>
                    <input type="text" name="subdomain" required autocomplete="off" data-subdomain-autocomplete
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"
                        placeholder="subdomain">
                    <div data-subdomain-dropdown class="hidden absolute left-0 right-0 mt-1 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto z-50"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">@lang('messages.amount') ($)</label>
                    <input type="number" name="amount" required min="1" max="1000" step="0.01"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] w-32"
                        placeholder="100">
                </div>
                <x-brand-button type="submit">
                    @lang('messages.grant_credit')
                </x-brand-button>
            </form>

            @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-300">
                @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            @if (session('success'))
            <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-700 dark:text-green-300">
                {{ session('success') }}
            </div>
            @endif

            @if ($rolesWithCredit->count() > 0)
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">@lang('messages.schedules_with_credit')</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-2 font-medium">@lang('messages.subdomain')</th>
                            <th class="pb-2 font-medium text-end">@lang('messages.balance')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($rolesWithCredit as $creditRole)
                        <tr>
                            <td class="py-2 text-gray-900 dark:text-white">{{ $creditRole->subdomain }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ \App\Utils\MoneyUtils::format($creditRole->boost_credit, $boostCurrency) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_schedules_with_credit')</p>
            @endif
        </div>

        {{-- Set Spending Limit Section --}}
        <div class="ap-card rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">@lang('messages.set_spending_limit')</h3>

            <form action="{{ route('admin.boost.set_limit') }}" method="POST" class="flex flex-wrap gap-4 items-end mb-6">
                @csrf
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">@lang('messages.schedule_subdomain')</label>
                    <input type="text" name="subdomain" required autocomplete="off" data-subdomain-autocomplete
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"
                        placeholder="subdomain">
                    <div data-subdomain-dropdown class="hidden absolute left-0 right-0 mt-1 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto z-50"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">@lang('messages.max_budget_per_campaign', ['symbol' => \App\Utils\MoneyUtils::symbol(config('services.meta.default_currency', 'USD'))])</label>
                    <input type="number" name="amount" required min="1" max="{{ config('services.meta.max_budget', 1000) }}" step="0.01"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] w-32"
                        placeholder="100">
                </div>
                <x-brand-button type="submit">
                    @lang('messages.set_limit')
                </x-brand-button>
            </form>

            @if ($rolesWithLimit->count() > 0)
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">@lang('messages.schedules_with_custom_limits')</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-2 font-medium">@lang('messages.subdomain')</th>
                            <th class="pb-2 font-medium text-end">@lang('messages.max_budget')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($rolesWithLimit as $limitRole)
                        <tr>
                            <td class="py-2 text-gray-900 dark:text-white">{{ $limitRole->subdomain }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ \App\Utils\MoneyUtils::format($limitRole->boost_max_budget, $boostCurrency) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_custom_limits', ['amount' => \App\Utils\MoneyUtils::format(config('services.meta.boost_default_limit', 10), config('services.meta.default_currency', 'USD'))])</p>
            @endif
        </div>

        {{-- Campaigns Table --}}
        <div class="ap-card rounded-xl shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">@lang('messages.campaigns')</h3>
                <select id="status-filter"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm">
                    <option value="">@lang('messages.all_statuses')</option>
                    @foreach (['active', 'paused', 'completed', 'cancelled', 'failed', 'pending_payment', 'rejected'] as $s)
                    <option value="{{ $s }}" {{ $statusFilter === $s ? 'selected' : '' }}>@lang('messages.boost_status_' . $s)</option>
                    @endforeach
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-2 font-medium">@lang('messages.name')</th>
                            <th class="pb-2 font-medium">@lang('messages.user')</th>
                            <th class="pb-2 font-medium">@lang('messages.event')</th>
                            <th class="pb-2 font-medium">@lang('messages.schedule')</th>
                            <th class="pb-2 font-medium">@lang('messages.status')</th>
                            <th class="pb-2 font-medium text-end">@lang('messages.budget')</th>
                            <th class="pb-2 font-medium text-end">@lang('messages.spend')</th>
                            <th class="pb-2 font-medium text-end">@lang('messages.impressions')</th>
                            <th class="pb-2 font-medium text-end">@lang('messages.clicks')</th>
                            <th class="pb-2 font-medium">@lang('messages.created')</th>
                            <th class="pb-2 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($campaigns as $campaign)
                        <tr>
                            <td class="py-2 text-gray-900 dark:text-white max-w-[200px] truncate">{{ $campaign->name }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-xs">{{ $campaign->user?->email ?? 'N/A' }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 max-w-[150px] truncate">{{ $campaign->event?->name ?? 'N/A' }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300">
                                @if ($campaign->role)
                                <a href="{{ route('role.view_admin', ['subdomain' => $campaign->role->subdomain, 'tab' => 'schedule']) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $campaign->role->subdomain }}</a>
                                @else
                                N/A
                                @endif
                            </td>
                            <td class="py-2">
                                @php
                                $badgeColors = match($campaign->status) {
                                    'active' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                    'paused' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                    'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                    'cancelled' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    'failed' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                    'pending_payment' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
                                    'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badgeColors }}">
                                    @lang('messages.boost_status_' . $campaign->status)
                                </span>
                            </td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ \App\Utils\MoneyUtils::format($campaign->user_budget, $campaign->currency_code) }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ \App\Utils\MoneyUtils::format($campaign->actual_spend ?? 0, $campaign->currency_code) }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ number_format($campaign->impressions ?? 0) }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ number_format($campaign->clicks ?? 0) }}</td>
                            <td class="py-2 text-gray-500 dark:text-gray-400 text-xs">{{ $campaign->created_at->format('M j, Y') }}</td>
                            <td class="py-2">
                                <a href="{{ route('boost.show', ['hash' => $campaign->hashedId()]) }}" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">@lang('messages.view')</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="py-4 text-center text-gray-500 dark:text-gray-400">@lang('messages.no_campaigns_found')</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $campaigns->links() }}
            </div>
        </div>

        {{-- Recent Billing Records --}}
        <div class="ap-card rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">@lang('messages.recent_billing_records')</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-2 font-medium">@lang('messages.campaign')</th>
                            <th class="pb-2 font-medium">@lang('messages.type')</th>
                            <th class="pb-2 font-medium text-end">@lang('messages.amount')</th>
                            <th class="pb-2 font-medium text-end">@lang('messages.markup')</th>
                            <th class="pb-2 font-medium">@lang('messages.status')</th>
                            <th class="pb-2 font-medium">@lang('messages.notes')</th>
                            <th class="pb-2 font-medium">@lang('messages.date')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($recentBilling as $record)
                        <tr>
                            <td class="py-2 text-gray-900 dark:text-white max-w-[200px] truncate">{{ $record->campaign?->name ?? 'N/A' }}</td>
                            <td class="py-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $record->type === 'charge' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                    {{ ucfirst($record->type) }}
                                </span>
                            </td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ \App\Utils\MoneyUtils::format($record->amount, $record->campaign?->currency_code) }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ \App\Utils\MoneyUtils::format($record->markup_amount ?? 0, $record->campaign?->currency_code) }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300">{{ ucfirst($record->status) }}</td>
                            <td class="py-2 text-gray-500 dark:text-gray-400 text-xs max-w-[200px] truncate">{{ $record->notes ?? '-' }}</td>
                            <td class="py-2 text-gray-500 dark:text-gray-400 text-xs">{{ $record->created_at->format('M j, Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-gray-500 dark:text-gray-400">@lang('messages.no_billing_records')</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

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
        const isDarkMode = document.documentElement.classList.contains('dark') ||
            (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches && !document.documentElement.classList.contains('light'));
        const textColor = isDarkMode ? '#9CA3AF' : '#6B7280';
        const gridColor = isDarkMode ? '#2d2d30' : '#E5E7EB';

        // Status filter auto-submit
        document.getElementById('status-filter').addEventListener('change', function() {
            var url = new URL(window.location.href);
            if (this.value) {
                url.searchParams.set('status', this.value);
            } else {
                url.searchParams.delete('status');
            }
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
        const statusLabels = Object.keys(statusData).map(s => s.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()));
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
        @if (count($trendLabels) > 0)
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
