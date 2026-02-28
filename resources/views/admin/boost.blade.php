<x-app-admin-layout>
    <div class="space-y-6">

        {{-- Navigation --}}
        @include('admin.partials._navigation', ['active' => 'boost'])
        @include('admin.partials._date-range-filter', ['range' => $range])

        {{-- Summary Metric Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.total_campaigns')</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalCampaignsAllTime) }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ number_format($totalCampaignsInPeriod) }} @lang('messages.in_period')</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.active_campaigns')</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($activeCampaigns) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.markup_revenue')</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($markupRevenue, 2) }}</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.total_ad_spend')</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($totalAdSpend, 2) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.total_refunds')</p>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">${{ number_format($totalRefunds, 2) }}</p>
            </div>
        </div>

        {{-- Average Performance Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.avg_ctr')</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($avgCtr, 2) }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.avg_cpc')</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($avgCpc, 2) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.avg_cpm')</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($avgCpm, 2) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.rejection_rate')</p>
                <p class="text-2xl font-bold {{ $rejectionRate > 20 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ number_format($rejectionRate, 1) }}%</p>
            </div>
        </div>

        {{-- Alerts --}}
        @if ($stuckPending->count() > 0 || $failedCampaigns->count() > 0 || $disapprovedCampaigns->count() > 0)
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Status Distribution --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
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
                                <td class="py-2 text-gray-700 dark:text-gray-300 text-end">${{ number_format($booster->total_budget, 2) }}</td>
                                <td class="py-2 text-gray-700 dark:text-gray-300 text-end">${{ number_format($booster->total_spend ?? 0, 2) }}</td>
                                <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ number_format($booster->total_clicks ?? 0) }}</td>
                                <td class="py-2 text-gray-700 dark:text-gray-300 text-end">${{ $boosterRole ? number_format($boosterRole->getBoostMaxBudget(), 0) : 'N/A' }}</td>
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">@lang('messages.grant_boost_credit')</h3>

            <form action="{{ route('admin.boost.grant_credit') }}" method="POST" class="flex flex-wrap gap-4 items-end mb-6">
                @csrf
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">@lang('messages.schedule_subdomain')</label>
                    <input type="text" name="subdomain" required autocomplete="off" data-subdomain-autocomplete
                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="subdomain">
                    <div data-subdomain-dropdown class="hidden absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto z-50"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">@lang('messages.amount') ($)</label>
                    <input type="number" name="amount" required min="1" max="1000" step="0.01"
                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-32"
                        placeholder="100">
                </div>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm text-sm">
                    @lang('messages.grant_credit')
                </button>
            </form>

            @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md text-sm text-red-700 dark:text-red-300">
                @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            @if (session('success'))
            <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-md text-sm text-green-700 dark:text-green-300">
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
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">${{ number_format($creditRole->boost_credit, 2) }}</td>
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">@lang('messages.set_spending_limit')</h3>

            <form action="{{ route('admin.boost.set_limit') }}" method="POST" class="flex flex-wrap gap-4 items-end mb-6">
                @csrf
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">@lang('messages.schedule_subdomain')</label>
                    <input type="text" name="subdomain" required autocomplete="off" data-subdomain-autocomplete
                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="subdomain">
                    <div data-subdomain-dropdown class="hidden absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto z-50"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">@lang('messages.max_budget_per_campaign')</label>
                    <input type="number" name="amount" required min="1" max="{{ config('services.meta.max_budget', 1000) }}" step="0.01"
                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-32"
                        placeholder="100">
                </div>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm text-sm">
                    @lang('messages.set_limit')
                </button>
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
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">${{ number_format($limitRole->boost_max_budget, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_custom_limits', ['amount' => number_format(config('services.meta.boost_default_limit', 10), 0)])</p>
            @endif
        </div>

        {{-- Campaigns Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">@lang('messages.campaigns')</h3>
                <select id="status-filter"
                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
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
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">${{ number_format($campaign->user_budget, 2) }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">${{ number_format($campaign->actual_spend ?? 0, 2) }}</td>
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
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
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">${{ number_format($record->amount, 2) }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">${{ number_format($record->markup_amount ?? 0, 2) }}</td>
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
                        borderColor: '#4E81FA',
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
                            callback: function(value) { return '$' + value.toFixed(0); }
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
