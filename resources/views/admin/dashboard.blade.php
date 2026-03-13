<x-app-admin-layout>

    <div class="space-y-4">
        @include('admin.partials._navigation', ['active' => 'dashboard'])

        @include('admin.partials._date-range-filter', ['range' => $range])

        {{-- Key Metrics Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Total Users --}}
            <div class="ap-card rounded-xl shadow p-6 flex flex-col items-center">
                <div class="flex items-center gap-3 mb-3 self-start">
                    <div class="dashboard-icon p-2 rounded-xl bg-blue-50 dark:bg-blue-500/10"
                         style="--icon-glow: rgba(59, 130, 246, 0.15)">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.total_users')</p>
                </div>
                <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white text-center">{{ number_format($totalUsers) }}</p>
                <div class="mt-4 flex items-center text-sm w-full">
                    <span class="{{ $usersChangePercent >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $usersChangePercent >= 0 ? '+' : '' }}{{ $usersChangePercent }}%
                    </span>
                    <span class="text-gray-500 dark:text-gray-400 ms-2">
                        +{{ number_format($usersInPeriod) }} @lang('messages.in_period')
                    </span>
                </div>
            </div>

            {{-- Total Schedules --}}
            <div class="ap-card rounded-xl shadow p-6 flex flex-col items-center">
                <div class="flex items-center gap-3 mb-3 self-start">
                    <div class="dashboard-icon p-2 rounded-xl bg-green-50 dark:bg-green-500/10"
                         style="--icon-glow: rgba(34, 197, 94, 0.15)">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.total_schedules')</p>
                </div>
                <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white text-center">{{ number_format($totalSchedules) }}</p>
                <div class="mt-4 flex items-center text-sm w-full">
                    <span class="{{ $schedulesChangePercent >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $schedulesChangePercent >= 0 ? '+' : '' }}{{ $schedulesChangePercent }}%
                    </span>
                    <span class="text-gray-500 dark:text-gray-400 ms-2">
                        +{{ number_format($schedulesInPeriod) }} @lang('messages.in_period')
                    </span>
                </div>
            </div>

            {{-- Total Events --}}
            <div class="ap-card rounded-xl shadow p-6 flex flex-col items-center">
                <div class="flex items-center gap-3 mb-3 self-start">
                    <div class="dashboard-icon p-2 rounded-xl bg-purple-50 dark:bg-purple-500/10"
                         style="--icon-glow: rgba(168, 85, 247, 0.15)">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.total_events')</p>
                </div>
                <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white text-center">{{ number_format($totalEvents) }}</p>
                <div class="mt-4 flex items-center text-sm w-full">
                    <span class="{{ $eventsChangePercent >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $eventsChangePercent >= 0 ? '+' : '' }}{{ $eventsChangePercent }}%
                    </span>
                    <span class="text-gray-500 dark:text-gray-400 ms-2">
                        +{{ number_format($eventsInPeriod) }} @lang('messages.in_period')
                    </span>
                </div>
            </div>
        </div>

        {{-- Activity Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="ap-card rounded-xl shadow p-6 text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.active_users_7_days')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($activeUsers7Days) }}</p>
            </div>
            <div class="ap-card rounded-xl shadow p-6 text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.active_users_30_days')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($activeUsers30Days) }}</p>
            </div>
            <div class="ap-card rounded-xl shadow p-6 text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.upcoming_online_events')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($upcomingOnlineEvents) }}</p>
            </div>
            <div class="ap-card rounded-xl shadow p-6 text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.private_events')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($privateEvents) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($passwordProtectedEvents) }} @lang('messages.with_password')</p>
            </div>
        </div>

        {{-- Boost & Newsletter Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="ap-card rounded-xl shadow p-6 text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.stripe_paid')</p>
                <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($stripePaidCount) }}</p>
            </div>
            <div class="ap-card rounded-xl shadow p-6 text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.active_boost_campaigns')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($activeBoostCampaigns) }}</p>
            </div>
            <div class="ap-card rounded-xl shadow p-6 text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.boost_markup_revenue')</p>
                <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">${{ number_format($boostMarkupRevenue, 2) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.in_period')</p>
            </div>
            <div class="ap-card rounded-xl shadow p-6 text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.newsletter_subscribers')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($newsletterSubscribers) }}</p>
            </div>
        </div>

        {{-- Events by Country --}}
        @if($eventsByCountry->count() > 0)
        <div class="ap-card rounded-xl shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.upcoming_events_by_country')</h3>
            <div class="space-y-3">
                @foreach($eventsByCountry as $country)
                    <div class="flex items-center">
                        <div class="w-24 flex-shrink-0">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ strtoupper($country->country_code) }}
                            </span>
                        </div>
                        <div class="flex-1 mx-4">
                            <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                                <div class="bg-indigo-500 h-4 rounded-full" style="width: {{ min(100, ($country->count / $eventsByCountry->max('count')) * 100) }}%"></div>
                            </div>
                        </div>
                        <div class="w-16 text-end">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($country->count) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Growth Trends Chart --}}
        <div class="ap-card rounded-xl shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.growth_trends')</h3>
            <div class="h-64">
                <canvas id="trendsChart"></canvas>
            </div>
        </div>

        {{-- Recent Signups --}}
        <div class="ap-card rounded-xl shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">@lang('messages.recent_signups')</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.name')</th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.date')</th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.source')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($recentSignups as $signup)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $signup->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $signup->created_at->diffForHumans() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $signup->utm_source ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">@lang('messages.no_users_yet')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Recent Schedules --}}
            <div class="ap-card rounded-xl shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">@lang('messages.recent_schedules')</h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($recentSchedules as $schedule)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('role.view_guest', ['subdomain' => $schedule->subdomain]) }}" target="_blank" class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 truncate block">
                                        {{ $schedule->name }}
                                    </a>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ ucfirst($schedule->type) }} &bull; {{ $schedule->subdomain }}
                                    </p>
                                </div>
                                <div class="text-end text-sm text-gray-500 dark:text-gray-400">
                                    <p>{{ $schedule->created_at->diffForHumans() }}</p>
                                    @if ($schedule->owner())
                                        <p class="text-xs">@lang('messages.by') {{ $schedule->owner()->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            @lang('messages.no_schedules_yet')
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Events --}}
            <div class="ap-card rounded-xl shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">@lang('messages.recent_events')</h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($recentEvents as $event)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    @php($viewableRole = $event->getViewableRole())
                                    @if ($viewableRole)
                                        <a href="{{ $event->getGuestUrl($viewableRole->subdomain) }}" target="_blank" class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 truncate block">
                                            {{ $event->name }}
                                        </a>
                                    @else
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $event->name }}
                                        </p>
                                    @endif
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        @if ($viewableRole)
                                            {{ $viewableRole->name }}
                                        @else
                                            @lang('messages.no_schedule')
                                        @endif
                                    </p>
                                </div>
                                <div class="text-end text-sm text-gray-500 dark:text-gray-400">
                                    <p>{{ $event->created_at->diffForHumans() }}</p>
                                    @if ($event->starts_at)
                                        <p class="text-xs">{{ \Carbon\Carbon::parse($event->starts_at)->format('M d, Y') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            @lang('messages.no_events_yet')
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Signups by Method (Selected Period) --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="ap-card rounded-xl shadow p-6 flex flex-col items-center">
                <div class="flex items-center gap-3 mb-3 self-start">
                    <div class="dashboard-icon p-2 rounded-xl bg-blue-50 dark:bg-blue-500/10"
                         style="--icon-glow: rgba(59, 130, 246, 0.15)">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.email_signups')</p>
                </div>
                <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white text-center">{{ number_format($emailUsersInPeriod) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 w-full">@lang('messages.in_period')</p>
            </div>
            <div class="ap-card rounded-xl shadow p-6 flex flex-col items-center">
                <div class="flex items-center gap-3 mb-3 self-start">
                    <div class="dashboard-icon p-2 rounded-xl bg-red-50 dark:bg-red-500/10"
                         style="--icon-glow: rgba(239, 68, 68, 0.15)">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.google_signups')</p>
                </div>
                <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white text-center">{{ number_format($googleUsersInPeriod) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 w-full">@lang('messages.in_period')</p>
            </div>
            <div class="ap-card rounded-xl shadow p-6 flex flex-col items-center">
                <div class="flex items-center gap-3 mb-3 self-start">
                    <div class="dashboard-icon p-2 rounded-xl bg-purple-50 dark:bg-purple-500/10"
                         style="--icon-glow: rgba(168, 85, 247, 0.15)">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.hybrid_signups')</p>
                </div>
                <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white text-center">{{ number_format($hybridUsersInPeriod) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 w-full">@lang('messages.in_period')</p>
            </div>
        </div>

        {{-- Domains --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="ap-card rounded-xl shadow p-6 flex flex-col items-center">
                <div class="flex items-center gap-3 mb-3 self-start">
                    <div class="dashboard-icon p-2 rounded-xl bg-blue-50 dark:bg-blue-500/10"
                         style="--icon-glow: rgba(59, 130, 246, 0.15)">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.total_custom_domains')</p>
                </div>
                <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white text-center">{{ number_format($totalCustomDomains) }}</p>
            </div>
            <div class="ap-card rounded-xl shadow p-6 flex flex-col items-center">
                <div class="flex items-center gap-3 mb-3 self-start">
                    <div class="dashboard-icon p-2 rounded-xl bg-green-50 dark:bg-green-500/10"
                         style="--icon-glow: rgba(34, 197, 94, 0.15)">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.active_domains')</p>
                </div>
                <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white text-center">{{ number_format($activeCount) }}</p>
            </div>
            <div class="ap-card rounded-xl shadow p-6 flex flex-col items-center">
                <div class="flex items-center gap-3 mb-3 self-start">
                    <div class="dashboard-icon p-2 rounded-xl bg-yellow-50 dark:bg-yellow-500/10"
                         style="--icon-glow: rgba(234, 179, 8, 0.15)">
                        <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.pending_domains')</p>
                </div>
                <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white text-center">{{ number_format($pendingCount) }}</p>
            </div>
        </div>

        {{-- Queue Health --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="ap-card rounded-xl shadow p-6 flex flex-col items-center">
                <div class="flex items-center gap-3 mb-3 self-start">
                    <div class="dashboard-icon p-2 rounded-xl bg-blue-50 dark:bg-blue-500/10"
                         style="--icon-glow: rgba(59, 130, 246, 0.15)">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.pending_jobs')</p>
                </div>
                <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white text-center">{{ number_format($pendingJobsCount) }}</p>
            </div>
            <div class="ap-card rounded-xl shadow p-6 flex flex-col items-center">
                <div class="flex items-center gap-3 mb-3 self-start">
                    <div class="dashboard-icon p-2 rounded-xl bg-red-50 dark:bg-red-500/10"
                         style="--icon-glow: rgba(239, 68, 68, 0.15)">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.failed_jobs')</p>
                </div>
                <p class="dashboard-stat-value text-3xl font-bold {{ $failedJobsCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }} text-center">{{ number_format($failedJobsCount) }}</p>
            </div>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>

    <script {!! nonce_attr() !!}>
        function initCharts() {
            if (typeof Chart === 'undefined') {
                setTimeout(initCharts, 50);
                return;
            }

            // Dark mode detection
            const isDarkMode = document.documentElement.classList.contains('dark') ||
                (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches && !document.documentElement.classList.contains('light'));

            const textColor = isDarkMode ? '#9ca3af' : '#6B7280';
            const gridColor = isDarkMode ? '#2d2d30' : '#E5E7EB';

            // Growth Trends Chart
            const trendsCtx = document.getElementById('trendsChart').getContext('2d');
            new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: @json($trendData['labels']),
                    datasets: [
                        {
                            label: @json(__('messages.users')),
                            data: @json($trendData['users']),
                            borderColor: getComputedStyle(document.documentElement).getPropertyValue('--brand-blue').trim(),
                            backgroundColor: 'rgba(78, 129, 250, 0.1)',
                            fill: false,
                            tension: 0.3
                        },
                        {
                            label: @json(__('messages.schedules')),
                            data: @json($trendData['schedules']),
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: false,
                            tension: 0.3
                        },
                        {
                            label: @json(__('messages.events')),
                            data: @json($trendData['events']),
                            borderColor: '#8B5CF6',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            fill: false,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: textColor
                            }
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
                                precision: 0
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
