{{-- People active in the seven days up to each date (App\Services\ActiveDays). The first weeks
     were seeded from the security log and run low: they are drawn dashed and never joined to the
     exact count, or the step up between them reads as growth. --}}
<section id="dash-active" class="ap-card rounded-xl flex flex-col scroll-mt-20">
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">@lang('messages.active_users_7_days')</h2>
        <span class="text-xs text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_last_12_weeks')</span>
    </div>

    @if (! $active)
        @include('admin.dashboard._failed')
    @elseif (! $active['available'])
        {{-- The tables are not there yet: this code is running ahead of its migration. --}}
        <p class="px-5 pb-5 text-sm text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_active_unavailable')</p>
    @else
        <div class="px-4 sm:px-5 flex flex-wrap items-end justify-between gap-x-6 gap-y-3">
            <div class="min-w-0">
                <p class="flex items-baseline gap-2">
                    <span class="dashboard-stat-value text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($active['active_7d']) }}</span>
                    @if ($active['change'] !== null)
                        <span class="text-sm font-medium {{ $tone($active['change']) }}">{{ $pct($active['change']) }}</span>
                    @endif
                </p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <span class="whitespace-nowrap">@lang('messages.admin_dash_organizers') {{ __('messages.admin_dash_count_of_total', ['count' => number_format($active['organizers_7d']), 'total' => number_format($active['active_7d'])]) }}</span>
                    <span aria-hidden="true">&middot;</span>
                    <span class="whitespace-nowrap">
                        @lang('messages.admin_dash_active_30_days') {{ number_format($active['active_30d']) }}
                        {{-- Thirty days of counting, not seven, before this one stops reaching back
                             into the seeded days. --}}
                        @if (! $active['exact_30d'])
                            ({{ __('messages.admin_dash_estimate') }})
                        @endif
                    </span>
                </p>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-1.5"><span class="w-4 h-0.5 rounded-full bg-[var(--brand-blue)]" aria-hidden="true"></span>@lang('messages.admin_dash_exact')</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-4 h-0 border-t-2 border-dashed border-gray-400 dark:border-gray-500" aria-hidden="true"></span>@lang('messages.admin_dash_estimate')</span>
            </div>
        </div>

        <div class="px-4 sm:px-5 mt-4">
            <div class="relative h-44">
                <div class="absolute inset-0"><canvas id="dash-active-chart" role="img" aria-label="{{ __('messages.active_users_7_days') }}"></canvas></div>
            </div>
        </div>

        <div class="mt-auto">
            <div class="mt-4 px-4 sm:px-5 py-3 flex items-start gap-2 text-xs text-gray-500 dark:text-gray-400" style="border-top: 1px solid var(--ap-hairline)">
                <svg class="w-4 h-4 shrink-0 mt-px" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <p>
                    @lang('messages.admin_dash_active_note')
                    @if ($active['exact_from_label'] && collect($active['series'])->contains('exact', false))
                        {{ __('messages.admin_dash_active_estimate_note', ['date' => $active['exact_from_label']]) }}
                    @elseif (! $active['exact_from_label'])
                        @lang('messages.admin_dash_active_all_estimate_note')
                    @endif
                </p>
            </div>
        </div>
    @endif
</section>
