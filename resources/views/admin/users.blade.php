<x-app-admin-layout>

    <div class="space-y-4">
        @include('admin.partials._navigation', ['active' => 'users'])

        @include('admin.partials._date-range-filter', ['range' => $range])

        {{-- ===================== Onboarding Funnel ===================== --}}
        @php
            $funnelStages = $funnel['stages'];
            $funnelStageLabel = fn ($key) => __('messages.funnel_stage_' . $key);
            $biggestDropToKey = $funnel['biggest_drop']['to_key'] ?? null;
            $isNexus = (bool) config('app.is_nexus');
            $trafficNote = null;
            if (! $isNexus) {
                $trafficNote = __('messages.funnel_traffic_not_tracked');
            } elseif (! $funnel['traffic_tracked']) {
                $trafficNote = $funnel['tracking_started_at']
                    ? __('messages.funnel_tracking_began', ['date' => \Illuminate\Support\Carbon::parse($funnel['tracking_started_at'])->format('M j, Y')])
                    : __('messages.funnel_tracking_pending');
            }
        @endphp

        <div class="flex items-center gap-3 pt-2">
            <div class="dashboard-icon p-2 rounded-xl bg-blue-50 dark:bg-blue-500/10" style="--icon-glow: rgba(59, 130, 246, 0.15)">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M6 8h12M9 12h6M11 16h2" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">@lang('messages.funnel_onboarding_title')</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.funnel_onboarding_subtitle')</p>
            </div>
        </div>

        {{-- Hero KPIs: north-star, biggest leak, overall --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- North-star: Signup to first event, with period-over-period change --}}
            <div class="ap-card rounded-xl shadow p-6 flex flex-col items-center">
                <div class="flex items-center gap-3 mb-3 self-start">
                    <div class="dashboard-icon p-2 rounded-xl bg-green-50 dark:bg-green-500/10" style="--icon-glow: rgba(16, 185, 129, 0.15)">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.funnel_north_star')</p>
                </div>
                <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white text-center">{{ $funnel['first_event_conv'] === null ? __('messages.funnel_na') : $funnel['first_event_conv'] . '%' }}</p>
                <div class="mt-4 flex items-center text-sm w-full">
                    @if($funnel['first_event_conv_change'] !== null)
                        <span class="{{ $funnel['first_event_conv_change'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $funnel['first_event_conv_change'] >= 0 ? '+' : '' }}{{ $funnel['first_event_conv_change'] }} @lang('messages.funnel_pts')
                        </span>
                        <span class="text-gray-500 dark:text-gray-400 ms-2">@lang('messages.vs_previous_period')</span>
                    @else
                        <span class="text-gray-500 dark:text-gray-400">{{ number_format($funnel['cohort_size']) }} @lang('messages.signups_total')</span>
                    @endif
                </div>
            </div>

            {{-- Biggest onboarding leak --}}
            <x-stat-panel label="{{ __('messages.funnel_biggest_leak') }}" color="amber">
                @if($funnel['biggest_drop'])
                    -{{ $funnel['biggest_drop']['drop_pct'] }}%
                    <x-slot:subtitle>
                        {{ $funnelStageLabel($funnel['biggest_drop']['from_key']) }} &rarr; {{ $funnelStageLabel($funnel['biggest_drop']['to_key']) }}<br>
                        {{ number_format($funnel['biggest_drop']['lost']) }} @lang('messages.funnel_users_lost')
                    </x-slot:subtitle>
                @else
                    {{ __('messages.funnel_na') }}
                    <x-slot:subtitle>@lang('messages.funnel_no_leak')</x-slot:subtitle>
                @endif
            </x-stat-panel>

            {{-- Overall visitor to first event --}}
            <x-stat-panel label="{{ __('messages.funnel_visitor_to_event') }}">
                {{ $funnel['visitor_to_event_conv'] === null ? __('messages.funnel_na') : $funnel['visitor_to_event_conv'] . '%' }}
                @if($funnel['visitor_to_event_conv'] === null && $trafficNote)
                    <x-slot:subtitle>{{ $trafficNote }}</x-slot:subtitle>
                @endif
            </x-stat-panel>
        </div>

        {{-- Funnel bars + conversion-over-time chart --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Funnel form --}}
            <div class="ap-card rounded-xl shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">@lang('messages.funnel_stages_title')</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    @if($funnel['cohort_size'] > 0)
                        @lang('messages.funnel_cohort_of', ['count' => number_format($funnel['cohort_size'])])
                    @else
                        @lang('messages.funnel_no_signups_period')
                    @endif
                </p>

                <div class="space-y-1">
                    @foreach($funnelStages as $i => $stage)
                        @php
                            $isTraffic = $stage['group'] === 'traffic';
                            $count = $stage['count'];
                            $label = $funnelStageLabel($stage['key']);
                            $ariaCount = $count === null ? __('messages.funnel_na') : number_format($count);
                            $barWidth = ($count !== null && $count > 0) ? max(2, $stage['width']) : 0;
                        @endphp

                        {{-- Group labels: site traffic (anonymous) vs signups this period (cohort) --}}
                        @if($i === 0)
                            <div class="flex items-center gap-2 pb-1">
                                <span class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500">@lang('messages.funnel_group_traffic')</span>
                                <span class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></span>
                            </div>
                        @elseif($i === 2)
                            <div class="flex items-center gap-2 pt-3 pb-1">
                                <span class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500">@lang('messages.funnel_group_cohort')</span>
                                <span class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></span>
                            </div>
                        @endif

                        {{-- Drop connector (users lost from the previous stage) --}}
                        @if($stage['drop_count'] !== null && $stage['drop_count'] > 0)
                            @php $isBiggest = $biggestDropToKey === $stage['key']; @endphp
                            <div class="text-center text-xs {{ $isBiggest ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-gray-400 dark:text-gray-500' }}">
                                &darr; {{ number_format($stage['drop_count']) }} @lang('messages.funnel_lost')
                                @if($stage['step_conv'] !== null)({{ round(max(0, 100 - $stage['step_conv']), 1) }}%)@endif
                                @if($isBiggest) &middot; @lang('messages.funnel_biggest_leak') @endif
                            </div>
                        @endif

                        {{-- Stage: label + count above, bar (track + fill) below --}}
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="font-medium text-gray-800 dark:text-gray-200">
                                    {{ $label }}
                                    @if($isTraffic)
                                        <span class="text-gray-400 dark:text-gray-500 cursor-help" title="{{ __('messages.funnel_tooltip_traffic') }}">&#9432;</span>
                                    @elseif($stage['key'] === 'account')
                                        <span class="text-gray-400 dark:text-gray-500 cursor-help" title="{{ __('messages.funnel_tooltip_cohort') }}">&#9432;</span>
                                    @elseif(in_array($stage['key'], ['reached_schedule', 'reached_event'], true))
                                        <span class="text-gray-400 dark:text-gray-500 cursor-help" title="{{ __('messages.funnel_tooltip_click_steps') }}">&#9432;</span>
                                    @endif
                                </span>
                                <span class="text-gray-900 dark:text-white font-semibold whitespace-nowrap">
                                    {{ $ariaCount }}
                                    @if($stage['step_conv'] !== null)
                                        <span class="text-gray-400 dark:text-gray-500 font-normal">({{ $stage['step_conv'] }}%)</span>
                                    @endif
                                </span>
                            </div>
                            <div class="h-8 w-full rounded-lg bg-gray-200 dark:bg-gray-700 overflow-hidden" role="img"
                                 aria-label="{{ $label }}: {{ $ariaCount }}{{ $stage['step_conv'] !== null ? ' (' . $stage['step_conv'] . '%)' : '' }}">
                                @if($count === null)
                                    <div class="h-full w-full rounded-lg border border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center">
                                        <span class="text-xs text-gray-400 dark:text-gray-500 px-2 text-center">{{ $trafficNote ?? __('messages.funnel_na') }}</span>
                                    </div>
                                @else
                                    <div class="h-full rounded-lg transition-all duration-200"
                                         style="width: {{ $barWidth }}%; {{ $isTraffic ? 'background: var(--brand-blue-light);' : 'background: linear-gradient(90deg, var(--brand-button-bg-light), var(--brand-button-bg));' }}"></div>
                                @endif
                            </div>

                            {{-- Verified attendee-intent signups (follow/ticket/...) excluded from the cohort --}}
                            @if($stage['key'] === 'account' && ! empty($funnel['excluded_intents']) && $funnel['excluded_intents']->isNotEmpty())
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                    @lang('messages.funnel_excluded_intents'):
                                    {{ $funnel['excluded_intents']->map(fn ($total, $intent) => number_format($total) . ' ' . __('messages.signup_intent_' . $intent))->implode(', ') }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Conversion over time --}}
            <div class="ap-card rounded-xl shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.funnel_over_time')</h3>
                @if(count($funnelTrend['labels']) >= 2)
                    <div class="h-64">
                        <canvas id="onboardingFunnelChart"></canvas>
                    </div>
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">@lang('messages.funnel_period_in_progress')</p>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.funnel_not_enough_history')</p>
                @endif
            </div>
        </div>

        {{-- User Count with Period Comparison --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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

            <x-stat-panel label="{{ __('messages.active_users_7_days') }}">
                {{ number_format($activeUsers7Days) }}
            </x-stat-panel>

            <x-stat-panel label="{{ __('messages.active_users_30_days') }}">
                {{ number_format($activeUsers30Days) }}
            </x-stat-panel>

            <x-stat-panel label="{{ __('messages.newsletter_subscribers') }}">
                {{ number_format($newsletterSubscribed) }}
                <x-slot:subtitle>{{ number_format($newsletterUnsubscribed) }} @lang('messages.unsubscribed')</x-slot:subtitle>
            </x-stat-panel>
        </div>

        {{-- User Signup Method Breakdown --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Signup Method Donut Chart --}}
            <div class="ap-card rounded-xl shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.signup_method_breakdown') (@lang('messages.all_time'))</h3>
                <div class="flex items-center gap-6">
                    <div class="w-48 h-48">
                        <canvas id="signupMethodChart"></canvas>
                    </div>
                    <div class="flex-1 space-y-3">
                        @php
                            $signupTotal = $emailUsers + $googleUsers + $hybridUsers;
                            $emailPercent = $signupTotal > 0 ? round(($emailUsers / $signupTotal) * 100, 1) : 0;
                            $googlePercent = $signupTotal > 0 ? round(($googleUsers / $signupTotal) * 100, 1) : 0;
                            $hybridPercent = $signupTotal > 0 ? round(($hybridUsers / $signupTotal) * 100, 1) : 0;
                        @endphp
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full bg-blue-500 me-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">@lang('messages.email')</span>
                            </div>
                            <div class="text-end">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($emailUsers) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ms-1">({{ $emailPercent }}%)</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full bg-red-500 me-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">@lang('messages.google')</span>
                            </div>
                            <div class="text-end">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($googleUsers) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ms-1">({{ $googlePercent }}%)</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full bg-amber-500 me-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">@lang('messages.hybrid')</span>
                            </div>
                            <div class="text-end">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($hybridUsers) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ms-1">({{ $hybridPercent }}%)</span>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('messages.hybrid') = @lang('messages.hybrid_description')
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Signup Method in Period --}}
            <div class="ap-card rounded-xl shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.signups_by_method') (@lang('messages.selected_period'))</h3>
                <div class="h-48">
                    <canvas id="signupMethodTrendChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($emailUsersInPeriod) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.email')</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($googleUsersInPeriod) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.google')</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($hybridUsersInPeriod) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.hybrid')</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- UTM Attribution Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- UTM Summary Card + Bar Chart --}}
            <div class="ap-card rounded-xl shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.utm_attribution') (@lang('messages.selected_period'))</h3>

                @if($usersWithUtmInPeriod + $usersWithoutUtmInPeriod > 0)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        {{ number_format($usersWithUtmInPeriod) }} @lang('messages.from_campaigns')
                        ({{ $usersWithUtmInPeriod + $usersWithoutUtmInPeriod > 0 ? round(($usersWithUtmInPeriod / ($usersWithUtmInPeriod + $usersWithoutUtmInPeriod)) * 100, 1) : 0 }}%
                        @lang('messages.of') {{ number_format($usersWithUtmInPeriod + $usersWithoutUtmInPeriod) }} @lang('messages.signups_total'))
                    </p>

                    @if($utmSourcesInPeriod->count() > 0)
                        <div class="h-48">
                            <canvas id="utmSourcesChart"></canvas>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_utm_data')</p>
                    @endif
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_utm_data')</p>
                @endif
            </div>

            {{-- Top Campaigns Table --}}
            <div class="ap-card rounded-xl shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.top_campaigns') (@lang('messages.all_time'))</h3>

                @if($topUtmCampaigns->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.source')</th>
                                    <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.medium')</th>
                                    <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.campaign')</th>
                                    <th class="text-end py-2 font-medium text-gray-500 dark:text-gray-400">@lang('messages.users')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topUtmCampaigns as $campaign)
                                    <tr class="border-b border-gray-100 dark:border-gray-700/50">
                                        <td class="py-2 pe-4 text-gray-900 dark:text-white">{{ $campaign->utm_source ?? '-' }}</td>
                                        <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ $campaign->utm_medium ?? '-' }}</td>
                                        <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ $campaign->utm_campaign }}</td>
                                        <td class="py-2 text-end font-medium text-gray-900 dark:text-white">{{ number_format($campaign->count) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_utm_data')</p>
                @endif
            </div>
        </div>

        {{-- Top UTM Sources & Top Referrers (All Time) --}}
        @if($topUtmSources->count() > 0 || $topReferrerDomains->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @if($topUtmSources->count() > 0)
                <div class="ap-card rounded-xl shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.top_sources') (@lang('messages.all_time'))</h3>
                    <div class="h-64">
                        <canvas id="utmTopSourcesChart"></canvas>
                    </div>
                </div>
            @endif

            @if($topReferrerDomains->count() > 0)
                <div class="ap-card rounded-xl shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.top_referrers') (@lang('messages.all_time'))</h3>
                    <div class="h-64">
                        <canvas id="topReferrersChart"></canvas>
                    </div>
                </div>
            @endif
        </div>
        @endif

        {{-- Onboarding progress (per-user work queue) --}}
        <div class="ap-card rounded-xl shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">@lang('messages.onboarding_progress_title')</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">@lang('messages.onboarding_progress_subtitle')</p>

            @if($onboardingProgress->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.name')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.funnel_signed_up')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.funnel_progress')</th>
                                <th class="text-start py-2 font-medium text-gray-500 dark:text-gray-400">@lang('messages.funnel_furthest')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($onboardingProgress as $u)
                                @php
                                    $steps = [
                                        'account' => true,
                                        'reached_schedule' => $u->schedule_form_viewed_at !== null || $u->schedules_count > 0,
                                        'saved_schedule' => $u->schedules_count > 0,
                                        'reached_event' => $u->event_form_viewed_at !== null || $u->events_count > 0,
                                        'saved_event' => $u->events_count > 0,
                                    ];
                                    $furthestKey = 'account';
                                    foreach ($steps as $stepKey => $reached) {
                                        if ($reached) { $furthestKey = $stepKey; }
                                    }
                                    $isStuck = $u->schedules_count > 0 && $u->events_count == 0;
                                @endphp
                                <tr class="border-b border-gray-100 dark:border-gray-700/50 {{ $isStuck ? 'bg-amber-50/60 dark:bg-amber-500/5' : '' }}">
                                    <td class="py-2 pe-4">
                                        <a href="mailto:{{ $u->email }}" class="text-[var(--brand-blue)] hover:underline" title="{{ $u->email }}">{{ $u->name ?: $u->email }}</a>
                                    </td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $u->created_at->diffForHumans() }}</td>
                                    <td class="py-2 pe-4">
                                        <div class="flex items-center gap-1" role="img" aria-label="{{ __('messages.funnel_stage_' . $furthestKey) }}">
                                            @foreach($steps as $stepKey => $reached)
                                                <span class="w-6 h-2 rounded-full {{ $reached ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-200 dark:bg-gray-700' }}"
                                                      title="{{ __('messages.funnel_stage_' . $stepKey) }}{{ $reached ? '' : ' (' . __('messages.funnel_not_reached') . ')' }}"></span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-2 whitespace-nowrap">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('messages.funnel_stage_' . $furthestKey) }}</span>
                                        @if($isStuck)
                                            <span class="ms-1 text-xs px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400">@lang('messages.onboarding_stuck')</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $onboardingProgress->links() }}
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_data')</p>
            @endif
        </div>

        {{-- Recent Signups --}}
        <div class="ap-card rounded-xl shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.recent_signups')</h3>

            @if($recentSignups->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.name')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.signup_intent')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.date')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.source')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.medium')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.campaign')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.content')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.term')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.referrer')</th>
                                <th class="text-start py-2 font-medium text-gray-500 dark:text-gray-400">@lang('messages.landing_page')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSignups as $signup)
                                <tr class="border-b border-gray-100 dark:border-gray-700/50">
                                    <td class="py-2 pe-4 text-gray-900 dark:text-white">{{ $signup->name }}</td>
                                    <td class="py-2 pe-4">
                                        @if($signup->signup_intent)
                                            <span class="inline-block px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-[#2d2d30] text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('messages.signup_intent_' . $signup->signup_intent) }}</span>
                                        @else
                                            <span class="text-gray-600 dark:text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $signup->created_at->format('M j, Y') }}</td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ $signup->utm_source ?? '-' }}</td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ $signup->utm_medium ?? '-' }}</td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ $signup->utm_campaign ?? '-' }}</td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ $signup->utm_content ?? '-' }}</td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ $signup->utm_term ?? '-' }}</td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400" title="{{ $signup->referrer_url }}">
                                        @if($signup->referrer_url)
                                            {{ parse_url($signup->referrer_url, PHP_URL_HOST) ?? '-' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-2 text-gray-600 dark:text-gray-400" title="{{ $signup->landing_page }}">{{ Str::limit($signup->landing_page ?? '-', 30) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $recentSignups->links() }}
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_data')</p>
            @endif
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

            const textColor = isDarkMode ? '#9CA3AF' : '#6B7280';
            const gridColor = isDarkMode ? '#2d2d30' : '#E5E7EB';
            const brandBlue = getComputedStyle(document.documentElement).getPropertyValue('--brand-blue').trim();

            // Signup Method Donut Chart
            const signupMethodCtx = document.getElementById('signupMethodChart').getContext('2d');
            new Chart(signupMethodCtx, {
                type: 'doughnut',
                data: {
                    labels: [@json(__('messages.email')), @json(__('messages.google')), @json(__('messages.hybrid'))],
                    datasets: [{
                        data: [{{ $emailUsers }}, {{ $googleUsers }}, {{ $hybridUsers }}],
                        backgroundColor: [brandBlue, '#EF4444', '#F59E0B'],
                        borderColor: isDarkMode ? '#252526' : '#FFFFFF',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Signup Method Trend Chart (Stacked Bar)
            const signupMethodTrendCtx = document.getElementById('signupMethodTrendChart').getContext('2d');
            new Chart(signupMethodTrendCtx, {
                type: 'bar',
                data: {
                    labels: @json($trendData['labels']),
                    datasets: [
                        {
                            label: @json(__('messages.email')),
                            data: @json($trendData['emailUsers']),
                            backgroundColor: brandBlue,
                            stack: 'signups'
                        },
                        {
                            label: @json(__('messages.google')),
                            data: @json($trendData['googleUsers']),
                            backgroundColor: '#EF4444',
                            stack: 'signups'
                        },
                        {
                            label: @json(__('messages.hybrid')),
                            data: @json($trendData['hybridUsers']),
                            backgroundColor: '#F59E0B',
                            stack: 'signups'
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
                                color: textColor,
                                boxWidth: 12,
                                padding: 8
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: textColor
                            }
                        },
                        y: {
                            stacked: true,
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

            // UTM Sources Bar Chart (selected period)
            @if($utmSourcesInPeriod->count() > 0)
                const utmSourcesCtx = document.getElementById('utmSourcesChart').getContext('2d');
                new Chart(utmSourcesCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($utmSourcesInPeriod->pluck('utm_source')->toArray()),
                        datasets: [{
                            label: @json(__('messages.users')),
                            data: @json($utmSourcesInPeriod->pluck('count')->toArray()),
                            backgroundColor: '#8B5CF6'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, precision: 0 }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: textColor }
                            }
                        }
                    }
                });
            @endif

            // UTM Top Sources Bar Chart (all time)
            @if($topUtmSources->count() > 0)
                const utmTopSourcesCtx = document.getElementById('utmTopSourcesChart').getContext('2d');
                new Chart(utmTopSourcesCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($topUtmSources->pluck('utm_source')->toArray()),
                        datasets: [{
                            label: @json(__('messages.users')),
                            data: @json($topUtmSources->pluck('count')->toArray()),
                            backgroundColor: '#8B5CF6'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, precision: 0 }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: textColor }
                            }
                        }
                    }
                });
            @endif

            // Top Referrer Domains Bar Chart (all time)
            @if($topReferrerDomains->count() > 0)
                const topReferrersCtx = document.getElementById('topReferrersChart').getContext('2d');
                new Chart(topReferrersCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($topReferrerDomains->pluck('domain')->toArray()),
                        datasets: [{
                            label: @json(__('messages.users')),
                            data: @json($topReferrerDomains->pluck('count')->toArray()),
                            backgroundColor: '#10B981'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, precision: 0 }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: textColor }
                            }
                        }
                    }
                });
            @endif

            // Onboarding funnel: conversion rates over time
            const onboardingCanvas = document.getElementById('onboardingFunnelChart');
            if (onboardingCanvas) {
                const funnelIsRtl = @json(is_rtl());
                const funnelLastIndex = @json($funnelTrend['last_index']);
                const dashLast = (ctx) => ctx.p1DataIndex === funnelLastIndex ? [6, 6] : undefined;
                const funnelDatasets = [];
                @if($funnelTrend['has_traffic'])
                    funnelDatasets.push({
                        label: @json(__('messages.funnel_visitor_to_signup')),
                        data: @json($funnelTrend['visitor_to_signup']),
                        borderColor: brandBlue,
                        backgroundColor: brandBlue,
                        spanGaps: true,
                        tension: 0.3,
                        segment: { borderDash: dashLast },
                    });
                @endif
                funnelDatasets.push({
                    label: @json(__('messages.funnel_signup_to_schedule')),
                    data: @json($funnelTrend['signup_to_schedule']),
                    borderColor: '#F59E0B',
                    backgroundColor: '#F59E0B',
                    spanGaps: true,
                    tension: 0.3,
                    segment: { borderDash: dashLast },
                });
                funnelDatasets.push({
                    label: @json(__('messages.funnel_signup_to_event')),
                    data: @json($funnelTrend['signup_to_event']),
                    borderColor: '#10B981',
                    backgroundColor: '#10B981',
                    spanGaps: true,
                    tension: 0.3,
                    segment: { borderDash: dashLast },
                });

                new Chart(onboardingCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: @json($funnelTrend['labels']),
                        datasets: funnelDatasets,
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: textColor, boxWidth: 12, padding: 8 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (c) => c.dataset.label + ': ' + (c.parsed.y === null ? 'n/a' : c.parsed.y + '%')
                                }
                            }
                        },
                        scales: {
                            x: {
                                reverse: funnelIsRtl,
                                grid: { color: gridColor },
                                ticks: { color: textColor }
                            },
                            y: {
                                beginAtZero: true,
                                suggestedMax: 100,
                                grid: { color: gridColor },
                                ticks: { color: textColor, callback: (v) => v + '%' }
                            }
                        }
                    }
                });
            }
        }

        // Initialize charts when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }
    </script>

</x-app-admin-layout>
