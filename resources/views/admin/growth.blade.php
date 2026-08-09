<x-app-admin-layout>
    <div class="space-y-4">
        @include('admin.partials._navigation', ['active' => 'growth'])
        @include('admin.partials._date-range-filter', ['range' => $range])

        @php
            $activation = $data['activation'];
            $pressure = $data['free_pressure'];
            $money = $data['monetization'];
            $accounts = max(1, $activation['accounts']);
            // The onboarding funnel itself lives on /admin/users; this page starts where
            // that one stops, so the two are not maintained twice.
            $pct = fn ($n) => round($n / $accounts * 100, 1);
        @endphp

        {{-- Header + download --}}
        <div class="ap-card rounded-xl shadow p-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">@lang('messages.growth')</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">@lang('messages.growth_description')</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 max-w-xl">@lang('messages.growth_download_help')</p>
            </div>
            <div class="sm:ms-auto">
                <x-secondary-link :href="route('admin.growth.export', ['range' => $range])">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    @lang('messages.growth_download')
                </x-secondary-link>
            </div>
        </div>

        {{-- Activation: how far the whole verified base actually gets --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-panel :label="__('messages.growth_signups')">
                {{ number_format($activation['accounts']) }}
            </x-stat-panel>
            <x-stat-panel :label="__('messages.funnel_stage_reached_schedule')">
                {{ $pct($activation['reached_schedule_form']) }}%
                <x-slot:subtitle>{{ number_format($activation['reached_schedule_form']) }}</x-slot:subtitle>
            </x-stat-panel>
            <x-stat-panel :label="__('messages.growth_saved_schedule')" color="blue">
                {{ $pct($activation['saved_schedule']) }}%
                <x-slot:subtitle>{{ number_format($activation['saved_schedule']) }}</x-slot:subtitle>
            </x-stat-panel>
            <x-stat-panel :label="__('messages.growth_saved_event')" color="emerald">
                {{ $pct($activation['saved_event']) }}%
                <x-slot:subtitle>{{ number_format($activation['saved_event']) }}</x-slot:subtitle>
            </x-stat-panel>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Free-plan pressure: does the ticket allowance ever actually bind? --}}
            <div class="ap-card rounded-xl shadow p-6 flex flex-col">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">@lang('messages.growth_free_pressure')</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">@lang('messages.growth_free_pressure_help')</p>

                @php
                    $bucketMax = max(1, max($pressure['peak_month_paid_tickets']));
                @endphp
                <div class="space-y-2 mt-auto">
                    @foreach ($pressure['peak_month_paid_tickets'] as $bucket => $count)
                        @php $atCap = $bucket === 'at_or_over_cap'; @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="font-medium {{ $atCap ? 'text-amber-600 dark:text-amber-400' : 'text-gray-800 dark:text-gray-200' }}">
                                    {{ $atCap ? $pressure['ticket_cap'] . '+' : $bucket }}
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">{{ number_format($count) }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                <div class="h-full rounded-full {{ $atCap ? 'bg-amber-500' : 'bg-[var(--brand-button-bg)]' }}"
                                     style="width: {{ $count > 0 ? max(2, round($count / $bucketMax * 100, 1)) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">
                    {{ number_format($pressure['ever_hit_ticket_cap']) }} /
                    {{ number_format($pressure['free_schedules']) }}
                    &middot; @lang('messages.growth_free_pressure')
                </p>
            </div>

            {{-- Monetization --}}
            <div class="ap-card rounded-xl shadow p-6 flex flex-col">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.revenue')</h3>
                <dl class="space-y-3 text-sm mt-auto">
                    @foreach ([
                        'free' => __('messages.free'),
                        'pro' => __('messages.pro'),
                        'enterprise' => __('messages.enterprise'),
                    ] as $tier => $label)
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-600 dark:text-gray-400">{{ $label }}</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ number_format($money['plan_counts'][$tier] ?? 0) }}</dd>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-3">
                        <dt class="text-gray-600 dark:text-gray-400">MRR</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">${{ number_format($money['mrr_usd'], 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-600 dark:text-gray-400">ARPU</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            {{ $money['arpu_usd'] === null ? __('messages.funnel_na') : '$' . number_format($money['arpu_usd'], 2) }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Acquisition: which landing pages produce signups that activate --}}
        <div class="ap-card rounded-xl shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.growth_acquisition')</h3>
            @php $landing = array_slice($data['acquisition']['by_landing_path'], 0, 12); @endphp
            @if (empty($landing))
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.growth_no_data')</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-start text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                <th class="py-2 pe-4 text-start font-medium">@lang('messages.growth_acquisition')</th>
                                <th class="py-2 pe-4 text-end font-medium">@lang('messages.growth_signups')</th>
                                <th class="py-2 pe-4 text-end font-medium">@lang('messages.growth_saved_schedule')</th>
                                <th class="py-2 text-end font-medium">@lang('messages.growth_saved_event')</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($landing as $row)
                                <tr>
                                    <td class="py-2 pe-4 text-gray-800 dark:text-gray-200 font-mono text-xs">{{ $row['key'] }}</td>
                                    <td class="py-2 pe-4 text-end text-gray-600 dark:text-gray-400">{{ number_format($row['signups']) }}</td>
                                    <td class="py-2 pe-4 text-end text-gray-600 dark:text-gray-400">
                                        {{ $row['signups'] > 0 ? round($row['saved_schedule'] / $row['signups'] * 100, 1) : 0 }}%
                                    </td>
                                    <td class="py-2 text-end text-gray-600 dark:text-gray-400">
                                        {{ $row['signups'] > 0 ? round($row['saved_event'] / $row['signups'] * 100, 1) : 0 }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-admin-layout>
