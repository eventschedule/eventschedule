{{-- Thirty days of new accounts, one bar a day: organizers and, on top, everyone else. --}}
<section id="dash-signups" class="ap-card rounded-xl flex flex-col scroll-mt-20">
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">@lang('messages.realtime_signups')</h2>
        <span class="text-xs text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_last_30_days')</span>
    </div>

    @if (! $signups)
        @include('admin.dashboard._failed')
    @else
        <div class="px-4 sm:px-5 flex flex-wrap items-end justify-between gap-x-6 gap-y-3">
            <div class="min-w-0">
                <p class="flex items-baseline gap-2">
                    <span class="dashboard-stat-value text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($signups['organizers']['last_30d']) }}</span>
                    <span class="text-sm text-gray-700 dark:text-gray-300">@lang('messages.admin_dash_organizers')</span>
                    @if ($signups['organizers']['change'] !== null)
                        <span class="text-sm font-medium {{ $tone($signups['organizers']['change']) }}">{{ $pct($signups['organizers']['change']) }}</span>
                    @endif
                </p>
                {{-- The total, then its parts in brackets: beside each other they read as four
                     separate groups. --}}
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @lang('messages.admin_dash_other_accounts') {{ number_format($signups['others']['total']) }}
                    @if ($signups['others']['by_intent'])
                        ({{ collect($signups['others']['by_intent'])->map(fn ($part) => (\Illuminate\Support\Facades\Lang::has('messages.signup_intent_'.$part['intent']) ? __('messages.signup_intent_'.$part['intent']) : $part['intent']).' '.number_format($part['count']))->implode(', ') }})
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-[var(--brand-blue)]" aria-hidden="true"></span>@lang('messages.admin_dash_organizers')</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-gray-400 dark:bg-gray-500" aria-hidden="true"></span>@lang('messages.admin_dash_other_accounts')</span>
            </div>
        </div>

        @if ($signups['organizers']['last_30d'] + $signups['others']['total'] > 0)
            <div class="px-4 sm:px-5 mt-4">
                <div class="relative h-44">
                    <div class="absolute inset-0"><canvas id="dash-signups-chart" role="img" aria-label="{{ __('messages.admin_dash_signups_chart') }}"></canvas></div>
                </div>
            </div>
        @else
            <p class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_no_signups')</p>
        @endif

        <div class="mt-auto">
            <div class="mt-4 px-4 sm:px-5 py-3 flex flex-wrap items-center justify-between gap-x-4 gap-y-2 text-sm" style="border-top: 1px solid var(--ap-hairline)">
                <p class="text-gray-500 dark:text-gray-400">
                    @lang('messages.admin_dash_signed_up_with')
                    <span class="text-gray-700 dark:text-gray-300">
                        <span class="whitespace-nowrap">@lang('messages.email') {{ number_format($signups['methods']['email']) }}</span>
                        <span aria-hidden="true">&middot;</span>
                        <span class="whitespace-nowrap">@lang('messages.google') {{ number_format($signups['methods']['google']) }}</span>
                        <span aria-hidden="true">&middot;</span>
                        <span class="whitespace-nowrap">@lang('messages.admin_dash_both') {{ number_format($signups['methods']['both']) }}</span>
                        {{-- Neither a password nor Google: Facebook, or an account made for someone. --}}
                        @if ($signups['methods']['other'] > 0)
                            <span aria-hidden="true">&middot;</span>
                            <span class="whitespace-nowrap">@lang('messages.other') {{ number_format($signups['methods']['other']) }}</span>
                        @endif
                    </span>
                </p>
                <x-link :href="route('admin.users')" class="font-medium whitespace-nowrap">@lang('messages.users')</x-link>
            </div>
        </div>
    @endif
</section>
