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

        {{-- Header. The full payload is pulled with `php artisan app:pull-growth` (docs/GROWTH_DATA.md), not downloaded here. --}}
        <div class="ap-card rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">@lang('messages.growth')</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">@lang('messages.growth_description')</p>
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
            {{-- Free schedules by peak monthly paid tickets. Paid selling is Pro/Enterprise now,
                 so this reads as a conversion list: who has sold before and is sitting on free. --}}
            <div class="ap-card rounded-xl shadow p-6 flex flex-col">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">@lang('messages.growth_free_pressure')</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">@lang('messages.growth_free_pressure_help')</p>

                @php
                    $bucketMax = max(1, max($pressure['peak_month_paid_tickets']));
                @endphp
                <div class="space-y-2 mt-auto">
                    @foreach ($pressure['peak_month_paid_tickets'] as $bucket => $count)
                        @php $sold = $bucket !== '0'; @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="font-medium {{ $sold ? 'text-amber-600 dark:text-amber-400' : 'text-gray-800 dark:text-gray-200' }}">
                                    {{ $bucket }}
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">{{ number_format($count) }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                <div class="h-full rounded-full {{ $sold ? 'bg-amber-500' : 'bg-[var(--brand-button-bg)]' }}"
                                     style="width: {{ $count > 0 ? max(2, round($count / $bucketMax * 100, 1)) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">
                    {{ number_format($pressure['ever_sold_paid']) }} /
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
                        <dd class="font-medium text-gray-900 dark:text-white">{{ plan_price($money['mrr']) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-600 dark:text-gray-400">ARR</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ plan_price($money['arr']) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-600 dark:text-gray-400">ARPU</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            {{ $money['arpu'] === null ? __('messages.funnel_na') : plan_price($money['arpu']) }}
                        </dd>
                    </div>
                    {{-- Left out of MRR, ARR and ARPU above: a trial has not paid anything yet. --}}
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-600 dark:text-gray-400">@lang('messages.trialing_subscriptions')</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ number_format($money['trialing_subscriptions']) }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- The two scheduled owner emails, so a bad first day after a deploy is visible here
                 rather than only in a query against schedule_nudges and owner_digests. --}}
            <div class="ap-card rounded-xl shadow p-6 flex flex-col">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">@lang('messages.growth_nudges')</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">@lang('messages.growth_nudges_help')</p>
                @if (empty($data['nudges']))
                    <div class="mt-auto bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2">
                        <svg class="w-5 h-5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <p class="text-sm text-amber-800 dark:text-amber-200">@lang('messages.growth_nudges_never_run')</p>
                    </div>
                @else
                    <table class="min-w-full text-sm mt-auto">
                        <thead>
                            <tr class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                <th class="py-2 pe-4 text-start font-medium">@lang('messages.growth_nudge_key')</th>
                                <th class="py-2 pe-4 text-end font-medium">@lang('messages.growth_nudge_total')</th>
                                <th class="py-2 pe-4 text-end font-medium">@lang('messages.growth_nudge_last_7_days')</th>
                                <th class="py-2 text-end font-medium">@lang('messages.growth_nudge_last_sent')</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($data['nudges'] as $key => $row)
                                <tr>
                                    <td class="py-2 pe-4 font-mono text-xs text-gray-800 dark:text-gray-200">{{ $key }}</td>
                                    <td class="py-2 pe-4 text-end text-gray-600 dark:text-gray-400">{{ number_format($row['total']) }}</td>
                                    <td class="py-2 pe-4 text-end text-gray-600 dark:text-gray-400">{{ number_format($row['last_7_days']) }}</td>
                                    <td class="py-2 text-end text-gray-600 dark:text-gray-400">{{ $row['last_sent_at'] ? \Carbon\Carbon::parse($row['last_sent_at'])->diffForHumans() : '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                {{-- The other scheduled owner email, so both can be watched in one place. --}}
                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700 text-sm">
                    <p class="font-medium text-gray-900 dark:text-white mb-2">@lang('messages.growth_digests')</p>
                    @forelse (array_slice($data['owner_digests'] ?? [], 0, 2, true) as $week => $row)
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs text-gray-600 dark:text-gray-400">{{ $week }}</span>
                            <span class="text-gray-900 dark:text-white">{{ trans_choice('messages.growth_digests_owners', $row['owners'], ['count' => number_format($row['owners'])]) }}</span>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400">@lang('messages.growth_digests_none')</p>
                    @endforelse
                </div>
            </div>

            {{-- Churn and the selling trial: the two ends of the plan, each new on 2026-09-28. --}}
            <div class="ap-card rounded-xl shadow p-6 flex flex-col">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.growth_churn_and_trials')</h3>
                @php
                    $churn = $data['churn'];
                    $trials = $money['ticket_trials'];
                @endphp
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-600 dark:text-gray-400">@lang('messages.growth_cancelled')</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ number_format($churn['cancelled']) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-600 dark:text-gray-400">@lang('messages.growth_resumed')</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ number_format($churn['resumed']) }}</dd>
                    </div>
                    @foreach (collect($churn['by_reason'])->take(4) as $reason => $count)
                        <div class="flex items-center justify-between ps-4">
                            <dt class="text-gray-500 dark:text-gray-400">{{ $reason === 'none' ? __('messages.growth_no_reason') : __('messages.cancel_reason_'.$reason) }}</dt>
                            <dd class="text-gray-700 dark:text-gray-300">{{ number_format($count) }}</dd>
                        </div>
                    @endforeach
                </dl>
                <dl class="space-y-3 text-sm mt-auto pt-3 border-t border-gray-200 dark:border-gray-700">
                    @foreach (['started', 'running', 'sold_during', 'converted', 'expired_unconverted'] as $key)
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-600 dark:text-gray-400">@lang('messages.growth_trials_'.$key)</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ number_format($trials[$key]) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>

        {{-- Acquisition: which landing pages produce signups that go on to SELL. The two ticket
             columns are the point of this table: activation stopping at "saved an event" ranked
             pages by an outcome that does not predict revenue, and the marketing site has ~156
             of them, so 12 rows could never rank it either. --}}
        <div class="ap-card rounded-xl shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.growth_acquisition')</h3>
            @php $landing = array_slice($data['acquisition']['by_landing_path'], 0, 25); @endphp
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
                                <th class="py-2 pe-4 text-end font-medium">@lang('messages.growth_saved_event')</th>
                                <th class="py-2 pe-4 text-end font-medium">@lang('messages.growth_saved_ticket')</th>
                                <th class="py-2 text-end font-medium">@lang('messages.growth_saved_paid_ticket')</th>
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
                                    <td class="py-2 pe-4 text-end text-gray-600 dark:text-gray-400">
                                        {{ $row['signups'] > 0 ? round($row['saved_event'] / $row['signups'] * 100, 1) : 0 }}%
                                    </td>
                                    <td class="py-2 pe-4 text-end text-gray-600 dark:text-gray-400">
                                        {{ $row['signups'] > 0 ? round($row['saved_ticket'] / $row['signups'] * 100, 1) : 0 }}%
                                    </td>
                                    <td class="py-2 text-end text-gray-600 dark:text-gray-400">
                                        {{ $row['signups'] > 0 ? round($row['saved_paid_ticket'] / $row['signups'] * 100, 1) : 0 }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- The homepage headline A/B test (App\Utils\HeroExperiment). It allocates traffic and
             picks its winner by itself; the only control is the reset, which starts it over. --}}
        @if (config('app.is_nexus'))
            <div id="hero-test" class="ap-card rounded-xl shadow p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">@lang('messages.hero_test')</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xl">@lang('messages.hero_test_help')</p>
                    </div>
                    @if ($heroTest !== null)
                        <form method="POST" action="{{ route('admin.growth.hero_test_reset') }}" class="sm:ms-auto"
                              data-confirm="{{ __('messages.hero_test_reset_confirm') }}">
                            @csrf
                            <input type="hidden" name="range" value="{{ $range }}">
                            <x-danger-button>@lang('messages.hero_test_reset')</x-danger-button>
                        </form>
                    @endif
                </div>

                @if ($heroTest === null)
                    <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.hero_test_unavailable')</p>
                @else
                    @php
                        // Challengers can share the control's headline, so the key is what tells them apart.
                        $heroHeadline = fn ($key) => (collect($heroTest['rows'])->firstWhere('key', $key)['headline'] ?? $key).' ('.$key.')';
                        $heroPct = fn ($value) => $value === null ? __('messages.funnel_na') : round($value * 100, 1).'%';
                    @endphp
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 {{ $heroTest['reset_at'] ? 'mb-1' : 'mb-4' }}">
                        @if ($heroTest['phase'] === 'winner')
                            {{ __('messages.hero_test_phase_winner', ['variant' => $heroHeadline($heroTest['winner']['key']), 'date' => $heroTest['winner']['date']]) }}
                        @elseif ($heroTest['phase'] === 'candidate')
                            {{ __('messages.hero_test_phase_candidate', ['variant' => $heroHeadline($heroTest['candidate']['key']), 'date' => $heroTest['lock_date']]) }}
                        @elseif ($heroTest['phase'] === 'clicks')
                            @lang('messages.hero_test_phase_clicks')
                        @else
                            @lang('messages.hero_test_phase_signups')
                        @endif
                    </p>
                    @if ($heroTest['reset_at'])
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('messages.hero_test_since', ['date' => $heroTest['reset_at']]) }}</p>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-start text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                    <th class="py-2 pe-4 text-start font-medium">@lang('messages.hero_test_headline')</th>
                                    <th class="py-2 pe-4 text-end font-medium">@lang('messages.hero_test_share')</th>
                                    <th class="py-2 pe-4 text-end font-medium">@lang('messages.hero_test_visitors')</th>
                                    <th class="py-2 pe-4 text-end font-medium">@lang('messages.click_rate')</th>
                                    <th class="py-2 pe-4 text-end font-medium">@lang('messages.growth_signups')</th>
                                    <th class="py-2 pe-4 text-end font-medium">@lang('messages.hero_test_signup_rate')</th>
                                    <th class="py-2 pe-4 text-end font-medium">@lang('messages.hero_test_chance_best_clicks')</th>
                                    <th class="py-2 text-end font-medium">@lang('messages.hero_test_chance_best')</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($heroTest['rows'] as $row)
                                    <tr>
                                        {{-- A floor, so on a phone the table scrolls sideways rather than
                                             squeezing the copy to a word per line. --}}
                                        <td class="py-2 pe-4 min-w-[16rem] text-gray-800 dark:text-gray-200">
                                            <div class="font-medium" dir="ltr">{{ $row['headline'] }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 max-w-md" dir="ltr">{{ $row['subtitle'] }}</div>
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                <span class="inline-block rounded-md bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 font-mono text-xs text-gray-600 dark:text-gray-300" dir="ltr">{{ $row['key'] }}</span>
                                                @if ($row['is_default'])
                                                    <span class="inline-block rounded-md bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 text-xs text-gray-600 dark:text-gray-300">@lang('messages.hero_test_default')</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-2 pe-4 text-end font-medium text-gray-900 dark:text-white">{{ $heroPct($row['share']) }}</td>
                                        <td class="py-2 pe-4 text-end text-gray-600 dark:text-gray-400">{{ number_format($row['visitors']) }}</td>
                                        <td class="py-2 pe-4 text-end text-gray-600 dark:text-gray-400">{{ $heroPct($row['click_rate']) }}</td>
                                        <td class="py-2 pe-4 text-end text-gray-600 dark:text-gray-400">{{ number_format($row['signups']) }}</td>
                                        <td class="py-2 pe-4 text-end text-gray-600 dark:text-gray-400">{{ $heroPct($row['signup_rate']) }}</td>
                                        <td class="py-2 pe-4 text-end text-gray-600 dark:text-gray-400">{{ $heroPct($row['p_best_clicks']) }}</td>
                                        <td class="py-2 text-end text-gray-600 dark:text-gray-400">{{ $heroPct($row['p_best']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-admin-layout>
